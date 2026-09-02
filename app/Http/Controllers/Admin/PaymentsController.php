<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    // Step 4: platform-wide list of every payment.
    public function index(Request $request)
    {
        $status   = $request->get('status');
        $method   = $request->get('method');
        $search   = $request->get('q');
        $from     = $request->get('from');
        $to       = $request->get('to');

        // "Due" is sourced from appointments (mirrors the doctor Payments page):
        // any appointment with a fee that hasn't been paid yet.
        if ($status === 'due') {
            $payments = $this->buildDueQuery($request);
            $totals   = $this->paymentTotals();

            return view('admin.payments.index', array_merge(
                compact('payments', 'totals', 'status', 'method', 'search', 'from', 'to'),
                ['source' => 'appointments']
            ));
        }

        $query = Payment::with([
            'user.profile',
            'appointment',
            'appointment.doctor.profile',
            'appointment.patient.profile',
        ])->latest('created_at');

        if ($status === 'paid') {
            $query->where('status', 'paid');
        } elseif ($status === 'failed') {
            $query->where('status', 'failed');
        }
        // Any other/unknown status value is intentionally ignored → "All"
        if ($method)   $query->where('payment_method', $method);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('razorpay_payment_id', 'like', "%{$search}%")
                  ->orWhere('razorpay_order_id', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('user.profile', fn($qq) => $qq->where('full_name', 'like', "%{$search}%"))
                  ->orWhereHas('appointment', fn($qq) => $qq->where('appointment_number', 'like', "%{$search}%"));
            });
        }
        if ($from) $query->whereDate('created_at', '>=', $from);
        if ($to)   $query->whereDate('created_at', '<=', $to);

        $payments = $query->paginate(25)->withQueryString();
        $totals   = $this->paymentTotals();

        return view('admin.payments.index', array_merge(
            compact('payments', 'totals', 'status', 'method', 'search', 'from', 'to'),
            ['source' => 'payments']
        ));
    }

    // Private helpers

    private function buildDueQuery(Request $request)
    {
        $method = $request->get('method');
        $search = $request->get('q');
        $from   = $request->get('from');
        $to     = $request->get('to');

        $query = Appointment::with([
            'doctor.profile',
            'patient.profile',
            'payment',
        ])
            ->where('payment_status', '!=', 'paid')
            ->whereNotNull('fee')
            ->where('fee', '>', 0)
            ->whereNotIn('status', ['cancelled']);

        if ($method === 'cash') {
            $query->where('payment_preference', 'cash');
        } elseif (in_array($method, ['razorpay', 'upi_qr'], true)) {
            $query->where(function ($q) {
                $q->whereNull('payment_preference')
                  ->orWhere('payment_preference', '!=', 'cash');
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('appointment_number', 'like', "%$search%")
                  ->orWhere('completion_note', 'like', "%$search%")
                  ->orWhereHas('doctor.profile',  fn($qq) => $qq->where('full_name', 'like', "%$search%"))
                  ->orWhereHas('patient.profile', fn($qq) => $qq->where('full_name', 'like', "%$search%"));
            });
        }

        if ($from) $query->whereDate('slot_datetime', '>=', $from);
        if ($to)   $query->whereDate('slot_datetime', '<=', $to);

        return $query->latest('slot_datetime')->paginate(25)->withQueryString();
    }

    private function paymentTotals(): array
    {
        // Platform-wide outstanding = sum of fees on appointments that haven't been paid yet.
        // Mirrors buildDueQuery() filter so the KPI matches what admin would see in the Due tab.
        $dueTotal = Appointment::where('payment_status', '!=', 'paid')
            ->whereNotNull('fee')
            ->where('fee', '>', 0)
            ->whereNotIn('status', ['cancelled'])
            ->sum('fee');

        return [
            'all'          => Payment::count(),
            'paid'         => Payment::where('status', 'paid')->sum('amount'),
            'pending'      => Payment::where('status', 'created')->sum('amount'),
            'failed'       => Payment::where('status', 'failed')->sum('amount'),
            'cash_total'   => Payment::where('status', 'paid')->where('payment_method', 'cash')->sum('amount'),
            'online_total' => Payment::where('status', 'paid')->whereIn('payment_method', ['razorpay','upi_qr'])->sum('amount'),
            'due_total'    => (float) $dueTotal,
        ];
    }
}
