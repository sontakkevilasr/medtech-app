<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\PasswordSetupController;

/*
|--------------------------------------------------------------------------
| Auth Routes — prefix: /auth,  name prefix: auth.
|--------------------------------------------------------------------------
|
| Flow 1 — OTP only:
|   GET  /auth/login              → enter mobile number
|   POST /auth/login              → send OTP
|   GET  /auth/otp/verify         → OTP input screen
|   POST /auth/otp/verify         → verify OTP → login
|
| Flow 2 — Password:
|   POST /auth/login/password     → login with mobile + password
|
| First-time setup:
|   GET  /auth/setup/role         → choose Doctor / Patient
|   POST /auth/setup/role         → save role
|   GET  /auth/setup/password     → set password (optional)
|   POST /auth/setup/password     → save password
|   GET  /auth/setup/profile      → complete profile
|   POST /auth/setup/profile      → save profile
|
| Logout:
|   POST /auth/logout             → logout
|
|--------------------------------------------------------------------------
*/

// ── Guest-only routes ────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {

    // Mobile number entry screen
    Route::get('/login',              [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login',             [LoginController::class, 'sendOtp'])      ->name('login.send-otp');
    Route::post('/login/password',    [LoginController::class, 'loginWithPassword'])->name('login.password');

    // OTP verification
    Route::get('/otp/verify',         [OtpController::class, 'showVerifyForm']) ->name('otp.verify');
    Route::post('/otp/verify',        [OtpController::class, 'verify'])         ->name('otp.verify.submit');
    Route::post('/otp/resend',        [OtpController::class, 'resend'])         ->name('otp.resend');

    // International / non-Indian number registration
    Route::get('/register/international', [LoginController::class, 'showInternationalForm'])->name('register.international');
    Route::post('/register/international',[LoginController::class, 'registerInternational']) ->name('register.international.submit');
});

// ── Routes accessible to authenticated-but-unverified users ─────────────────
// (verified.mobile middleware is intentionally NOT applied here)
Route::middleware('auth')->group(function () {

    // OTP verify for logged-in but unverified users
    Route::get('/otp/verify',         [OtpController::class, 'showVerifyForm']) ->name('otp.verify');
    Route::post('/otp/verify',        [OtpController::class, 'verify'])         ->name('otp.verify.submit');
    Route::post('/otp/resend',        [OtpController::class, 'resend'])         ->name('otp.resend');

    // First-time onboarding wizard
    Route::prefix('setup')->name('setup.')->group(function () {

        // Step 1 — Choose role
        Route::get('/role',            [LoginController::class, 'showRoleSelect'])  ->name('role');
        Route::post('/role',           [LoginController::class, 'saveRole'])        ->name('role.save');

        // Step 2 — Set password (optional — can skip)
        Route::get('/password',        [PasswordSetupController::class, 'show'])    ->name('password');
        Route::post('/password',       [PasswordSetupController::class, 'store'])   ->name('password.save');
        Route::post('/password/skip',  [PasswordSetupController::class, 'skip'])    ->name('password.skip');

        // Step 3 — Basic profile details
        Route::get('/profile',         [PasswordSetupController::class, 'showProfile'])  ->name('profile');
        Route::post('/profile',        [PasswordSetupController::class, 'storeProfile']) ->name('profile.save');
    });
});

// ── Logout (auth required) ───────────────────────────────────────────────────
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
