@extends('layouts.patient')
@section('title', 'Appointment #' . $appointment->id)
@section('page-title')
    <a href="{{ route('patient.appointments.index') }}" style="color:var(--txt-lt);text-decoration:none;font-size:.85rem;font-weight:400">Appointments</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    {{ $appointment->appointment_number ?? 'Appointment #'.$appointment->id }}
@endsection

@push('styles')
<style>
.apt-card { background:var(--cream);border:1px solid var(--warm-bd);border-radius:14px;overflow:hidden }
.apt-card-head { padding:11px 18px;border-bottom:1px solid var(--warm-bd);font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt-lt) }
.apt-card-body { padding:18px }
.meta-label { font-size:.68rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:4px }
.meta-value { font-size:.9rem;font-weight:500;color:var(--txt) }

@keyframes spin   { to { transform:rotate(360deg) } }
@keyframes fadeIn { from { opacity:0;transform:scale(.96) } to { opacity:1;transform:scale(1) } }
.pay-overlay {
    position:fixed;inset:0;background:rgba(0,0,0,.45);
    display:flex;align-items:center;justify-content:center;
    z-index:1000;animation:fadeIn .18s ease;
}
.pay-box {
    background:#fff;border-radius:18px;padding:28px 30px;
    width:420px;max-width:94vw;box-shadow:0 20px 60px rgba(0,0,0,.2);
}
@@media (max-width:768px) {
    .apt-grid { grid-template-columns:1fr !important }
    .apt-sidebar { position:static !important }
}
</style>
@endpush

@section('content')
@php
    $doctor  = $appointment->doctor;
    $dp      = $doctor?->doctorProfile;
    $profile = $doctor?->profile;
    $name    = $profile?->full_name ?? 'Doctor';
    $member  = $appointment->familyMember;

    $statusColors = [
        'booked'    => ['bg'=>'#dbeafe','color'=>'#1e40af','label'=>'Booked'],
        'pending'   => ['bg'=>'#fef9c3','color'=>'#854d0e','label'=>'Pending'],
        'confirmed' => ['bg'=>'#dbeafe','color'=>'#1e40af','label'=>'Confirmed'],
        'completed' => ['bg'=>'#dcfce7','color'=>'#166534','label'=>'Completed'],
        'cancelled' => ['bg'=>'#fee2e2','color'=>'#991b1b','label'=>'Cancelled'],
        'no_show'   => ['bg'=>'#f3f4f6','color'=>'#374151','label'=>'No Show'],
    ];
    $sc = $statusColors[$appointment->status] ?? ['bg'=>'#f3f4f6','color'=>'#374151','label'=>ucfirst($appointment->status)];

    $isPast    = $appointment->slot_datetime?->isPast();
    $canCancel = in_array($appointment->status, ['booked','pending','confirmed']) && !$isPast;
    $payStatus = $appointment->payment_status ?? 'pending';
    $hasFee    = $appointment->fee && $appointment->fee > 0;
    $needsPay  = $hasFee && $payStatus !== 'paid' && $appointment->status !== 'cancelled';
    $payColors = ['paid'=>['bg'=>'#d1fae5','color'=>'#065f46'],'pending'=>['bg'=>'#fef9c3','color'=>'#854d0e'],'refunded'=>['bg'=>'#e0e7ff','color'=>'#3730a3']];
    $pc = $payColors[$payStatus] ?? ['bg'=>'#f3f4f6','color'=>'#374151'];
@endphp

<div x-data="aptPay()" class="fade-slide">

<div class="apt-grid" style="display:grid;grid-template-columns:1fr 290px;gap:20px;align-items:start">

