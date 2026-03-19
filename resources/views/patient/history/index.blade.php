@extends('layouts.patient')
@section('title', isset($fm) ? $fm->full_name.'\'s History' : 'Medical History')
@section('page-title', isset($fm) ? $fm->full_name.'\'s History' : 'Medical History')

@section('content')
@php
$vtColors = [
    'consultation'    => ['color'=>'#3d7a6e','bg'=>'#eef5f3'],
    'follow_up'       => ['color'=>'#6a9e8e','bg'=>'#eef5f3'],
    'emergency'       => ['color'=>'#c0737a','bg'=>'#fce7ef'],
    'procedure'       => ['color'=>'#4a3760','bg'=>'#f4f0fa'],
    'teleconsultation'=> ['color'=>'#3d5e7a','bg'=>'#e8f0f9'],
];
@endphp

<div class="fade-slide">

{{-- ── Family member switcher ───────────────────────────────────────────────── --}}
@if($patient->familyMembers->isNotEmpty())
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:18px">
    <a href="{{ route('patient.history.index') }}"
       style="padding:5px 14px;border-radius:20px;font-size:.8rem;font-weight:500;text-decoration:none;border:1.5px solid {{ !isset($fm) ? 'var(--plum)' : 'var(--warm-bd)' }};background:{{ !isset($fm) ? 'var(--plum)' : 'transparent' }};color:{{ !isset($fm) ? '#fff' : 'var(--txt-md)' }}">
        {{ $patient->profile?->full_name ?? 'Me' }}
    </a>
    @foreach($patient->familyMembers as $member)
    <a href="{{ route('patient.history.member', $member->id) }}"
       style="padding:5px 14px;border-radius:20px;font-size:.8rem;font-weight:500;text-decoration:none;border:1.5px solid {{ (isset($fm) && $fm->id === $member->id) ? 'var(--plum)' : 'var(--warm-bd)' }};background:{{ (isset($fm) && $fm->id === $member->id) ? 'var(--plum)' : 'transparent' }};color:{{ (isset($fm) && $fm->id === $member->id) ? '#fff' : 'var(--txt-md)' }}">
        {{ $member->full_name }}
    </a>
    @endforeach
</div>
@endif

