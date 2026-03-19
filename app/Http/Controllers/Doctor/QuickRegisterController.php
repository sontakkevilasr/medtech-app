<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\FamilyMember;
use App\Models\DoctorAccessRequest;
use App\Services\SubIdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class QuickRegisterController extends Controller
{
    public function __construct(private SubIdService $subIdService) {}

    // ── Registration form ─────────────────────────────────────────────────────

    public function create()
    {
        return view('doctor.quick-register.create');
    }

    // ── Store: create account + profile + Sub-ID + access ─────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'full_name'      => ['required', 'string', 'max:100'],
            'mobile_number'  => ['required', 'digits:10', 'regex:/^[6-9]\d{9}$/'],
            'country_code'   => ['nullable', 'string', 'max:6'],
            'dob'            => ['nullable', 'date', 'before:today'],
            'gender'         => ['nullable', 'in:male,female,other'],
            'blood_group'    => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'address'        => ['nullable', 'string', 'max:255'],
            'city'           => ['nullable', 'string', 'max:80'],
            'state'          => ['nullable', 'string', 'max:80'],
            'emergency_contact_name'   => ['nullable', 'string', 'max:100'],
            'emergency_contact_number' => ['nullable', 'digits:10'],
        ]);

        $countryCode = $request->input('country_code', '+91');
        $mobile      = $request->mobile_number;

        // ── Check if patient already exists ───────────────────────────────────
        $existingUser = User::where('mobile_number', $mobile)
            ->where('country_code', $countryCode)
            ->first();

        if ($existingUser) {
            // Patient already registered — just grant access and redirect
            $this->grantDoctorAccess($existingUser);

            return redirect()
                ->route('doctor.quick-register.success', $existingUser->id)
                ->with('info', "Patient {$existingUser->profile?->full_name} already has an account. Access granted.");
        }

        // ── Create new patient account ─────────────────────────────────────────
        DB::transaction(function () use ($request, $mobile, $countryCode, &$patient) {

            // 1. Create User account
            $patient = User::create([
                'mobile_number' => $mobile,
                'country_code'  => $countryCode,
                'role'          => 'patient',
                'password'      => Hash::make(Str::random(16)), // temp password
                'is_verified'   => true,   // doctor-registered patients are pre-verified
                'is_active'     => true,
            ]);

            // 2. Create profile
            UserProfile::create([
                'user_id'                  => $patient->id,
                'full_name'                => $request->full_name,
                'dob'                      => $request->dob,
                'gender'                   => $request->gender,
                'blood_group'              => $request->blood_group,
                'address'                  => $request->address,
                'city'                     => $request->city,
                'state'                    => $request->state,
                'emergency_contact_name'   => $request->emergency_contact_name,
                'emergency_contact_number' => $request->emergency_contact_number,
            ]);

            // 3. Generate "self" Sub-ID
            $subId = $this->subIdService->generate($patient);

            FamilyMember::create([
                'primary_user_id' => $patient->id,
                'sub_id'          => $subId,
                'full_name'       => $request->full_name,
                'relation'        => 'self',
                'dob'             => $request->dob,
                'gender'          => $request->gender,
                'blood_group'     => $request->blood_group,
                'is_delinked'     => false,
            ]);

            // 4. Grant doctor immediate access (auto-approved)
            $this->grantDoctorAccess($patient);
        });

        return redirect()
            ->route('doctor.quick-register.success', $patient->id)
            ->with('success', "Patient {$request->full_name} registered successfully!");
    }

    // ── Success screen ────────────────────────────────────────────────────────

    public function success(int $patient)
    {
        $patient = User::where('id', $patient)
            ->where('role', 'patient')
            ->with(['profile', 'familyMembers'])
            ->firstOrFail();

        $selfSubId = $patient->familyMembers->firstWhere('relation', 'self')?->sub_id;

        return view('doctor.quick-register.success', compact('patient', 'selfSubId'));
    }

    // ── Private: grant doctor direct access to patient ────────────────────────

    private function grantDoctorAccess(User $patient): void
    {
        $doctor = auth()->user();

        // Expire any existing pending requests
        DoctorAccessRequest::where('doctor_user_id', $doctor->id)
            ->where('patient_user_id', $patient->id)
            ->where('status', 'pending')
            ->update(['status' => 'expired']);

        // Create pre-approved access grant (no OTP needed for walk-in)
        DoctorAccessRequest::create([
            'doctor_user_id'    => $doctor->id,
            'patient_user_id'   => $patient->id,
            'family_member_id'  => null,
            'patient_identifier'=> $patient->mobile_number,
            'identifier_type'   => 'mobile',
            'status'            => 'approved',
            'approved_at'       => now(),
            'access_expires_at' => now()->addDays(
                config('medtech.access.duration_days', 30)
            ),
        ]);
    }
}