{{-- ════════════════════ LEFT COLUMN ════════════════════ --}}
<div style="display:flex;flex-direction:column;gap:14px">

    {{-- Status banner --}}
    <div style="background:{{ $sc['bg'] }};border:1px solid {{ $sc['color'] }}33;border-radius:14px;padding:16px 20px;display:flex;align-items:center;gap:14px">
        <div style="width:44px;height:44px;border-radius:50%;background:{{ $sc['color'] }}18;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            @if($appointment->status === 'completed')
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="{{ $sc['color'] }}" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            @elseif($appointment->status === 'cancelled')
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="{{ $sc['color'] }}" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            @else
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="{{ $sc['color'] }}" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
            @endif
        </div>
        <div style="flex:1">
            <div style="font-weight:700;font-size:1rem;color:{{ $sc['color'] }}">{{ $sc['label'] }}</div>
            <div style="font-size:.78rem;color:{{ $sc['color'] }}bb;margin-top:2px">
                @if(in_array($appointment->status, ['booked','confirmed','pending']))
                    Your appointment is on <strong>{{ $appointment->slot_datetime?->format('D, d M Y') }}</strong> at {{ $appointment->slot_datetime?->format('h:i A') }}
                @elseif($appointment->status === 'completed')
                    Completed on {{ $appointment->slot_datetime?->format('d M Y') }}
                @elseif($appointment->status === 'cancelled')
                    This appointment was cancelled
                @else
                    {{ $appointment->slot_datetime?->format('D, d M Y \a\t h:i A') }}
                @endif
            </div>
        </div>
        {{-- Pay Now badge in banner if payment pending --}}
        @if($needsPay)
        <button type="button"
                @click="open({{ $appointment->id }}, {{ $appointment->fee }}, '{{ addslashes($name) }}', '{{ $appointment->appointment_number }}')"
                style="flex-shrink:0;padding:8px 18px;background:var(--plum);color:#fff;border:none;border-radius:10px;font-size:.8rem;font-weight:700;cursor:pointer;font-family:inherit;white-space:nowrap;transition:opacity .15s"
                onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
            💳 Pay Now
        </button>
        @endif
    </div>

    {{-- Doctor --}}
    <div class="panel tl-card">
        <div class="apt-card-head">Doctor</div>
        <div class="apt-card-body" style="display:flex;align-items:center;gap:14px">
            <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,var(--plum),var(--plum-md));display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:1.15rem;flex-shrink:0">
                {{ strtoupper(substr($name,0,1)) }}
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-weight:600;font-size:.95rem;color:var(--txt)">{{ str_starts_with($name,'Dr') ? $name : 'Dr. '.$name }}</div>
                @if($dp?->specialization)<div style="font-size:.78rem;color:var(--txt-lt);margin-top:2px">{{ $dp->specialization }}</div>@endif
                @if($dp?->clinic_name)<div style="font-size:.75rem;color:var(--txt-lt);margin-top:1px">{{ $dp->clinic_name }}</div>@endif
            </div>
            @if($dp?->consultation_fee)
            <div style="text-align:right;flex-shrink:0">
                <div style="font-family:'Lora',serif;font-size:1.35rem;font-weight:500;color:var(--txt)">₹{{ number_format($dp->consultation_fee) }}</div>
                <div style="font-size:.68rem;color:var(--txt-lt)">consultation fee</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Appointment details --}}
    <div class="panel tl-card">
        <div class="apt-card-head">Appointment Details</div>
        <div class="apt-card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px 16px">
                <div>
                    <div class="meta-label">Date</div>
                    <div class="meta-value">{{ $appointment->slot_datetime?->format('D, d M Y') }}</div>
                </div>
                <div>
                    <div class="meta-label">Time</div>
                    <div class="meta-value">{{ $appointment->slot_datetime?->format('h:i A') }}</div>
                </div>
                <div>
                    <div class="meta-label">Type</div>
                    <div class="meta-value">{{ ucfirst(str_replace('_',' ',$appointment->type ?? 'in_person')) }}</div>
                </div>
                <div>
                    <div class="meta-label">Duration</div>
                    <div class="meta-value">{{ $appointment->duration_minutes ?? 15 }} min</div>
                </div>
                @if($appointment->appointment_number)
                <div>
                    <div class="meta-label">Appointment No.</div>
                    <div style="font-size:.88rem;font-weight:600;color:var(--txt);font-family:monospace">{{ $appointment->appointment_number }}</div>
                </div>
                @endif
                <div>
                    <div class="meta-label">Booked On</div>
                    <div style="font-size:.85rem;color:var(--txt)">{{ $appointment->created_at?->format('d M Y, h:i A') }}</div>
                </div>
            </div>

            @if($member)
            <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--warm-bd)">
                <div class="meta-label">Booking For</div>
                <div class="meta-value">{{ $member->full_name }} <span style="color:var(--txt-lt);font-size:.8rem;font-weight:400">({{ ucfirst($member->relation) }})</span></div>
            </div>
            @endif

            @if($appointment->reason)
            <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--warm-bd)">
                <div class="meta-label">Reason for Visit</div>
                <div style="font-size:.875rem;color:var(--txt);line-height:1.55">{{ $appointment->reason }}</div>
            </div>
            @endif

            @if($appointment->chief_complaint)
            <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--warm-bd)">
                <div class="meta-label">Chief Complaint</div>
                <div style="font-size:.875rem;color:var(--txt);line-height:1.55">{{ $appointment->chief_complaint }}</div>
            </div>
            @endif

            @if($appointment->notes)
            <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--warm-bd)">
                <div class="meta-label">Doctor's Notes</div>
                <div style="font-size:.875rem;color:var(--txt);line-height:1.55">{{ $appointment->notes }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Payment section --}}
    @if($hasFee)
    <div class="panel tl-card">
        <div class="apt-card-head">Payment</div>
        <div class="apt-card-body">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
                <div>
                    <div class="meta-label">Consultation Fee</div>
                    <div style="font-family:'Lora',serif;font-size:1.6rem;font-weight:500;color:var(--txt);margin-top:2px">₹{{ number_format($appointment->fee, 2) }}</div>
                    <span style="display:inline-block;margin-top:6px;padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:600;background:{{ $pc['bg'] }};color:{{ $pc['color'] }}">
                        {{ ucfirst($payStatus) }}
                    </span>
                </div>

                @if($needsPay)
                <div style="text-align:right;flex-shrink:0">
                    <button type="button"
                            @click="open({{ $appointment->id }}, {{ $appointment->fee }}, '{{ addslashes($name) }}', '{{ $appointment->appointment_number }}')"
                            style="padding:11px 24px;background:var(--plum);color:#fff;border:none;border-radius:11px;font-size:.9rem;font-weight:700;cursor:pointer;font-family:inherit;transition:opacity .15s;display:flex;align-items:center;gap:8px"
                            onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path stroke-linecap="round" d="M1 10h22"/></svg>
                        Pay Now
                    </button>
                    <div style="font-size:.68rem;color:var(--txt-lt);margin-top:6px">🔒 Secured by Razorpay</div>
                </div>
                @elseif($payStatus === 'paid')
                <div style="display:flex;align-items:center;gap:6px;color:#065f46;font-size:.82rem;font-weight:600">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Payment Received
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Cancel --}}
    @if($canCancel)
    <div class="panel tl-card">
        <div class="apt-card-body" style="display:flex;align-items:center;justify-content:space-between;gap:12px">
            <div>
                <div style="font-size:.88rem;font-weight:500;color:var(--txt)">Need to cancel?</div>
                <div style="font-size:.75rem;color:var(--txt-lt);margin-top:2px">This action cannot be undone</div>
            </div>
            <form method="POST" action="{{ route('patient.appointments.cancel', $appointment) }}"
                  onsubmit="return confirm('Are you sure you want to cancel this appointment?')">
                @csrf
                <button type="submit"
                        style="padding:9px 20px;background:transparent;color:#ef4444;border:1.5px solid #ef4444;border-radius:10px;font-size:.82rem;font-weight:600;cursor:pointer;font-family:inherit;transition:all .15s"
                        onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                    Cancel Appointment
                </button>
            </form>
        </div>
    </div>
    @endif

