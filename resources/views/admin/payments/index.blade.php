@extends('layouts.admin')
@section('title', 'Platform Payments')
@section('page-title', 'Platform Payments')

@section('content')
@php
    $fmt = fn($n) => '₹' . number_format((float) $n, 2);
    $methodLabel = function($m) {
        return match($m) {
            'razorpay' => 'Online (Razorpay)',
            'upi_qr'   => 'UPI QR',
            'cash'     => 'Cash',
            'other'    => 'Other',
            default    => ucfirst((string) $m),
        };
    };
    $statusBadge = function($s) {
        return match($s) {
            'paid'     => ['#d1fae5','#065f46'],
            'created'  => ['#fef9c3','#854d0e'],
            'failed'   => ['#fee2e2','#991b1b'],
            default    => ['#f3f4f6','#374151'],
        };
    };
@endphp

<div class="admin-kpi-grid" style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:18px">
    <div class="stat-card" style="background:var(--card);border:1px solid var(--bd);border-radius:12px;padding:16px 18px">
        <div style="font-size:.7rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt-lt)">Total Payments</div>
        <div style="font-size:1.6rem;font-weight:700;color:var(--txt);margin-top:6px">{{ number_format($totals['all']) }}</div>
    </div>
    <div class="stat-card" style="background:var(--card);border:1px solid var(--bd);border-radius:12px;padding:16px 18px">
        <div style="font-size:.7rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt-lt)">Paid (all time)</div>
        <div style="font-size:1.6rem;font-weight:700;color:#065f46;margin-top:6px">{{ $fmt($totals['paid']) }}</div>
    </div>
    <div class="stat-card" style="background:var(--card);border:1px solid var(--bd);border-radius:12px;padding:16px 18px">
        <div style="font-size:.7rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt-lt)">Online (Razorpay+UPI)</div>
        <div style="font-size:1.6rem;font-weight:700;color:#1e40af;margin-top:6px">{{ $fmt($totals['online_total']) }}</div>
    </div>
    <div class="stat-card" style="background:var(--card);border:1px solid var(--bd);border-radius:12px;padding:16px 18px">
        <div style="font-size:.7rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt-lt)">Cash collected</div>
        <div style="font-size:1.6rem;font-weight:700;color:#166534;margin-top:6px">{{ $fmt($totals['cash_total']) }}</div>
    </div>
    <div class="stat-card" style="background:var(--card);border:1px solid var(--bd);border-radius:12px;padding:16px 18px">
        <div style="font-size:.7rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt-lt)">Total Owed</div>
        <div style="font-size:1.6rem;font-weight:700;color:#b45309;margin-top:6px">{{ $fmt($totals['due_total'] ?? 0) }}</div>
        <div style="font-size:.7rem;color:var(--txt-lt);margin-top:4px">Unpaid appointment fees</div>
    </div>
</div>


<form method="GET" action="{{ route('admin.payments.index') }}" class="admin-filter-form" style="display:flex;flex-wrap:wrap;gap:10px;align-items:end;background:var(--card);border:1px solid var(--bd);border-radius:12px;padding:14px 16px;margin-bottom:14px">
    <div class="filter-search" style="flex:1;min-width:200px">
        <label style="display:block;font-size:.68rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:4px">Search</label>
        <input type="text" name="q" value="{{ $search }}" class="inp" placeholder="Patient, doctor, ID, notes..." style="width:100%">
    </div>
    <div>
        <label style="display:block;font-size:.68rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:4px">Status</label>
        <select name="status" class="inp">
            <option value="">All</option>
            <option value="paid"    {{ $status === 'paid'    ? 'selected' : '' }}>Paid</option>
            <option value="due"     {{ $status === 'due'     ? 'selected' : '' }}>Due</option>
            <option value="failed"  {{ $status === 'failed'  ? 'selected' : '' }}>Failed</option>
        </select>
    </div>
    <div>
        <label style="display:block;font-size:.68rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:4px">Method</label>
        <select name="method" class="inp">
            <option value="">All</option>
            <option value="razorpay" {{ $method === 'razorpay' ? 'selected' : '' }}>Razorpay</option>
            <option value="upi_qr"   {{ $method === 'upi_qr' ? 'selected' : '' }}>UPI QR</option>
            <option value="cash"     {{ $method === 'cash' ? 'selected' : '' }}>Cash</option>
            <option value="other"    {{ $method === 'other' ? 'selected' : '' }}>Other</option>
        </select>
    </div>
    <div>
        <label style="display:block;font-size:.68rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:4px">From</label>
        <input type="date" name="from" value="{{ $from }}" class="inp">
    </div>
    <div>
        <label style="display:block;font-size:.68rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:4px">To</label>
        <input type="date" name="to" value="{{ $to }}" class="inp">
    </div>
    <div>
        <button type="submit" class="btn btn-primary">Filter</button>
        @if($status || $method || $search || $from || $to)
            <a href="{{ route('admin.payments.index') }}" class="btn btn-ghost" style="margin-left:6px">Clear</a>
        @endif
    </div>
</form>

