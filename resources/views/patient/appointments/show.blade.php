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
    $profile = $doctor?->profile;
    $name    = $profile?->full_name ?? 'Doctor';
    $member  = $appointment->familyMember;
    $statusColors = [
        'booked'    => ['bg'=>'#dbeafe','color'=>'#1e40af','icon'=>'📋'],
        'pending'   => ['bg'=>'#fef9c3','color'=>'#854d0e','icon'=>'⏳'],
        'confirmed' => ['bg'=>'#dbeafe','color'=>'#1e40af','icon'=>'✓'],
        'completed' => ['bg'=>'#dcfce7','color'=>'#166534','icon'=>'✓'],
        'cancelled' => ['bg'=>'#fee2e2','color'=>'#991b1b','icon'=>'✕'],
        'no_show'   => ['bg'=>'#f3f4f6','color'=>'#374151','icon'=>'?'],
    ];
    $sc = $statusColors[$appointment->status] ?? ['bg'=>'#f3f4f6','color'=>'#374151','icon'=>'•'];
    $isPast = $appointment->slot_datetime?->isPast();
    $canCancel = in_array($appointment->status, ['booked','pending','confirmed']) && !$isPast;
@endphp

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">

{{-- ── Left column ──────────────────────────────────────────────────────────── --}}
<div style="display:flex;flex-direction:column;gap:16px">

    {{-- Status banner --}}
    <div style="background:{{ $sc['bg'] }};border:1px solid {{ $sc['color'] }}22;border-radius:14px;padding:16px 20px;display:flex;align-items:center;gap:12px">
        <div style="width:40px;height:40px;border-radius:50%;background:{{ $sc['color'] }}18;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0">
            {{ $sc['icon'] }}
        </div>
        <div>
            <div style="font-weight:700;font-size:1rem;color:{{ $sc['color'] }}">{{ ucfirst(str_replace('_',' ',$appointment->status)) }}</div>
            <div style="font-size:.78rem;color:{{ $sc['color'] }}99;margin-top:2px">
                @if($appointment->status === 'booked' || $appointment->status === 'confirmed')
                    Your appointment is on {{ $appointment->slot_datetime?->format('D, d M Y') }} at {{ $appointment->slot_datetime?->format('h:i A') }}
                @elseif($appointment->status === 'completed')
                    Completed on {{ $appointment->slot_datetime?->format('d M Y') }}
                @elseif($appointment->status === 'cancelled')
                    This appointment was cancelled
                @else
                    {{ $appointment->slot_datetime?->format('D, d M Y \a\t h:i A') }}
                @endif
            </div>
        </div>
    </div>

    {{-- Doctor card --}}
    <div style="background:var(--cream);border:1px solid var(--warm-bd);border-radius:14px;overflow:hidden">
        <div style="padding:13px 20px;border-bottom:1px solid var(--warm-bd);font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--txt-lt)">Doctor</div>
        <div style="padding:16px 20px;display:flex;align-items:center;gap:14px">
            <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,var(--plum),var(--plum-md));display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:1.1rem;flex-shrink:0">
                {{ strtoupper(substr($name,0,1)) }}
            </div>
            <div style="flex:1">
                <div style="font-weight:600;font-size:.95rem;color:var(--txt)">{{ str_starts_with($name, 'Dr') ? $name : 'Dr. ' . $name }}</div>
                @if($dp?->specialization)
                <div style="font-size:.78rem;color:var(--txt-lt);margin-top:3px">{{ $dp->specialization }}</div>
                @endif
                @if($dp?->clinic_name)
                <div style="font-size:.75rem;color:var(--txt-lt);margin-top:1px">{{ $dp->clinic_name }}</div>
                @endif
            </div>
            @if($dp?->consultation_fee)
            <div style="text-align:right">
                <div style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;font-weight:600;color:var(--txt)">₹{{ number_format($dp->consultation_fee) }}</div>
                <div style="font-size:.68rem;color:var(--txt-lt)">consultation fee</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Appointment details --}}
    <div style="background:var(--cream);border:1px solid var(--warm-bd);border-radius:14px;overflow:hidden">
        <div style="padding:13px 20px;border-bottom:1px solid var(--warm-bd);font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--txt-lt)">Appointment Details</div>
        <div style="padding:18px 20px">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px 16px">
                <div>
                    <div style="font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:5px">Date</div>
                    <div style="font-size:.9rem;font-weight:500;color:var(--txt)">{{ $appointment->slot_datetime?->format('D, d M Y') }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:5px">Time</div>
                    <div style="font-size:.9rem;font-weight:500;color:var(--txt)">{{ $appointment->slot_datetime?->format('h:i A') }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:5px">Type</div>
                    <div style="font-size:.9rem;color:var(--txt)">{{ ucfirst(str_replace('_',' ',$appointment->type ?? 'in_person')) }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:5px">Duration</div>
                    <div style="font-size:.9rem;color:var(--txt)">{{ $appointment->duration_minutes ?? 15 }} minutes</div>
                </div>
                @if($appointment->appointment_number)
                <div>
                    <div style="font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:5px">Appointment No.</div>
                    <div style="font-size:.9rem;font-weight:500;color:var(--txt);font-family:monospace">{{ $appointment->appointment_number }}</div>
                </div>
                @endif
                <div>
                    <div style="font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:5px">Booked On</div>
                    <div style="font-size:.9rem;color:var(--txt)">{{ $appointment->created_at?->format('d M Y, h:i A') }}</div>
                </div>
            </div>

            @if($member)
            <div style="margin-top:18px;padding-top:16px;border-top:1px solid var(--warm-bd)">
                <div style="font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:5px">Booking For</div>
                <div style="font-size:.9rem;color:var(--txt)">{{ $member->full_name }} <span style="color:var(--txt-lt);font-size:.82rem">({{ ucfirst($member->relation) }})</span></div>
            </div>
            @endif

            @if($appointment->reason)
            <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--warm-bd)">
                <div style="font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:5px">Reason</div>
                <div style="font-size:.88rem;color:var(--txt);line-height:1.5">{{ $appointment->reason }}</div>
            </div>
            @endif

            @if($appointment->chief_complaint)
            <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--warm-bd)">
                <div style="font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:5px">Chief Complaint</div>
                <div style="font-size:.88rem;color:var(--txt);line-height:1.5">{{ $appointment->chief_complaint }}</div>
            </div>
            @endif

            @if($appointment->notes)
            <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--warm-bd)">
                <div style="font-size:.72rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:5px">Doctor's Notes</div>
                <div style="font-size:.88rem;color:var(--txt);line-height:1.5">{{ $appointment->notes }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Payment info --}}
    @if($appointment->fee)
    <div style="background:var(--cream);border:1px solid var(--warm-bd);border-radius:14px;overflow:hidden">
        <div style="padding:13px 20px;border-bottom:1px solid var(--warm-bd);font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--txt-lt)">Payment</div>
        <div style="padding:16px 20px;display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-size:.9rem;color:var(--txt)">Consultation Fee</div>
                <div style="font-size:.75rem;color:var(--txt-lt);margin-top:2px">
                    @php
                        $payStatus = $appointment->payment_status ?? 'pending';
                        $payColors = ['paid'=>'#166534','pending'=>'#854d0e','refunded'=>'#991b1b'];
                    @endphp
                    <span style="color:{{ $payColors[$payStatus] ?? '#374151' }};font-weight:600">{{ ucfirst($payStatus) }}</span>
                </div>
            </div>
            <div style="font-family:'Cormorant Garamond',serif;font-size:1.5rem;font-weight:600;color:var(--txt)">₹{{ number_format($appointment->fee, 2) }}</div>
        </div>
    </div>
    @endif

    {{-- Cancel action --}}
    @if($canCancel)
    <div style="background:var(--cream);border:1px solid var(--warm-bd);border-radius:14px;padding:18px 20px;display:flex;align-items:center;justify-content:space-between">
        <div>
            <div style="font-size:.88rem;font-weight:500;color:var(--txt)">Need to cancel?</div>
            <div style="font-size:.75rem;color:var(--txt-lt);margin-top:2px">This action cannot be undone</div>
        </div>
        <form method="POST" action="{{ route('patient.appointments.cancel', $appointment) }}" onsubmit="return confirm('Are you sure you want to cancel this appointment?')">
            @csrf
            <button type="submit" style="padding:9px 20px;background:transparent;color:#ef4444;border:1.5px solid #ef4444;border-radius:10px;font-size:.82rem;font-weight:600;cursor:pointer;font-family:'Outfit',sans-serif;transition:all .15s" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">Cancel Appointment</button>
        </form>
    </div>
    @endif

