<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DoctorProfile;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Core counts ───────────────────────────────────────────────────────
        $stats = [
            'total_doctors'       => User::doctors()->count(),
            'verified_doctors'    => User::doctors()->whereHas('doctorProfile', fn($q) => $q->verified())->count(),
            'pending_verification'=> DoctorProfile::where('is_verified', false)
                                        ->whereHas('user', fn($q) => $q->active())->count(),
            'total_patients'      => User::patients()->count(),
            'active_users'        => User::active()->whereIn('role', ['doctor','patient'])->count(),
            'suspended_users'     => User::whereIn('role',['doctor','patient'])->where('is_active', false)->count(),

            'appointments_today'  => Appointment::whereDate('slot_datetime', today())->count(),
            'appointments_month'  => Appointment::whereMonth('slot_datetime', now()->month)->whereYear('slot_datetime', now()->year)->count(),
            'total_prescriptions' => Prescription::count(),
            'prescriptions_today' => Prescription::whereDate('prescribed_date', today())->count(),

            'revenue_month'       => Payment::where('status', 'completed')
                                        ->whereMonth('created_at', now()->month)->sum('amount'),
            'revenue_total'       => Payment::where('status', 'completed')->sum('amount'),
        ];

        // ── User growth (last 6 months) ───────────────────────────────────────
        $userGrowth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $userGrowth[] = [
                'label'    => $month->format('M'),
                'doctors'  => User::doctors()->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->count(),
                'patients' => User::patients()->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->count(),
            ];
        }

        // ── Appointment stats (last 7 days) ───────────────────────────────────
        $aptTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $aptTrend[] = [
                'label'     => $day->format('D'),
                'total'     => Appointment::whereDate('slot_datetime', $day)->whereNotIn('status',['cancelled'])->count(),
                'cancelled' => Appointment::whereDate('slot_datetime', $day)->where('status','cancelled')->count(),
            ];
        }

        // ── Specialization distribution ───────────────────────────────────────
        $specializations = DoctorProfile::whereNotNull('specialization')
            ->where('is_verified', true)
            ->select('specialization', DB::raw('count(*) as total'))
            ->groupBy('specialization')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'specialization')
            ->toArray();

        // ── Recent activity ───────────────────────────────────────────────────
        $recentUsers = User::whereIn('role', ['doctor','patient'])
            ->with('profile')
            ->latest()
            ->limit(8)
            ->get();

        $pendingDoctors = DoctorProfile::where('is_verified', false)
            ->with('user.profile')
            ->whereHas('user', fn($q) => $q->active())
            ->latest()
            ->limit(5)
            ->get();

        $recentAppointments = Appointment::with(['doctor.profile','patient.profile'])
            ->latest('slot_datetime')
            ->limit(6)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'userGrowth', 'aptTrend', 'specializations',
            'recentUsers', 'pendingDoctors', 'recentAppointments'
        ));
    }
}
