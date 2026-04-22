<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\HealthLog;
use App\Models\FamilyMember;
use App\Enums\HealthLogType;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HealthLogController extends Controller
{
    // ── Main dashboard ────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $patient   = auth()->user()->load('familyMembers');
        $memberId  = $request->get('member'); // null = self
        $member    = $memberId
            ? $patient->familyMembers()->findOrFail($memberId)
            : null;

        // Latest reading per vital type
        $latestReadings = $this->latestByType($patient->id, $memberId);

        // Last 30-day logs (most recent first) for the log table
        $logs = HealthLog::where('patient_user_id', $patient->id)
            ->when($memberId, fn($q) => $q->where('family_member_id', $memberId),
                             fn($q) => $q->whereNull('family_member_id'))
            ->orderByDesc('logged_at')
            ->limit(50)
            ->get();

        // 30-day summary stats
        $stats = $this->summaryStats($patient->id, $memberId);

        return view('patient.health.index', compact(
            'patient', 'member', 'memberId',
            'latestReadings', 'logs', 'stats'
        ));
    }

    // ── Log table (paginated, for full history) ───────────────────────────────

    public function logs(Request $request)
    {
        $patient  = auth()->user();
        $memberId = $request->get('member');
        $type     = $request->get('type');

        $logs = HealthLog::where('patient_user_id', $patient->id)
            ->when($memberId, fn($q) => $q->where('family_member_id', $memberId),
                             fn($q) => $q->whereNull('family_member_id'))
            ->when($type, fn($q) => $q->where('log_type', $type))
            ->orderByDesc('logged_at')
            ->paginate(20)
            ->withQueryString();

        return view('patient.health.logs', compact('logs', 'memberId', 'type'));
    }

    // ── Store a new log entry ─────────────────────────────────────────────────

    public function store(Request $request)
    {
        $type = HealthLogType::from($request->log_type);

        $rules = [
            'log_type'  => ['required', 'in:bp,sugar,weight,oxygen,temperature,pulse'],
            'value_1'   => ['required', 'numeric', 'min:0', 'max:999'],
            'context'   => ['nullable', 'in:fasting,post_meal,random,morning,night,other'],
            'notes'     => ['nullable', 'string', 'max:255'],
            'logged_at' => ['nullable', 'date'],
        ];

        if ($type->hasTwoValues()) {
            $rules['value_2'] = ['required', 'numeric', 'min:0', 'max:999'];
        }

        $request->validate($rules);

        HealthLog::create([
            'patient_user_id'  => auth()->id(),
            'family_member_id' => $request->family_member_id ?: null,
            'log_type'         => $type->value,
            'value_1'          => $request->value_1,
            'value_2'          => $type->hasTwoValues() ? $request->value_2 : null,
            'unit'             => $type->unit(),
            'context'          => $request->context,
            'notes'            => $request->notes,
            'logged_at'        => $request->logged_at ? Carbon::parse($request->logged_at) : now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Reading logged.']);
        }

        return back()->with('success', ucfirst($type->value).' reading logged successfully.');
    }

    // ── Delete a log ──────────────────────────────────────────────────────────

    public function destroy(HealthLog $log)
    {
        if ($log->patient_user_id !== auth()->id()) abort(403);
        $log->delete();

        return back()->with('success', 'Reading deleted.');
    }

    // ── AJAX: chart data for a given type (last N days) ───────────────────────

    public function chartData(Request $request, string $type)
    {
        abort_unless(in_array($type, ['bp','sugar','weight','oxygen','temperature','pulse']), 404);

        $patient  = auth()->user();
        $days     = min((int) $request->get('days', 30), 90);
        $memberId = $request->get('member');

        $logs = HealthLog::where('patient_user_id', $patient->id)
            ->where('log_type', $type)
            ->when($memberId, fn($q) => $q->where('family_member_id', $memberId),
                             fn($q) => $q->whereNull('family_member_id'))
            ->where('logged_at', '>=', now()->subDays($days))
            ->orderBy('logged_at')
            ->get(['logged_at', 'value_1', 'value_2', 'context']);

        $data = $logs->map(fn($l) => [
            'date'    => $l->logged_at->format('d M'),
            'ts'      => $l->logged_at->toISOString(),
            'v1'      => (float) $l->value_1,
            'v2'      => $l->value_2 ? (float) $l->value_2 : null,
            'context' => $l->context,
        ]);

        // Reference ranges from config
        $ranges = config('medtech.health_ranges', []);

        return response()->json([
            'type'   => $type,
            'days'   => $days,
            'data'   => $data,
            'ranges' => $ranges,
            'count'  => $logs->count(),
        ]);
    }

    // ── Family member logs ────────────────────────────────────────────────────

    public function memberLogs(Request $request, int $member)
    {
        $fm = auth()->user()->familyMembers()->findOrFail($member);
        return redirect()->route('patient.health.index', ['member' => $fm->id]);
    }

    public function storeMemberLog(Request $request, int $member)
    {
        auth()->user()->familyMembers()->findOrFail($member); // ownership check
        $request->merge(['family_member_id' => $member]);
        return $this->store($request);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function latestByType(int $patientId, ?int $memberId): array
    {
        $results = [];
        foreach (['bp','sugar','weight','oxygen','temperature','pulse'] as $type) {
            $log = HealthLog::where('patient_user_id', $patientId)
                ->where('log_type', $type)
                ->when($memberId, fn($q) => $q->where('family_member_id', $memberId),
                                 fn($q) => $q->whereNull('family_member_id'))
                ->orderByDesc('logged_at')
                ->first();

            $results[$type] = $log ? [
                'value'     => $log->formatted_value,
                'v1'        => (float) $log->value_1,
                'v2'        => $log->value_2 ? (float) $log->value_2 : null,
                'logged_at' => $log->logged_at,
                'context'   => $log->context,
                'status'    => $this->vitalStatus($type, (float) $log->value_1, $log->value_2 ? (float) $log->value_2 : null),
            ] : null;
        }
        return $results;
    }

    private function summaryStats(int $patientId, ?int $memberId): array
    {
        $base = HealthLog::where('patient_user_id', $patientId)
            ->when($memberId, fn($q) => $q->where('family_member_id', $memberId),
                             fn($q) => $q->whereNull('family_member_id'));

        return [
            'total_logs'   => (clone $base)->count(),
            'logs_30d'     => (clone $base)->where('logged_at', '>=', now()->subDays(30))->count(),
            'logs_7d'      => (clone $base)->where('logged_at', '>=', now()->subDays(7))->count(),
            'types_tracked'=> (clone $base)->distinct('log_type')->count('log_type'),
            'last_logged'  => (clone $base)->max('logged_at'),
        ];
    }

    private function vitalStatus(string $type, float $v1, ?float $v2): string
    {
        $ranges = config('medtech.health_ranges', []);

        if ($type === 'bp') {
            $sys = $this->rangeStatus($ranges['bp_systolic'] ?? [], $v1);
            $dia = $v2 ? $this->rangeStatus($ranges['bp_diastolic'] ?? [], $v2) : 'ok';
            return ($sys === 'danger' || $dia === 'danger') ? 'danger'
                : (($sys === 'warning' || $dia === 'warning') ? 'warning' : 'ok');
        }

        $rangeKey = match($type) {
            'sugar'       => 'sugar_fasting',
            'oxygen'      => 'oxygen',
            'pulse'       => 'pulse',
            'temperature' => 'temperature',
            default       => null,
        };

        return $rangeKey ? $this->rangeStatus($ranges[$rangeKey] ?? [], $v1) : 'ok';
    }

    private function rangeStatus(array $range, float $val): string
    {
        if (empty($range)) return 'ok';

        // Oxygen: lower is worse
        $isOxygen = isset($range['normal']) && $range['normal'][0] > $range['danger'][0] ?? false;

        if (isset($range['normal']) && $val >= $range['normal'][0] && $val <= $range['normal'][1]) return 'ok';
        if (isset($range['warning']) && $val >= $range['warning'][0] && $val <= $range['warning'][1]) return 'warning';
        return 'danger';
    }
}
