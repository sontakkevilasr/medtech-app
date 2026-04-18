<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class OtpService
{
    private int    $length;
    private int    $expiresMinutes;
    private int    $maxAttempts;
    private int    $lockoutMinutes;
    private int    $resendCooldown;
    private string $provider;

    public function __construct()
    {
        $this->length         = config('otp.length', 6);
        $this->expiresMinutes = config('otp.expires_in', 10);
        $this->maxAttempts    = config('otp.max_attempts', 5);
        $this->lockoutMinutes = config('otp.lockout_minutes', 30);
        $this->resendCooldown = config('otp.resend_cooldown_seconds', 60);
        $this->provider       = config('otp.provider', 'mock');
    }

    // ─── Public API ──────────────────────────────────────────────────────────

    /**
     * Generate a fresh OTP and send it to the user's mobile.
     * Returns ['success' => bool, 'message' => string, 'expires_at' => Carbon]
     */
    public function sendOtp(User $user): array
    {
        // Check lockout
        if ($this->isLockedOut($user)) {
            $unlockAt = $this->lockoutEndsAt($user);
            return [
                'success'   => false,
                'message'   => "Too many attempts. Try again after {$unlockAt->diffForHumans()}.",
                'locked'    => true,
            ];
        }

        // Check resend cooldown
        if ($this->isInCooldown($user)) {
            $secondsLeft = $this->cooldownSecondsLeft($user);
            return [
                'success'     => false,
                'message'     => "Please wait {$secondsLeft}s before requesting a new OTP.",
                'cooldown'    => true,
                'retry_after' => $secondsLeft,
            ];
        }

        $otp       = $this->generate();
        $expiresAt = now()->addMinutes($this->expiresMinutes);

        // Persist OTP to user record
        $user->update([
            'otp'            => $otp,
            'otp_expires_at' => $expiresAt,
        ]);

        // Set cooldown in cache (store expiry timestamp so we can calculate seconds left)
        Cache::put(
            $this->cooldownKey($user),
            now()->addSeconds($this->resendCooldown)->timestamp,
            now()->addSeconds($this->resendCooldown)
        );

        // Send via provider
        $sent = $this->dispatch($user, $otp);

        if (! $sent) {
            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
            ];
        }

        return [
            'success'    => true,
            'message'    => "OTP sent to {$user->country_code} {$this->masked($user->mobile_number)}",
            'expires_at' => $expiresAt,
            'expires_in' => $this->expiresMinutes,
        ];
    }

    /**
     * Verify the OTP entered by the user.
     * Returns ['success' => bool, 'message' => string]
     */
    public function verifyOtp(User $user, string $enteredOtp): array
    {
        // Check lockout
        if ($this->isLockedOut($user)) {
            return [
                'success' => false,
                'message' => 'Account temporarily locked due to too many failed attempts.',
                'locked'  => true,
            ];
        }

        // OTP not set
        if (! $user->otp) {
            return [
                'success' => false,
                'message' => 'No OTP found. Please request a new one.',
            ];
        }

        // Expired
        if (now()->isAfter($user->otp_expires_at)) {
            $this->clearOtp($user);
            return [
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.',
                'expired' => true,
            ];
        }

        // Wrong OTP — increment attempt counter
        if (! hash_equals((string) $user->otp, (string) $enteredOtp)) {
            $this->incrementAttempts($user);
            $remaining = $this->remainingAttempts($user);

            if ($remaining <= 0) {
                $this->lockout($user);
                return [
                    'success' => false,
                    'message' => "Too many wrong attempts. Account locked for {$this->lockoutMinutes} minutes.",
                    'locked'  => true,
                ];
            }

            return [
                'success'   => false,
                'message'   => "Incorrect OTP. {$remaining} attempt(s) remaining.",
                'remaining' => $remaining,
            ];
        }

        // ✅ Correct OTP
        $this->clearOtp($user);
        $this->clearAttempts($user);

        $user->update([
            'is_verified'   => true,
            'last_login_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Mobile number verified successfully.',
        ];
    }

    /**
     * Check whether a resend is currently allowed (cooldown check only, no send).
     */
    public function canResend(User $user): array
    {
        if ($this->isLockedOut($user)) {
            return ['can_resend' => false, 'reason' => 'locked'];
        }

        if ($this->isInCooldown($user)) {
            return [
                'can_resend'  => false,
                'reason'      => 'cooldown',
                'retry_after' => $this->cooldownSecondsLeft($user),
            ];
        }

        return ['can_resend' => true];
    }

    // ─── Generation ──────────────────────────────────────────────────────────

    private function generate(): string
    {
        // In mock mode, use fixed OTP for testing
        if ($this->provider === 'mock') {
            return config('otp.mock.fixed_otp', '123456');
        }

        return str_pad((string) random_int(0, (int) str_repeat('9', $this->length)), $this->length, '0', STR_PAD_LEFT);
    }

    // ─── Provider Dispatch ───────────────────────────────────────────────────

    private function dispatch(User $user, string $otp): bool
    {
        return match ($this->provider) {
            'mock'     => $this->sendMock($user, $otp),
            'msg91'    => $this->sendMsg91($user, $otp),
            'fast2sms' => $this->sendFast2Sms($user, $otp),
            'twilio'   => $this->sendTwilio($user, $otp),
            default    => $this->sendMock($user, $otp),
        };
    }

    private function sendMock(User $user, string $otp): bool
    {
        Log::info('[OTP MOCK] Sending OTP', [
            'mobile' => $user->country_code . $user->mobile_number,
            'otp'    => $otp,
            'expires'=> now()->addMinutes($this->expiresMinutes)->toDateTimeString(),
        ]);
        return true;
    }

    private function sendMsg91(User $user, string $otp): bool
    {
        try {
            $response = Http::post('https://api.msg91.com/api/v5/otp', [
                'authkey'     => config('otp.msg91.auth_key'),
                'template_id' => config('otp.msg91.template_id'),
                'mobile'      => $user->country_code . $user->mobile_number,
                'otp'         => $otp,
            ]);

            if ($response->successful()) {
                Log::info('[OTP MSG91] Sent', ['mobile' => $user->mobile_number]);
                return true;
            }

            Log::error('[OTP MSG91] Failed', ['response' => $response->body()]);
            return false;

        } catch (\Throwable $e) {
            Log::error('[OTP MSG91] Exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function sendFast2Sms(User $user, string $otp): bool
    {
        try {
            $message = str_replace(
                ['{otp}', '{minutes}'],
                [$otp, $this->expiresMinutes],
                config('otp.fast2sms.message')
            );

            $response = Http::withHeaders([
                'authorization' => config('otp.fast2sms.api_key'),
            ])->post('https://www.fast2sms.com/dev/bulkV2', [
                'route'    => 'v3',
                'sender_id'=> config('otp.fast2sms.sender_id'),
                'message'  => $message,
                'language' => 'english',
                'flash'    => 0,
                'numbers'  => $user->mobile_number,
            ]);

            if ($response->json('return') === true) {
                return true;
            }

            Log::error('[OTP Fast2SMS] Failed', ['response' => $response->body()]);
            return false;

        } catch (\Throwable $e) {
            Log::error('[OTP Fast2SMS] Exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function sendTwilio(User $user, string $otp): bool
    {
        try {
            $response = Http::withBasicAuth(
                config('otp.twilio.sid'),
                config('otp.twilio.token')
            )->timeout(8)->asForm()->post(
                "https://api.twilio.com/2010-04-01/Accounts/" . config('otp.twilio.sid') . "/Messages.json",
                [
                    'To'   => $user->country_code . $user->mobile_number,
                    'From' => config('otp.twilio.from'),
                    'Body' => "Your Naumah Clinic OTP is {$otp}. Valid for {$this->expiresMinutes} minutes. Do not share.",
                ]
            );

            if ($response->successful()) {
                return true;
            }

            Log::error('[OTP Twilio] Failed', ['response' => $response->body()]);
            return false;

        } catch (\Throwable $e) {
            Log::error('[OTP Twilio] Exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    // ─── Rate Limiting Helpers ───────────────────────────────────────────────

    private function attemptsKey(User $user): string
    {
        return "otp_attempts_{$user->id}";
    }

    private function lockoutKey(User $user): string
    {
        return "otp_lockout_{$user->id}";
    }

    private function cooldownKey(User $user): string
    {
        return "otp_cooldown_{$user->id}";
    }

    private function isLockedOut(User $user): bool
    {
        return Cache::has($this->lockoutKey($user));
    }

    private function lockoutEndsAt(User $user): Carbon
    {
        return Carbon::createFromTimestamp(Cache::get($this->lockoutKey($user)));
    }

    private function lockout(User $user): void
    {
        $expiresAt = now()->addMinutes($this->lockoutMinutes);
        Cache::put($this->lockoutKey($user), $expiresAt->timestamp, $expiresAt);
        $this->clearAttempts($user);
    }

    private function isInCooldown(User $user): bool
    {
        return Cache::has($this->cooldownKey($user));
    }

    private function cooldownSecondsLeft(User $user): int
    {
        $expiresAt = Cache::get($this->cooldownKey($user));
        if (! $expiresAt) return 0;
        return max(0, (int) ($expiresAt - now()->timestamp));
    }

    private function incrementAttempts(User $user): void
    {
        $key = $this->attemptsKey($user);
        Cache::increment($key);
        // Set expiry if first attempt
        if (Cache::get($key) === 1) {
            Cache::put($key, 1, now()->addMinutes($this->lockoutMinutes));
        }
    }

    private function remainingAttempts(User $user): int
    {
        $attempts = (int) Cache::get($this->attemptsKey($user), 0);
        return max(0, $this->maxAttempts - $attempts);
    }

    private function clearAttempts(User $user): void
    {
        Cache::forget($this->attemptsKey($user));
    }

    private function clearOtp(User $user): void
    {
        $user->update(['otp' => null, 'otp_expires_at' => null]);
    }

    // ─── Utilities ───────────────────────────────────────────────────────────

    /** Mask mobile number for display: 98765XXXXX */
    private function masked(string $mobile): string
    {
        return substr($mobile, 0, 5) . str_repeat('X', strlen($mobile) - 5);
    }
}