</div>

{{-- ── Right sidebar ────────────────────────────────────────────────────────── --}}
<div style="position:sticky;top:78px;display:flex;flex-direction:column;gap:14px">

    {{-- Clinic card --}}
    <div style="background:var(--cream);border:1px solid var(--warm-bd);border-radius:14px;overflow:hidden">
        <div style="background:linear-gradient(135deg,var(--plum),var(--plum-md));padding:20px 18px">
            <div style="font-family:'Cormorant Garamond',serif;font-size:1.15rem;color:#fff;font-weight:500">
                {{ $dp?->clinic_name ?? 'Clinic' }}
            </div>
            <div style="font-size:.75rem;color:rgba(255,255,255,.6);margin-top:4px">{{ str_starts_with($name, 'Dr') ? $name : 'Dr. ' . $name }}</div>
        </div>
        <div style="padding:16px 18px;display:flex;flex-direction:column;gap:14px">
            <div>
                <div style="font-size:.68rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:4px">Appointment ID</div>
                <div style="font-weight:600;font-family:monospace;color:var(--txt)">{{ $appointment->appointment_number ?? '#'.$appointment->id }}</div>
            </div>
            <div>
                <div style="font-size:.68rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:4px">Date & Time</div>
                <div style="font-weight:500;color:var(--txt)">{{ $appointment->slot_datetime?->format('D, d M Y') }}</div>
                <div style="font-weight:500;color:var(--txt)">{{ $appointment->slot_datetime?->format('h:i A') }}</div>
            </div>
            @if($dp?->clinic_address)
            <div>
                <div style="font-size:.68rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:4px">Clinic Address</div>
                <div style="font-size:.85rem;font-weight:500;color:var(--txt);line-height:1.5">{{ $dp->clinic_address }}</div>
            </div>
            @endif
            @if($dp?->phone)
            <div>
                <div style="font-size:.68rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:4px">Contact</div>
                <div style="font-size:.85rem;font-weight:500;color:var(--txt)">{{ $dp->phone }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Quick actions --}}
    <div style="display:flex;flex-direction:column;gap:8px">
        <a href="{{ route('patient.appointments.index') }}"
           style="display:flex;align-items:center;justify-content:center;gap:6px;padding:10px;background:var(--cream);border:1px solid var(--warm-bd);border-radius:10px;text-decoration:none;color:var(--txt-md);font-size:.82rem;font-weight:500;transition:background .15s"
           onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='var(--cream)'">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            All Appointments
        </a>
        <a href="{{ route('patient.appointments.book') }}"
           style="display:flex;align-items:center;justify-content:center;gap:6px;padding:10px;background:var(--plum);border:1px solid var(--plum);border-radius:10px;text-decoration:none;color:#fff;font-size:.82rem;font-weight:600;transition:opacity .15s"
           onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Book Another
        </a>
    </div>

</div>

</div>

@push('styles')
<style>
@@media (max-width: 768px) {
    div[style*="grid-template-columns:1fr 300px"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endpush
@endsection
