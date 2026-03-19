<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(private OtpService $otpService) {}

    // ─── Login Form ──────────────────────────────────────────────────────────

    public function showLoginForm()
    {
        return view('auth.login');
    }

    // ─── Step 1: Send OTP ────────────────────────────────────────────────────

    public function sendOtp(Request $request)
    {
        $request->validate([
            'mobile_number' => ['required', 'string', 'min:7', 'max:15', 'regex:/^[0-9]+$/'],
            'country_code'  => ['required', 'string', 'max:5'],
        ], [
            'mobile_number.regex' => 'Please enter a valid mobile number (digits only).',
        ]);

        $mobile      = $request->mobile_number;
        $countryCode = $request->country_code;

        // Find or create user
        $user = User::firstOrCreate(
            ['mobile_number' => $mobile, 'country_code' => $countryCode],
            [
                'role'               => 'patient', // default; changed in setup step
                'is_verified'        => false,
                'is_active'          => true,
                'preferred_language' => 'en',
            ]
        );

        // Block suspended accounts early
        if (! $user->is_active) {
            return back()->withErrors([
                'mobile_number' => 'This account has been suspended. Please contact support.',
            ])->withInput();
        }

        // Send OTP
        $result = $this->otpService->sendOtp($user);

        if (! $result['success']) {
            return back()->withErrors(['mobile_number' => $result['message']])->withInput();
        }

        // Log the user in (unverified) so the OTP screen can access them
        Auth::login($user, remember: true);

        // Store the flag so we know OTP was just sent
        session([
            'otp_sent_at'   => now()->timestamp,
            'otp_mobile'    => $user->full_mobile,
            'otp_expires_in'=> $result['expires_in'],
        ]);

        return redirect()->route('auth.otp.verify')
            ->with('success', $result['message']);
    }

    // ─── Password Login ──────────────────────────────────────────────────────

    public function loginWithPassword(Request $request)
    {
        $request->validate([
            'mobile_number' => ['required', 'string'],
            'country_code'  => ['required', 'string'],
            'password'      => ['required', 'string'],
        ]);

        $user = User::where('mobile_number', $request->mobile_number)
            ->where('country_code', $request->country_code)
            ->first();

        if (! $user || ! $user->password || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'The mobile number or password is incorrect.',
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'mobile_number' => 'Your account has been suspended.',
            ]);
        }

        Auth::login($user, remember: $request->boolean('remember'));
        $request->session()->regenerate();

        $user->update(['last_login_at' => now()]);

        return redirect()->intended(route($user->role . '.dashboard'));
    }

    // ─── Role Selection (first-time setup) ──────────────────────────────────

    public function showRoleSelect()
    {
        // Skip if user already has a role set from a previous session
        if (auth()->user()->profile) {
            return redirect()->route(auth()->user()->role . '.dashboard');
        }

        return view('auth.setup.role');
    }

    public function saveRole(Request $request)
    {
        $request->validate([
            'role' => ['required', 'in:doctor,patient'],
        ]);

        auth()->user()->update(['role' => $request->role]);

        return redirect()->route('auth.setup.password');
    }

    // ─── International Registration ──────────────────────────────────────────

    public function showInternationalForm()
    {
        return view('auth.register-international');
    }

    public function registerInternational(Request $request)
    {
        $request->validate([
            'mobile_number' => ['required', 'string', 'min:5', 'max:15'],
            'country_code'  => ['required', 'string', 'max:6'],
            'full_name'     => ['required', 'string', 'max:100'],
            'role'          => ['required', 'in:doctor,patient'],
        ]);

        $exists = User::where('mobile_number', $request->mobile_number)
            ->where('country_code', $request->country_code)
            ->exists();

        if ($exists) {
            // Existing user → send OTP to login
            return redirect()->route('auth.login')
                ->with('info', 'An account with this number already exists. Please log in.')
                ->withInput(['mobile_number' => $request->mobile_number, 'country_code' => $request->country_code]);
        }

        $user = User::create([
            'mobile_number' => $request->mobile_number,
            'country_code'  => $request->country_code,
            'role'          => $request->role,
            'is_verified'   => false,
            'is_active'     => true,
        ]);

        \App\Models\UserProfile::create([
            'user_id'   => $user->id,
            'full_name' => $request->full_name,
        ]);

        Auth::login($user, remember: true);

        $result = $this->otpService->sendOtp($user);

        return redirect()->route('auth.otp.verify')
            ->with('success', $result['message'] ?? 'OTP sent.');
    }

    // ─── Logout ──────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'You have been logged out successfully.');
    }
}
