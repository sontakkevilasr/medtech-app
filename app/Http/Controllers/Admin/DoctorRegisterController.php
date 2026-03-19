<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\DoctorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DoctorRegisterController extends Controller
{
    // ── Registration form ─────────────────────────────────────────────────────

    public function create()
    {
        return view('admin.doctors.register');
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            // Account
            'mobile_number'       => ['required', 'digits:10', 'regex:/^[6-9]\d{9}$/',
                                       'unique:users,mobile_number'],
            'country_code'        => ['nullable', 'string', 'max:6'],
            // Personal
            'full_name'           => ['required', 'string', 'max:100'],
            'dob'                 => ['nullable', 'date', 'before:today'],
            'gender'              => ['nullable', 'in:male,female,other'],
            'email'               => ['nullable', 'email', 'max:150'],
            // Professional
            'specialization'      => ['required', 'string', 'max:100'],
            'sub_specialization'  => ['nullable', 'string', 'max:100'],
            'qualification'       => ['required', 'string', 'max:200'],
            'registration_number' => ['required', 'string', 'max:50'],
            'registration_council'=> ['required', 'string', 'max:100'],
            'experience_years'    => ['nullable', 'integer', 'min:0', 'max:60'],
            // Clinic
            'clinic_name'         => ['nullable', 'string', 'max:150'],
            'clinic_address'      => ['nullable', 'string', 'max:255'],
            'clinic_city'         => ['nullable', 'string', 'max:80'],
            'clinic_state'        => ['nullable', 'string', 'max:80'],
            'consultation_fee'    => ['nullable', 'numeric', 'min:0'],
            'upi_id'              => ['nullable', 'string', 'max:100'],
            // Languages
            'languages_spoken'    => ['nullable', 'array'],
            'languages_spoken.*'  => ['string', 'max:50'],
            // Flags
            'send_credentials'    => ['nullable', 'boolean'],
        ]);

        $countryCode = $request->input('country_code', '+91');

        DB::transaction(function () use ($request, $countryCode, &$doctor) {

            // 1. Create User
            $doctor = User::create([
                'mobile_number' => $request->mobile_number,
                'country_code'  => $countryCode,
                'role'          => 'doctor',
                'password'      => Hash::make(Str::random(16)), // temp — doctor resets on first login
                'is_verified'   => true,
                'is_active'     => true,
            ]);

            // 2. User profile
            UserProfile::create([
                'user_id'    => $doctor->id,
                'full_name'  => $request->full_name,
                'dob'        => $request->dob,
                'gender'     => $request->gender,
                'email'      => $request->email,
                'city'       => $request->clinic_city,
                'state'      => $request->clinic_state,
            ]);

            // 3. Doctor profile
            DoctorProfile::create([
                'user_id'              => $doctor->id,
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
                'languages_spoken'     => $request->languages_spoken ?? ['English', 'Hindi'],
                'is_verified'          => false,  // requires admin verification
                'is_premium'           => false,
                'available_slots'      => $this->defaultSlots(),
            ]);
        });

        return redirect()
            ->route('admin.verification.show', $doctor->id)
            ->with('success', "Dr. {$request->full_name} registered. Please verify their credentials.");
    }

    // ── Default Mon-Fri 9am–1pm 15-min slots ─────────────────────────────────

    private function defaultSlots(): array
    {
        $slots = [];
        foreach (['Mon','Tue','Wed','Thu','Fri'] as $day) {
            $times   = [];
            $current = \Carbon\Carbon::createFromFormat('H:i', '09:00');
            $end     = \Carbon\Carbon::createFromFormat('H:i', '13:00');
            while ($current < $end) {
                $times[] = $current->format('H:i');
                $current->addMinutes(15);
            }
            $slots[$day] = $times;
        }
        return $slots;
    }
}
