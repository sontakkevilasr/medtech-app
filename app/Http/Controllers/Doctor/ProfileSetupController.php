<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use Illuminate\Http\Request;

class ProfileSetupController extends Controller
{
    public function edit()
    {
        $doctor  = auth()->user()->load(['profile', 'doctorProfile']);
        $profile = $doctor->doctorProfile ?? new DoctorProfile(['user_id' => $doctor->id]);

        return view('doctor.profile-setup', compact('doctor', 'profile'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'specialization'       => ['required', 'string', 'max:100'],
            'sub_specialization'   => ['nullable', 'string', 'max:100'],
            'qualification'        => ['required', 'string', 'max:200'],
            'registration_number'  => ['required', 'string', 'max:50'],
            'registration_council' => ['required', 'string', 'max:100'],
            'experience_years'     => ['nullable', 'integer', 'min:0', 'max:60'],
            'clinic_name'          => ['nullable', 'string', 'max:150'],
            'clinic_address'       => ['nullable', 'string', 'max:255'],
            'clinic_city'          => ['nullable', 'string', 'max:80'],
            'clinic_state'         => ['nullable', 'string', 'max:80'],
            'consultation_fee'     => ['nullable', 'numeric', 'min:0'],
            'upi_id'               => ['nullable', 'string', 'max:100'],
            'bio'                  => ['nullable', 'string', 'max:1000'],
            'languages_spoken'     => ['nullable', 'array'],
            'languages_spoken.*'   => ['string'],
        ]);

        $doctor = auth()->user();

        DoctorProfile::updateOrCreate(
            ['user_id' => $doctor->id],
            [
                'specialization'       => $request->specialization,
                'sub_specialization'   => $request->sub_specialization,
                'qualification'        => $request->qualification,
                'registration_number'  => $request->registration_number,
                'registration_council' => $request->registration_council,
                'experience_years'     => $request->experience_years,
                'clinic_name'          => $request->clinic_name,
                'clinic_address'       => $request->clinic_address,
                'clinic_city'          => $request->clinic_city,
                'clinic_state'         => $request->clinic_state,
                'consultation_fee'     => $request->consultation_fee,
                'upi_id'               => $request->upi_id,
                'bio'                  => $request->bio,
                'languages_spoken'     => $request->languages_spoken ?? ['English'],
                // Keep is_verified as-is — admin sets this
            ]
        );

        // Also update user profile city/state
        $doctor->profile?->update([
            'city'  => $request->clinic_city,
            'state' => $request->clinic_state,
        ]);

        return redirect()
            ->route('doctor.dashboard')
            ->with('success', 'Profile updated! Your account is pending admin verification.');
    }
}
