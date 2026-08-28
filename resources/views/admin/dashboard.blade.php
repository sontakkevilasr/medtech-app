@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Platform Dashboard')

@section('content')

{{-- ── KPI Row ──────────────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:22px">

    @php
    $kpis = [
        ['label'=>'Total Doctors',      'val'=>$stats['total_doctors'],    'sub'=>$stats['verified_doctors'].' verified',        'icon'=>'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'color'=>'#6366f1','bg'=>'#e0e7ff'],
        ['label'=>'Total Patients',     'val'=>$stats['total_patients'],   'sub'=>$stats['active_users'].' active',              'icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',                                                                                                                                                                                                           'color'=>'#10b981','bg'=>'#d1fae5'],
        ['label'=>'Appointments Today', 'val'=>$stats['appointments_today'], 'sub'=>$stats['appointments_month'].' this month',  'icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',                                                                                                                                                                                 'color'=>'#f59e0b','bg'=>'#fef9c3'],
        ['label'=>'Pending Verification','val'=>$stats['pending_verification'],'sub'=>'doctors awaiting review',                 'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4','color'=>'#ef4444','bg'=>'#fee2e2'],
    ];
    @endphp

    @foreach($kpis as $kpi)
    <div class="stat-card">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px">
            <div style="width:40px;height:40px;border-radius:11px;background:{{ $kpi['bg'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="{{ $kpi['color'] }}" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $kpi['icon'] }}"/>
                </svg>
            </div>
        </div>
        <div style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-weight:500;color:var(--txt);line-height:1">
            {{ number_format($kpi['val']) }}
        </div>
        <div style="font-size:.8125rem;font-weight:600;color:var(--txt-md);margin-top:4px">{{ $kpi['label'] }}</div>
        <div style="font-size:.75rem;color:var(--txt-lt);margin-top:2px">{{ $kpi['sub'] }}</div>
    </div>
    @endforeach
</div>

{{-- ── Row 2: Charts ────────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:22px">

    {{-- User Growth Chart --}}
    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:16px 20px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;color:var(--txt)">User Growth</div>
                <div style="font-size:.75rem;color:var(--txt-lt);margin-top:1px">New registrations over last 6 months</div>
            </div>
        </div>
        <div style="padding:20px;height:220px">
            <canvas id="userGrowthChart"></canvas>
        </div>
    </div>

    {{-- Specialization donut --}}
    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:16px 20px;border-bottom:1px solid var(--bd)">
            <div style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;color:var(--txt)">Specializations</div>
            <div style="font-size:.75rem;color:var(--txt-lt);margin-top:1px">Verified doctors by specialty</div>
        </div>
        <div style="padding:16px;display:flex;flex-direction:column;gap:8px;max-height:220px;overflow-y:auto">
            @php
                $specColors = ['#6366f1','#10b981','#f59e0b','#ef4444','#3b82f6','#8b5cf6','#ec4899','#14b8a6'];
            @endphp
            @foreach($specializations as $spec => $count)
            @php $pct = $stats['verified_doctors'] > 0 ? round($count / $stats['verified_doctors'] * 100) : 0; @endphp
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:10px;height:10px;border-radius:3px;background:{{ $specColors[$loop->index % count($specColors)] }};flex-shrink:0"></div>
                <div style="flex:1;min-width:0">
                    <div style="display:flex;justify-content:space-between;margin-bottom:3px">
                        <span style="font-size:.75rem;color:var(--txt-md);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:130px">{{ $spec }}</span>
                        <span style="font-size:.75rem;font-weight:600;color:var(--txt)">{{ $count }}</span>
                    </div>
                    <div style="height:3px;background:var(--bd);border-radius:2px;overflow:hidden">
                        <div style="height:100%;background:{{ $specColors[$loop->index % count($specColors)] }};width:{{ $pct }}%"></div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Row 3: Appointment trend + Recent --}}
<div style="display:grid;grid-template-columns:1.2fr 1fr;gap:16px;margin-bottom:22px">

    {{-- Appointment Trend (7-day bar) --}}
    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:16px 20px;border-bottom:1px solid var(--bd)">
            <div style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;color:var(--txt)">Appointments — Last 7 Days</div>
        </div>
        <div style="padding:20px;height:200px">
            <canvas id="aptTrendChart"></canvas>
        </div>
    </div>

    {{-- Pending verifications mini list --}}
    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:14px 18px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between">
            <div style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;color:var(--txt)">Pending Verifications</div>
            <a href="{{ route('admin.verification.pending') }}" class="btn btn-ghost" style="font-size:.72rem;padding:4px 10px">View all</a>
        </div>
        @if($pendingDoctors->isEmpty())
        <div style="padding:28px;text-align:center;color:var(--txt-lt);font-size:.875rem">
            ✓ All doctors verified
        </div>
        @else
        @foreach($pendingDoctors as $dp)
        <div style="display:flex;align-items:center;gap:10px;padding:11px 18px;border-bottom:1px solid var(--bd);transition:background .1s" onmouseover="this.style.background='#f8f9fc'" onmouseout="this.style.background='transparent'">
            <div style="width:34px;height:34px;border-radius:9px;background:#e0e7ff;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;color:#6366f1;flex-shrink:0">
                {{ strtoupper(substr($dp->user?->profile?->full_name ?? 'D', 0, 1)) }}
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:.875rem;font-weight:500;color:var(--txt);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    Dr. {{ $dp->user?->profile?->full_name ?? 'Unknown' }}
                </div>
                <div style="font-size:.72rem;color:var(--txt-lt)">{{ $dp->specialization }} · Reg: {{ $dp->registration_number ?? '—' }}</div>
            </div>
            <a href="{{ route('admin.verification.show', $dp->user_id) }}" class="btn btn-primary" style="font-size:.72rem;padding:4px 10px;white-space:nowrap">Review</a>
        </div>
        @endforeach
        @endif
    </div>
</div>

{{-- ── Row 4: Recent Registrations ─────────────────────────────────────────── --}}
<div class="card" style="padding:0;overflow:hidden">
    <div style="padding:14px 20px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between">
        <div style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;color:var(--txt)">Recent Registrations</div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost" style="font-size:.72rem;padding:4px 10px">View all</a>
    </div>
    <table class="admin-table">
        <thead><tr>
            <th>User</th><th>Role</th><th>Mobile</th><th>Joined</th><th>Status</th><th></th>
        </tr></thead>
        <tbody>
            @foreach($recentUsers as $user)
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:9px">
                        @php
                            $n = $user->profile?->full_name ?? 'Unknown';
                            $colors = ['#6366f1','#10b981','#f59e0b','#ef4444','#3b82f6'];
                            $c = $colors[$user->id % 5];
                        @endphp
                        <div style="width:30px;height:30px;border-radius:8px;background:{{ $c }};display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:#fff;flex-shrink:0">
                            {{ strtoupper(substr($n,0,1)) }}
                        </div>
                        <div>
                            <div style="font-weight:500;color:var(--txt);font-size:.875rem">{{ $n }}</div>
                            @if($user->isDoctor() && $user->doctorProfile?->specialization)
                            <div style="font-size:.7rem;color:var(--txt-lt)">{{ $user->doctorProfile->specialization }}</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge {{ $user->isDoctor() ? 'badge-purple' : 'badge-blue' }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </td>
                <td style="font-family:monospace;font-size:.82rem">{{ $user->country_code }} {{ $user->mobile_number }}</td>
                <td style="color:var(--txt-lt);font-size:.8rem">{{ $user->created_at->format('d M Y') }}</td>
                <td>
                    <span class="badge {{ $user->is_active ? 'badge-green' : 'badge-red' }}">
                        {{ $user->is_active ? 'Active' : 'Suspended' }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost" style="font-size:.72rem;padding:4px 10px">View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection

@push('scripts')
<script>
// User Growth Chart
const ugCtx = document.getElementById('userGrowthChart').getContext('2d');
new Chart(ugCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_column($userGrowth, 'label')) !!},
        datasets: [
            {
                label: 'Doctors',
                data: {!! json_encode(array_column($userGrowth, 'doctors')) !!},
                backgroundColor: '#6366f1',
                borderRadius: 5, borderSkipped: false,
            },
            {
                label: 'Patients',
                data: {!! json_encode(array_column($userGrowth, 'patients')) !!},
                backgroundColor: '#10b981',
                borderRadius: 5, borderSkipped: false,
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { font: { family: 'Outfit', size: 11 }, boxWidth: 10 } } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { family: 'Outfit', size: 11 } } },
            y: { grid: { color: '#f0f2f8' }, ticks: { font: { family: 'Outfit', size: 11 }, precision: 0 } }
        }
    }
});

// Appointment Trend
const atCtx = document.getElementById('aptTrendChart').getContext('2d');
new Chart(atCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($aptTrend, 'label')) !!},
        datasets: [
            {
                label: 'Booked',
                data: {!! json_encode(array_column($aptTrend, 'total')) !!},
                borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,.08)',
                fill: true, tension: 0.4, pointRadius: 4, pointBackgroundColor: '#6366f1',
            },
            {
                label: 'Cancelled',
                data: {!! json_encode(array_column($aptTrend, 'cancelled')) !!},
                borderColor: '#ef4444', backgroundColor: 'transparent',
                fill: false, tension: 0.4, pointRadius: 4, pointBackgroundColor: '#ef4444',
                borderDash: [4,3],
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { font: { family: 'Outfit', size: 11 }, boxWidth: 10 } } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { family: 'Outfit', size: 11 } } },
            y: { grid: { color: '#f0f2f8' }, ticks: { font: { family: 'Outfit', size: 11 }, precision: 0 } }
        }
    }
});
</script>
@endpush
