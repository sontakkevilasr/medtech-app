@extends('layouts.doctor')
@section('title', 'Analytics')
@section('page-title', 'Analytics')

@section('content')

{{-- KPI Cards --}}
<div class="stat-grid" style="margin-bottom:24px">
    <div class="stat-card">
        <div class="stat-label">Total Appointments</div>
        <div class="stat-value">{{ number_format($totalAppointments) }}</div>
        <div class="stat-sub">{{ $completedAppointments }} completed · {{ $cancelledAppointments }} cancelled</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value">₹{{ number_format($totalRevenue) }}</div>
        <div class="stat-sub">all time collected</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Patients</div>
        <div class="stat-value">{{ number_format($totalPatients) }}</div>
        <div class="stat-sub">{{ $newPatientsThisMonth }} new this month</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Prescriptions</div>
        <div class="stat-value">{{ number_format($totalPrescriptions) }}</div>
        <div class="stat-sub">{{ $prescriptionsThisMonth }} this month</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start">

    {{-- Appointment trend --}}
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title">Appointments — Last 12 Months</div>
        </div>
        @php $maxApt = $appointmentChart->max('total') ?: 1; @endphp
        <div style="padding:16px 20px">
            <div style="display:flex;align-items:flex-end;gap:6px;height:80px">
                @foreach($appointmentChart as $bar)
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:3px" title="{{ $bar['month'] }}: {{ $bar['total'] }} appointments">
                    <div style="width:100%;background:var(--leaf);border-radius:3px 3px 0 0;height:{{ max(3, round(($bar['total'] / $maxApt) * 70)) }}px"></div>
                </div>
                @endforeach
            </div>
            <div style="display:flex;gap:6px;margin-top:4px">
                @foreach($appointmentChart as $bar)
                <div style="flex:1;text-align:center;font-size:.55rem;color:var(--txt-lt)">{{ explode(' ', $bar['month'])[0] }}</div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Revenue trend --}}
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title">Revenue — Last 12 Months</div>
        </div>
        @php $maxRev = $revenueChart->max('amount') ?: 1; @endphp
        <div style="padding:16px 20px">
            <div style="display:flex;align-items:flex-end;gap:6px;height:80px">
                @foreach($revenueChart as $bar)
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:3px" title="{{ $bar['month'] }}: ₹{{ number_format($bar['amount']) }}">
                    <div style="width:100%;background:var(--amber);border-radius:3px 3px 0 0;height:{{ max(3, round(($bar['amount'] / $maxRev) * 70)) }}px"></div>
                </div>
                @endforeach
            </div>
            <div style="display:flex;gap:6px;margin-top:4px">
                @foreach($revenueChart as $bar)
                <div style="flex:1;text-align:center;font-size:.55rem;color:var(--txt-lt)">{{ explode(' ', $bar['month'])[0] }}</div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Visit type breakdown --}}
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title">Visit Types</div>
        </div>
        <div style="padding:16px 20px">
            @forelse($visitTypes as $type => $count)
            @php $pct = $totalAppointments > 0 ? round(($count / $totalAppointments) * 100) : 0; @endphp
            <div style="margin-bottom:12px">
                <div style="display:flex;justify-content:space-between;font-size:.8rem;margin-bottom:4px">
                    <span style="color:var(--txt-md)">{{ ucfirst(str_replace('_',' ',$type ?? 'in_person')) }}</span>
                    <span style="color:var(--txt-lt)">{{ $count }} ({{ $pct }}%)</span>
                </div>
                <div style="height:6px;background:var(--warm-bd);border-radius:3px;overflow:hidden">
                    <div style="height:100%;background:var(--leaf);border-radius:3px;width:{{ $pct }}%"></div>
                </div>
            </div>
            @empty
            <div style="color:var(--txt-lt);font-size:.85rem">No data yet.</div>
            @endforelse
        </div>
    </div>

    {{-- Completion rate --}}
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title">Appointment Completion Rate</div>
        </div>
        <div style="padding:24px 20px;text-align:center">
            @php $rate = $totalAppointments > 0 ? round(($completedAppointments / $totalAppointments) * 100) : 0; @endphp
            <svg width="100" height="100" viewBox="0 0 100 100" style="display:block;margin:0 auto 12px">
                <circle cx="50" cy="50" r="40" fill="none" stroke="var(--warm-bd)" stroke-width="8"/>
                <circle cx="50" cy="50" r="40" fill="none" stroke="var(--leaf)" stroke-width="8"
                        stroke-dasharray="{{ round(2 * 3.14159 * 40) }}"
                        stroke-dashoffset="{{ round(2 * 3.14159 * 40 * (1 - $rate/100)) }}"
                        stroke-linecap="round" transform="rotate(-90 50 50)"/>
                <text x="50" y="55" text-anchor="middle" font-size="18" font-weight="600" fill="var(--txt)">{{ $rate }}%</text>
            </svg>
            <div style="font-size:.8rem;color:var(--txt-lt)">{{ $completedAppointments }} completed of {{ $totalAppointments }} total</div>
        </div>
    </div>

</div>
@endsection
