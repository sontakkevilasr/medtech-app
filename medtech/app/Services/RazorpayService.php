<?php

namespace App\Services;

use App\Models\User;
use App\Models\Payment;
use App\Models\Appointment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class RazorpayService
{
    private string $keyId;
    private string $keySecret;
    private string $baseUrl = 'https://api.razorpay.com/v1';

    public function __construct()
    {
        $this->keyId     = config('services.razorpay.key_id');
        $this->keySecret = config('services.razorpay.key_secret');
    }

    // ─── Order Creation ──────────────────────────────────────────────────────

    /**
     * Create a Razorpay order for an appointment payment.
     * Returns the order data needed to initialise Razorpay checkout JS.
     */
    public function createAppointmentOrder(Appointment $appointment): array
    {
        $amountPaisa = (int) ($appointment->fee * 100); // Razorpay uses paise

        $order = $this->createOrder($amountPaisa, [
            'receipt'   => $appointment->appointment_number,
            'notes'     => [
                'appointment_id' => $appointment->id,
                'patient_id'     => $appointment->patient_user_id,
                'doctor_id'      => $appointment->doctor_user_id,
                'type'           => 'appointment',
            ],
        ]);

        if (! $order) {
            return ['success' => false, 'message' => 'Could not create payment order.'];
        }

        // Save pending payment record
        $payment = Payment::create([
            'user_id'            => $appointment->patient_user_id,
            'appointment_id'     => $appointment->id,
            'razorpay_order_id'  => $order['id'],
            'amount'             => $appointment->fee,
            'currency'           => 'INR',
            'status'             => 'created',
            'purpose'            => 'appointment',
            'payment_method'     => 'razorpay',
        ]);

        return [
            'success'          => true,
            'order_id'         => $order['id'],
            'amount'           => $amountPaisa,
            'currency'         => 'INR',
            'key_id'           => $this->keyId,
            'payment_id'       => $payment->id,
            'appointment_number' => $appointment->appointment_number,
        ];
    }

    /**
     * Create a Razorpay order for a doctor subscription.
     */
    public function createSubscriptionOrder(User $doctor, string $plan, float $amount): array
    {
        $amountPaisa = (int) ($amount * 100);

        $order = $this->createOrder($amountPaisa, [
            'receipt' => 'SUB-' . $doctor->id . '-' . time(),
            'notes'   => [
                'doctor_id' => $doctor->id,
                'plan'      => $plan,
                'type'      => 'subscription',
            ],
        ]);

        if (! $order) {
            return ['success' => false, 'message' => 'Could not create payment order.'];
        }

        $payment = Payment::create([
            'user_id'           => $doctor->id,
            'razorpay_order_id' => $order['id'],
            'amount'            => $amount,
            'currency'          => 'INR',
            'status'            => 'created',
            'purpose'           => 'subscription',
            'payment_method'    => 'razorpay',
        ]);

        return [
            'success'    => true,
            'order_id'   => $order['id'],
            'amount'     => $amountPaisa,
            'currency'   => 'INR',
            'key_id'     => $this->keyId,
            'payment_id' => $payment->id,
        ];
    }

    // ─── Payment Verification ────────────────────────────────────────────────

    /**
     * Verify a Razorpay payment signature and mark it as paid.
     * Returns ['success' => bool, 'payment' => Payment|null]
     */
    public function verifyPayment(
        string $razorpayOrderId,
        string $razorpayPaymentId,
        string $razorpaySignature
    ): array {
        // Verify signature
        $expectedSignature = hash_hmac(
            'sha256',
            "{$razorpayOrderId}|{$razorpayPaymentId}",
            $this->keySecret
        );

        if (! hash_equals($expectedSignature, $razorpaySignature)) {
            Log::warning('[Razorpay] Signature mismatch', [
                'order_id'   => $razorpayOrderId,
                'payment_id' => $razorpayPaymentId,
            ]);
            return ['success' => false, 'message' => 'Payment verification failed.'];
        }

        // Update payment record
        $payment = Payment::where('razorpay_order_id', $razorpayOrderId)->first();

        if (! $payment) {
            Log::error('[Razorpay] Payment record not found', ['order_id' => $razorpayOrderId]);
            return ['success' => false, 'message' => 'Payment record not found.'];
        }

        $payment->update([
            'razorpay_payment_id' => $razorpayPaymentId,
            'razorpay_signature'  => $razorpaySignature,
            'status'              => 'paid',
            'paid_at'             => now(),
        ]);

        // Update related appointment payment status
        if ($payment->appointment_id) {
            $payment->appointment->update(['payment_status' => 'paid']);
        }

        Log::info('[Razorpay] Payment verified', [
            'payment_id' => $razorpayPaymentId,
            'amount'     => $payment->amount,
        ]);

        return ['success' => true, 'payment' => $payment->fresh()];
    }

    /**
     * Handle Razorpay webhook events.
     */
    public function handleWebhook(array $payload, string $signature): bool
    {
        // Verify webhook signature
        $expectedSig = hash_hmac('sha256', json_encode($payload), config('services.razorpay.webhook_secret'));

        if (! hash_equals($expectedSig, $signature)) {
            Log::warning('[Razorpay Webhook] Invalid signature');
            return false;
        }

        $event = $payload['event'] ?? '';

        match ($event) {
            'payment.captured' => $this->handlePaymentCaptured($payload['payload']['payment']['entity']),
            'payment.failed'   => $this->handlePaymentFailed($payload['payload']['payment']['entity']),
            'refund.created'   => $this->handleRefundCreated($payload['payload']['refund']['entity']),
            default            => Log::info("[Razorpay Webhook] Unhandled event: {$event}"),
        };

        return true;
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    private function createOrder(int $amountPaisa, array $extra = []): ?array
    {
        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post("{$this->baseUrl}/orders", array_merge([
                    'amount'   => $amountPaisa,
                    'currency' => 'INR',
                ], $extra));

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('[Razorpay] Order creation failed', ['body' => $response->body()]);
            return null;

        } catch (\Throwable $e) {
            Log::error('[Razorpay] Exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function handlePaymentCaptured(array $entity): void
    {
        $payment = Payment::where('razorpay_payment_id', $entity['id'])->first();
        $payment?->update(['status' => 'paid', 'paid_at' => now()]);
    }

    private function handlePaymentFailed(array $entity): void
    {
        $payment = Payment::where('razorpay_order_id', $entity['order_id'])->first();
        $payment?->update(['status' => 'failed']);
    }

    private function handleRefundCreated(array $entity): void
    {
        $payment = Payment::where('razorpay_payment_id', $entity['payment_id'])->first();
        $payment?->update(['status' => 'refunded']);
    }
}
