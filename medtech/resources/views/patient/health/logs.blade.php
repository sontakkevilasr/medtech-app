@extends('layouts.patient')
@section('title', 'Reading History')
@section('page-title')
    <a href="{{ route('patient.health.index') }}" style="font-size:.85rem;font-weight:400;color:var(--txt-lt);text-decoration:none">Health Tracker</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    Full History
@endsection

@section('content')
@php
$vitals = ['bp'=>['❤️','Blood Pressure'],'sugar'=>['🩸','Blood Sugar'],'weight'=>['⚖️','Weight'],'oxygen'=>['💨','Oxygen (SpO₂)'],'temperature'=>['🌡️','Temperature'],'pulse'=>['💓','Pulse']];
@endphp
<div class="fade-slide">

{{-- Filters --}}
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:18px;align-items:center">
    <a href="{{ route('patient.health.logs') }}"
       style="padding:5px 13px;border-radius:20px;font-size:.8rem;font-weight:500;text-decoration:none;border:1.5px solid {{ !$type ? 'var(--plum)' : 'var(--warm-bd)' }};background:{{ !$type ? 'var(--plum)' : 'transparent' }};color:{{ !$type ? '#fff' : 'var(--txt-md)' }}">
        All
    </a>
    @foreach($vitals as $key => [$icon, $label])
    <a href="{{ route('patient.health.logs', array_filter(['type'=>$key,'member'=>$memberId])) }}"
       style="padding:5px 13px;border-radius:20px;font-size:.8rem;font-weight:500;text-decoration:none;border:1.5px solid {{ $type===$key ? 'var(--plum)' : 'var(--warm-bd)' }};background:{{ $type===$key ? 'var(--plum)' : 'transparent' }};color:{{ $type===$key ? '#fff' : 'var(--txt-md)' }}">
        {{ $icon }} {{ explode(' ', $label)[0] }}
    </a>
    @endforeach
    <div style="margin-left:auto;font-size:.78rem;color:var(--txt-lt)">{{ $logs->total() }} readings</div>
</div>

<div class="panel" style="padding:0;overflow:hidden">
    @if($logs->isEmpty())
    <div style="padding:40px;text-align:center;color:var(--txt-lt)">
        No readings found. <a href="{{ route('patient.health.index') }}" style="color:var(--plum)">Log one →</a>
    </div>
    @else
    <table style="width:100%;border-collapse:collapse">
        <thead><tr style="border-bottom:1.5px solid var(--warm-bd)">
            <th style="padding:9px 16px;text-align:left;font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt-lt)">Type</th>
            <th style="padding:9px 16px;text-align:left;font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt-lt)">Reading</th>
            <th style="padding:9px 16px;text-align:left;font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt-lt)">Context</th>
            <th style="padding:9px 16px;text-align:left;font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt-lt)">Notes</th>
            <th style="padding:9px 16px;text-align:left;font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt-lt)">Date</th>
            <th style="padding:9px 16px"></th>
        </tr></thead>
        <tbody>
        @foreach($logs as $log)
        @php [$icon, $label] = $vitals[$log->log_type] ?? ['📋', ucfirst($log->log_type)]; @endphp
        <tr style="border-bottom:1px solid var(--warm-bd);transition:background .1s"
            onmouseover="this.style.background='var(--sand)'" onmouseout="this.style.background='transparent'">
            <td style="padding:10px 16px">
                <div style="display:flex;align-items:center;gap:7px">
                    <span style="font-size:.95rem">{{ $icon }}</span>
                    <span style="font-size:.8rem;font-weight:500;color:var(--txt-md)">{{ $label }}</span>
                </div>
            </td>
            <td style="padding:10px 16px;font-family:'Lora',serif;font-size:.95rem;font-weight:500;color:var(--txt)">
                {{ $log->formatted_value }}
            </td>
            <td style="padding:10px 16px">
                @if($log->context)
                <span style="font-size:.7rem;padding:2px 8px;border-radius:20px;background:var(--sand);color:var(--txt-md)">
                    {{ ucwords(str_replace('_',' ',$log->context)) }}
                </span>
                @else <span style="color:var(--txt-lt)">—</span> @endif
            </td>
            <td style="padding:10px 16px;font-size:.78rem;color:var(--txt-md);max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                {{ $log->notes ?? '—' }}
            </td>
            <td style="padding:10px 16px;font-size:.78rem;color:var(--txt-lt)">
                {{ $log->logged_at->format('d M Y') }}
                <span style="font-size:.7rem"> {{ $log->logged_at->format('h:i A') }}</span>
            </td>
            <td style="padding:10px 16px;text-align:right">
                <form method="POST" action="{{ route('patient.health.logs.destroy', $log) }}"
                      onsubmit="return confirm('Delete this reading?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="width:24px;height:24px;border:1px solid #fecaca;border-radius:6px;background:transparent;color:#dc2626;cursor:pointer;font-size:.75rem;display:inline-flex;align-items:center;justify-content:center">×</button>
                </form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div style="display:flex;justify-content:center;align-items:center;gap:6px;padding:12px 16px;border-top:1px solid var(--warm-bd)">
        @if(!$logs->onFirstPage())
        <a href="{{ $logs->previousPageUrl() }}"
           style="padding:6px 14px;border:1.5px solid var(--warm-bd);border-radius:8px;font-size:.8rem;text-decoration:none;color:var(--txt-md)">← Prev</a>
        @endif
        <span style="font-size:.78rem;color:var(--txt-lt)">Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}</span>
        @if($logs->hasMorePages())
        <a href="{{ $logs->nextPageUrl() }}"
           style="padding:6px 14px;border:1.5px solid var(--warm-bd);border-radius:8px;font-size:.8rem;text-decoration:none;color:var(--txt-md)">Next →</a>
        @endif
    </div>
    @endif
    @endif
</div>
</div>
@endsection
