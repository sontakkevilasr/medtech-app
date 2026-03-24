<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Common\ProfileController;
use App\Http\Controllers\Common\NotificationController;
use App\Http\Controllers\Common\WhatsAppController;

/*
|--------------------------------------------------------------------------
| Public Routes — No authentication required
|--------------------------------------------------------------------------
*/

// Landing page
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->role . '.dashboard');
    }
    //return view('welcome');
    return redirect()->route('auth.login');
})->name('home');

// Health check (for uptime monitoring)
Route::get('/health', fn() => response()->json(['status' => 'ok', 'time' => now()]));

/*
|--------------------------------------------------------------------------
| Shared Authenticated Routes
| Available to ALL roles (doctor, patient, admin)
|--------------------------------------------------------------------------
*/

// ── Serve medical record attachments (works without storage symlink) ────────
Route::middleware(['auth'])->group(function () {
    Route::get('/attachments/medical-records/{patientId}/{filename}', function (int $patientId, string $filename) {
        $user = auth()->user();

        // Block path traversal attempts
        if (str_contains($filename, '/') || str_contains($filename, '..')) {
            abort(403);
        }

        // Authorization: only the patient, their doctor (with access), or admin
        $isOwner  = $user->id === $patientId;
        $isAdmin  = $user->role === 'admin';
        $isDoctor = $user->role === 'doctor'
            && \App\Models\MedicalRecord::where('patient_user_id', $patientId)
                ->where('doctor_user_id', $user->id)
                ->exists();

        if (! $isOwner && ! $isAdmin && ! $isDoctor) {
            abort(403);
        }

        $fullPath = "medical-records/{$patientId}/{$filename}";
        if (! Storage::disk('public')->exists($fullPath)) {
            abort(404);
        }

        return Storage::disk('public')->response($fullPath);
    })->where('patientId', '[0-9]+')
      ->where('filename', '[a-zA-Z0-9._-]+')
      ->name('attachments.medical-record');
});

Route::middleware(['auth', 'active', 'verified.mobile', 'locale'])->group(function () {

    // ── Role-based dashboard redirect ────────────────────────────────────────
    Route::get('/dashboard', function () {
        return redirect()->route(auth()->user()->role . '.dashboard');
    })->name('dashboard');

    // ── Profile (shared across all roles) ────────────────────────────────────
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',                 [ProfileController::class, 'show'])  ->name('show');
        Route::get('/edit',             [ProfileController::class, 'edit'])  ->name('edit');
        Route::put('/',                 [ProfileController::class, 'update'])->name('update');
        Route::post('/photo',           [ProfileController::class, 'updatePhoto'])->name('photo');
        Route::put('/language',         [ProfileController::class, 'updateLanguage'])->name('language');
        Route::put('/password',         [ProfileController::class, 'updatePassword'])->name('password');
        Route::delete('/',              [ProfileController::class, 'destroy'])->name('destroy');
    });

    // ── Notifications ────────────────────────────────────────────────────────
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',                 [NotificationController::class, 'index'])  ->name('index');
        Route::post('/{id}/read',       [NotificationController::class, 'markRead'])->name('read');
        Route::post('/read-all',        [NotificationController::class, 'markAllRead'])->name('read-all');
        Route::delete('/{id}',          [NotificationController::class, 'destroy'])->name('destroy');
        Route::get('/count',            [NotificationController::class, 'unreadCount'])->name('count');
    });

    // ── Locale switcher ──────────────────────────────────────────────────────
    Route::post('/locale/{lang}', function (string $lang) {
        $supported = ['en', 'hi', 'mr'];
        if (in_array($lang, $supported)) {
            auth()->user()->update(['preferred_language' => $lang]);
            session(['locale' => $lang]);
        }
        return back();
    })->name('locale.switch')->where('lang', 'en|hi|mr');
});
