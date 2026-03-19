<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\FamilyMember;
use App\Services\PdfService;
use Illuminate\Http\Request;

class MedicalHistoryController extends Controller
{
    public function __construct(private PdfService $pdfService) {}

    // ── Own history (self) ────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $patient = auth()->user()->load(['profile', 'familyMembers']);

        // Filters
        $type   = $request->get('type');    // 'record' | 'prescription'
        $filter = $request->get('filter');  // 'all' | 'thisYear' | 'last6m' | 'last30d'

        [$records, $prescriptions] = $this->fetchHistory(
            patientId:  $patient->id,
            memberId:   null,
            type:       $type,
            filter:     $filter,
        );

        // Merge into a unified chronological timeline
        $timeline = $this->buildTimeline($records, $prescriptions);

        // Stats
        $stats = $this->historyStats($patient->id, null);

        return view('patient.history.index', compact(
            'patient', 'timeline', 'stats', 'type', 'filter'
        ));
    }

    // ── Single medical record ─────────────────────────────────────────────────

    public function show(MedicalRecord $record)
    {
        if ($record->patient_user_id !== auth()->id()) abort(403);

        $record->load([
            'doctor.profile',
            'doctor.doctorProfile',
            'familyMember',
            'prescription.medicines',
        ]);

        return view('patient.history.record', compact('record'));
    }

    // ── Family member history ─────────────────────────────────────────────────

    public function memberHistory(Request $request, int $member)
    {
        $patient  = auth()->user()->load('familyMembers');
        $fm       = $patient->familyMembers()->findOrFail($member);

        $type   = $request->get('type');
        $filter = $request->get('filter');

        [$records, $prescriptions] = $this->fetchHistory(
            patientId: $patient->id,
            memberId:  $fm->id,
            type:      $type,
            filter:    $filter,
        );

        $timeline = $this->buildTimeline($records, $prescriptions);
        $stats    = $this->historyStats($patient->id, $fm->id);

        return view('patient.history.index', compact(
            'patient', 'fm', 'timeline', 'stats', 'type', 'filter'
        ));
    }

    // ── Single family member record ───────────────────────────────────────────

    public function memberRecord(int $member, MedicalRecord $record)
    {
        // Ensure the family member belongs to this patient
        auth()->user()->familyMembers()->findOrFail($member);

        if ($record->patient_user_id !== auth()->id()) abort(403);

        $record->load([
            'doctor.profile',
            'doctor.doctorProfile',
            'familyMember',
            'prescription.medicines',
        ]);

        return view('patient.history.record', compact('record'));
    }

    // ── Download prescription PDF ─────────────────────────────────────────────

    public function downloadPdf(Prescription $prescription)
    {
        // Patient can only download their own prescriptions
        if ($prescription->patient_user_id !== auth()->id()) abort(403);

        return $this->pdfService->downloadPrescription($prescription);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Fetch medical records and prescriptions for the given patient/member,
     * applying optional type and date filters.
     */
    private function fetchHistory(
        int    $patientId,
        ?int   $memberId,
        ?string $type,
        ?string $filter,
    ): array {
        $dateClause = $this->dateFilter($filter);

        // Medical records
        $records = [];
        if (!$type || $type === 'record') {
            $query = MedicalRecord::where('patient_user_id', $patientId)
                ->when($memberId,
                    fn($q) => $q->where('family_member_id', $memberId),
                    fn($q) => $q->whereNull('family_member_id')
                )
                ->with(['doctor.profile', 'doctor.doctorProfile', 'prescription'])
                ->orderByDesc('visit_date');

            if ($dateClause) {
                $query->where('visit_date', '>=', $dateClause);
            }

            $records = $query->get();
        }

        // Prescriptions NOT linked to a medical record (standalone)
        $prescriptions = [];
        if (!$type || $type === 'prescription') {
            $query = Prescription::where('patient_user_id', $patientId)
                ->whereNull('medical_record_id')          // avoid duplicates
                ->when($memberId,
                    fn($q) => $q->where('family_member_id', $memberId),
                    fn($q) => $q->whereNull('family_member_id')
                )
                ->with(['doctor.profile', 'doctor.doctorProfile', 'medicines'])
                ->orderByDesc('prescribed_date');

            if ($dateClause) {
                $query->where('prescribed_date', '>=', $dateClause);
            }

            $prescriptions = $query->get();
        }

        return [$records, $prescriptions];
    }

    /**
     * Merge records and prescriptions into a single date-sorted timeline array.
     * Each item has: type, date, object, and a display label.
     */
    private function buildTimeline($records, $prescriptions): \Illuminate\Support\Collection
    {
        $items = collect();

        foreach ($records as $rec) {
            $items->push([
                'type'    => 'record',
                'date'    => $rec->visit_date,
                'object'  => $rec,
                'label'   => $rec->diagnosis ?? $rec->chief_complaint ?? 'Medical Visit',
                'doctor'  => $rec->doctor?->profile?->full_name,
                'spec'    => $rec->doctor?->doctorProfile?->specialization,
            ]);
        }

        foreach ($prescriptions as $rx) {
            $items->push([
                'type'    => 'prescription',
                'date'    => $rx->prescribed_date,
                'object'  => $rx,
                'label'   => $rx->diagnosis_summary ?? 'Prescription',
                'doctor'  => $rx->doctor?->profile?->full_name,
                'spec'    => $rx->doctor?->doctorProfile?->specialization,
            ]);
        }

        return $items->sortByDesc('date')->values();
    }

    /**
     * Summary counts for the stats strip.
     */
    private function historyStats(int $patientId, ?int $memberId): array
    {
        $baseRecord = MedicalRecord::where('patient_user_id', $patientId)
            ->when($memberId,
                fn($q) => $q->where('family_member_id', $memberId),
                fn($q) => $q->whereNull('family_member_id')
            );

        $baseRx = Prescription::where('patient_user_id', $patientId)
            ->when($memberId,
                fn($q) => $q->where('family_member_id', $memberId),
                fn($q) => $q->whereNull('family_member_id')
            );

        return [
            'total_visits'       => (clone $baseRecord)->count(),
            'visits_this_year'   => (clone $baseRecord)->whereYear('visit_date', now()->year)->count(),
            'total_prescriptions'=> (clone $baseRx)->count(),
            'doctors_seen'       => (clone $baseRecord)->distinct('doctor_user_id')->count('doctor_user_id'),
            'last_visit'         => (clone $baseRecord)->max('visit_date'),
        ];
    }

    /**
     * Convert the filter slug to a Carbon date for a WHERE clause.
     */
    private function dateFilter(?string $filter): ?\Carbon\Carbon
    {
        return match ($filter) {
            'last30d'  => now()->subDays(30),
            'last6m'   => now()->subMonths(6),
            'thisYear' => now()->startOfYear(),
            default    => null,
        };
    }
}