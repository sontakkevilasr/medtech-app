<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Doctor\DashboardController;
use App\Http\Controllers\Doctor\PatientController;
use App\Http\Controllers\Doctor\AccessRequestController;
use App\Http\Controllers\Doctor\MedicalRecordController;
use App\Http\Controllers\Doctor\PrescriptionController;
use App\Http\Controllers\Doctor\AppointmentController;
use App\Http\Controllers\Doctor\TimelineController;
use App\Http\Controllers\Doctor\AnalyticsController;
use App\Http\Controllers\Payment\SubscriptionController;
use App\Http\Controllers\Payment\RazorpayController;

/*
|--------------------------------------------------------------------------
| Doctor Routes
| Prefix  : /doctor
| Name    : doctor.
| Guards  : auth + active + verified.mobile + role:doctor
|--------------------------------------------------------------------------
*/

Route::middleware('role:doctor')->group(function () {

    // ── Dashboard ────────────────────────────────────────────────────────────
    Route::get('/',          [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.alias');
    Route::patch('/dashboard/appointments/{appointment}/status', [DashboardController::class, 'updateAppointmentStatus'])->name('dashboard.update-status');

    /*
    |----------------------------------------------------------------------
    | Patient Management
    |----------------------------------------------------------------------
    | GET  /doctor/patients                   → patient list
    | GET  /doctor/patients/search            → AJAX search
    | GET  /doctor/patients/{patient}         → patient overview
    | GET  /doctor/patients/{patient}/history → medical history (access-gated)
    | POST /doctor/patients/{patient}/notes   → add/update note
    |----------------------------------------------------------------------
    */
    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('/',                                   [PatientController::class, 'index'])          ->name('index');
        Route::get('/search',                             [PatientController::class, 'search'])         ->name('search');
        Route::post('/request-access',                   [PatientController::class, 'requestAccess'])  ->name('request-access');
        Route::post('/access/{accessRequest}/verify-otp',[PatientController::class, 'verifyAccessOtp'])->name('verify-otp');
        // History: access-gate UI is handled in controller (not middleware-redirect)
        Route::get('/{patient}/history',                 [PatientController::class, 'history'])        ->name('history');
        Route::get('/{patient}',                         [PatientController::class, 'show'])            ->name('show');
        Route::post('/{patient}/notes',                  [PatientController::class, 'addNote'])         ->name('notes');
    });

    /*
    |----------------------------------------------------------------------
    | Medical Records
    |----------------------------------------------------------------------
    */
    Route::prefix('records')->name('records.')->group(function () {
        Route::get('/create/{patient}',       [MedicalRecordController::class, 'create'])  ->name('create');
        Route::post('/{patient}',             [MedicalRecordController::class, 'store'])   ->name('store');
        Route::get('/{record}',               [MedicalRecordController::class, 'show'])    ->name('show');
        Route::get('/{record}/edit',          [MedicalRecordController::class, 'edit'])    ->name('edit');
        Route::put('/{record}',               [MedicalRecordController::class, 'update'])  ->name('update');
        Route::delete('/{record}',            [MedicalRecordController::class, 'destroy']) ->name('destroy');
        Route::post('/{record}/attachment',   [MedicalRecordController::class, 'uploadAttachment'])->name('attachment');
    });

    /*
    |----------------------------------------------------------------------
    | Prescriptions
    |----------------------------------------------------------------------
    */
    Route::prefix('prescriptions')->name('prescriptions.')->group(function () {
        Route::get('/',                               [PrescriptionController::class, 'index'])            ->name('index');
        Route::get('/create',                         [PrescriptionController::class, 'create'])           ->name('create');
        Route::post('/',                              [PrescriptionController::class, 'store'])            ->name('store');
        Route::get('/{prescription}',                 [PrescriptionController::class, 'show'])             ->name('show');
        Route::get('/{prescription}/pdf',             [PrescriptionController::class, 'pdf'])              ->name('pdf');
        Route::get('/{prescription}/send-whatsapp',   [PrescriptionController::class, 'showSendWhatsApp']) ->name('send-whatsapp');
        Route::post('/{prescription}/send-whatsapp',  [PrescriptionController::class, 'sendWhatsApp'])     ->name('send-whatsapp.send');
        Route::post('/{prescription}/regenerate-pdf', [PrescriptionController::class, 'regeneratePdf'])   ->name('regenerate-pdf');
    });

    /*
    |----------------------------------------------------------------------
    | Appointments & Calendar
    |----------------------------------------------------------------------
    */
    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('/',                       [AppointmentController::class, 'index'])         ->name('index');
        Route::get('/calendar',               [AppointmentController::class, 'calendar'])      ->name('calendar');
        Route::get('/today',                  [AppointmentController::class, 'today'])         ->name('today');
        Route::get('/{appointment}',          [AppointmentController::class, 'show'])          ->name('show');
        Route::post('/{appointment}/confirm', [AppointmentController::class, 'confirm'])       ->name('confirm');
        Route::post('/{appointment}/complete',[AppointmentController::class, 'complete'])      ->name('complete');
        Route::post('/{appointment}/cancel',  [AppointmentController::class, 'cancel'])        ->name('cancel');
        Route::post('/{appointment}/remind',  [AppointmentController::class, 'sendReminder'])  ->name('remind');

        // Slot management
        Route::get('/slots/manage',           [AppointmentController::class, 'manageSlots'])   ->name('slots');
        Route::post('/slots/save',            [AppointmentController::class, 'saveSlots'])     ->name('slots.save');
        Route::get('/slots/available',        [AppointmentController::class, 'availableSlots'])->name('slots.available');
    });

    /*
    |----------------------------------------------------------------------
    | Specialty Timelines   [PREMIUM]
    |----------------------------------------------------------------------
    */
    Route::prefix('timelines')->name('timelines.')->middleware('premium')->group(function () {
        // Template management
        Route::get('/',                           [TimelineController::class, 'index'])         ->name('index');
        Route::get('/create',                     [TimelineController::class, 'create'])        ->name('create');
        Route::post('/',                          [TimelineController::class, 'store'])         ->name('store');
        Route::get('/{template}',                 [TimelineController::class, 'show'])          ->name('show');
        Route::get('/{template}/edit',            [TimelineController::class, 'edit'])          ->name('edit');
        Route::put('/{template}',                 [TimelineController::class, 'update'])        ->name('update');
        Route::delete('/{template}',              [TimelineController::class, 'destroy'])       ->name('destroy');

        // Milestones CRUD
        Route::post('/{template}/milestones',         [TimelineController::class, 'storeMilestone'])  ->name('milestones.store');
        Route::put('/{template}/milestones/{milestone}', [TimelineController::class, 'updateMilestone'])->name('milestones.update');
        Route::delete('/{template}/milestones/{milestone}',[TimelineController::class, 'destroyMilestone'])->name('milestones.destroy');

        // Assign to patient
        Route::get('/assign/{patient}',           [TimelineController::class, 'showAssign'])    ->name('assign');
        Route::post('/assign/{patient}',          [TimelineController::class, 'assign'])        ->name('assign.save');
        Route::delete('/patient/{patientTimeline}',[TimelineController::class, 'unassign'])     ->name('unassign');
    });

    /*
    |----------------------------------------------------------------------
    | Analytics & Reports  [PREMIUM]
    |----------------------------------------------------------------------
    */
    Route::prefix('analytics')->name('analytics.')->middleware('premium')->group(function () {
        Route::get('/',                       [AnalyticsController::class, 'index'])       ->name('index');
        Route::get('/patients',               [AnalyticsController::class, 'patients'])    ->name('patients');
        Route::get('/appointments',           [AnalyticsController::class, 'appointments'])->name('appointments');
        Route::get('/revenue',                [AnalyticsController::class, 'revenue'])     ->name('revenue');

        // Excel exports
        Route::get('/export/patients',        [AnalyticsController::class, 'exportPatients'])     ->name('export.patients');
        Route::get('/export/appointments',    [AnalyticsController::class, 'exportAppointments']) ->name('export.appointments');
        Route::get('/export/prescriptions',   [AnalyticsController::class, 'exportPrescriptions'])->name('export.prescriptions');
    });

    /*
    |----------------------------------------------------------------------
    | Subscription Plans
    |----------------------------------------------------------------------
    */
    Route::prefix('subscription')->name('subscription.')->group(function () {
        Route::get('/plans',                  [SubscriptionController::class, 'plans'])    ->name('plans');
        Route::post('/checkout/{plan}',       [SubscriptionController::class, 'checkout'])->name('checkout');
        Route::get('/success',                [SubscriptionController::class, 'success'])  ->name('success');
        Route::get('/history',                [SubscriptionController::class, 'history'])  ->name('history');
    });

    /*
    |----------------------------------------------------------------------
    | Payment (UPI QR + Razorpay)
    |----------------------------------------------------------------------
    */
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/',                       [RazorpayController::class, 'index'])        ->name('index');
        Route::post('/order',                 [RazorpayController::class, 'createOrder'])  ->name('order');
        Route::post('/verify',                [RazorpayController::class, 'verifyPayment'])->name('verify');
        Route::get('/qr-setup',               [RazorpayController::class, 'qrSetup'])      ->name('qr-setup');
        Route::post('/qr-save',               [RazorpayController::class, 'saveQr'])       ->name('qr-save');
    });

    // Razorpay webhook (no CSRF — exempted in VerifyCsrfToken)
    Route::post('/payments/webhook',          [RazorpayController::class, 'webhook'])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
        ->name('payments.webhook');

    // ── Profile ──────────────────────────────────────────────────────────────
    Route::get('/profile/edit',               [DashboardController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile',                    [DashboardController::class, 'updateProfile'])->name('profile.update');

});
