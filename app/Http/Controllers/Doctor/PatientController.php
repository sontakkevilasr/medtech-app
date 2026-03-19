<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\FamilyMember;
use App\Models\DoctorAccessRequest;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\HealthLog;
use App\Models\PatientTimeline;
use App\Services\AccessControlService;
use App\Services\OtpService;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function __construct(
        private AccessControlService $accessControl,
        private OtpService           $otpService,
    ) {}

    // ─── Patient List ────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $doctor = auth()->user();

        // All unique patients who have ever had an approved access grant
        // OR have medical records / appointments with this doctor
        $patientIds = collect()
            ->merge(
                DoctorAccessRequest::where('doctor_user_id', $doctor->id)
                    ->whereIn('status', ['approved', 'expired'])
                    ->pluck('patient_user_id')
            )
            ->merge(
                \App\Models\MedicalRecord::where('doctor_user_id', $doctor->id)
                    ->pluck('patient_user_id')
            )
            ->merge(
                \App\Models\Appointment::where('doctor_user_id', $doctor->id)
                    ->pluck('patient_user_id')
            )
            ->unique()
            ->values();

        $query = User::whereIn('id', $patientIds)
            ->where('role', 'patient')
            ->with(['profile', 'familyMembers' => fn($q) => $q->where('is_delinked', false)]);

        // Search
        if ($search = $request->get('q')) {
            $query->whereHas('profile', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%");
            })->orWhere('mobile_number', 'like', "%{$search}%");
        }

        // Filter
        if ($filter = $request->get('filter')) {
            match ($filter) {
                'active'  => $query->whereHas('accessRequests', fn($q) =>
                    $q->where('doctor_user_id', $doctor->id)->active()
                ),
                'recent'  => $query->whereHas('medicalRecords', fn($q) =>
                    $q->where('doctor_user_id', $doctor->id)->where('visit_date', '>=', now()->subDays(30))
                ),
                default   => null,
            };
        }

        $patients = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Attach per-patient access status + last visit for the list
        $patients->each(function (User $p) use ($doctor) {
            $p->active_access = DoctorAccessRequest::where('doctor_user_id', $doctor->id)
                ->where('patient_user_id', $p->id)
                ->active()
                ->exists();

            $p->last_visit = MedicalRecord::where('doctor_user_id', $doctor->id)
                ->where('patient_user_id', $p->id)
                ->latest('visit_date')
                ->value('visit_date');

            $p->total_visits = MedicalRecord::where('doctor_user_id', $doctor->id)
                ->where('patient_user_id', $p->id)
                ->count();
        });

        return view('doctor.patients.index', compact('patients', 'search', 'filter'));
    }

    // ─── AJAX Patient Search (for access request modal) ──────────────────────

    public function search(Request $request)
    {
        $q      = $request->get('q', '');
        $type   = $request->get('type', 'mobile'); // mobile | sub_id | aadhaar

        if (strlen($q) < 3) {
            return response()->json(['found' => false]);
        }

        $result = $this->accessControl->findPatient($q, $type);

        if (! $result['found']) {
            return response()->json(['found' => false, 'message' => 'No patient found.']);
        }

        $patient = $result['user'];
        $member  = $result['member'];

        return response()->json([
            'found'   => true,
            'patient' => [
                'id'       => $patient->id,
                'name'     => $patient->profile?->full_name ?? 'Unknown',
                'mobile'   => $patient->full_mobile,
                'age'      => $patient->profile?->age,
                'gender'   => $patient->profile?->gender,
                'city'     => $patient->profile?->city,
            ],
            'family_member' => $member ? [
                'id'       => $member->id,
                'name'     => $member->full_name,
                'relation' => $member->relation,
                'sub_id'   => $member->sub_id,
            ] : null,
            'identifier'      => $q,
            'identifier_type' => $type,
        ]);
    }

    // ─── Raise Access Request (from patient list or modal) ───────────────────

    public function requestAccess(Request $request)
    {
        $request->validate([
            'identifier'      => ['required', 'string', 'min:3'],
            'identifier_type' => ['required', 'in:mobile,sub_id,aadhaar'],
        ]);

        $doctor = auth()->user();
        $result = $this->accessControl->raiseRequest(
            $doctor,
            $request->identifier,
            $request->identifier_type
        );

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        if ($result['status'] === 'error') {
            return back()->withErrors(['identifier' => $result['message']]);
        }

        $msg = match ($result['status']) {
            'approved'       => 'Access granted immediately. You can now view this patient\'s records.',
            'already_active' => 'You already have active access to this patient.',
            'pending'        => 'OTP sent to patient\'s WhatsApp. Ask them to share the code.',
            default          => $result['message'],
        };

        return back()->with('success', $msg)
            ->with('access_result', $result);
    }

    // ─── Verify OTP (AJAX) ───────────────────────────────────────────────────

    public function verifyAccessOtp(Request $request, DoctorAccessRequest $accessRequest)
    {
        $request->validate(['otp' => ['required', 'digits:6']]);

        if ($accessRequest->doctor_user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $result = $this->accessControl->verifyAccessOtp($accessRequest, $request->otp);

        return response()->json($result);
    }

    // ─── Full Patient History ────────────────────────────────────────────────

    public function history(Request $request, int $patientId)
    {
        $doctor  = auth()->user();
        $patient = User::where('id', $patientId)->where('role', 'patient')
            ->with(['profile', 'familyMembers' => fn($q) => $q->where('is_delinked', false)])
            ->firstOrFail();

        // Access check: doctor must have active access OR patient has full-access perm
        $hasAccess  = $this->accessControl->doctorHasAccess($doctor, $patientId);
        $pendingReq = DoctorAccessRequest::where('doctor_user_id', $doctor->id)
            ->where('patient_user_id', $patientId)
            ->where('status', 'pending')
            ->where('otp_expires_at', '>', now())
            ->first();

        // Active access grant details
        $accessGrant = DoctorAccessRequest::where('doctor_user_id', $doctor->id)
            ->where('patient_user_id', $patientId)
            ->active()
            ->first();

        // Which family member are we viewing (default: primary patient)
        $familyMemberId = $request->get('member'); // null = primary
        $viewingMember  = $familyMemberId
            ? $patient->familyMembers->find($familyMemberId)
            : null;

        // ── Medical Records ───────────────────────────────────────────────────
        $records = $hasAccess
            ? MedicalRecord::where('patient_user_id', $patientId)
                ->when($familyMemberId, fn($q) => $q->where('family_member_id', $familyMemberId))
                ->when(!$familyMemberId, fn($q) => $q->whereNull('family_member_id'))
                ->with(['doctor.profile', 'doctor.doctorProfile', 'prescription.medicines', 'prescriptions'])
                ->latest('visit_date')
                ->paginate(10, ['*'], 'rpage')
            : collect();
        // dd($records);
        // ── Prescriptions ─────────────────────────────────────────────────────
        $prescriptions = $hasAccess
            ? Prescription::where('patient_user_id', $patientId)
                ->when($familyMemberId, fn($q) => $q->where('family_member_id', $familyMemberId))
                ->when(!$familyMemberId, fn($q) => $q->whereNull('family_member_id'))
                ->with(['doctor.profile', 'medicines'])
                ->latest('prescribed_date')
                ->paginate(8, ['*'], 'ppage')
            : collect();
        // ── Vitals (last 30 days, for chart) ──────────────────────────────────
        $vitalsData = [];
        if ($hasAccess) {
            foreach (['blood_pressure', 'blood_sugar', 'weight', 'pulse'] as $type) {
                $vitalsData[$type] = HealthLog::where('patient_user_id', $patientId)
                    ->where('log_type', $type)
                    ->where('logged_at', '>=', now()->subDays(30))
                    ->orderBy('logged_at')
                    ->get()
                    ->map(fn($l) => [
                        'date'  => $l->logged_at->format('d M'),
                        'val1'  => $l->value_1,
                        'val2'  => $l->value_2,
                        'label' => $l->logged_at->format('d M Y H:i'),
                    ]);
            }
        }

        // ── Active timelines ──────────────────────────────────────────────────
        $timelines = $hasAccess
            ? PatientTimeline::where('patient_user_id', $patientId)
                ->where('is_active', true)
                ->with('template')
                ->get()
                ->map(fn($pt) => [
                    'timeline'   => $pt,
                    'milestones' => $pt->getMilestonesWithDates(),
                ])
            : collect();

        // ── Summary stats ─────────────────────────────────────────────────────
        $stats = $hasAccess ? [
            'total_visits'   => MedicalRecord::where('patient_user_id', $patientId)->count(),
            'total_rx'       => Prescription::where('patient_user_id', $patientId)->count(),
            'last_visit'     => MedicalRecord::where('patient_user_id', $patientId)->max('visit_date'),
            'first_visit'    => MedicalRecord::where('patient_user_id', $patientId)->min('visit_date'),
            'my_visits'      => MedicalRecord::where('patient_user_id', $patientId)->where('doctor_user_id', $doctor->id)->count(),
        ] : [];

        return view('doctor.patients.history', compact(
            'patient', 'doctor',
            'hasAccess', 'pendingReq', 'accessGrant',
            'viewingMember', 'familyMemberId',
            'records', 'prescriptions',
            'vitalsData', 'timelines', 'stats'
        ));
    }
}
