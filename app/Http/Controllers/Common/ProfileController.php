<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use App\Models\DoctorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    // ── Show profile ──────────────────────────────────────────────────────────

    public function show()
    {
        return redirect()->route('profile.edit');
    }

    // ── Edit form ─────────────────────────────────────────────────────────────

    public function edit()
    {
        $user    = auth()->user()->load(['profile', 'doctorProfile']);
        $profile = $user->profile;
        $dp      = $user->doctorProfile;

        return view('profile.edit', compact('user', 'profile', 'dp'));
    }

    // ── Update personal profile ───────────────────────────────────────────────

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'full_name'                => ['required', 'string', 'max:100'],
            'dob'                      => ['nullable', 'date', 'before:today'],
            'gender'                   => ['nullable', 'in:male,female,other'],
            'blood_group'              => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'address'                  => ['nullable', 'string', 'max:255'],
            'city'                     => ['nullable', 'string', 'max:80'],
            'state'                    => ['nullable', 'string', 'max:80'],
            'pincode'                  => ['nullable', 'digits:6'],
            'emergency_contact_name'   => ['nullable', 'string', 'max:100'],
            'emergency_contact_number' => ['nullable', 'digits:10'],
        ]);

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            $request->only(
                'full_name', 'dob', 'gender', 'blood_group',
                'address', 'city', 'state', 'pincode',
                'emergency_contact_name', 'emergency_contact_number'
            )
        );

        // If doctor, update clinic details too
        if ($user->isDoctor() && $request->has('clinic_name')) {
            $request->validate([
                'clinic_name'    => ['nullable', 'string', 'max:150'],
                'clinic_address' => ['nullable', 'string', 'max:255'],
                'clinic_city'    => ['nullable', 'string', 'max:80'],
                'clinic_state'   => ['nullable', 'string', 'max:80'],
                'consultation_fee' => ['nullable', 'numeric', 'min:0'],
                'upi_id'         => ['nullable', 'string', 'max:100'],
                'bio'            => ['nullable', 'string', 'max:1000'],
                'languages_spoken' => ['nullable', 'array'],
            ]);

            $user->doctorProfile?->update($request->only(
                'clinic_name', 'clinic_address', 'clinic_city', 'clinic_state',
                'consultation_fee', 'upi_id', 'bio', 'languages_spoken'
            ));
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    // ── Profile photo upload ──────────────────────────────────────────────────

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user    = auth()->user();
        $profile = $user->profile;

        // Delete old photo
        if ($profile?->profile_photo) {
            Storage::disk('public')->delete($profile->profile_photo);
        }

        $path = $request->file('photo')->store("profile-photos/{$user->id}", 'public');

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['profile_photo' => $path]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'url'     => Storage::disk('public')->url($path),
            ]);
        }

        return back()->with('success', 'Profile photo updated.');
    }

    // ── Change password ───────────────────────────────────────────────────────

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed',
                                   'regex:/^(?=.*[A-Z])(?=.*\d).+$/'],
        ], [
            'password.regex' => 'Password must have at least one uppercase letter and one number.',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed successfully.');
    }

    // ── Update language preference ────────────────────────────────────────────

    public function updateLanguage(Request $request)
    {
        $request->validate([
            'language' => ['required', 'in:en,hi,mr,ta,te,kn,ml,gu,bn,pa'],
        ]);

        session(['app_language' => $request->language]);

        return back()->with('success', 'Language preference saved.');
    }

    // ── Delete account ────────────────────────────────────────────────────────

    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password is incorrect.']);
        }

        auth()->logout();
        $user->delete(); // soft delete

        return redirect('/')->with('success', 'Account deleted.');
    }
}
