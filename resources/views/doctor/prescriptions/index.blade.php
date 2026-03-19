@extends('layouts.doctor')
@section('title', 'Prescriptions')
@section('page-title', 'Prescriptions')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
    <form method="GET" style="display:flex;gap:8px;flex:1;max-width:460px">
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Search by patient name or Rx number…"
               style="flex:1;padding:.55rem .85rem;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.875rem;color:var(--txt);background:var(--cream);outline:none;font-family:'Outfit',sans-serif">
        <button type="submit" style="padding:.55rem 1rem;background:var(--ink);color:#fff;border:none;border-radius:9px;font-size:.875rem;cursor:pointer;font-family:'Outfit',sans-serif">Search</button>
        @if(request('q') || request('filter'))
        <a href="{{ route('doctor.prescriptions.index') }}" style="padding:.55rem .85rem;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.875rem;color:var(--txt-md);text-decoration:none">Clear</a>
        @endif
    </form>
    <a href="{{ route('doctor.prescriptions.create') }}"
       style="display:flex;align-items:center;gap:7px;padding:9px 18px;background:var(--ink);color:#fff;border-radius:10px;font-size:.875rem;font-weight:600;text-decoration:none">
        + New Prescription
    </a>
</div>

{{-- Filters --}}
<div style="display:flex;gap:6px;margin-bottom:18px">
    @foreach(['all' => 'All', 'today' => 'Today', 'this_week' => 'This Week', 'unsent' => 'Not Sent'] as $val => $lbl)
    <a href="{{ route('doctor.prescriptions.index', array_merge(request()->only('q'), ['filter' => $val === 'all' ? null : $val])) }}"
       style="padding:6px 13px;border-radius:8px;font-size:.8rem;font-weight:500;text-decoration:none;border:1.5px solid;transition:all .15s;
              {{ (request('filter', 'all')) === $val ? 'background:var(--ink);color:#fff;border-color:var(--ink)' : 'background:transparent;color:var(--txt-md);border-color:var(--warm-bd)' }}">
        {{ $lbl }}
    </a>
    @endforeach
</div>

@if($prescriptions->isEmpty())
<div style="text-align:center;padding:60px 20px;color:var(--txt-lt)">
    <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 12px;display:block;opacity:.4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    <div style="font-size:.95rem">No prescriptions found</div>
</div>
@else
<div style="background:var(--cream);border:1px solid var(--warm-bd);border-radius:14px;overflow:hidden">
    <table style="width:100%;border-collapse:collapse;font-size:.875rem">
        <thead>
            <tr style="background:var(--parch);border-bottom:1px solid var(--warm-bd)">
                <th style="padding:11px 16px;text-align:left;font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt)">Rx #</th>
                <th style="padding:11px 16px;text-align:left;font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt)">Patient</th>
                <th style="padding:11px 16px;text-align:left;font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt)">Date</th>
                <th style="padding:11px 16px;text-align:left;font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt)">Medicines</th>
                <th style="padding:11px 16px;text-align:left;font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt)">WhatsApp</th>
                <th style="padding:11px 16px;text-align:left;font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt)"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($prescriptions as $rx)
            <tr style="border-bottom:1px solid var(--parch);transition:background .1s" onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
                <td style="padding:12px 16px;font-weight:600;color:var(--txt);font-family:'Cormorant Garamond',serif;font-size:.95rem">
                    {{ $rx->prescription_number }}
                </td>
                <td style="padding:12px 16px;color:var(--txt)">
                    {{ $rx->patient->profile?->full_name ?? '—' }}
                    <div style="font-size:.72rem;color:var(--txt-lt)">{{ $rx->patient->country_code }} {{ $rx->patient->mobile_number }}</div>
                </td>
                <td style="padding:12px 16px;color:var(--txt-md);font-size:.82rem">
                    {{ $rx->prescribed_date?->format('d M Y') }}
                </td>
                <td style="padding:12px 16px;color:var(--txt-md)">
                    {{ $rx->medicines->count() }} item{{ $rx->medicines->count() !== 1 ? 's' : '' }}
                </td>
                <td style="padding:12px 16px">
                    @if($rx->is_sent_whatsapp)
                    <span style="font-size:.72rem;padding:3px 8px;background:#dcfce7;color:#166534;border-radius:6px;font-weight:600">Sent</span>
                    @else
                    <span style="font-size:.72rem;padding:3px 8px;background:var(--parch);color:var(--txt-lt);border-radius:6px">Pending</span>
                    @endif
                </td>
                <td style="padding:12px 16px;text-align:right">
                    <a href="{{ route('doctor.prescriptions.show', $rx) }}"
                       style="font-size:.8rem;padding:5px 12px;border:1.5px solid var(--warm-bd);border-radius:8px;color:var(--txt-md);text-decoration:none;transition:all .15s"
                       onmouseover="this.style.borderColor='var(--leaf)';this.style.color='var(--leaf)'"
                       onmouseout="this.style.borderColor='var(--warm-bd)';this.style.color='var(--txt-md)'">
                        View
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($prescriptions->hasPages())
<div style="margin-top:16px">{{ $prescriptions->links() }}</div>
@endif
@endif
@endsection
