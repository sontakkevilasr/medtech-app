<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordSetupController extends Controller
{
    // ─── Password Setup (optional) ───────────────────────────────────────────

    public function show()
    {
        return view('auth.setup.password');
    }

    public function store(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[A-Z])(?=.*[0-9]).+$/'],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter and one number.',
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('auth.setup.profile')
            ->with('success', 'Password set successfully!');
    }

    public function skip()
    {
        // User prefers OTP-only login — skip password
        return redirect()->route('auth.setup.profile');
    }

    // ─── Profile Completion ──────────────────────────────────────────────────

    public function showProfile()
    {
        $user = auth()->user();

        return view('auth.setup.profile', [
            'role' => $user->role,
        ]);
    }

    public function storeProfile(Request $request)
    {
        $user = auth()->user();

        $commonRules = [
            'full_name'   => ['required', 'string', 'max:100'],
            'dob'         => ['required', 'date', 'before:today'],
            'gender'      => ['required', 'in:male,female,other'],
            'blood_group' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'city'        => ['nullable', 'string', 'max:100'],
            'state'       => ['nullable', 'string', 'max:100'],
        ];

        // Doctor-specific extra fields
        $doctorRules = $user->isDoctor() ? [
            'specialization'      => ['required', 'string', 'max:100'],
            'registration_number' => ['required', 'string', 'max:50'],
            'qualification'       => ['required', 'string', 'max:255'],
            'clinic_name'         => ['nullable', 'string', 'max:150'],
            'experience_years'    => ['nullable', 'integer', 'min:0', 'max:70'],
            'consultation_fee'    => ['nullable', 'numeric', 'min:0'],
        ] : [];

        $request->validate(array_merge($commonRules, $doctorRules));

        // Create/update profile
        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name'   => $request->full_name,
                'dob'         => $request->dob,
                'gender'      => $request->gender,
                'blood_group' => $request->blood_group,
                'city'        => $request->city,
                'state'       => $request->state,
            ]
        );

        // Doctor profile
        if ($user->isDoctor()) {
            \App\Models\DoctorProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'specialization'      => $request->specialization,
                    'registration_number' => $request->registration_number,
                    'qualification'       => $request->qualification,
                    'clinic_name'         => $request->clinic_name,
                    'experience_years'    => $request->experience_years ?? 0,
                    'consultation_fee'    => $request->consultation_fee ?? 0,
                ]
            );
        }

        return redirect()->route($user->role . '.dashboard')
            ->with('success', 'Profile complete! Welcome to MedTech, ' . $request->full_name . '.');
    }
}
