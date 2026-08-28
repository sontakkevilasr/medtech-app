<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\HealthLog;
use App\Models\MedicationReminder;
use App\Models\PatientTimeline;
use App\Enums\HealthLogType;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $patient = auth()->user();
        $profile = $patient->profile;

        // ── Upcoming appointments ────────────────────────────────────────────
        $upcomingApts = Appointment::where('patient_user_id', $patient->id)
            ->where('slot_datetime', '>', now())
            ->whereNotIn('status', ['cancelled'])
            ->with(['doctor.profile', 'doctor.doctorProfile'])
            ->orderBy('slot_datetime')
            ->limit(5)
            ->get();

        // ── Next appointment (soonest) ───────────────────────────────────────
        $nextApt = $upcomingApts->first();

        // ── Recent prescriptions ─────────────────────────────────────────────
        $recentRx = Prescription::where('patient_user_id', $patient->id)
            ->with(['doctor.profile', 'medicines'])
            ->latest('prescribed_date')
            ->limit(4)
            ->get();

        // ── Family members ───────────────────────────────────────────────────
        $familyMembers = $patient->familyMembers()
            ->where('is_delinked', false)
            ->orderBy('relation')
            ->get();

        // ── Active medication reminders ──────────────────────────────────────
        $activeMeds = MedicationReminder::where('patient_user_id', $patient->id)
            ->where('is_active', true)
            ->orderBy('medicine_name')
            ->limit(6)
            ->get();

        // ── Latest health logs (last 7 days) ─────────────────────────────────
        $recentLogs = HealthLog::where('patient_user_id', $patient->id)
            ->where('logged_at', '>=', now()->subDays(7))
            ->orderByDesc('logged_at')
            ->limit(20)
            ->get();

        // ── Latest vitals per type ───────────────────────────────────────────
        $latestVitals = [];
        foreach (HealthLogType::cases() as $type) {
            $log = HealthLog::where('patient_user_id', $patient->id)
                ->where('log_type', $type->value)
                ->latest('logged_at')
                ->first();
            if ($log) {
                $latestVitals[$type->value] = $log;
            }
        }

        // ── BP trend last 14 days (for sparkline) ────────────────────────────
        $bpTrend = HealthLog::where('patient_user_id', $patient->id)
            ->where('log_type', 'blood_pressure')
            ->where('logged_at', '>=', now()->subDays(14))
            ->orderBy('logged_at')
            ->get()
            ->map(fn($l) => [
                'date' => $l->logged_at->format('d M'),
                'sys'  => $l->value_1,
                'dia'  => $l->value_2,
            ]);

        // ── Active timelines (pregnancy, IVF, etc.) ──────────────────────────
        $activeTimelines = PatientTimeline::where('patient_user_id', $patient->id)
            ->where('is_active', true)
            ->with(['template', 'familyMember'])
            ->get()
            ->map(function ($pt) {
                $milestones = $pt->getMilestonesWithDates();
                $next = $milestones->firstWhere('is_past', false);
                $prev = $milestones->filter(fn($m) => $m->is_past)->last();
                return [
                    'timeline'          => $pt,
                    'next_milestone'    => $next,
                    'prev_milestone'    => $prev,
                    'total_milestones'  => $milestones->count(),
                    'done_milestones'   => $milestones->filter(fn($m) => $m->is_past)->count(),
                ];
            });

        // ── Doctors who have access ──────────────────────────────────────────
        $activeDoctors = \App\Models\DoctorAccessRequest::where('patient_user_id', $patient->id)
            ->active()
            ->with(['doctor.profile', 'doctor.doctorProfile'])
            ->latest('approved_at')
            ->get();

        // ── Quick stats ──────────────────────────────────────────────────────
        $totalVisits       = MedicalRecord::where('patient_user_id', $patient->id)->count();
        $totalPrescriptions= Prescription::where('patient_user_id', $patient->id)->count();
        $pendingAccessReqs = \App\Models\DoctorAccessRequest::where('patient_user_id', $patient->id)
            ->where('status', 'pending')
            ->where('otp_expires_at', '>', now())
            ->with(['doctor.profile', 'doctor.doctorProfile'])
            ->get();

        return view('patient.dashboard', compact(
            'patient', 'profile',
            'upcomingApts', 'nextApt',
            'recentRx', 'familyMembers',
            'activeMeds', 'recentLogs',
            'latestVitals', 'bpTrend',
            'activeTimelines', 'activeDoctors',
            'totalVisits', 'totalPrescriptions',
            'pendingAccessReqs'
        ));
    }
}
