<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Patient\DashboardController;
use App\Http\Controllers\Patient\FamilyMemberController;
use App\Http\Controllers\Patient\MedicalHistoryController;
use App\Http\Controllers\Patient\AppointmentController;
use App\Http\Controllers\Patient\AccessPermissionController;
use App\Http\Controllers\Patient\TimelineController;
use App\Http\Controllers\Patient\HealthLogController;
use App\Http\Controllers\Patient\MedicationReminderController;
use App\Http\Controllers\Payment\RazorpayController;
use App\Http\Controllers\Patient\AccessPermissionController;

/*
|--------------------------------------------------------------------------
| Patient Routes
| Prefix  : /patient
| Name    : patient.
| Guards  : auth + active + verified.mobile + role:patient
|--------------------------------------------------------------------------
*/

Route::middleware('role:patient')->group(function () {

    // ── Dashboard ────────────────────────────────────────────────────────────
    Route::get('/',          [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.alias');

    /*
    |----------------------------------------------------------------------
    | Family Members & Sub-IDs
    |----------------------------------------------------------------------
    | GET  /patient/family                 → list all family members
    | POST /patient/family                 → add new member
    | GET  /patient/family/{member}        → view member profile
    | PUT  /patient/family/{member}        → update member details
    | POST /patient/family/{member}/delink → delink sub-ID from this account
    | POST /patient/family/{member}/relink → relink to this account
    |----------------------------------------------------------------------
    */
    Route::prefix('family')->name('family.')->group(function () {
        Route::get('/',                       [FamilyMemberController::class, 'index'])   ->name('index');
        Route::get('/create',                 [FamilyMemberController::class, 'create'])  ->name('create');
        Route::post('/',                      [FamilyMemberController::class, 'store'])   ->name('store');
        Route::get('/{member}',               [FamilyMemberController::class, 'show'])    ->name('show');
        Route::get('/{member}/edit',          [FamilyMemberController::class, 'edit'])    ->name('edit');
        Route::put('/{member}',               [FamilyMemberController::class, 'update'])  ->name('update');
        Route::delete('/{member}',            [FamilyMemberController::class, 'destroy']) ->name('destroy');

        // Sub-ID delink / relink
        Route::post('/{member}/delink',       [FamilyMemberController::class, 'delink'])  ->name('delink');
        Route::post('/{member}/relink',       [FamilyMemberController::class, 'relink'])  ->name('relink');

        // Generate new sub-ID (if lost)
        Route::post('/{member}/regenerate-id',[FamilyMemberController::class, 'regenerateSubId'])->name('regenerate-id');
    });

    /*
    |----------------------------------------------------------------------
    | Medical History (own + family members)
    |----------------------------------------------------------------------
    */
    Route::prefix('history')->name('history.')->group(function () {
        // Own history
        Route::get('/',                       [MedicalHistoryController::class, 'index'])            ->name('index');
        Route::get('/{record}',               [MedicalHistoryController::class, 'show'])             ->name('show');

        // Family member history
        Route::get('/member/{member}',        [MedicalHistoryController::class, 'memberHistory'])    ->name('member');
        Route::get('/member/{member}/{record}',[MedicalHistoryController::class, 'memberRecord'])    ->name('member.record');

        // Prescription actions from patient side
        Route::get('/prescription/{prescription}/pdf', [MedicalHistoryController::class, 'downloadPdf'])->name('prescription.pdf');
    });

    /*
    |----------------------------------------------------------------------
    | Appointments — Booking & Management
    |----------------------------------------------------------------------
    */
    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('/',                       [AppointmentController::class, 'index'])        ->name('index');
        Route::get('/book',                   [AppointmentController::class, 'showDoctors'])  ->name('book');
        Route::get('/book/{doctor}',          [AppointmentController::class, 'showSlots'])    ->name('book.slots');
        Route::post('/book/{doctor}',         [AppointmentController::class, 'store'])        ->name('store');
        Route::get('/{appointment}',          [AppointmentController::class, 'show'])         ->name('show');
        Route::post('/{appointment}/cancel',  [AppointmentController::class, 'cancel'])       ->name('cancel');
        Route::post('/{appointment}/reschedule',[AppointmentController::class, 'reschedule']) ->name('reschedule');

        // Book for a family member
        Route::get('/book/{doctor}/member/{member}',  [AppointmentController::class, 'showSlotsForMember'])->name('book.member.slots');
        Route::post('/book/{doctor}/member/{member}', [AppointmentController::class, 'storeForMember'])    ->name('store.member');

        // AJAX: slot availability
        Route::get('/book/{doctor}/slots',            [AppointmentController::class, 'availableSlotsForDate'])->name('slots');
        Route::get('/book/{doctor}/dates',            [AppointmentController::class, 'availableDatesForMonth'])->name('dates');
    });

    /*
    |----------------------------------------------------------------------
    | Access Permissions — Control who can see my records
    |----------------------------------------------------------------------
    */
    Route::prefix('access')->name('access.')->group(function () {
        Route::get('/',                       [AccessPermissionController::class, 'index'])        ->name('index');

        // Toggle own access type (full / OTP)
        Route::put('/type',                   [AccessPermissionController::class, 'updateType'])   ->name('type');

        // Per-family-member overrides
        Route::put('/member/{member}',        [AccessPermissionController::class, 'updateMemberType'])->name('member');

        // Approve / deny an incoming doctor access request
        Route::get('/requests',               [AccessPermissionController::class, 'pendingRequests'])->name('requests');
        Route::post('/requests/{request}/approve', [AccessPermissionController::class, 'approve']) ->name('approve');
        Route::post('/requests/{request}/deny',    [AccessPermissionController::class, 'deny'])    ->name('deny');

        // Send OTP to doctor for OTP-required flow
        Route::post('/requests/{request}/send-otp', [AccessPermissionController::class, 'sendOtp'])->name('send-otp');

        // Revoke an active access grant
        Route::post('/revoke/{doctor}',             [AccessPermissionController::class, 'revoke'])   ->name('revoke');

        // History of all requests (approved, denied, expired)
        Route::get('/history',                      [AccessPermissionController::class, 'history'])  ->name('history');
    });

    /*
    |----------------------------------------------------------------------
    | Specialty Timelines — View assigned timelines
    |----------------------------------------------------------------------
    */
    Route::prefix('timelines')->name('timelines.')->group(function () {
        Route::get('/',                       [TimelineController::class, 'index'])         ->name('index');
        Route::get('/{patientTimeline}',      [TimelineController::class, 'show'])          ->name('show');

        // Family member's timeline
        Route::get('/member/{member}',        [TimelineController::class, 'memberTimelines'])->name('member');
        Route::get('/member/{member}/{patientTimeline}', [TimelineController::class, 'memberShow'])->name('member.show');
    });

    /*
    |----------------------------------------------------------------------
    | Health Tracker — BP, Sugar, Weight, SpO2, etc.
    |----------------------------------------------------------------------
    */
    Route::prefix('health')->name('health.')->group(function () {
        Route::get('/',                       [HealthLogController::class, 'index'])          ->name('index');
        Route::get('/logs',                   [HealthLogController::class, 'logs'])           ->name('logs');
        Route::post('/logs',                  [HealthLogController::class, 'store'])          ->name('logs.store');
        Route::delete('/logs/{log}',          [HealthLogController::class, 'destroy'])        ->name('logs.destroy');

        // Chart data for Alpine.js / charts
        Route::get('/chart/{type}',           [HealthLogController::class, 'chartData'])      ->name('chart');

        // Family member logs
        Route::get('/member/{member}',        [HealthLogController::class, 'memberLogs'])     ->name('member');
        Route::post('/member/{member}/logs',  [HealthLogController::class, 'storeMemberLog']) ->name('member.store');
    });

    /*
    |----------------------------------------------------------------------
    | Medication Reminders
    |----------------------------------------------------------------------
    */
    Route::prefix('reminders')->name('reminders.')->group(function () {
        Route::get('/',                       [MedicationReminderController::class, 'index'])  ->name('index');
        Route::get('/create',                 [MedicationReminderController::class, 'create']) ->name('create');
        Route::post('/',                      [MedicationReminderController::class, 'store'])  ->name('store');
        Route::put('/{reminder}',             [MedicationReminderController::class, 'update']) ->name('update');
        Route::delete('/{reminder}',          [MedicationReminderController::class, 'destroy'])->name('destroy');
        Route::post('/{reminder}/toggle',     [MedicationReminderController::class, 'toggle']) ->name('toggle');
    });

    /*
    |----------------------------------------------------------------------
    | Payments (appointment fees)
    |----------------------------------------------------------------------
    */
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/',                       [RazorpayController::class, 'patientIndex'])     ->name('index');
        Route::post('/order',                 [RazorpayController::class, 'createOrder'])      ->name('order');
        Route::post('/verify',                [RazorpayController::class, 'verifyPayment'])    ->name('verify');
        Route::get('/{payment}/receipt',      [RazorpayController::class, 'receipt'])          ->name('receipt');
    });

    // ── Profile / Settings ───────────────────────────────────────────────────
    Route::get('/profile/edit',               [DashboardController::class, 'editProfile'])    ->name('profile.edit');
    Route::put('/profile',                    [DashboardController::class, 'updateProfile'])  ->name('profile.update');

});
