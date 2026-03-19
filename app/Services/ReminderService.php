<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\MedicationReminder;
use App\Models\PatientTimeline;
use App\Jobs\SendAppointmentReminderJob;
use App\Jobs\SendWhatsAppReminderJob;
use App\Jobs\SendVaccinationReminderJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReminderService
{
    public function __construct(
        private WhatsAppService $whatsAppService
    ) {}

    // ─── Appointment Reminders ───────────────────────────────────────────────

    /**
     * Schedule both 24h and 1h reminders for a newly booked appointment.
     * Called after appointment is created/confirmed.
     */
    public function scheduleAppointmentReminders(Appointment $appointment): void
    {
        $slotTime = $appointment->slot_datetime;

        // 24-hour reminder
        $remind24h = $slotTime->copy()->subHours(24);
        if ($remind24h->isFuture()) {
            SendAppointmentReminderJob::dispatch($appointment->id, '24h')
                ->delay($remind24h);
            Log::info("[Reminder] 24h reminder scheduled", [
                'appointment' => $appointment->id,
                'at'          => $remind24h->toDateTimeString(),
            ]);
        }

        // 1-hour reminder
        $remind1h = $slotTime->copy()->subMinutes(60);
        if ($remind1h->isFuture()) {
            SendAppointmentReminderJob::dispatch($appointment->id, '1h')
                ->delay($remind1h);
            Log::info("[Reminder] 1h reminder scheduled", [
                'appointment' => $appointment->id,
                'at'          => $remind1h->toDateTimeString(),
            ]);
        }
    }

    /**
     * Immediately send a manual reminder (doctor can trigger from dashboard).
     */
    public function sendImmediateReminder(Appointment $appointment): bool
    {
        return $this->whatsAppService->sendAppointmentReminder($appointment, 'custom');
    }

    /**
     * Schedule a follow-up reminder based on follow_up_date in medical record.
     */
    public function scheduleFollowUpReminder(
        \App\Models\MedicalRecord $record,
        int $daysBefore = 3
    ): void {
        if (! $record->follow_up_date) return;

        $remindAt = $record->follow_up_date->copy()->subDays($daysBefore)->setTime(9, 0);

        if ($remindAt->isFuture()) {
            SendWhatsAppReminderJob::dispatch(
                userId:   $record->patient_user_id,
                type:     'follow_up',
                payload:  [
                    'doctor_name'    => $record->doctor->profile->full_name,
                    'follow_up_date' => $record->follow_up_date->format('d M Y'),
                ]
            )->delay($remindAt);
        }
    }

    // ─── Medication Reminders ────────────────────────────────────────────────

    /**
     * Schedule daily medication reminders for all active reminder times.
     * Called when a new reminder is created.
     * Uses a recurring Job that re-schedules itself each day.
     */
    public function scheduleMedicationReminders(MedicationReminder $reminder): void
    {
        if (! $reminder->isActive()) return;

        foreach ($reminder->reminder_times as $time) {
            $this->scheduleOneMedicationReminder($reminder, $time);
        }
    }

    private function scheduleOneMedicationReminder(MedicationReminder $reminder, string $time): void
    {
        [$hour, $minute] = explode(':', $time);
        $remindAt = now()->setTime((int) $hour, (int) $minute);

        // If today's time has already passed, schedule for tomorrow
        if ($remindAt->isPast()) {
            $remindAt->addDay();
        }

        SendWhatsAppReminderJob::dispatch(
            userId:  $reminder->patient_user_id,
            type:    'medication',
            payload: [
                'reminder_id'   => $reminder->id,
                'medicine_name' => $reminder->medicine_name,
                'dosage'        => $reminder->dosage,
                'time'          => $time,
            ]
        )->delay($remindAt);
    }

    // ─── Timeline / Milestone Reminders ─────────────────────────────────────

    /**
     * Schedule WhatsApp reminders for all upcoming milestones in a patient timeline.
     * Called when a timeline is assigned to a patient.
     */
    public function scheduleTimelineReminders(PatientTimeline $patientTimeline): void
    {
        $milestonesWithDates = $patientTimeline->getMilestonesWithDates();
        $patient             = $patientTimeline->patient;
        $familyMember        = $patientTimeline->familyMember;
        $subjectName         = $familyMember?->full_name ?? $patient->profile->full_name;

        foreach ($milestonesWithDates as $milestone) {
            if (! $milestone->reminder_days_before) continue;

            foreach ($milestone->reminder_days_before as $daysBefore) {
                $remindAt = $milestone->actual_date->copy()
                    ->subDays($daysBefore)
                    ->setTime(9, 0);  // 9 AM on reminder day

                if ($remindAt->isPast()) continue;

                // Choose the right job type based on specialty
                $specialty = $patientTimeline->template->specialty_type;

                if ($specialty === 'pediatrics') {
                    SendVaccinationReminderJob::dispatch(
                        userId:       $patient->id,
                        childName:    $subjectName,
                        vaccineName:  $milestone->title,
                        dueDate:      $milestone->actual_date->format('d M Y'),
                        daysAway:     $daysBefore,
                    )->delay($remindAt);

                } else {
                    SendWhatsAppReminderJob::dispatch(
                        userId:  $patient->id,
                        type:    $specialty === 'obstetrics' ? 'pregnancy_milestone' : 'milestone',
                        payload: [
                            'milestone_name' => $milestone->title,
                            'description'    => $milestone->description,
                            'date'           => $milestone->actual_date->format('d M Y'),
                            'week'           => $milestone->offset_value,
                            'days_away'      => $daysBefore,
                            'subject_name'   => $subjectName,
                        ]
                    )->delay($remindAt);
                }
            }
        }

        Log::info("[Reminder] Timeline reminders scheduled", [
            'timeline'   => $patientTimeline->id,
            'milestones' => $milestonesWithDates->count(),
        ]);
    }

    // ─── Batch / Scheduled (called by artisan schedule) ─────────────────────

    /**
     * Process all appointments in the next 25 hours that haven't had
     * a 24h reminder sent yet. Run this via scheduler every hour.
     */
    public function processPending24hReminders(): int
    {
        $count = 0;

        Appointment::upcoming()
            ->where('reminder_24h_sent', false)
            ->whereBetween('slot_datetime', [
                now()->addHours(23),
                now()->addHours(25),
            ])
            ->each(function (Appointment $apt) use (&$count) {
                if ($this->whatsAppService->sendAppointmentReminder($apt, '24h')) {
                    $apt->update(['reminder_24h_sent' => true]);
                    $count++;
                }
            });

        return $count;
    }

    /**
     * Process all appointments in the next 65 minutes that haven't had
     * a 1h reminder sent yet. Run this via scheduler every 15 minutes.
     */
    public function processPending1hReminders(): int
    {
        $count = 0;

        Appointment::upcoming()
            ->where('reminder_1h_sent', false)
            ->whereBetween('slot_datetime', [
                now()->addMinutes(55),
                now()->addMinutes(65),
            ])
            ->each(function (Appointment $apt) use (&$count) {
                if ($this->whatsAppService->sendAppointmentReminder($apt, '1h')) {
                    $apt->update(['reminder_1h_sent' => true]);
                    $count++;
                }
            });

        return $count;
    }
}
