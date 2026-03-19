<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function __construct(private OtpService $otpService) {}

    // ─── Show OTP Screen ─────────────────────────────────────────────────────

    public function showVerifyForm()
    {
        // If already verified, redirect to dashboard
        if (auth()->check() && auth()->user()->is_verified) {
            $user = auth()->user();

            // New user with no profile → go to setup
            if (! $user->profile) {
                return redirect()->route('auth.setup.role');
            }

            return redirect()->route($user->role . '.dashboard');
        }

        return view('auth.otp-verify', [
            'maskedMobile' => session('otp_mobile', ''),
            'expiresIn'    => session('otp_expires_in', 10),
            'sentAt'       => session('otp_sent_at', now()->timestamp),
            'resendCooldown' => config('otp.resend_cooldown_seconds', 60),
        ]);
    }

    // ─── Verify OTP ──────────────────────────────────────────────────────────

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:' . config('otp.length', 6)],
        ], [
            'otp.size' => 'OTP must be exactly ' . config('otp.length', 6) . ' digits.',
        ]);

        $user   = auth()->user();
        $result = $this->otpService->verifyOtp($user, $request->otp);

        if (! $result['success']) {
            if ($request->expectsJson()) {
                return response()->json($result, 422);
            }
            return back()->withErrors(['otp' => $result['message']]);
        }

        $request->session()->regenerate();

        // Clear OTP session data
        session()->forget(['otp_sent_at', 'otp_mobile', 'otp_expires_in']);

        // First-time user — no profile yet → onboarding wizard
        if (! $user->fresh()->profile) {
            return redirect()->route('auth.setup.role')
                ->with('success', 'Mobile verified! Let\'s set up your profile.');
        }

        return redirect()->intended(route($user->role . '.dashboard'))
            ->with('success', 'Welcome back, ' . ($user->profile->full_name ?? '') . '!');
    }

    // ─── Resend OTP ──────────────────────────────────────────────────────────

    public function resend(Request $request)
    {
        $user = auth()->user();

        $canResend = $this->otpService->canResend($user);

        if (! $canResend['can_resend']) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success'     => false,
                    'message'     => $canResend['reason'] === 'locked'
                        ? 'Account locked. Try again later.'
                        : 'Please wait before requesting a new OTP.',
                    'retry_after' => $canResend['retry_after'] ?? null,
                ], 429);
            }
            return back()->withErrors(['otp' => 'Please wait before requesting a new OTP.']);
        }

        $result = $this->otpService->sendOtp($user);

        if ($request->expectsJson()) {
            return response()->json([
                'success'    => $result['success'],
                'message'    => $result['message'],
                'expires_in' => $result['expires_in'] ?? null,
                'sent_at'    => now()->timestamp,
            ]);
        }

        if (! $result['success']) {
            return back()->withErrors(['otp' => $result['message']]);
        }

        session([
            'otp_sent_at'    => now()->timestamp,
            'otp_expires_in' => $result['expires_in'],
        ]);

        return back()->with('success', $result['message']);
    }
}
