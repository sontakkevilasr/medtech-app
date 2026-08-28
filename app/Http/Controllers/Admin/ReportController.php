<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\DoctorProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
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

    public function exportUsers()
    {
        $users = User::whereIn('role',['doctor','patient'])
            ->with(['profile','doctorProfile'])
            ->get()
            ->map(fn($u) => [
                'ID'           => $u->id,
                'Name'         => $u->profile?->full_name,
                'Role'         => ucfirst($u->role),
                'Mobile'       => $u->country_code.' '.$u->mobile_number,
                'Email'        => $u->profile?->email,
                'City'         => $u->profile?->city ?? $u->doctorProfile?->clinic_city,
                'Verified'     => $u->is_verified ? 'Yes' : 'No',
                'Active'       => $u->is_active   ? 'Yes' : 'No',
                'Joined'       => $u->created_at->format('Y-m-d'),
                'Specialization' => $u->doctorProfile?->specialization,
                'Premium'      => $u->doctorProfile?->is_premium ? 'Yes' : 'No',
            ]);

        $csv = collect($users)->toArray();
        $header = array_keys($csv[0] ?? []);
        $rows   = array_merge([$header], array_map('array_values', $csv));

        $content = implode("\n", array_map(fn($r) => implode(',', array_map(fn($v) => '"'.$v.'"', $r)), $rows));

        return response($content, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users-'.today()->format('Y-m-d').'.csv"',
        ]);
    }

    public function exportRevenue()
    {
        return redirect()->route('admin.reports.index')
            ->with('warning', 'Revenue export requires payment integration.');
    }
}