<div class="admin-card" style="background:var(--card);border:1px solid var(--bd);border-radius:12px;overflow:hidden">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Doctor</th>
                    <th>Patient</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
            @forelse($payments as $pay)
                @if(($source ?? 'payments') === 'appointments')
                    @php
                        $doc = $pay->doctor;
                        $pat = $pay->patient;
                        $prefLabel = $pay->payment_preference === 'cash' ? 'Cash expected' : 'Online pending';
                    @endphp
                    <tr>
                        <td data-label="Date">
                            <div style="font-weight:500;color:var(--txt)">{{ $pay->slot_datetime?->format('d M Y') }}</div>
                            <div style="font-size:.72rem;color:var(--txt-lt)">{{ $pay->slot_datetime?->format('h:i A') }}</div>
                        </td>
                        <td data-label="Doctor">
                            <div style="font-weight:500;color:var(--txt)">{{ $doc?->profile?->full_name ?? '—' }}</div>
                            <div style="font-size:.72rem;color:var(--txt-lt)">{{ $doc?->email ?? '' }}</div>
                        </td>
                        <td data-label="Patient">
                            <div style="font-weight:500;color:var(--txt)">{{ $pat?->profile?->full_name ?? '—' }}</div>
                            <div style="font-size:.72rem;color:var(--txt-lt);font-family:monospace">{{ $pay->appointment_number }}</div>
                        </td>
                        <td data-label="Method">
                            <span class="badge badge-gray">{{ $prefLabel }}</span>
                        </td>
                        <td data-label="Status">
                            <span class="badge" style="background:#fef9c3;color:#854d0e">Due</span>
                        </td>
                        <td data-label="Amount" style="font-weight:600;color:var(--txt)">{{ $fmt($pay->fee) }} INR</td>
                        <td data-label="Notes" style="max-width:280px;white-space:normal;font-size:.78rem;color:var(--txt-md)">
                            @if($pay->completion_note)
                                <span style="color:#475569"><em>{{ \Illuminate\Support\Str::limit($pay->completion_note, 100) }}</em></span>
                            @else
                                <span style="color:var(--txt-lt)">—</span>
                            @endif
                            @if($pay->payment_requested_at)
                                <div style="font-size:.7rem;color:#b45309;margin-top:2px">
                                    Payment requested {{ $pay->payment_requested_at->diffForHumans(null, true, true) }} ago
                                </div>
                            @endif
                        </td>
                    </tr>
                @else
                    @php
                        $doc = $pay->appointment?->doctor;
                        $pat = $pay->appointment?->patient;
                        [$bg, $fg] = $statusBadge($pay->status);
                    @endphp
                <tr>
                    <td data-label="Date">
                        <div style="font-weight:500;color:var(--txt)">{{ $pay->created_at?->format('d M Y') }}</div>
                        <div style="font-size:.72rem;color:var(--txt-lt)">{{ $pay->created_at?->format('h:i A') }}</div>
                    </td>
                    <td data-label="Doctor">
                        <div style="font-weight:500;color:var(--txt)">{{ $doc?->profile?->full_name ?? '—' }}</div>
                        <div style="font-size:.72rem;color:var(--txt-lt)">{{ $doc?->email ?? '' }}</div>
                    </td>
                    <td data-label="Patient">
                        <div style="font-weight:500;color:var(--txt)">{{ $pat?->profile?->full_name ?? ($pay->user?->profile?->full_name ?? '—') }}</div>
                        @if($pay->appointment?->appointment_number)
                            <div style="font-size:.72rem;color:var(--txt-lt);font-family:monospace">{{ $pay->appointment->appointment_number }}</div>
                        @endif
                    </td>
                    <td data-label="Method">
                        <span class="badge badge-gray">{{ $methodLabel($pay->payment_method) }}</span>
                    </td>
                    <td data-label="Status">
                        <span class="badge" style="background:{{ $bg }};color:{{ $fg }}">{{ ucfirst($pay->status) }}</span>
                    </td>
                    <td data-label="Amount" style="font-weight:600;color:var(--txt)">{{ $fmt($pay->amount) }} {{ $pay->currency }}</td>
                    <td data-label="Notes" style="max-width:280px;white-space:normal;font-size:.78rem;color:var(--txt-md)">
                        {{ \Illuminate\Support\Str::limit($pay->notes ?? '—', 100) }}
                        @if($pay->razorpay_payment_id)
                            <div style="font-size:.7rem;color:var(--txt-lt);margin-top:2px;font-family:monospace">Rzp: {{ $pay->razorpay_payment_id }}</div>
                        @endif
                    </td>
                </tr>
                @endif
            @empty
                <tr>
                    <td colspan="7" style="padding:30px;text-align:center;color:var(--txt-lt)">
                        @if(($source ?? 'payments') === 'appointments')
                            No outstanding appointments. Every appointment with a fee has been paid.
                        @else
                            No payments found.
                        @endif
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pager">
        <span>{{ $payments->total() }} total</span>
        <div>{{ $payments->links() }}</div>
    </div>
</div>
@endsection