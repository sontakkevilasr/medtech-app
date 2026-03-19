@extends('layouts.admin')
@section('title', 'Platform Reports')
@section('page-title', 'Platform Reports')

@section('content')
<div class="fade-in">

{{-- ── Summary stat grid ────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px">
@php
$summaryCards = [
    ['label'=>'Total Users',          'val'=>$summary['total_users'],         'sub'=>$summary['new_users_30d'].' new in 30 days', 'color'=>'#6366f1'],
    ['label'=>'Verified Doctors',     'val'=>$summary['verified_doctors'],     'sub'=>'of '.$summary['total_doctors'].' total',    'color'=>'#10b981'],
    ['label'=>'Total Appointments',   'val'=>$summary['total_appointments'],   'sub'=>$summary['new_apts_30d'].' in 30 days',     'color'=>'#f59e0b'],
    ['label'=>'Total Prescriptions',  'val'=>$summary['total_prescriptions'],  'sub'=>$summary['completed_apts'].' completed',    'color'=>'#3b82f6'],
];
@endphp
@foreach($summaryCards as $sc)
<div class="stat-card">
    <div style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-weight:500;color:var(--txt);line-height:1">
        {{ number_format($sc['val']) }}
    </div>
    <div style="font-size:.8rem;font-weight:600;color:var(--txt-md);margin-top:5px">{{ $sc['label'] }}</div>
    <div style="font-size:.72rem;color:var(--txt-lt);margin-top:2px">{{ $sc['sub'] }}</div>
    <div style="height:3px;border-radius:2px;background:{{ $sc['color'] }};margin-top:12px;opacity:.7"></div>
</div>
@endforeach
</div>

{{-- ── Row 1: User growth (12m bar) + Appointment types (pie) ─────────────── --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:20px">

    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:14px 20px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;color:var(--txt)">User Registrations</div>
                <div style="font-size:.75rem;color:var(--txt-lt)">Last 12 months</div>
            </div>
            <a href="{{ route('admin.reports.export.users') }}" class="btn btn-ghost" style="font-size:.75rem;padding:5px 12px">
                ↓ Export CSV
            </a>
        </div>
        <div style="padding:20px;height:240px">
            <canvas id="userGrowthChart"></canvas>
        </div>
    </div>

    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:14px 20px;border-bottom:1px solid var(--bd)">
            <div style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;color:var(--txt)">Appointment Types</div>
            <div style="font-size:.75rem;color:var(--txt-lt)">All time</div>
        </div>
        <div style="padding:16px;height:240px;display:flex;align-items:center;justify-content:center">
            <canvas id="aptTypesChart"></canvas>
        </div>
    </div>
</div>

{{-- ── Row 2: Appointment trend (30d line) ─────────────────────────────────── --}}
<div class="card" style="padding:0;overflow:hidden;margin-bottom:20px">
    <div style="padding:14px 20px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between">
        <div>
            <div style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;color:var(--txt)">Appointment Activity</div>
            <div style="font-size:.75rem;color:var(--txt-lt)">Last 30 days — booked vs cancelled vs completed</div>
        </div>
        <div style="display:flex;gap:14px">
            @foreach(['booked'=>['color'=>'#6366f1','label'=>'Booked'],'completed'=>['color'=>'#10b981','label'=>'Completed'],'cancelled'=>['color'=>'#ef4444','label'=>'Cancelled']] as $k=>$cfg)
            <div style="display:flex;align-items:center;gap:5px;font-size:.75rem;color:var(--txt-md)">
                <div style="width:10px;height:3px;border-radius:2px;background:{{ $cfg['color'] }}"></div>
                {{ $cfg['label'] }}
            </div>
            @endforeach
        </div>
    </div>
    <div style="padding:20px;height:220px">
        <canvas id="aptTrendChart"></canvas>
    </div>
</div>

{{-- ── Row 3: Specialization bar + quick stats ─────────────────────────────── --}}
<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:16px">

    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:14px 20px;border-bottom:1px solid var(--bd)">
            <div style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;color:var(--txt)">Top Specializations</div>
            <div style="font-size:.75rem;color:var(--txt-lt)">All doctors (verified + unverified)</div>
        </div>
        <div style="padding:20px;height:260px">
            <canvas id="specChart"></canvas>
        </div>
    </div>

    <div class="card" style="padding:18px 20px">
        <div style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;color:var(--txt);margin-bottom:14px">Platform Health</div>
        @php
        $healthItems = [
            ['label'=>'Doctor Verification Rate', 'val'=>$summary['total_doctors']>0 ? round($summary['verified_doctors']/$summary['total_doctors']*100).'%' : '—', 'color'=>'#10b981'],
            ['label'=>'Premium Doctors',          'val'=>$summary['premium_doctors'], 'color'=>'#6366f1'],
            ['label'=>'Appointment Completion',   'val'=>$summary['total_appointments']>0 ? round($summary['completed_apts']/$summary['total_appointments']*100).'%' : '—', 'color'=>'#3b82f6'],
            ['label'=>'Cancellation Rate',        'val'=>$summary['total_appointments']>0 ? round($summary['cancelled_apts']/$summary['total_appointments']*100).'%' : '—', 'color'=>'#f59e0b'],
            ['label'=>'Patients per Doctor',      'val'=>$summary['total_doctors']>0 ? round($summary['total_patients']/$summary['total_doctors'],1) : '—', 'color'=>'#8b5cf6'],
        ];
        @endphp
        @foreach($healthItems as $hi)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--bd)">
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:3px;height:18px;border-radius:2px;background:{{ $hi['color'] }}"></div>
                <span style="font-size:.8125rem;color:var(--txt-md)">{{ $hi['label'] }}</span>
            </div>
            <span style="font-family:'Cormorant Garamond',serif;font-size:1.25rem;font-weight:500;color:var(--txt)">{{ $hi['val'] }}</span>
        </div>
        @endforeach

        <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--bd)">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:8px">Exports</div>
            <a href="{{ route('admin.reports.export.users') }}" class="btn btn-ghost" style="width:100%;justify-content:center;margin-bottom:6px;font-size:.8rem">
                ↓ Users CSV
            </a>
        </div>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script>
const chartDefaults = {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { labels: { font: { family: 'Outfit', size: 11 }, boxWidth: 10 } } },
    scales: {
        x: { grid: { display: false }, ticks: { font: { family: 'Outfit', size: 10 }, maxTicksLimit: 8 } },
        y: { grid: { color: '#f0f2f8' }, ticks: { font: { family: 'Outfit', size: 10 }, precision: 0 } }
    }
};

// User Growth — stacked bar
new Chart(document.getElementById('userGrowthChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_column($userGrowth, 'label')) !!},
        datasets: [
            { label:'Doctors',  data: {!! json_encode(array_column($userGrowth, 'doctors'))  !!}, backgroundColor:'#6366f1', borderRadius:4, borderSkipped:false },
            { label:'Patients', data: {!! json_encode(array_column($userGrowth, 'patients')) !!}, backgroundColor:'#10b981', borderRadius:4, borderSkipped:false },
        ]
    },
    options: { ...chartDefaults }
});