</div>

{{-- ════════════════════ RIGHT SIDEBAR ════════════════════ --}}
<div class="apt-sidebar" style="position:sticky;top:78px;display:flex;flex-direction:column;gap:14px">

    {{-- Summary card --}}
    <div style="background:linear-gradient(135deg,var(--plum) 0%,var(--plum-md) 100%);border-radius:14px;overflow:hidden">
        <div style="padding:20px 18px;border-bottom:1px solid rgba(255,255,255,.15)">
            <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.55);margin-bottom:4px">Appointment ID</div>
            <div style="font-family:monospace;font-size:1rem;font-weight:700;color:#fff">{{ $appointment->appointment_number ?? '#'.$appointment->id }}</div>
        </div>
        <div style="padding:16px 18px;display:flex;flex-direction:column;gap:14px">
            <div>
                <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.55);margin-bottom:4px">Date & Time</div>
                <div style="font-size:.9rem;font-weight:600;color:#fff">{{ $appointment->slot_datetime?->format('D, d M Y') }}</div>
                <div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-top:1px">{{ $appointment->slot_datetime?->format('h:i A') }}</div>
            </div>
            @if($dp?->clinic_name)
            <div>
                <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.55);margin-bottom:4px">Clinic</div>
                <div style="font-size:.88rem;font-weight:500;color:#fff">{{ $dp->clinic_name }}</div>
            </div>
            @endif
            @if($dp?->clinic_address)
            <div>
                <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.55);margin-bottom:4px">Address</div>
                <div style="font-size:.8rem;color:rgba(255,255,255,.75);line-height:1.5">{{ $dp->clinic_address }}</div>
            </div>
            @endif
            @if($dp?->phone)
            <div>
                <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.55);margin-bottom:4px">Contact</div>
                <div style="font-size:.85rem;color:rgba(255,255,255,.85)">{{ $dp->phone }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Status pill --}}
    <div style="background:{{ $sc['bg'] }};border:1px solid {{ $sc['color'] }}33;border-radius:12px;padding:12px 16px;display:flex;align-items:center;gap:10px">
        <div style="width:8px;height:8px;border-radius:50%;background:{{ $sc['color'] }};flex-shrink:0"></div>
        <div style="flex:1">
            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:{{ $sc['color'] }}">{{ $sc['label'] }}</div>
        </div>
        @if($hasFee)
        <div style="font-size:.72rem;font-weight:600;padding:2px 9px;border-radius:20px;background:{{ $pc['bg'] }};color:{{ $pc['color'] }}">{{ ucfirst($payStatus) }}</div>
        @endif
    </div>

    {{-- Quick actions --}}
    <div style="display:flex;flex-direction:column;gap:8px">
        @if($needsPay)
        <button type="button"
                @click="open({{ $appointment->id }}, {{ $appointment->fee }}, '{{ addslashes($name) }}', '{{ $appointment->appointment_number }}')"
                style="display:flex;align-items:center;justify-content:center;gap:7px;padding:11px;background:var(--plum);color:#fff;border:none;border-radius:10px;font-size:.85rem;font-weight:700;cursor:pointer;font-family:inherit;transition:opacity .15s"
                onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path stroke-linecap="round" d="M1 10h22"/></svg>
            Pay ₹{{ number_format($appointment->fee, 0) }}
        </button>
        @endif
        <a href="{{ route('patient.appointments.index') }}"
           style="display:flex;align-items:center;justify-content:center;gap:6px;padding:10px;background:var(--cream);border:1px solid var(--warm-bd);border-radius:10px;text-decoration:none;color:var(--txt-md);font-size:.82rem;font-weight:500;transition:background .15s"
           onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='var(--cream)'">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            All Appointments
        </a>
        <a href="{{ route('patient.appointments.book') }}"
           style="display:flex;align-items:center;justify-content:center;gap:7px;padding:11px;background:var(--plum);color:#fff;border:none;border-radius:10px;font-size:.85rem;font-weight:700;cursor:pointer;font-family:inherit;transition:opacity .15s"
                onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Book Another
        </a>
    </div>

