@extends('layouts.patient')
@section('title', 'Appointment #' . $appointment->id)
@section('page-title')
    <a href="{{ route('patient.appointments.index') }}" style="color:var(--txt-lt);text-decoration:none;font-size:.85rem;font-weight:400">Appointments</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    Appointment #{{ $appointment->id }}
@endsection

@section('content')
@php
    $doctor  = $appointment->doctor;
    $dp      = $doctor?->doctorProfile;
    $name    = $doctor?->profile?->full_name ?? 'Doctor';
    $member  = $appointment->familyMember;
    $statusColors = [
        'pending'   => ['bg'=>'#fef9c3','color'=>'#854d0e'],
        'confirmed' => ['bg'=>'#dbeafe','color'=>'#1e40af'],
        'completed' => ['bg'=>'#dcfce7','color'=>'#166534'],
        'cancelled' => ['bg'=>'#fee2e2','color'=>'#991b1b'],
        'no_show'   => ['bg'=>'#f3f4f6','color'=>'#374151'],
    ];
    $sc = $statusColors[$appointment->status] ?? ['bg'=>'#f3f4f6','color'=>'#374151'];
@endphp

<div style="display:grid;grid-template-columns:1fr 300px;gap:18px;align-items:start">

{{-- Left --}}
<div>

{{-- Doctor card --}}
<div style="background:var(--cream);border:1px solid var(--warm-bd);border-radius:14px;margin-bottom:16px;overflow:hidden">
    <div style="padding:13px 20px;border-bottom:1px solid var(--warm-bd);font-family:'Cormorant Garamond',serif;font-size:1.05rem;font-weight:500;color:var(--txt)">Doctor</div>
    <div style="padding:18px 20px;display:flex;align-items:center;gap:12px">
        <div style="width:42px;height:42px;border-radius:11px;background:var(--ink);display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:1rem;flex-shrink:0">{{ strtoupper(substr($name,0,1)) }}</div>
        <div>
            <div style="font-weight:600;color:var(--txt)">Dr. {{ $name }}</div>
            <div style="font-size:.75rem;color:var(--txt-lt);margin-top:2px">
                {{ $dp?->specialization ?? '' }}{{ $dp?->clinic_name ? ' · ' . $dp->clinic_name : '' }}
            </div>
        </div>
    </div>
</div>

{{-- Appointment details --}}
<div style="background:var(--cream);border:1px solid var(--warm-bd);border-radius:14px;margin-bottom:16px">
    <div style="padding:13px 20px;border-bottom:1px solid var(--warm-bd);font-family:'Cormorant Garamond',serif;font-size:1.05rem;font-weight:500;color:var(--txt)">Details</div>
    <div style="padding:18px 20px;display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div>
            <div style="font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:4px">Date &amp; Time</div>
            <div style="font-size:.95rem;color:var(--txt)">{{ $appointment->slot_datetime?->format('d M Y, h:i A') }}</div>
        </div>
        <div>
            <div style="font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:4px">Status</div>
            <span style="font-size:.8rem;padding:4px 10px;border-radius:7px;font-weight:600;background:{{ $sc['bg'] }};color:{{ $sc['color'] }}">{{ ucfirst($appointment->status) }}</span>
        </div>
        <div>
            <div style="font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:4px">Type</div>
            <div style="font-size:.9rem;color:var(--txt)">{{ ucfirst(str_replace('_',' ',$appointment->visit_type ?? 'in_person')) }}</div>
        </div>
        <div>
            <div style="font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:4px">Booked</div>
            <div style="font-size:.9rem;color:var(--txt)">{{ $appointment->created_at?->format('d M Y') }}</div>
        </div>
        @if($member)
        <div style="grid-column:1/-1">
            <div style="font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:4px">For</div>
            <div style="font-size:.9rem;color:var(--txt)">{{ $member->full_name }} <span style="color:var(--txt-lt)">({{ ucfirst($member->relation) }})</span></div>
        </div>
        @endif
        @if($appointment->chief_complaint)
        <div style="grid-column:1/-1">
            <div style="font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:4px">Chief Complaint</div>
            <div style="font-size:.9rem;color:var(--txt)">{{ $appointment->chief_complaint }}</div>
        </div>
        @endif
        @if($appointment->notes)
        <div style="grid-column:1/-1">
            <div style="font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:4px">Notes</div>
            <div style="font-size:.9rem;color:var(--txt)">{{ $appointment->notes }}</div>
        </div>
        @endif
    </div>
</div>

{{-- Cancel action --}}
@if(in_array($appointment->status, ['pending','confirmed']))
<div style="background:var(--cream);border:1px solid var(--warm-bd);border-radius:14px;padding:18px 20px">
    <form method="POST" action="{{ route('patient.appointments.cancel', $appointment) }}" onsubmit="return confirm('Cancel this appointment?')">
        @csrf
        <button type="submit" style="padding:9px 18px;background:transparent;color:#ef4444;border:1.5px solid #ef4444;border-radius:10px;font-size:.875rem;font-weight:600;cursor:pointer;font-family:'Outfit',sans-serif">Cancel Appointment</button>
    </form>
</div>
@endif

</div>

{{-- Right sidebar --}}
<div>
    <div style="background:var(--cream);border:1px solid var(--warm-bd);border-radius:14px;overflow:hidden">
        <div style="background:var(--ink);padding:16px 18px">
            <div style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;color:#fff;font-weight:500">{{ $dp?->clinic_name ?? 'Clinic' }}</div>
            <div style="font-size:.72rem;color:rgba(255,255,255,.6);margin-top:3px">Dr. {{ $name }}</div>
        </div>
        <div style="padding:14px 18px;font-size:.82rem;color:var(--txt-md)">
            <div style="margin-bottom:8px"><span style="color:var(--txt-lt)">Appointment ID</span><br><span style="font-family:monospace;font-weight:600">#{{ $appointment->id }}</span></div>
            <div style="margin-bottom:8px"><span style="color:var(--txt-lt)">Slot</span><br><span style="font-weight:500">{{ $appointment->slot_datetime?->format('D, d M Y') }}<br>{{ $appointment->slot_datetime?->format('h:i A') }}</span></div>
            @if($dp?->clinic_address)
            <div><span style="color:var(--txt-lt)">Address</span><br><span style="font-weight:500">{{ $dp->clinic_address }}</span></div>
            @endif
        </div>
    </div>
</div>

</div>
@endsection
