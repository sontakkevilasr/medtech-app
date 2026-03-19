<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $doctor  = auth()->user();
        $profile = $doctor->doctorProfile;

        // ── Today's appointments ─────────────────────────────────────────────
        $todayApts = Appointment::where('doctor_user_id', $doctor->id)
            ->whereDate('slot_datetime', today())
            ->whereNotIn('status', ['cancelled'])
            ->with(['patient.profile', 'patient.familyMembers'])
            ->orderBy('slot_datetime')
            ->get();

        // ── Upcoming (next 7 days, not today) ────────────────────────────────
        $upcomingApts = Appointment::where('doctor_user_id', $doctor->id)
            ->whereBetween('slot_datetime', [
                now()->endOfDay(),
                now()->addDays(7)->endOfDay(),
            ])
            ->whereNotIn('status', ['cancelled'])
            ->with(['patient.profile'])
            ->orderBy('slot_datetime')
            ->limit(8)
            ->get();

        // ── Pending access requests ──────────────────────────────────────────
        $pendingAccess = \App\Models\DoctorAccessRequest::where('doctor_user_id', $doctor->id)
            ->where('status', 'pending')
            ->where('otp_expires_at', '>', now())
            ->with(['patient.profile'])
            ->count();

        // ── Stats ────────────────────────────────────────────────────────────
        $totalPatients = \App\Models\DoctorAccessRequest::where('doctor_user_id', $doctor->id)
            ->where('status', 'approved')
            ->distinct('patient_user_id')
            ->count('patient_user_id');

        $thisMonthApts = Appointment::where('doctor_user_id', $doctor->id)
            ->whereYear('slot_datetime', now()->year)
            ->whereMonth('slot_datetime', now()->month)
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $thisMonthRevenue = Payment::whereHas('appointment', fn($q) =>
            $q->where('doctor_user_id', $doctor->id)
        )
            ->where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        $pendingRxCount = Prescription::where('doctor_user_id', $doctor->id)
            ->where('is_sent_whatsapp', false)
            ->whereDate('prescribed_date', today())
            ->count();

        // ── Revenue chart data (last 6 months) ───────────────────────────────
        $revenueChart = collect(range(5, 0))->map(function ($monthsBack) use ($doctor) {
            $date = now()->subMonths($monthsBack);
            $amount = Payment::whereHas('appointment', fn($q) =>
                $q->where('doctor_user_id', $doctor->id)
            )
                ->where('status', 'paid')
                ->whereYear('paid_at', $date->year)
                ->whereMonth('paid_at', $date->month)
                ->sum('amount');

            return [
                'month'  => $date->format('M'),
                'amount' => (float) $amount,
            ];
        });

        // ── Recent prescriptions ─────────────────────────────────────────────
        $recentRx = Prescription::where('doctor_user_id', $doctor->id)
            ->with(['patient.profile', 'medicines'])
            ->latest('prescribed_date')
            ->limit(5)
            ->get();

        return view('doctor.dashboard', compact(
            'doctor', 'profile',
            'todayApts', 'upcomingApts',
            'pendingAccess', 'totalPatients',
            'thisMonthApts', 'thisMonthRevenue',
            'pendingRxCount', 'revenueChart',
            'recentRx'
        ));
    }

    /**
     * Quick action: mark appointment as completed / no-show from dashboard
     */
    public function updateAppointmentStatus(Request $request, Appointment $appointment)
    {
        $request->validate(['status' => ['required', 'in:completed,no_show,confirmed']]);

        if ($appointment->doctor_user_id !== auth()->id()) {
            abort(403);
        }

        $appointment->update(['status' => $request->status]);

        return back()->with('success', 'Appointment updated.');
    }

    public function editProfile()
    {
        $doctor  = auth()->user();
        $profile = $doctor->profile;
        $dp      = $doctor->doctorProfile;
        return view('doctor.profile.edit', compact('doctor', 'profile', 'dp'));
    }

    public function updateProfile(Request $request)
    {
        $doctor = auth()->user();

        $request->validate([
            'full_name'        => ['required', 'string', 'max:100'],
            'specialization'   => ['nullable', 'string', 'max:100'],
            'clinic_name'      => ['nullable', 'string', 'max:150'],
            'clinic_address'   => ['nullable', 'string', 'max:300'],
            'clinic_city'      => ['nullable', 'string', 'max:100'],
            'bio'              => ['nullable', 'string', 'max:1000'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0'],
        ]);

        $doctor->profile()->updateOrCreate(
            ['user_id' => $doctor->id],
            ['full_name' => $request->full_name]
        );

        $doctor->doctorProfile()->updateOrCreate(
            ['user_id' => $doctor->id],
            $request->only('specialization', 'clinic_name', 'clinic_address', 'clinic_city', 'bio', 'consultation_fee')
        );

        return redirect()->route('doctor.profile.edit')->with('success', 'Profile updated.');
    }
}