</div>

</div>{{-- apt-grid --}}

{{-- ══ Payment Modal ════════════════════════════════════════════════════════════ --}}
<div class="pay-overlay" x-show="showModal" x-cloak @click.self="showModal=false">
    <div class="pay-box">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px">
            <div>
                <div style="font-family:'Lora',serif;font-size:1.15rem;color:var(--txt)">Pay Consultation Fee</div>
                <div x-text="doctorName" style="font-size:.8rem;color:var(--txt-lt);margin-top:2px"></div>
            </div>
            <button @click="showModal=false" style="width:28px;height:28px;border:1px solid var(--warm-bd);border-radius:8px;background:transparent;cursor:pointer;font-size:1.1rem;color:var(--txt-lt);line-height:1">×</button>
        </div>

        <div style="text-align:center;padding:22px;background:var(--parch);border-radius:13px;margin-bottom:20px">
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--txt-lt);margin-bottom:4px">Amount Due</div>
            <div x-text="'₹' + amount.toLocaleString('en-IN')" style="font-family:'Lora',serif;font-size:2.2rem;font-weight:500;color:var(--txt)"></div>
            <div x-text="aptNumber" style="font-size:.75rem;color:var(--txt-lt);margin-top:3px;font-family:monospace"></div>
        </div>

        <button type="button" @click="payWithRazorpay()"
                :disabled="processing"
                style="width:100%;padding:13px;background:var(--plum);color:#fff;border:none;border-radius:11px;font-size:.9375rem;font-weight:600;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px;transition:opacity .15s;margin-bottom:12px"
                :style="processing ? 'opacity:.6;cursor:not-allowed' : ''"
                onmouseover="if(!this.disabled) this.style.opacity='.88'" onmouseout="if(!this.disabled) this.style.opacity='1'">
            <span x-show="!processing">💳 Pay with Razorpay (Card / UPI / Net Banking)</span>
            <span x-show="processing" style="display:flex;align-items:center;gap:8px">
                <span style="width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite"></span>
                Processing…
            </span>
        </button>

        <div x-show="errorMsg" x-text="errorMsg"
             style="padding:9px 12px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;font-size:.8rem;color:#dc2626;text-align:center;margin-bottom:10px"></div>

        <div style="text-align:center;font-size:.7rem;color:var(--txt-lt)">🔒 Secured by Razorpay · PCI-DSS compliant</div>
    </div>
