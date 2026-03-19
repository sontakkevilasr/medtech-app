@extends('layouts.patient')
@section('title', 'Payments')
@section('page-title', 'Payments')

@push('styles')
<style>
.pay-row { transition: background .12s; }
.pay-row:hover { background: var(--sand); }

.status-paid     { background: #d1fae5; color: #065f46; }
.status-created  { background: #fef9c3; color: #854d0e; }
.status-failed   { background: #fee2e2; color: #991b1b; }
.status-refunded { background: #e0e7ff; color: #3730a3; }

@keyframes spin    { to { transform: rotate(360deg); } }
@keyframes fadeIn  { from { opacity:0; transform:scale(.95); } to { opacity:1; transform:scale(1); } }
.modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.45);
    display: flex; align-items: center; justify-content: center;
    z-index: 1000; animation: fadeIn .18s ease;
}
.modal-box {
    background: #fff; border-radius: 18px; padding: 28px 30px;
    width: 420px; max-width: 94vw; box-shadow: 0 20px 60px rgba(0,0,0,.2);
}
</style>
@endpush

@section('content')
<div class="fade-slide" x-data="payments()">

{{-- ── Stats strip ──────────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:22px">
    <div class="panel" style="padding:16px 18px;text-align:center">
        <div style="font-family:'Lora',serif;font-size:1.8rem;font-weight:500;color:var(--txt)">
            ₹{{ number_format($totalPaid, 0) }}
        </div>
        <div style="font-size:.75rem;font-weight:600;color:var(--txt-md);margin-top:3px">Total Paid</div>
        <div style="font-size:.7rem;color:var(--txt-lt)">all time</div>
    </div>
    <div class="panel" style="padding:16px 18px;text-align:center">
        <div style="font-family:'Lora',serif;font-size:1.8rem;font-weight:500;color:{{ $unpaidApts->count() ? 'var(--rose)' : 'var(--txt)' }}">
            {{ $unpaidApts->count() }}
        </div>
        <div style="font-size:.75rem;font-weight:600;color:var(--txt-md);margin-top:3px">Pending</div>
        <div style="font-size:.7rem;color:var(--txt-lt)">payments due</div>
    </div>
    <div class="panel" style="padding:16px 18px;text-align:center">
        <div style="font-family:'Lora',serif;font-size:1.8rem;font-weight:500;color:var(--txt)">
            {{ $payments->total() }}
        </div>
        <div style="font-size:.75rem;font-weight:600;color:var(--txt-md);margin-top:3px">Total</div>
        <div style="font-size:.7rem;color:var(--txt-lt)">transactions</div>
    </div>
</div>

{{-- ── Unpaid appointments ───────────────────────────────────────────────────── --}}
@if($unpaidApts->isNotEmpty())
<div style="margin-bottom:22px">
    <div style="font-family:'Lora',serif;font-size:1rem;font-weight:500;color:var(--txt);margin-bottom:12px;display:flex;align-items:center;gap:8px">
        <span style="width:8px;height:8px;border-radius:50%;background:var(--rose);animation:pulse 1.5s infinite;display:inline-block"></span>
        Pending Payments
    </div>
    <div style="display:flex;flex-direction:column;gap:9px">
        @foreach($unpaidApts as $apt)
        <div class="panel" style="padding:15px 20px;display:flex;align-items:center;gap:14px">
            <div style="width:42px;height:42px;border-radius:11px;background:var(--parch);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--txt-lt)" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-weight:600;font-size:.9rem;color:var(--txt)">
                    Dr. {{ $apt->doctor?->profile?->full_name }}
                </div>
                <div style="font-size:.75rem;color:var(--txt-lt)">
                    {{ $apt->slot_datetime->format('d M Y, h:i A') }}
                    @if($apt->doctor?->doctorProfile?->specialization)
                    · {{ $apt->doctor->doctorProfile->specialization }}
                    @endif
                </div>
            </div>
            <div style="text-align:right;flex-shrink:0">
                <div style="font-family:'Lora',serif;font-size:1.2rem;font-weight:500;color:var(--txt)">
                    ₹{{ number_format($apt->fee, 0) }}
                </div>
                <button type="button"
                        @click="openCheckout({{ $apt->id }}, {{ $apt->fee }}, 'Dr. {{ addslashes($apt->doctor?->profile?->full_name ?? '') }}', '{{ $apt->appointment_number }}')"
                        style="margin-top:6px;padding:6px 16px;background:var(--plum);color:#fff;border:none;border-radius:9px;font-size:.78rem;font-weight:600;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:opacity .15s"
                        onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                    Pay Now
                </button>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── Payment history ──────────────────────────────────────────────────────── --}}
<div style="font-family:'Lora',serif;font-size:1rem;font-weight:500;color:var(--txt);margin-bottom:12px">
    Payment History
</div>

@if($payments->isEmpty())
<div class="panel" style="padding:40px;text-align:center;color:var(--txt-lt)">
    <div style="font-size:2rem;margin-bottom:10px">💳</div>
    <div style="font-family:'Lora',serif;color:var(--txt-md)">No payments yet</div>
</div>
@else
<div class="panel" style="padding:0;overflow:hidden">
    <table style="width:100%;border-collapse:collapse">
        <thead><tr style="border-bottom:1.5px solid var(--warm-bd)">
            <th style="padding:9px 18px;text-align:left;font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt-lt)">Doctor</th>
            <th style="padding:9px 18px;text-align:left;font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt-lt)">Appointment</th>
            <th style="padding:9px 18px;text-align:left;font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt-lt)">Amount</th>
            <th style="padding:9px 18px;text-align:left;font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt-lt)">Status</th>
            <th style="padding:9px 18px;text-align:left;font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt-lt)">Date</th>
            <th style="padding:9px 18px"></th>
        </tr></thead>
        <tbody>
        @foreach($payments as $pay)
        <tr class="pay-row" style="border-bottom:1px solid var(--warm-bd)">
            <td style="padding:12px 18px;font-size:.875rem;font-weight:500;color:var(--txt)">
                Dr. {{ $pay->appointment?->doctor?->profile?->full_name ?? '—' }}
                @if($pay->appointment?->doctor?->doctorProfile?->specialization)
                <div style="font-size:.7rem;color:var(--txt-lt)">{{ $pay->appointment->doctor->doctorProfile->specialization }}</div>
                @endif
            </td>
            <td style="padding:12px 18px;font-size:.8rem;color:var(--txt-md)">
                {{ $pay->appointment?->appointment_number ?? '—' }}
                @if($pay->appointment?->slot_datetime)
                <div style="font-size:.7rem;color:var(--txt-lt)">{{ $pay->appointment->slot_datetime->format('d M Y') }}</div>
                @endif
            </td>
            <td style="padding:12px 18px;font-family:'Lora',serif;font-size:1rem;font-weight:500;color:var(--txt)">
                ₹{{ number_format($pay->amount, 0) }}
            </td>
            <td style="padding:12px 18px">
                <span class="badge status-{{ $pay->status }}" style="font-size:.68rem;font-weight:700;padding:3px 9px;border-radius:20px">
                    {{ ucfirst($pay->status) }}
                </span>
            </td>
            <td style="padding:12px 18px;font-size:.75rem;color:var(--txt-lt)">
                {{ $pay->paid_at ? $pay->paid_at->format('d M Y') : $pay->created_at->format('d M Y') }}
            </td>
            <td style="padding:12px 18px;text-align:right">
                @if($pay->isPaid())
                <a href="{{ route('patient.payments.receipt', $pay) }}"
                   style="font-size:.72rem;padding:4px 11px;border:1.5px solid var(--warm-bd);border-radius:8px;color:var(--txt-md);text-decoration:none;transition:background .12s"
                   onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
                    Receipt
                </a>
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>

    @if($payments->hasPages())
    <div style="display:flex;justify-content:center;align-items:center;gap:6px;padding:12px 16px;border-top:1px solid var(--warm-bd)">
        @if(!$payments->onFirstPage())
        <a href="{{ $payments->previousPageUrl() }}" style="padding:6px 14px;border:1.5px solid var(--warm-bd);border-radius:8px;font-size:.8rem;text-decoration:none;color:var(--txt-md)">← Prev</a>
        @endif
        <span style="font-size:.75rem;color:var(--txt-lt)">{{ $payments->currentPage() }} / {{ $payments->lastPage() }}</span>
        @if($payments->hasMorePages())
        <a href="{{ $payments->nextPageUrl() }}" style="padding:6px 14px;border:1.5px solid var(--warm-bd);border-radius:8px;font-size:.8rem;text-decoration:none;color:var(--txt-md)">Next →</a>
        @endif
    </div>
    @endif
</div>
@endif

{{-- ── Checkout Modal ────────────────────────────────────────────────────────── --}}
<div class="modal-overlay" x-show="showModal" x-transition @click.self="showModal=false" style="display:none">
    <div class="modal-box">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px">
            <div>
                <div style="font-family:'Lora',serif;font-size:1.2rem;color:var(--txt)">Pay Consultation Fee</div>
                <div x-text="doctorName" style="font-size:.82rem;color:var(--txt-lt);margin-top:2px"></div>
            </div>
            <button @click="showModal=false" style="width:28px;height:28px;border:1px solid var(--warm-bd);border-radius:8px;background:transparent;cursor:pointer;font-size:1rem;color:var(--txt-lt)">×</button>
        </div>

        {{-- Amount display --}}
        <div style="text-align:center;padding:20px;background:var(--parch);border-radius:13px;margin-bottom:20px">
            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--txt-lt);margin-bottom:4px">Amount Due</div>
            <div x-text="'₹' + amount.toLocaleString('en-IN')" style="font-family:'Lora',serif;font-size:2.2rem;font-weight:500;color:var(--txt)"></div>
            <div x-text="aptNumber" style="font-size:.75rem;color:var(--txt-lt);margin-top:3px"></div>
        </div>

        {{-- Pay buttons --}}
        <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:12px">
            <button type="button" @click="payWithRazorpay()"
                    :disabled="processing"
                    style="padding:12px;background:var(--plum);color:#fff;border:none;border-radius:11px;font-size:.9rem;font-weight:600;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;display:flex;align-items:center;justify-content:center;gap:8px;transition:opacity .15s"
                    :style="processing ? 'opacity:.6;cursor:not-allowed' : ''"
                    onmouseover="if(!this.disabled) this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                <span x-show="!processing">💳 Pay with Razorpay (Card / UPI / Net Banking)</span>
                <span x-show="processing" style="display:flex;align-items:center;gap:8px">
                    <span style="width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite"></span>
                    Processing…
                </span>
            </button>
        </div>

        {{-- Error message --}}
        <div x-show="errorMsg" x-text="errorMsg"
             style="padding:9px 12px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;font-size:.8rem;color:#dc2626;text-align:center"></div>

        <div style="text-align:center;font-size:.72rem;color:var(--txt-lt);margin-top:10px">
            🔒 Secured by Razorpay · PCI-DSS compliant
        </div>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
function payments() {
    return {
        showModal:     false,
        processing:    false,
        errorMsg:      '',
        amount:        0,
        doctorName:    '',
        aptNumber:     '',
        appointmentId: null,

        openCheckout(aptId, fee, doctorName, aptNo) {
            this.appointmentId = aptId;
            this.amount        = fee;
            this.doctorName    = 'Dr. ' + doctorName;
            this.aptNumber     = aptNo;
            this.errorMsg      = '';
            this.showModal     = true;
        },

        async payWithRazorpay() {
            this.processing = true;
            this.errorMsg   = '';

            try {
                // Step 1: Create order
                const orderRes = await fetch('{{ route('patient.payments.order') }}', {
                    method:  'POST',
                    headers: {
                        'X-CSRF-TOKEN':  '{{ csrf_token() }}',
                        'Content-Type':  'application/json',
                        'Accept':        'application/json',
                    },
                    body: JSON.stringify({ appointment_id: this.appointmentId }),
                });

                const order = await orderRes.json();
                if (!order.success) {
                    this.errorMsg = order.message || 'Could not create order.';
                    this.processing = false;
                    return;
                }

                // Step 2: Open Razorpay checkout
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
                        // Step 3: Verify on server
                        const verRes = await fetch('{{ route('patient.payments.verify') }}', {
                            method:  'POST',
                            headers: {
                                'X-CSRF-TOKEN':  '{{ csrf_token() }}',
                                'Content-Type':  'application/json',
                                'Accept':        'application/json',
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
