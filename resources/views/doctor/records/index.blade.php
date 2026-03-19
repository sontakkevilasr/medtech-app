@extends('layouts.doctor')
@section('title', 'Medical Records')
@section('page-title', 'Medical Records')

@section('content')

{{-- Search --}}
<form method="GET" style="margin-bottom:18px;display:flex;gap:10px">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Search by patient name…"
           style="flex:1;padding:9px 14px;border:1.5px solid var(--warm-bd);border-radius:10px;font-size:.875rem;font-family:'Outfit',sans-serif;color:var(--txt);background:#fff">
    <button type="submit" style="padding:9px 18px;background:var(--ink);color:#fff;border:none;border-radius:10px;font-size:.875rem;font-weight:600;cursor:pointer;font-family:'Outfit',sans-serif">Search</button>
    @if(request('search'))
    <a href="{{ route('doctor.records.index') }}" style="padding:9px 14px;border:1.5px solid var(--warm-bd);border-radius:10px;font-size:.875rem;color:var(--txt-md);text-decoration:none">Clear</a>
    @endif
</form>

@if($records->isEmpty())
<div style="text-align:center;padding:60px 20px;color:var(--txt-lt)">
    <div style="font-size:.95rem">No medical records found.</div>
</div>
@else
<div style="background:var(--cream);border:1px solid var(--warm-bd);border-radius:14px;overflow:hidden">
    <table style="width:100%;border-collapse:collapse">
        <thead>
            <tr style="border-bottom:1px solid var(--warm-bd)">
                <th style="padding:11px 18px;text-align:left;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--txt-lt)">Patient</th>
                <th style="padding:11px 18px;text-align:left;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--txt-lt)">Diagnosis</th>
                <th style="padding:11px 18px;text-align:left;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--txt-lt)">Visit Date</th>
                <th style="padding:11px 18px;text-align:left;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--txt-lt)">Rx</th>
                <th style="padding:11px 18px;text-align:right;font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--txt-lt)"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
            @php $name = $record->familyMember?->full_name ?? $record->patient?->profile?->full_name ?? '—'; @endphp
            <tr style="border-bottom:1px solid var(--warm-bd);transition:background .1s" onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
                <td style="padding:13px 18px">
                    <div style="font-weight:600;font-size:.875rem;color:var(--txt)">{{ $name }}</div>
                    @if($record->familyMember)
                    <div style="font-size:.72rem;color:var(--txt-lt)">{{ ucfirst($record->familyMember->relation) }} of {{ $record->patient?->profile?->full_name }}</div>
                    @endif
                </td>
                <td style="padding:13px 18px;font-size:.85rem;color:var(--txt-md);max-width:220px">
                    <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $record->diagnosis ?? '—' }}</div>
                </td>
                <td style="padding:13px 18px;font-size:.85rem;color:var(--txt-md);white-space:nowrap">
                    {{ $record->visit_date?->format('d M Y') }}
                </td>
                <td style="padding:13px 18px">
                    @if($record->prescription)
                    <span style="font-size:.72rem;padding:2px 8px;background:#dcfce7;color:#166534;border-radius:6px;font-weight:600">Rx</span>
                    @else
                    <span style="color:var(--txt-lt);font-size:.8rem">—</span>
                    @endif
                </td>
                <td style="padding:13px 18px;text-align:right">
                    <a href="{{ route('doctor.records.show', $record) }}"
                       style="font-size:.8rem;padding:5px 12px;border:1.5px solid var(--warm-bd);border-radius:8px;color:var(--txt-md);text-decoration:none">View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($records->hasPages())
<div style="display:flex;gap:8px;justify-content:center;margin-top:18px">
    @if(!$records->onFirstPage())
    <a href="{{ $records->previousPageUrl() }}" style="padding:7px 14px;border:1.5px solid var(--warm-bd);border-radius:9px;color:var(--txt);text-decoration:none;font-size:.85rem">← Prev</a>
    @endif
    <span style="padding:7px 14px;font-size:.85rem;color:var(--txt-lt)">Page {{ $records->currentPage() }} of {{ $records->lastPage() }}</span>
    @if($records->hasMorePages())
    <a href="{{ $records->nextPageUrl() }}" style="padding:7px 14px;border:1.5px solid var(--warm-bd);border-radius:9px;color:var(--txt);text-decoration:none;font-size:.85rem">Next →</a>
    @endif
</div>
@endif
@endif

@endsection
