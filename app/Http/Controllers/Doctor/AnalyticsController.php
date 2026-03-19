<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\MedicalRecord;
use App\Models\Payment;
use App\Models\DoctorAccessRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $doctor = auth()->user();
        $id     = $doctor->id;

        // ── Date range (default: last 12 months) ─────────────────────────────
        $months = 12;

        // ── Appointment stats ─────────────────────────────────────────────────
        $totalAppointments = Appointment::where('doctor_user_id', $id)->count();
        $completedAppointments = Appointment::where('doctor_user_id', $id)
            ->where('status', 'completed')->count();
        $cancelledAppointments = Appointment::where('doctor_user_id', $id)
            ->where('status', 'cancelled')->count();

        // ── Monthly appointments (last 12 months) ─────────────────────────────
        $appointmentChart = collect(range($months - 1, 0))->map(function ($m) use ($id) {
            $date = now()->subMonths($m);
            return [
                'month' => $date->format('M y'),
                'total' => Appointment::where('doctor_user_id', $id)
                    ->whereYear('slot_datetime', $date->year)
                    ->whereMonth('slot_datetime', $date->month)
                    ->count(),
                'completed' => Appointment::where('doctor_user_id', $id)
                    ->where('status', 'completed')
                    ->whereYear('slot_datetime', $date->year)
                    ->whereMonth('slot_datetime', $date->month)
                    ->count(),
            ];
        });

        // ── Revenue ───────────────────────────────────────────────────────────
        $totalRevenue = Payment::whereHas('appointment', fn($q) => $q->where('doctor_user_id', $id))
            ->where('status', 'paid')->sum('amount');

        $revenueChart = collect(range($months - 1, 0))->map(function ($m) use ($id) {
            $date = now()->subMonths($m);
            return [
                'month'  => $date->format('M y'),
                'amount' => (float) Payment::whereHas('appointment', fn($q) => $q->where('doctor_user_id', $id))
                    ->where('status', 'paid')
                    ->whereYear('paid_at', $date->year)
                    ->whereMonth('paid_at', $date->month)
                    ->sum('amount'),
            ];
        });

        // ── Patients ──────────────────────────────────────────────────────────
        $totalPatients = DoctorAccessRequest::where('doctor_user_id', $id)
            ->where('status', 'approved')->distinct('patient_user_id')->count('patient_user_id');

        $newPatientsThisMonth = DoctorAccessRequest::where('doctor_user_id', $id)
            ->where('status', 'approved')
            ->whereMonth('approved_at', now()->month)
            ->whereYear('approved_at', now()->year)
            ->count();

        // ── Prescriptions ─────────────────────────────────────────────────────
        $totalPrescriptions = Prescription::where('doctor_user_id', $id)->count();

        $prescriptionsThisMonth = Prescription::where('doctor_user_id', $id)
            ->whereMonth('prescribed_date', now()->month)
            ->whereYear('prescribed_date', now()->year)
            ->count();

        // ── Visit types breakdown ─────────────────────────────────────────────
        $visitTypes = Appointment::where('doctor_user_id', $id)
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        return view('doctor.analytics.index', compact(
            'totalAppointments', 'completedAppointments', 'cancelledAppointments',
            'appointmentChart', 'revenueChart', 'totalRevenue',
            'totalPatients', 'newPatientsThisMonth',
            'totalPrescriptions', 'prescriptionsThisMonth',
            'visitTypes'
        ));
    }

    public function patients()     { return redirect()->route('doctor.analytics.index'); }
    public function appointments() { return redirect()->route('doctor.analytics.index'); }
    public function revenue()      { return redirect()->route('doctor.analytics.index'); }
    public function exportPatients()      { return back()->with('info', 'Export feature coming soon.'); }
    public function exportAppointments()  { return back()->with('info', 'Export feature coming soon.'); }
    public function exportPrescriptions() { return back()->with('info', 'Export feature coming soon.'); }
}
