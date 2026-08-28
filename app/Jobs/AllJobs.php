<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Appointment;
use App\Services\WhatsAppService;

// ─── SendAppointmentReminderJob ───────────────────────────────────────────────

class SendAppointmentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60; // seconds between retries

    public function __construct(
        private int    $appointmentId,
        private string $when   // '24h' or '1h'
    ) {}

    public function handle(WhatsAppService $whatsApp): void
    {
        $appointment = Appointment::find($this->appointmentId);

        if (! $appointment) {
            Log::warning("[ReminderJob] Appointment not found: {$this->appointmentId}");
            return;
        }

        // Don't send if appointment was cancelled
        if (in_array($appointment->status, ['cancelled', 'completed', 'no_show'])) {
            Log::info("[ReminderJob] Skipped — status: {$appointment->status}");
            return;
        }

        // Don't resend if already sent
        $alreadySentField = $this->when === '1h' ? 'reminder_1h_sent' : 'reminder_24h_sent';
        if ($appointment->$alreadySentField) {
            return;
        }

        $sent = $whatsApp->sendAppointmentReminder($appointment, $this->when);

        if ($sent) {
            $appointment->update([$alreadySentField => true]);
            Log::info("[ReminderJob] {$this->when} reminder sent", ['appointment' => $this->appointmentId]);
        }
    }
}


// ─── SendWhatsAppReminderJob ──────────────────────────────────────────────────

class SendWhatsAppReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(
        private int    $userId,
        private string $type,     // 'follow_up' | 'medication' | 'pregnancy_milestone' | 'milestone'
        private array  $payload
    ) {}

    public function handle(WhatsAppService $whatsApp): void
    {
        $user = \App\Models\User::find($this->userId);

        if (! $user) {
            Log::warning("[WhatsAppJob] User not found: {$this->userId}");
            return;
        }

        match ($this->type) {
            'follow_up' => $whatsApp->sendFollowUpReminder(
                $user,
                $this->payload['doctor_name'],
                $this->payload['follow_up_date']
            ),
            'medication' => $this->handleMedicationReminder($user, $whatsApp),
            'pregnancy_milestone' => $whatsApp->sendPregnancyMilestone(
                $user,
                $this->payload['milestone_name'],
                $this->payload['week'],
                $this->payload['description'] ?? ''
            ),
            default => Log::info("[WhatsAppJob] Unknown type: {$this->type}"),
        };
    }

    private function handleMedicationReminder(\App\Models\User $user, WhatsAppService $whatsApp): void
    {
        // Check if the reminder is still active before sending
        $reminder = \App\Models\MedicationReminder::find($this->payload['reminder_id'] ?? null);

        if (! $reminder || ! $reminder->isActive()) {
            return;
        }

        $whatsApp->sendMedicationReminder(
            $user,
            $this->payload['medicine_name'],
            $this->payload['dosage'],
            $this->payload['time']
        );

        // Re-schedule for tomorrow (self-rescheduling daily job)
        [$hour, $minute] = explode(':', $this->payload['time']);
        $nextRemindAt = now()->setTime((int) $hour, (int) $minute)->addDay();

        static::dispatch(
            userId:  $this->userId,
            type:    'medication',
            payload: $this->payload
        )->delay($nextRemindAt);
    }
}


// ─── SendVaccinationReminderJob ───────────────────────────────────────────────

class SendVaccinationReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(
        private int    $userId,
        private string $childName,
        private string $vaccineName,
        private string $dueDate,
        private int    $daysAway
    ) {}

    public function handle(WhatsAppService $whatsApp): void
    {
        $user = \App\Models\User::find($this->userId);

        if (! $user) {
            Log::warning("[VaccineJob] User not found: {$this->userId}");
            return;
        }

        $whatsApp->sendVaccinationReminder(
            $user,
            $this->childName,
            $this->vaccineName,
            $this->dueDate,
            $this->daysAway
        );
    }
}


// ─── GeneratePrescriptionPdfJob ───────────────────────────────────────────────

class GeneratePrescriptionPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $backoff = 30;

    public function __construct(
        private int  $prescriptionId,
        private bool $sendWhatsApp = false
    ) {}

    public function handle(\App\Services\PdfService $pdfService, WhatsAppService $whatsApp): void
    {
        $prescription = \App\Models\Prescription::with([
            'doctor.profile', 'doctor.doctorProfile',
            'patient.profile', 'medicines', 'familyMember',
        ])->find($this->prescriptionId);

        if (! $prescription) {
            Log::warning("[PdfJob] Prescription not found: {$this->prescriptionId}");
            return;
        }

        // Generate PDF
        $path = $pdfService->generatePrescriptionPdf($prescription);

        Log::info("[PdfJob] PDF generated", [
            'prescription' => $this->prescriptionId,
            'path'         => $path,
        ]);

        // Optionally send via WhatsApp after generation
        if ($this->sendWhatsApp) {
            $whatsApp->sendPrescription($prescription->fresh());
        }
    }
}
