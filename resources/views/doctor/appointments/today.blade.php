@extends('layouts.doctor')
@section('title', "Today's Appointments")
@section('page-title', "Today's Appointments — " . \Carbon\Carbon::today()->format('d M Y'))

@section('content')
@if($appointments->isEmpty())
<div style="text-align:center;padding:60px 20px;color:var(--txt-lt)">
    <div style="font-size:.95rem">No appointments scheduled for today.</div>
    <a href="{{ route('doctor.appointments.index') }}" style="display:inline-block;margin-top:12px;font-size:.85rem;color:var(--leaf)">View all appointments</a>
</div>
@else
<div style="display:flex;flex-direction:column;gap:12px">
    @foreach($appointments as $apt)
    @php
        $name = $apt->familyMember?->full_name ?? $apt->patient?->profile?->full_name ?? '—';
        $statusColors = ['pending'=>['#fef9c3','#854d0e'],'confirmed'=>['#dbeafe','#1e40af'],'completed'=>['#dcfce7','#166534'],'cancelled'=>['#fee2e2','#991b1b']];
        $sc = $statusColors[$apt->status] ?? ['#f3f4f6','#374151'];
    @endphp
    <div style="background:var(--cream);border:1px solid var(--warm-bd);border-radius:12px;padding:16px 20px;display:flex;align-items:center;gap:14px">
        <div style="font-size:1.1rem;font-weight:700;color:var(--txt);min-width:60px">{{ $apt->slot_datetime?->format('h:i') }}<span style="font-size:.7rem;font-weight:400;color:var(--txt-lt)"> {{ $apt->slot_datetime?->format('A') }}</span></div>
        <div style="width:1px;height:36px;background:var(--warm-bd)"></div>
        <div style="flex:1">
            <div style="font-weight:600;color:var(--txt)">{{ $name }}</div>
            <div style="font-size:.75rem;color:var(--txt-lt)">{{ $apt->patient?->country_code }} {{ $apt->patient?->mobile_number }}{{ $apt->chief_complaint ? ' · ' . $apt->chief_complaint : '' }}</div>
        </div>
        <span style="font-size:.75rem;padding:3px 10px;border-radius:7px;font-weight:600;background:{{ $sc[0] }};color:{{ $sc[1] }}">{{ ucfirst($apt->status) }}</span>
        <a href="{{ route('doctor.appointments.show', $apt) }}" style="font-size:.8rem;padding:6px 14px;border:1.5px solid var(--warm-bd);border-radius:8px;color:var(--txt-md);text-decoration:none">View</a>
    </div>
    @endforeach
</div>
@endif
@endsection
