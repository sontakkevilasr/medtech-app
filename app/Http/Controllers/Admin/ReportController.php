<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\DoctorProfile;
use App\Services\ExcelExportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct(private ExcelExportService $excel) {}

    public function index()
    {
        // ── 12-month user growth ──────────────────────────────────────────────
        $userGrowth = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $userGrowth[] = [
                'label'    => $m->format('M y'),
                'doctors'  => User::doctors()->whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->count(),
                'patients' => User::patients()->whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->count(),
            ];
        }

        // ── 30-day appointment trend ──────────────────────────────────────────
        $aptTrend = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $aptTrend[] = [
                'label'     => $day->format('d M'),
                'booked'    => Appointment::whereDate('slot_datetime', $day)->whereNotIn('status',['cancelled'])->count(),
                'completed' => Appointment::whereDate('slot_datetime', $day)->where('status','completed')->count(),
                'cancelled' => Appointment::whereDate('slot_datetime', $day)->where('status','cancelled')->count(),
            ];
        }

        // ── Specialization breakdown ──────────────────────────────────────────
        $specData = DoctorProfile::select('specialization', DB::raw('count(*) as total'))
            ->whereNotNull('specialization')
            ->groupBy('specialization')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'specialization')
            ->toArray();

        // ── Appointment types ─────────────────────────────────────────────────
        $aptTypes = Appointment::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        // ── Summary stats ─────────────────────────────────────────────────────
        $summary = [
            'total_users'         => User::whereIn('role',['doctor','patient'])->count(),
            'total_doctors'       => User::doctors()->count(),
            'verified_doctors'    => User::doctors()->whereHas('doctorProfile', fn($q) => $q->verified())->count(),
            'premium_doctors'     => User::doctors()->whereHas('doctorProfile', fn($q) => $q->where('is_premium', true))->count(),
            'total_patients'      => User::patients()->count(),
            'total_appointments'  => Appointment::count(),
            'completed_apts'      => Appointment::where('status','completed')->count(),
            'cancelled_apts'      => Appointment::where('status','cancelled')->count(),
            'total_prescriptions' => Prescription::count(),
            'new_users_30d'       => User::whereIn('role',['doctor','patient'])->where('created_at','>=', now()->subDays(30))->count(),
            'new_apts_30d'        => Appointment::where('created_at','>=', now()->subDays(30))->count(),
        ];

        return view('admin.reports.index', compact(
            'userGrowth', 'aptTrend', 'specData', 'aptTypes', 'summary'
        ));
    }

    // Stubs for other report routes (can expand later)
    public function userGrowth()   { return redirect()->route('admin.reports.index'); }
    public function appointments() { return redirect()->route('admin.reports.index'); }
    public function revenue()      { return redirect()->route('admin.reports.index'); }
    public function specializations() { return redirect()->route('admin.reports.index'); }

    public function exportUsers(Request $request)
    {
        return $this->excel->exportAllUsers($request->get('role'));
    }

    public function exportDoctors()
    {
        return $this->excel->exportAllUsers('doctor');
    }

    public function exportPatients()
    {
        return $this->excel->exportAllUsers('patient');
    }

    public function exportAppointments(Request $request)
    {
        return $this->excel->exportAllAppointments(
            $request->get('from'),
            $request->get('to')
        );
    }

    public function exportVerification()
    {
        return $this->excel->exportDoctorVerification();
    }

    public function exportRevenue()
    {
        return $this->excel->exportPlatformStats();
    }

    public function exportPage()
    {
        return view('admin.reports.exports');
    }
}