{{-- ── Stats strip ──────────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-bottom:22px">
    @php
    $strips = [
        ['v' => $stats['total_visits'],        'l' => 'Total Visits',       'icon' => '🏥'],
        ['v' => $stats['visits_this_year'],     'l' => 'This Year',          'icon' => '📅'],
        ['v' => $stats['total_prescriptions'],  'l' => 'Prescriptions',      'icon' => '📋'],
        ['v' => $stats['doctors_seen'],         'l' => 'Doctors Seen',       'icon' => '👨‍⚕️'],
        ['v' => $stats['last_visit'] ? \Carbon\Carbon::parse($stats['last_visit'])->format('d M') : '—',
         'l' => 'Last Visit', 'icon' => '🕐', 'raw' => true],
    ];
    @endphp
    @foreach($strips as $s)
    <div class="panel" style="padding:12px 14px;text-align:center">
        <div style="font-size:1.1rem;margin-bottom:4px">{{ $s['icon'] }}</div>
        <div style="font-family:'Lora',serif;font-size:{{ ($s['raw'] ?? false) ? '1rem' : '1.6rem' }};font-weight:500;color:var(--txt);line-height:1.1">
            {{ $s['v'] }}
        </div>
        <div style="font-size:.68rem;font-weight:600;color:var(--txt-lt);margin-top:3px;text-transform:uppercase;letter-spacing:.04em">{{ $s['l'] }}</div>
    </div>
    @endforeach
</div>

{{-- ── Filter bar ───────────────────────────────────────────────────────────── --}}
@php $baseParams = isset($fm) ? ['member' => $fm->id] : []; @endphp
<div style="display:flex;gap:14px;margin-bottom:20px;flex-wrap:wrap;align-items:center">

    {{-- Type filter --}}
    <div style="display:flex;gap:5px">
        @foreach([null => 'All', 'record' => '🏥 Visits', 'prescription' => '📋 Prescriptions'] as $val => $lbl)
        <a href="{{ route(isset($fm) ? 'patient.history.member' : 'patient.history.index', array_filter(array_merge($baseParams, ['type'=>$val,'filter'=>$filter]))) }}"
           style="padding:5px 12px;border-radius:7px;font-size:.78rem;font-weight:500;text-decoration:none;border:1.5px solid {{ $type===$val ? 'var(--plum)' : 'var(--warm-bd)' }};background:{{ $type===$val ? 'var(--plum)' : 'transparent' }};color:{{ $type===$val ? '#fff' : 'var(--txt-md)' }};transition:all .12s">
            {{ $lbl }}
        </a>
        @endforeach
    </div>

    {{-- Date filter --}}
    <div style="display:flex;gap:5px;margin-left:auto">
        @foreach([null => 'All time', 'thisYear' => 'This year', 'last6m' => 'Last 6m', 'last30d' => 'Last 30d'] as $val => $lbl)
        <a href="{{ route(isset($fm) ? 'patient.history.member' : 'patient.history.index', array_filter(array_merge($baseParams, ['type'=>$type,'filter'=>$val]))) }}"
           style="padding:5px 12px;border-radius:7px;font-size:.75rem;font-weight:500;text-decoration:none;border:1.5px solid {{ $filter===$val ? 'var(--plum)' : 'var(--warm-bd)' }};background:{{ $filter===$val ? 'var(--plum)' : 'transparent' }};color:{{ $filter===$val ? '#fff' : 'var(--txt-md)' }};transition:all .12s">
            {{ $lbl }}
        </a>
        @endforeach
    </div>
</div>

{{-- ── Timeline ─────────────────────────────────────────────────────────────── --}}
@if($timeline->isEmpty())
<div class="panel" style="padding:44px 24px;text-align:center;color:var(--txt-lt)">
    <div style="font-size:2.5rem;margin-bottom:12px">📭</div>
    <div style="font-family:'Lora',serif;font-size:1rem;color:var(--txt-md)">No history found</div>
    <p style="font-size:.8rem;margin-top:4px">
        {{ $type || $filter ? 'Try clearing the filters.' : 'Records will appear here after doctor visits.' }}
    </p>
</div>
@else

{{-- Group by year-month --}}
@php
$grouped = $timeline->groupBy(fn($item) => $item['date']->format('M Y'));
@endphp

@foreach($grouped as $monthYear => $items)
<div style="margin-bottom:26px">
    {{-- Month header --}}
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
        <div style="font-family:'Lora',serif;font-size:.95rem;font-weight:500;color:var(--txt)">{{ $monthYear }}</div>
        <div style="flex:1;height:1px;background:var(--warm-bd)"></div>
        <div style="font-size:.7rem;color:var(--txt-lt)">{{ $items->count() }} {{ Str::plural('entry', $items->count()) }}</div>
    </div>

    <div style="display:flex;flex-direction:column;gap:8px">
    @foreach($items as $item)
    @if($item['type'] === 'record')
    {{-- ── Medical Record row ──────────────────────────────────────────────── --}}
    @php $vt = $vtColors[$item['object']->visit_type] ?? $vtColors['consultation']; @endphp
    <a href="{{ route(isset($fm) ? 'patient.history.member.record' : 'patient.history.show', isset($fm) ? [$fm->id, $item['object']->id] : $item['object']->id) }}"
       style="text-decoration:none">
    <div class="panel" style="padding:14px 18px;display:flex;align-items:center;gap:14px;transition:box-shadow .15s"
         onmouseover="this.style.boxShadow='0 3px 16px rgba(74,55,96,.1)'" onmouseout="this.style.boxShadow='none'">

        {{-- Icon --}}
        <div style="width:42px;height:42px;border-radius:11px;background:{{ $vt['bg'] }};display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0">
            🏥
        </div>

        {{-- Info --}}
        <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;gap:7px;flex-wrap:wrap;margin-bottom:3px">
                <span style="font-weight:600;font-size:.9rem;color:var(--txt)">
                    {{ Str::limit($item['label'], 70) }}
                </span>
                <span style="font-size:.68rem;font-weight:700;padding:2px 8px;border-radius:20px;background:{{ $vt['bg'] }};color:{{ $vt['color'] }}">
                    {{ ucwords(str_replace('_',' ',$item['object']->visit_type)) }}
                </span>
            </div>
            <div style="font-size:.75rem;color:var(--txt-lt);display:flex;gap:10px;flex-wrap:wrap">
                <span>Dr. {{ $item['doctor'] }}</span>
                @if($item['spec'])<span>{{ $item['spec'] }}</span>@endif
                @if($item['object']->vitals)<span>🔬 Vitals</span>@endif
                @if($item['object']->attachments && count($item['object']->attachments))
                <span>📎 {{ count($item['object']->attachments) }} file{{ count($item['object']->attachments)>1?'s':'' }}</span>
                @endif
                @if($item['object']->follow_up_date)
                <span>📅 Follow-up {{ $item['object']->follow_up_date->format('d M Y') }}</span>
                @endif
            </div>
        </div>

        {{-- Date + arrow --}}
        <div style="text-align:right;flex-shrink:0">
            <div style="font-size:.75rem;font-weight:600;color:var(--txt-md)">{{ $item['date']->format('d M') }}</div>
            <div style="font-size:.68rem;color:var(--txt-lt)">{{ $item['date']->format('Y') }}</div>
        </div>
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--txt-lt);flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </div>
    </a>

    @else
    {{-- ── Prescription row ─────────────────────────────────────────────────── --}}
    <div class="panel" style="padding:14px 18px;display:flex;align-items:center;gap:14px">

        {{-- Icon --}}
        <div style="width:42px;height:42px;border-radius:11px;background:#f4f0fa;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0">
            📋
        </div>

        {{-- Info --}}
        <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;gap:7px;flex-wrap:wrap;margin-bottom:3px">
                <span style="font-weight:600;font-size:.875rem;color:var(--txt)">{{ $item['object']->prescription_number }}</span>
                <span style="font-size:.68rem;font-weight:700;padding:2px 8px;border-radius:20px;background:#f4f0fa;color:#4a3760">Prescription</span>
                @if($item['object']->is_sent_whatsapp)
                <span style="font-size:.65rem;padding:2px 7px;border-radius:20px;background:#d1fae5;color:#065f46">✓ WhatsApp</span>
                @endif
            </div>
            <div style="font-size:.75rem;color:var(--txt-lt)">
                <span>Dr. {{ $item['doctor'] }}</span>
                @if($item['spec'])<span style="margin-left:8px">{{ $item['spec'] }}</span>@endif
            </div>
            {{-- Medicine pills --}}
            @if($item['object']->medicines->isNotEmpty())
            <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:5px">
                @foreach($item['object']->medicines->take(4) as $med)
                <span style="font-size:.68rem;padding:2px 8px;border-radius:20px;background:var(--parch);color:var(--txt-md);border:1px solid var(--warm-bd)">
                    {{ $med->medicine_name }}
                </span>
                @endforeach
                @if($item['object']->medicines->count() > 4)
                <span style="font-size:.68rem;color:var(--txt-lt)">+{{ $item['object']->medicines->count()-4 }} more</span>
                @endif
            </div>
            @endif
        </div>

        {{-- Date + download --}}
        <div style="text-align:right;flex-shrink:0;display:flex;flex-direction:column;align-items:flex-end;gap:5px">
            <div>
                <div style="font-size:.75rem;font-weight:600;color:var(--txt-md)">{{ $item['date']->format('d M') }}</div>
                <div style="font-size:.68rem;color:var(--txt-lt)">{{ $item['date']->format('Y') }}</div>
            </div>
            <a href="{{ route('patient.history.prescription.pdf', $item['object']->id) }}"
               style="font-size:.7rem;padding:4px 11px;background:var(--plum);color:#fff;border-radius:7px;text-decoration:none;font-weight:600;transition:opacity .12s"
               onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                ↓ PDF
            </a>
        </div>
    </div>
    @endif

    @endforeach
    </div>
</div>
@endforeach

@endif

</div>
@endsection
