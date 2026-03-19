<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\DoctorVerificationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\DoctorRegisterController;
/*
|--------------------------------------------------------------------------
| Admin Routes
| Prefix  : /admin
| Name    : admin.
| Guards  : auth + active + verified.mobile + role:admin
|--------------------------------------------------------------------------
*/

Route::middleware('role:admin')->group(function () {

    // ── Dashboard ────────────────────────────────────────────────────────────
    Route::get('/',          [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.alias');

/*
    |----------------------------------------------------------------------
    | Doctor Registration (admin adds a new doctor)
    |----------------------------------------------------------------------
    */
    Route::prefix('doctors')->name('doctors.')->group(function () {
        Route::get('/register',  [DoctorRegisterController::class, 'create']) ->name('create');
        Route::post('/register', [DoctorRegisterController::class, 'store'])  ->name('store');
    });

    /*
    |----------------------------------------------------------------------
    | User Management
    |----------------------------------------------------------------------
    */
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',                       [UserManagementController::class, 'index'])        ->name('index');
        Route::get('/doctors',                [UserManagementController::class, 'doctors'])      ->name('doctors');
        Route::get('/patients',               [UserManagementController::class, 'patients'])     ->name('patients');
        Route::get('/{user}',                 [UserManagementController::class, 'show'])         ->name('show');
        Route::post('/{user}/activate',       [UserManagementController::class, 'activate'])     ->name('activate');
        Route::post('/{user}/suspend',        [UserManagementController::class, 'suspend'])      ->name('suspend');
        Route::post('/{user}/grant-premium',  [UserManagementController::class, 'grantPremium']) ->name('grant-premium');
        Route::delete('/{user}',              [UserManagementController::class, 'destroy'])      ->name('destroy');
    });



    /*
    |----------------------------------------------------------------------
    | Doctor Verification (MCI/State Council)
    |----------------------------------------------------------------------
    */
    Route::prefix('verification')->name('verification.')->group(function () {
        Route::get('/',                       [DoctorVerificationController::class, 'index'])    ->name('index');
        Route::get('/pending',                [DoctorVerificationController::class, 'pending'])  ->name('pending');
        Route::get('/{doctor}',               [DoctorVerificationController::class, 'show'])     ->name('show');
        Route::post('/{doctor}/approve',      [DoctorVerificationController::class, 'approve'])  ->name('approve');
        Route::post('/{doctor}/reject',       [DoctorVerificationController::class, 'reject'])   ->name('reject');
    });

    /*
    |----------------------------------------------------------------------
    | Platform Reports & Analytics
    |----------------------------------------------------------------------
    */
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/',                       [ReportController::class, 'index'])            ->name('index');
        Route::get('/users',                  [ReportController::class, 'userGrowth'])       ->name('users');
        Route::get('/appointments',           [ReportController::class, 'appointments'])     ->name('appointments');
        Route::get('/revenue',                [ReportController::class, 'revenue'])          ->name('revenue');
        Route::get('/specializations',        [ReportController::class, 'specializations'])  ->name('specializations');

        // Exports
        Route::get('/export',                 [ReportController::class, 'exportPage'])        ->name('export');
        Route::get('/export/users',           [ReportController::class, 'exportUsers'])       ->name('export.users');
        Route::get('/export/doctors',         [ReportController::class, 'exportDoctors'])     ->name('export.doctors');
        Route::get('/export/patients',        [ReportController::class, 'exportPatients'])    ->name('export.patients');
        Route::get('/export/appointments',    [ReportController::class, 'exportAppointments'])->name('export.appointments');
        Route::get('/export/verification',    [ReportController::class, 'exportVerification'])->name('export.verification');
        Route::get('/export/revenue',         [ReportController::class, 'exportRevenue'])     ->name('export.revenue');
    });

    /*
    |----------------------------------------------------------------------
    | Timeline Template Management (system templates)
    |----------------------------------------------------------------------
    */
    Route::prefix('timelines')->name('timelines.')->group(function () {
        Route::get('/',                       [\App\Http\Controllers\Doctor\TimelineController::class, 'adminIndex'])   ->name('index');
        Route::post('/{template}/activate',   [\App\Http\Controllers\Doctor\TimelineController::class, 'activate'])    ->name('activate');
        Route::post('/{template}/deactivate', [\App\Http\Controllers\Doctor\TimelineController::class, 'deactivate'])  ->name('deactivate');
    });

    /*
    |----------------------------------------------------------------------
    | Subscription Plan Management
    |----------------------------------------------------------------------
    */
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/',                       [\App\Http\Controllers\Payment\SubscriptionController::class, 'adminIndex'])  ->name('index');
        Route::get('/revenue',                [\App\Http\Controllers\Payment\SubscriptionController::class, 'revenue'])     ->name('revenue');
    });
});
