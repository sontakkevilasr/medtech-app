<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\DoctorAccessRequest;
use App\Models\PatientAccessPermission;
use App\Models\User;
use App\Services\AccessControlService;
use App\Services\OtpService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AccessPermissionController extends Controller
{
    public function __construct(
        private AccessControlService $accessControl,
        private OtpService           $otpService,
    ) {}

    // ── Main hub ─────────────────────────────────────────────────────────────
    // Shows: pending requests, active grants, global settings, family overrides

    public function index()
    {
        $patient = auth()->user();
        $patient->load(['familyMembers', 'profile']);

        // Pending requests (OTP not yet expired)
        $pending = DoctorAccessRequest::with(['doctor.profile', 'doctor.doctorProfile'])
            ->where('patient_user_id', $patient->id)
            ->where('status', 'pending')
            ->where('otp_expires_at', '>', now())
            ->latest()
            ->get();

        // Active grants (approved + not expired)
        $active = DoctorAccessRequest::with(['doctor.profile', 'doctor.doctorProfile', 'familyMember'])
            ->where('patient_user_id', $patient->id)
            ->where('status', 'approved')
            ->where('access_expires_at', '>', now())
            ->latest('approved_at')
            ->get();

        // Global + per-member access type settings
        $globalPermission = PatientAccessPermission::where('patient_user_id', $patient->id)
            ->whereNull('family_member_id')
            ->first();

        $memberPermissions = PatientAccessPermission::where('patient_user_id', $patient->id)
            ->whereNotNull('family_member_id')
            ->with('familyMember')
            ->get()
            ->keyBy('family_member_id');

        // Recently expired / denied (last 30 days, for context)
        $recentClosed = DoctorAccessRequest::with(['doctor.profile', 'doctor.doctorProfile', 'familyMember'])
            ->where('patient_user_id', $patient->id)
            ->whereIn('status', ['denied', 'expired'])
            ->where('updated_at', '>=', now()->subDays(30))
            ->latest()
            ->limit(5)
            ->get();

        return view('patient.access.index', compact(
            'patient', 'pending', 'active',
            'globalPermission', 'memberPermissions', 'recentClosed'
        ));
    }

    // ── Pending requests list ─────────────────────────────────────────────────

    public function pendingRequests()
    {
        return redirect()->route('patient.access.index');
    }

    // ── Approve a pending request ─────────────────────────────────────────────

    public function approve(Request $request, DoctorAccessRequest $request_model)
    {
        // Ownership check
        if ($request_model->patient_user_id !== auth()->id()) abort(403);

        $result = $this->accessControl->patientApprove($request_model);

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    // ── Deny a pending request ────────────────────────────────────────────────

    public function deny(Request $request, DoctorAccessRequest $request_model)
    {
        if ($request_model->patient_user_id !== auth()->id()) abort(403);

        $result = $this->accessControl->patientDeny($request_model);

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    // ── Revoke an active grant ────────────────────────────────────────────────

    public function revoke(Request $request, int $doctorId)
    {
        $patient = auth()->user();
        $doctor  = User::where('id', $doctorId)->where('role', 'doctor')->firstOrFail();

        $ok = $this->accessControl->revokeAccess($patient, $doctorId);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $ok,
                'message' => $ok ? 'Access revoked.' : 'No active grant found.',
            ]);
        }

        return back()->with(
            $ok ? 'success' : 'error',
            $ok
                ? "Dr. {$doctor->profile?->full_name}'s access has been revoked."
                : 'No active access grant was found.'
        );
    }

    // ── Update global access type ─────────────────────────────────────────────

    public function updateType(Request $request)
    {
        $request->validate([
            'access_type' => ['required', 'in:full,otp_required'],
        ]);

        $this->accessControl->updateAccessType(
            patient:    auth()->user(),
            accessType: $request->access_type,
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Your default access setting has been updated.');
    }

    // ── Update per-family-member access type ──────────────────────────────────

    public function updateMemberType(Request $request, int $member)
    {
        $request->validate([
            'access_type' => ['required', 'in:full,otp_required,blocked'],
        ]);

        $fm = auth()->user()->familyMembers()->where('id', $member)->firstOrFail();

        $this->accessControl->updateAccessType(
            patient:        auth()->user(),
            accessType:     $request->access_type,
            familyMemberId: $fm->id,
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', "Access setting updated for {$fm->full_name}.");
    }

    // ── Send OTP to doctor (patient-initiated, for OTP-required flow) ─────────

    public function sendOtp(Request $request, DoctorAccessRequest $request_model)
    {
        if ($request_model->patient_user_id !== auth()->id()) abort(403);

        if (! $request_model->isPending()) {
            return back()->withErrors(['otp' => 'This request is no longer pending.']);
        }

        // Generate fresh OTP
        $otp       = $this->otpService->generateOtp();
        $expiresAt = now()->addMinutes(config('medtech.access.otp_expires', 10));

        $request_model->update([
            'otp'            => $otp,
            'otp_expires_at' => $expiresAt,
        ]);

        // Send to doctor's mobile
        try {
            $this->otpService->sendAccessOtp(
                mobile:      $request_model->doctor->country_code . $request_model->doctor->mobile_number,
                otp:         $otp,
                patientName: auth()->user()->profile?->full_name ?? 'Patient',
                expiresMinutes: config('medtech.access.otp_expires', 10),
            );
        } catch (\Exception $e) {
            // log but continue
        }

        return back()->with('success', 'OTP sent to doctor\'s registered mobile.');
    }

    // ── Full history ──────────────────────────────────────────────────────────

    public function history(Request $request)
    {
        $patient = auth()->user();
        $tab     = $request->get('tab', 'all');

        $query = DoctorAccessRequest::with(['doctor.profile', 'doctor.doctorProfile', 'familyMember'])
            ->where('patient_user_id', $patient->id);

        match ($tab) {
            'approved' => $query->where('status', 'approved'),
            'denied'   => $query->where('status', 'denied'),
            'expired'  => $query->where('status', 'expired'),
            'pending'  => $query->where('status', 'pending'),
            default    => null,
        };

        $requests = $query->latest()->paginate(15)->withQueryString();

        $counts = DoctorAccessRequest::where('patient_user_id', $patient->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return view('patient.access.history', compact('requests', 'tab', 'counts'));
    }
}