</div>

</div>{{-- x-data --}}
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
function aptPay() {
    return {
        showModal:     false,
        processing:    false,
        errorMsg:      '',
        amount:        0,
        doctorName:    '',
        aptNumber:     '',
        appointmentId: null,

        open(aptId, fee, doctor, aptNo) {
            this.appointmentId = aptId;
            this.amount        = fee;
            this.doctorName    = 'Dr. ' + doctor;
            this.aptNumber     = aptNo;
            this.errorMsg      = '';
            this.showModal     = true;
        },

        async payWithRazorpay() {
            this.processing = true;
            this.errorMsg   = '';
            try {
                const orderRes = await fetch('{{ route('patient.payments.order') }}', {
                    method:  'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify({ appointment_id: this.appointmentId }),
                });
                const order = await orderRes.json();
                if (!order.success) {
                    this.errorMsg = order.message || 'Could not create order.';
                    this.processing = false;
                    return;
                }

                const self = this;
                const rzp  = new Razorpay({
                    key:         order.key_id,
                    amount:      order.amount,
                    currency:    order.currency,
                    name:        order.name,
                    description: order.description,
                    order_id:    order.order_id,
                    prefill: {
                        name:    order.prefill_name,
                        contact: order.prefill_mobile,
                    },
                    theme: { color: '#4a3760' },
                    modal: {
                        ondismiss() { self.processing = false; }
                    },
                    handler: async function(response) {
                        const verRes = await fetch('{{ route('patient.payments.verify') }}', {
                            method:  'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept':       'application/json',
                            },
                            body: JSON.stringify({
                                razorpay_order_id:   response.razorpay_order_id,
                                razorpay_payment_id: response.razorpay_payment_id,
                                razorpay_signature:  response.razorpay_signature,
                            }),
                        });
                        const ver = await verRes.json();
                        if (ver.success) {
                            window.location.href = ver.receipt_url;
                        } else {
                            self.errorMsg   = ver.message || 'Payment verification failed.';
                            self.processing = false;
                        }
                    }
                });
                rzp.open();
            } catch (e) {
                this.errorMsg   = 'Something went wrong. Please try again.';
                this.processing = false;
            }
        }
    };
}
</script>
@endpush
