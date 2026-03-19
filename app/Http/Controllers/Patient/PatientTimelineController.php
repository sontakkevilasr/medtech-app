<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\PatientTimeline;
use App\Models\FamilyMember;
use Illuminate\Http\Request;

class TimelineController extends Controller
{
    // ── All timelines for the patient (self + family) ─────────────────────────
    public function index()
    {
        $patient = auth()->user()->load('familyMembers');

        // Self timelines
        $selfTimelines = PatientTimeline::where('patient_user_id', $patient->id)
            ->whereNull('family_member_id')
            ->with(['template.milestones', 'assignedByDoctor.profile'])
            ->where('is_active', true)
            ->latest('start_date')
            ->get()
            ->map(fn($pt) => $this->enrichTimeline($pt));

        // Family member timelines
        $memberTimelines = PatientTimeline::where('patient_user_id', $patient->id)
            ->whereNotNull('family_member_id')
            ->with(['template.milestones', 'assignedByDoctor.profile', 'familyMember'])
            ->where('is_active', true)
            ->latest('start_date')
            ->get()
            ->map(fn($pt) => $this->enrichTimeline($pt));

        return view('patient.timelines.index', compact(
            'patient', 'selfTimelines', 'memberTimelines'
        ));
    }

    // ── Single timeline detail with full milestone list ───────────────────────
    public function show(PatientTimeline $patientTimeline)
    {
        // Ownership check
        if ($patientTimeline->patient_user_id !== auth()->id()) abort(403);

        $patientTimeline->load([
            'template.milestones' => fn($q) => $q->orderBy('sort_order'),
            'assignedByDoctor.profile',
            'assignedByDoctor.doctorProfile',
            'familyMember',
        ]);

        $milestones = $patientTimeline->getMilestonesWithDates();

        // Group milestones: past / today+upcoming
        $past     = $milestones->filter(fn($m) => $m->is_past && !$m->is_today);
        $upcoming = $milestones->filter(fn($m) => !$m->is_past || $m->is_today)->values();
        $next     = $upcoming->first();

        // Progress percentage
        $total    = $milestones->count();
        $done     = $past->count();
        $progress = $total > 0 ? round($done / $total * 100) : 0;

        return view('patient.timelines.show', compact(
            'patientTimeline', 'milestones', 'past', 'upcoming', 'next', 'progress'
        ));
    }

    // ── Family member timelines ───────────────────────────────────────────────
    public function memberTimelines(int $member)
    {
        $fm = auth()->user()->familyMembers()->findOrFail($member);
        return redirect()->route('patient.timelines.index');
    }

    public function memberShow(int $member, PatientTimeline $patientTimeline)
    {
        auth()->user()->familyMembers()->findOrFail($member); // ownership
        return $this->show($patientTimeline);
    }

    // ── Private helpers ───────────────────────────────────────────────────────
    private function enrichTimeline(PatientTimeline $pt): PatientTimeline
    {
        if (!$pt->template) return $pt;

        $milestones = $pt->getMilestonesWithDates();
        $total      = $milestones->count();
        $done       = $milestones->filter(fn($m) => $m->is_past && !$m->is_today)->count();

        $pt->setAttribute('progress_pct',   $total > 0 ? round($done / $total * 100) : 0);
        $pt->setAttribute('milestones_done', $done);
        $pt->setAttribute('milestones_total', $total);
        $pt->setAttribute('next_milestone',
            $milestones->filter(fn($m) => !$m->is_past || $m->is_today)->first()
        );

        return $pt;
    }
}
