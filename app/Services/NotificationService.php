<?php

namespace App\Services;

use App\Models\User;

class NotificationService
{

    // ── Strip duplicate "Dr." prefix ─────────────────────────────────────────
    private static function drName(string $name): string
    {
        // If name already starts with "Dr." or "Dr " don't add another one
        if (str_starts_with(strtolower($name), 'dr.') || str_starts_with(strtolower($name), 'dr ')) {
            return $name;
        }
        return 'Dr. ' . $name;
    }

    // ── Core creator ──────────────────────────────────────────────────────────

    public static function create(
        int    $userId,
        string $type,
        string $title,
        string $body,
        array  $data    = [],
        string $channel = 'in_app'
    ): void {
        try {
            \App\Models\Notification::create([
                'user_id' => $userId,
                'type'    => $type,
                'title'   => $title,
                'body'    => $body,
                'data'    => $data ?: null,
                'channel' => $channel,
                'is_read' => false,
            ]);
        } catch (\Throwable $e) {
            // Never crash the main flow due to notification failure
            \Illuminate\Support\Facades\Log::error('[NotificationService] Failed', [
                'user_id' => $userId, 'type' => $type, 'error' => $e->getMessage()
            ]);
        }
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // APPOINTMENT NOTIFICATIONS
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    public static function appointmentBooked(\App\Models\Appointment $apt): void
    {
        // $drName      = $apt->doctor?->profile?->full_name   ?? 'Doctor';
        $drName      = self::drName($apt->doctor?->profile?->full_name ?? 'Doctor');
        $patName     = $apt->patient?->profile?->full_name  ?? 'Patient';
        $dateTime    = $apt->slot_datetime->format('d M Y, h:i A');
        $aptNo       = $apt->appointment_number;

        // Notify patient
        self::create(
            userId: $apt->patient_user_id,
            type:   'appointment.booked',
            title:  'Appointment Booked',
            body:   "Your appointment with Dr. {$drName} is confirmed for {$dateTime}.",
            data:   ['appointment_id' => $apt->id, 'apt_number' => $aptNo],
        );

        // Notify doctor
        self::create(
            userId: $apt->doctor_user_id,
            type:   'appointment.new',
            title:  'New Appointment',
            body:   "{$patName} has booked an appointment on {$dateTime}.",
            data:   ['appointment_id' => $apt->id, 'patient_id' => $apt->patient_user_id],
        );
    }

    public static function appointmentConfirmed(\App\Models\Appointment $apt): void
    {
        $drName   = $apt->doctor?->profile?->full_name ?? 'Doctor';
        $dateTime = $apt->slot_datetime->format('d M Y, h:i A');

        self::create(
            userId: $apt->patient_user_id,
            type:   'appointment.confirmed',
            title:  'Appointment Confirmed ✓',
            body:   "Dr. {$drName} confirmed your appointment for {$dateTime}.",
            data:   ['appointment_id' => $apt->id],
        );
    }

    public static function appointmentCancelled(\App\Models\Appointment $apt, string $cancelledBy = 'doctor'): void
    {
        // $drName   = $apt->doctor?->profile?->full_name  ?? 'Doctor';
        $drName   = self::drName($apt->doctor?->profile?->full_name  ?? 'Doctor');     
        $patName  = $apt->patient?->profile?->full_name ?? 'Patient';
        $dateTime = $apt->slot_datetime->format('d M Y, h:i A');

        if ($cancelledBy === 'doctor') {
            // Notify patient
            self::create(
                userId: $apt->patient_user_id,
                type:   'appointment.cancelled',
                title:  'Appointment Cancelled',
                body:   "Dr. {$drName} cancelled the appointment for {$dateTime}. Please rebook.",
                data:   ['appointment_id' => $apt->id],
            );
        } else {
            // Notify doctor
            self::create(
                userId: $apt->doctor_user_id,
                type:   'appointment.cancelled',
                title:  'Appointment Cancelled',
                body:   "{$patName} cancelled the appointment for {$dateTime}.",
                data:   ['appointment_id' => $apt->id],
            );
        }
    }

    public static function appointmentCompleted(\App\Models\Appointment $apt): void
    {
        // $drName = $apt->doctor?->profile?->full_name ?? 'Doctor';
        $drName   = self::drName($apt->doctor?->profile?->full_name  ?? 'Doctor');     
        self::create(
            userId: $apt->patient_user_id,
            type:   'appointment.completed',
            title:  'Visit Completed',
            body:   "Your visit with Dr. {$drName} has been marked complete. Check your records for notes.",
            data:   ['appointment_id' => $apt->id],
        );
    }

    public static function appointmentReminder(\App\Models\Appointment $apt, int $hoursAhead): void
    {
        // $drName   = $apt->doctor?->profile?->full_name ?? 'Doctor';
        $drName   = self::drName($apt->doctor?->profile?->full_name  ?? 'Doctor');     
        $dateTime = $apt->slot_datetime->format('d M Y, h:i A');
        $label    = $hoursAhead === 24 ? 'tomorrow' : 'in 1 hour';

        self::create(
            userId: $apt->patient_user_id,
            type:   'appointment.reminder',
            title:  "Appointment Reminder ⏰",
            body:   "You have an appointment with Dr. {$drName} {$label} at {$dateTime}.",
            data:   ['appointment_id' => $apt->id, 'hours_ahead' => $hoursAhead],
        );
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // PRESCRIPTION NOTIFICATIONS
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    public static function prescriptionCreated(\App\Models\Prescription $rx): void
    {
        // $drName = $rx->doctor?->profile?->full_name ?? 'Doctor';
        $drName   = self::drName($rx->doctor?->profile?->full_name  ?? 'Doctor');     
        $rxNo   = $rx->prescription_number;

        self::create(
            userId: $rx->patient_user_id,
            type:   'prescription.created',
            title:  'New Prescription 💊',
            body:   "Dr. {$drName} has written a prescription ({$rxNo}) for you. Tap to view and download.",
            data:   ['prescription_id' => $rx->id, 'rx_number' => $rxNo],
        );
    }

    public static function prescriptionSentWhatsApp(\App\Models\Prescription $rx): void
    {
        $rxNo = $rx->prescription_number;

        self::create(
            userId: $rx->patient_user_id,
            type:   'prescription.whatsapp',
            title:  'Prescription Sent via WhatsApp',
            body:   "Prescription {$rxNo} has been sent to your WhatsApp number.",
            data:   ['prescription_id' => $rx->id],
        );
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // ACCESS CONTROL NOTIFICATIONS
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    public static function accessRequested(\App\Models\DoctorAccessRequest $req): void
    {
        // $drName = $req->doctor?->profile?->full_name ?? 'Doctor';
        $drName   = self::drName($req->doctor?->profile?->full_name  ?? 'Doctor');     
        self::create(
            userId: $req->patient_user_id,
            type:   'access.requested',
            title:  'Doctor Access Request 🔔',
            body:   "Dr. {$drName} is requesting access to your health records. Tap to approve or deny.",
            data:   ['request_id' => $req->id, 'doctor_id' => $req->doctor_user_id],
        );
    }

    public static function accessApproved(\App\Models\DoctorAccessRequest $req): void
    {
        $patName = $req->patient?->profile?->full_name ?? 'Patient';

        // Notify doctor
        self::create(
            userId: $req->doctor_user_id,
            type:   'access.approved',
            title:  'Access Approved ✓',
            body:   "{$patName} approved your access request. You can now view their records.",
            data:   ['request_id' => $req->id, 'patient_id' => $req->patient_user_id],
        );
    }

    public static function accessDenied(\App\Models\DoctorAccessRequest $req): void
    {
        $patName = $req->patient?->profile?->full_name ?? 'Patient';

        self::create(
            userId: $req->doctor_user_id,
            type:   'access.denied',
            title:  'Access Request Denied',
            body:   "{$patName} denied your access request.",
            data:   ['request_id' => $req->id],
        );
    }

    public static function accessRevoked(\App\Models\DoctorAccessRequest $req): void
    {
        $patName = $req->patient?->profile?->full_name ?? 'Patient';

        self::create(
            userId: $req->doctor_user_id,
            type:   'access.revoked',
            title:  'Access Revoked',
            body:   "{$patName} has revoked your access to their health records.",
            data:   ['request_id' => $req->id],
        );
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // DOCTOR VERIFICATION NOTIFICATIONS
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    public static function doctorVerified(User $doctor): void
    {
        self::create(
            userId: $doctor->id,
            type:   'doctor.verified',
            title:  'Account Verified ✅',
            body:   'Your account has been verified by admin. You can now accept appointments from patients.',
            data:   [],
        );
    }

    public static function doctorRejected(User $doctor, string $reason): void
    {
        self::create(
            userId: $doctor->id,
            type:   'doctor.rejected',
            title:  'Verification Rejected',
            body:   "Your verification was rejected. Reason: {$reason}. Please update your credentials.",
            data:   ['reason' => $reason],
        );
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // PAYMENT NOTIFICATIONS
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    public static function paymentReceived(\App\Models\Payment $payment): void
    {
        // $drName = $payment->appointment?->doctor?->profile?->full_name ?? 'Doctor';
        $drName   = self::drName($payment->appointment?->doctor?->profile?->full_name  ?? 'Doctor');     
        $amount = '₹' . number_format($payment->amount, 0);

        self::create(
            userId: $payment->user_id,
            type:   'payment.success',
            title:  'Payment Successful 💳',
            body:   "Payment of {$amount} to Dr. {$drName} was successful. Receipt is ready.",
            data:   ['payment_id' => $payment->id],
        );
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // TIMELINE NOTIFICATIONS
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    public static function timelineAssigned(\App\Models\PatientTimeline $pt): void
    {
        // $drName   = $pt->assignedByDoctor?->profile?->full_name ?? 'Doctor';
        $drName   = self::drName($pt->assignedByDoctor?->profile?->full_name  ?? 'Doctor');     
        $title    = $pt->template?->title ?? 'Care Timeline';

        self::create(
            userId: $pt->patient_user_id,
            type:   'timeline.assigned',
            title:  'Care Timeline Assigned 📅',
            body:   "Dr. {$drName} has assigned you a \"{$title}\" care timeline. View your milestones.",
            data:   ['timeline_id' => $pt->id],
        );
    }

    public static function milestoneDue(\App\Models\PatientTimeline $pt, string $milestoneTitle, int $daysAhead): void
    {
        $label = $daysAhead === 0 ? 'today' : "in {$daysAhead} day" . ($daysAhead > 1 ? 's' : '');

        self::create(
            userId: $pt->patient_user_id,
            type:   'timeline.milestone',
            title:  "Milestone Due {$label} ⏰",
            body:   "\"{$milestoneTitle}\" from your care timeline is due {$label}.",
            data:   ['timeline_id' => $pt->id, 'milestone' => $milestoneTitle],
        );
    }
}
