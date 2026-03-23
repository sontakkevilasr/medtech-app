@extends('layouts.patient')
@section('title', 'Payment Receipt')
@section('page-title')
    <a href="{{ route('patient.payments.index') }}" style="font-size:.85rem;font-weight:400;color:var(--txt-lt);text-decoration:none">Payments</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    Receipt · {{ $payment->razorpay_payment_id ?? 'PAY-'.$payment->id }}
@endsection

@push('styles')
<style>
@media print {
    .no-print { display: none !important; }
    .panel { border: 1px solid #ddd !important; box-shadow: none !important; }
    body { background: #fff !important; }
}
</style>
@endpush

@section('content')
<div class="fade-slide" style="max-width:600px;margin:0 auto">

{{-- Success banner --}}
@if($payment->isPaid())
<div style="text-align:center;padding:28px;background:linear-gradient(135deg,#10b981 0%,#059669 100%);border-radius:18px;margin-bottom:22px;color:#fff">
    <div style="font-size:3rem;margin-bottom:10px">✅</div>
    <div style="font-family:'Lora',serif;font-size:1.4rem;font-weight:500">Payment Successful</div>
    <div style="font-size:.875rem;opacity:.85;margin-top:4px">₹{{ number_format($payment->amount, 2) }} paid on {{ $payment->paid_at?->format('d M Y, h:i A') }}</div>
</div>
@else
<div style="text-align:center;padding:22px;background:#fef9ec;border:1.5px solid #fde68a;border-radius:14px;margin-bottom:22px">
    <div style="font-size:2rem;margin-bottom:8px">⏳</div>
    <div style="font-family:'Lora',serif;font-size:1.1rem;color:var(--txt)">Payment {{ ucfirst($payment->status) }}</div>
</div>
@endif

{{-- Receipt card --}}
<div class="panel" style="padding:0;overflow:hidden">

    {{-- Header --}}
    <div style="padding:20px 24px;border-bottom:1.5px solid var(--warm-bd);display:flex;align-items:center;justify-content:space-between">
        <div>
            <div style="font-family:'Lora',serif;font-size:1.1rem;color:var(--txt)">Naumah Clinic</div>
            <div style="font-size:.72rem;color:var(--txt-lt)">Payment Receipt</div>
        </div>
        <div style="text-align:right">
            <div style="font-size:.72rem;color:var(--txt-lt)">Receipt No.</div>
            <div style="font-family:'Lora',serif;font-size:.95rem;font-weight:500;color:var(--txt)">
                {{ $payment->razorpay_payment_id ?? 'PAY-'.str_pad($payment->id, 8, '0', STR_PAD_LEFT) }}
            </div>
        </div>
    </div>

    {{-- Appointment details --}}
    <div style="padding:20px 24px;border-bottom:1px solid var(--warm-bd)">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);margin-bottom:12px">Appointment Details</div>
        @php $apt = $payment->appointment; @endphp
        @if($apt)
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            @foreach([
                'Appointment No.' => $apt->appointment_number,
                'Doctor'          => 'Dr. '.($apt->doctor?->profile?->full_name ?? ''),
                'Specialization'  => $apt->doctor?->doctorProfile?->specialization ?? '—',
                'Clinic'          => $apt->doctor?->doctorProfile?->clinic_name ?? '—',
                'Date & Time'     => $apt->slot_datetime->format('d M Y, h:i A'),
                'Type'            => ucwords(str_replace('_',' ',$apt->type)),
            ] as $k => $v)
            @if($v && $v !== '—')
            <div>
                <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:2px">{{ $k }}</div>
                <div style="font-size:.8125rem;color:var(--txt-md)">{{ $v }}</div>
            </div>
            @endif
            @endforeach
        </div>
        @endif
    </div>

    {{-- Payment details --}}
    <div style="padding:20px 24px;border-bottom:1px solid var(--warm-bd)">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);margin-bottom:12px">Payment Details</div>
        @foreach([
            'Amount'         => '₹'.number_format($payment->amount, 2).' '.$payment->currency,
            'Method'         => ucwords(str_replace('_',' ',$payment->payment_method)),
            'Status'         => ucfirst($payment->status),
            'Razorpay ID'    => $payment->razorpay_payment_id ?? '—',
            'Order ID'       => $payment->razorpay_order_id ?? '—',
            'Paid At'        => $payment->paid_at?->format('d M Y, h:i A') ?? '—',
        ] as $k => $v)
        <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--parch);font-size:.8125rem">
            <span style="color:var(--txt-lt)">{{ $k }}</span>
            <span style="color:{{ $k==='Status' && $payment->isPaid() ? '#065f46' : 'var(--txt-md)' }};font-weight:{{ $k==='Amount' || $k==='Status' ? '600' : '400' }}">{{ $v }}</span>
        </div>
        @endforeach
    </div>

    {{-- Total --}}
    <div style="padding:18px 24px;display:flex;justify-content:space-between;align-items:center;background:var(--parch)">
        <span style="font-family:'Lora',serif;font-size:1rem;color:var(--txt)">Total Paid</span>
        <span style="font-family:'Lora',serif;font-size:1.5rem;font-weight:500;color:var(--plum)">
            ₹{{ number_format($payment->amount, 2) }}
        </span>
    </div>
</div>

{{-- Actions --}}
<div class="no-print" style="display:flex;gap:10px;margin-top:16px">
    <button onclick="window.print()"
            style="flex:1;padding:10px;background:var(--plum);color:#fff;border:none;border-radius:11px;font-size:.875rem;font-weight:600;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:opacity .15s"
            onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
        🖨️ Print Receipt
    </button>
    <a href="{{ route('patient.payments.index') }}"
       style="flex:1;text-align:center;padding:10px;border:1.5px solid var(--warm-bd);border-radius:11px;font-size:.875rem;font-weight:500;color:var(--txt-md);text-decoration:none;transition:background .12s"
       onmouseover="this.style.background='var(--sand)'" onmouseout="this.style.background='transparent'">
        ← Back to Payments
    </a>
</div>

<div class="no-print" style="text-align:center;font-size:.72rem;color:var(--txt-lt);margin-top:14px">
    For payment disputes contact support. This is a computer-generated receipt.
</div>

</div>
@endsection
