<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Appointment;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $drPriya   = User::where('mobile_number', '9100000001')->first();
        $drRajesh  = User::where('mobile_number', '9100000002')->first();
        $drKavitha = User::where('mobile_number', '9100000005')->first();
        $drArjun   = User::where('mobile_number', '9100000008')->first();
        $drAmit    = User::where('mobile_number', '9100000004')->first();

        $ananya  = User::where('mobile_number', '9200000001')->first();
        $vikram  = User::where('mobile_number', '9200000002')->first();
        $sneha   = User::where('mobile_number', '9200000003')->first();
        $suresh  = User::where('mobile_number', '9200000006')->first();
        $dinesh  = User::where('mobile_number', '9200000008')->first();
        $pooja   = User::where('mobile_number', '9200000007')->first();

        $aarav = $vikram->familyMembers()->where('full_name', 'Aarav Desai')->first();

        $appointments = [
            // ── Upcoming appointments ──────────────────────────────────────
            [
                'doctor'     => $drPriya,
                'patient'    => $ananya,
                'member'     => null,
                'datetime'   => now()->addDays(3)->setTime(10, 0),
                'duration'   => 15,
                'type'       => 'in_person',
                'status'     => 'confirmed',
                'reason'     => '32-week antenatal check-up and growth scan review',
                'fee'        => 600,
                'r24'        => false,
                'r1'         => false,
            ],
            [
                'doctor'     => $drRajesh,
                'patient'    => $vikram,
                'member'     => $aarav,
                'datetime'   => now()->addDays(7)->setTime(11, 0),
                'duration'   => 20,
                'type'       => 'in_person',
                'status'     => 'booked',
                'reason'     => 'MMR booster vaccination',
                'fee'        => 500,
                'r24'        => false,
                'r1'         => false,
            ],
            [
                'doctor'     => $drKavitha,
                'patient'    => $suresh,
                'member'     => null,
                'datetime'   => now()->addDays(5)->setTime(9, 0),
                'duration'   => 15,
                'type'       => 'in_person',
                'status'     => 'confirmed',
                'reason'     => 'Diabetes follow-up. HbA1c review after 3 months of medication.',
                'fee'        => 350,
                'r24'        => false,
                'r1'         => false,
            ],
            [
                'doctor'     => $drArjun,
                'patient'    => $dinesh,
                'member'     => null,
                'datetime'   => now()->addDays(2)->setTime(9, 30),
                'duration'   => 30,
                'type'       => 'in_person',
                'status'     => 'confirmed',
                'reason'     => 'TMT (Treadmill Stress Test) result review and further cardiac workup',
                'fee'        => 1500,
                'r24'        => false,
                'r1'         => false,
            ],
            [
                'doctor'     => $drAmit,
                'patient'    => $sneha,
                'member'     => null,
                'datetime'   => now()->addDays(10)->setTime(10, 30),
                'duration'   => 30,
                'type'       => 'in_person',
                'status'     => 'booked',
                'reason'     => 'Consultation for dental braces',
                'fee'        => 400,
                'r24'        => false,
                'r1'         => false,
            ],
            [
                'doctor'     => $drKavitha,
                'patient'    => $pooja,
                'member'     => null,
                'datetime'   => now()->addDays(1)->setTime(8, 30),
                'duration'   => 10,
                'type'       => 'in_person',
                'status'     => 'booked',
                'reason'     => 'Fever + sore throat × 3 days',
                'fee'        => 350,
                'r24'        => false,
                'r1'         => false,
            ],

            // ── Today's appointments ───────────────────────────────────────
            [
                'doctor'     => $drPriya,
                'patient'    => $ananya,
                'member'     => null,
                'datetime'   => now()->setTime(11, 0),
                'duration'   => 15,
                'type'       => 'in_person',
                'status'     => 'confirmed',
                'reason'     => 'Emergency visit — reduced fetal movement',
                'fee'        => 600,
                'r24'        => true,
                'r1'         => true,
            ],

            // ── Past / Completed appointments ─────────────────────────────
            [
                'doctor'     => $drPriya,
                'patient'    => $ananya,
                'member'     => null,
                'datetime'   => now()->subDays(10)->setTime(10, 0),
                'duration'   => 15,
                'type'       => 'in_person',
                'status'     => 'completed',
                'reason'     => '28-week growth scan and routine check',
                'fee'        => 600,
                'r24'        => true,
                'r1'         => true,
            ],
            [
                'doctor'     => $drKavitha,
                'patient'    => $suresh,
                'member'     => null,
                'datetime'   => now()->subMonths(2)->setTime(9, 0),
                'duration'   => 15,
                'type'       => 'in_person',
                'status'     => 'completed',
                'reason'     => 'Initial consultation for diabetes',
                'fee'        => 350,
                'r24'        => true,
                'r1'         => true,
            ],
            [
                'doctor'     => $drArjun,
                'patient'    => $dinesh,
                'member'     => null,
                'datetime'   => now()->subWeeks(3)->setTime(9, 30),
                'duration'   => 30,
                'type'       => 'in_person',
                'status'     => 'completed',
                'reason'     => 'Chest discomfort — initial cardiology consultation',
                'fee'        => 1500,
                'r24'        => true,
                'r1'         => true,
            ],
            [
                'doctor'     => $drRajesh,
                'patient'    => $vikram,
                'member'     => $aarav,
                'datetime'   => now()->subMonths(1)->setTime(10, 0),
                'duration'   => 20,
                'type'       => 'in_person',
                'status'     => 'completed',
                'reason'     => 'High fever and cold — Aarav unwell',
                'fee'        => 500,
                'r24'        => true,
                'r1'         => true,
            ],
            [
                'doctor'     => $drAmit,
                'patient'    => $sneha,
                'member'     => null,
                'datetime'   => now()->subWeeks(2)->setTime(11, 0),
                'duration'   => 30,
                'type'       => 'in_person',
                'status'     => 'cancelled',
                'reason'     => 'Tooth pain — upper left molar',
                'fee'        => 400,
                'r24'        => true,
                'r1'         => false,
            ],
        ];

        foreach ($appointments as $data) {
            Appointment::create([
                'doctor_user_id'          => $data['doctor']->id,
                'patient_user_id'         => $data['patient']->id,
                'family_member_id'        => $data['member']?->id,
                'slot_datetime'           => $data['datetime'],
                'duration_minutes'        => $data['duration'],
                'type'                    => $data['type'],
                'status'                  => $data['status'],
                'reason'                  => $data['reason'],
                'fee'                     => $data['fee'],
                'payment_status'          => $data['status'] === 'completed' ? 'paid' : 'pending',
                'reminder_24h_sent'       => $data['r24'],
                'reminder_1h_sent'        => $data['r1'],
            ]);
        }

        $this->command->info('✔ 12 appointments seeded (6 upcoming, 1 today, 5 past)');
    }
}