// Appointment types — donut
const aptTypesRaw = @json($aptTypes);
const typeLabels = Object.keys(aptTypesRaw).map(t => t.replace('_',' ').replace(/^\w/, c => c.toUpperCase()));
new Chart(document.getElementById('aptTypesChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: typeLabels,
        datasets: [{ data: Object.values(aptTypesRaw), backgroundColor:['#6366f1','#10b981','#f59e0b','#ef4444'], borderWidth:0 }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position:'bottom', labels: { font:{ family:'Outfit', size:11 }, boxWidth:10, padding:14 } } }
    }
});

// Appointment trend — multi-line
new Chart(document.getElementById('aptTrendChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($aptTrend, 'label')) !!},
        datasets: [
            { label:'Booked',    data:{!! json_encode(array_column($aptTrend,'booked'))    !!}, borderColor:'#6366f1', backgroundColor:'rgba(99,102,241,.06)', fill:true,  tension:0.4, pointRadius:2, pointHoverRadius:5 },
            { label:'Completed', data:{!! json_encode(array_column($aptTrend,'completed')) !!}, borderColor:'#10b981', backgroundColor:'transparent', fill:false, tension:0.4, pointRadius:2, pointHoverRadius:5 },
            { label:'Cancelled', data:{!! json_encode(array_column($aptTrend,'cancelled')) !!}, borderColor:'#ef4444', backgroundColor:'transparent', fill:false, tension:0.4, pointRadius:2, pointHoverRadius:5, borderDash:[4,3] },
        ]
    },
    options: { ...chartDefaults }
});

// Specializations — horizontal bar
const specRaw = @json($specData);
new Chart(document.getElementById('specChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: Object.keys(specRaw),
        datasets: [{ label:'Doctors', data:Object.values(specRaw),
            backgroundColor:['#6366f1','#10b981','#f59e0b','#ef4444','#3b82f6','#8b5cf6','#ec4899','#14b8a6','#f97316','#84cc16'],
            borderRadius:4, borderSkipped:false }]
    },
    options: {
        ...chartDefaults,
        indexAxis: 'y',
        plugins: { legend: { display:false } },
        scales: {
            x: { grid:{ color:'#f0f2f8' }, ticks:{ font:{ family:'Outfit', size:10 }, precision:0 } },
            y: { grid:{ display:false }, ticks:{ font:{ family:'Outfit', size:10 } } }
        }
    }
});
</script>
@endpush
