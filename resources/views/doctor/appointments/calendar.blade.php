@extends('layouts.doctor')
@section('title', 'Appointments')
@section('page-title', 'Appointments')

@section('content')
@php
    [$yr, $mon] = explode('-', $month);
    $prev = \Carbon\Carbon::create($yr, $mon, 1)->subMonth()->format('Y-m');
    $next = \Carbon\Carbon::create($yr, $mon, 1)->addMonth()->format('Y-m');
    $today = today()->format('Y-m-d');

    // Build 42-cell grid (Mon-first)
    $firstDay = \Carbon\Carbon::create($yr, $mon, 1);
    $startDow = $firstDay->dayOfWeek === 0 ? 6 : $firstDay->dayOfWeek - 1;
    $daysInMon = \Carbon\Carbon::create($yr, $mon, 1)->daysInMonth;
    $statusColors = ['booked'=>'#f59e0b','confirmed'=>'#10b981','completed'=>'#0ea5e9','cancelled'=>'#ef4444'];
@endphp
<div class="fade-in">

{{-- Month nav + view switcher --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px">
    <div style="display:flex;align-items:center;gap:10px">
        <a href="{{ route('doctor.appointments.calendar', ['month' => $prev]) }}"
           style="width:34px;height:34px;display:flex;align-items:center;justify-content:center;border:1.5px solid var(--warm-bd);border-radius:9px;text-decoration:none;color:var(--txt-md);transition:background .12s"
           onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;font-weight:500;color:var(--txt)">
            {{ $start->format('F Y') }}
        </h2>
        <a href="{{ route('doctor.appointments.calendar', ['month' => $next]) }}"
           style="width:34px;height:34px;display:flex;align-items:center;justify-content:center;border:1.5px solid var(--warm-bd);border-radius:9px;text-decoration:none;color:var(--txt-md);transition:background .12s"
           onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
        <a href="{{ route('doctor.appointments.calendar', ['month' => today()->format('Y-m')]) }}"
           style="font-size:.78rem;padding:6px 12px;border:1.5px solid var(--warm-bd);border-radius:8px;color:var(--txt-md);text-decoration:none;transition:background .12s"
           onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
            Today
        </a>
    </div>
    <div style="display:flex;gap:8px">
        <a href="{{ route('doctor.appointments.index') }}"
           style="display:flex;align-items:center;gap:5px;font-size:.8rem;padding:7px 13px;border:1.5px solid var(--warm-bd);border-radius:9px;color:var(--txt-md);text-decoration:none;transition:background .12s"
           onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
            List View
        </a>
        <a href="{{ route('doctor.appointments.slots') }}"
           style="display:flex;align-items:center;gap:5px;font-size:.8rem;padding:7px 13px;background:var(--ink);color:#fff;border-radius:9px;text-decoration:none">
            Manage Slots
        </a>
    </div>
</div>

<div class="panel">
    {{-- Day headers --}}
    <div style="display:grid;grid-template-columns:repeat(7,1fr);border-bottom:1.5px solid var(--warm-bd)">
        @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $dh)
        <div style="padding:10px;text-align:center;font-size:.72rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--txt-lt);
                    {{ in_array($dh,['Saturday','Sunday']) ? 'color:var(--coral,#c0737a)' : '' }}">
            {{ substr($dh, 0, 3) }}
        </div>
        @endforeach
    </div>

    {{-- Calendar grid --}}
    <div style="display:grid;grid-template-columns:repeat(7,1fr)">
        @for($i = 0; $i < $startDow; $i++)
        <div style="min-height:90px;border-bottom:1px solid var(--parch);border-right:1px solid var(--parch)"></div>
        @endfor

        @for($d = 1; $d <= $daysInMon; $d++)
        @php
            $dateStr  = sprintf('%04d-%02d-%02d', $yr, $mon, $d);
            $dayApts  = $appointments->get($dateStr, collect());
            $isToday  = $dateStr === $today;
            $dayOfWeek= \Carbon\Carbon::parse($dateStr)->dayOfWeek;
            $isWeekend= in_array($dayOfWeek, [0, 6]);
            $col      = ($startDow + $d - 1) % 7 + 1;
            $isLast   = $d === $daysInMon;
        @endphp
        <div style="min-height:90px;padding:6px 8px;border-bottom:1px solid var(--parch);border-right:1px solid var(--parch);
                    {{ $isToday ? 'background:#f7f3ee' : '' }}
                    {{ $isWeekend ? 'background:rgba(0,0,0,.012)' : '' }};
                    transition:background .12s;cursor:{{ $dayApts->count() ? 'pointer' : 'default' }}"
             @if($dayApts->count())
             onclick="window.location='{{ route('doctor.appointments.index', ['date' => $dateStr]) }}'"
             onmouseover="this.style.background='#f0ece6'" onmouseout="this.style.background='{{ $isToday ? '#f7f3ee' : ($isWeekend ? 'rgba(0,0,0,.012)' : 'transparent') }}'"
             @endif>

            {{-- Day number --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                <span style="font-size:.8125rem;font-weight:{{ $isToday ? '700' : '500' }};
                             {{ $isToday ? 'width:22px;height:22px;background:var(--ink);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.75rem' : ($isWeekend ? 'color:var(--coral,#c0737a)' : 'color:var(--txt-md)') }}">
                    {{ $d }}
                </span>
                @if($dayApts->count() > 0)
                <span style="font-size:.65rem;font-weight:700;background:var(--ink);color:#fff;padding:1px 6px;border-radius:20px">
                    {{ $dayApts->count() }}
                </span>
                @endif
            </div>

            {{-- Appointment pills (max 3) --}}
            @foreach($dayApts->take(3) as $apt)
            @php $aptColor = $statusColors[$apt->status] ?? '#94a3b8'; @endphp
            <div style="font-size:.65rem;padding:2px 6px;border-radius:4px;margin-bottom:2px;background:{{ $aptColor }}18;color:{{ $aptColor }};border-left:2px solid {{ $aptColor }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:500">
                {{ $apt->slot_datetime->format('h:i A') }} {{ $apt->patient?->profile?->full_name ? Str::limit($apt->patient->profile->full_name, 12) : '' }}
            </div>
            @endforeach
            @if($dayApts->count() > 3)
            <div style="font-size:.62rem;color:var(--txt-lt);padding:1px 6px">+{{ $dayApts->count()-3 }} more</div>
            @endif
        </div>
        @endfor
    </div>
</div>

{{-- Legend --}}
<div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:12px;padding:0 2px">
    @foreach(['booked'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled'] as $st => $lbl)
    <div style="display:flex;align-items:center;gap:5px;font-size:.75rem;color:var(--txt-md)">
        <div style="width:10px;height:10px;border-radius:2px;background:{{ $statusColors[$st] }}"></div>
        {{ $lbl }}
    </div>
    @endforeach
</div>

</div>
@endsection
