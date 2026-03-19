<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\HealthLog;
use Carbon\Carbon;

class HealthLogSeeder extends Seeder
{
    public function run(): void
    {
        $suresh = User::where('mobile_number', '9200000006')->first(); // Diabetic
        $dinesh = User::where('mobile_number', '9200000008')->first(); // Cardiac
        $ananya = User::where('mobile_number', '9200000001')->first(); // Pregnant

        // ── Suresh — 30 days of BP + sugar logs (diabetic / hypertensive) ──
        for ($i = 30; $i >= 0; $i--) {
            $date = now()->subDays($i);

            // Morning fasting sugar (improving over time as medication kicks in)
            HealthLog::create([
                'patient_user_id' => $suresh->id,
                'log_type'        => 'sugar',
                'value_1'         => rand($i > 15 ? 170 : 130, $i > 15 ? 220 : 170),
                'unit'            => 'mg/dL',
                'context'         => 'fasting',
                'logged_at'       => $date->copy()->setTime(7, 30),
            ]);

            // Post-meal sugar
            HealthLog::create([
                'patient_user_id' => $suresh->id,
                'log_type'        => 'sugar',
                'value_1'         => rand($i > 15 ? 200 : 160, $i > 15 ? 280 : 220),
                'unit'            => 'mg/dL',
                'context'         => 'post_meal',
                'logged_at'       => $date->copy()->setTime(13, 0),
            ]);

            // BP reading
            if ($i % 2 === 0) { // Every other day
                HealthLog::create([
                    'patient_user_id' => $suresh->id,
                    'log_type'        => 'bp',
                    'value_1'         => rand($i > 15 ? 140 : 128, $i > 15 ? 158 : 142), // systolic
                    'value_2'         => rand($i > 15 ? 88 : 82, $i > 15 ? 98 : 90),     // diastolic
                    'unit'            => 'mmHg',
                    'context'         => 'morning',
                    'logged_at'       => $date->copy()->setTime(8, 0),
                ]);
            }

            // Weight — weekly
            if ($i % 7 === 0) {
                HealthLog::create([
                    'patient_user_id' => $suresh->id,
                    'log_type'        => 'weight',
                    'value_1'         => 82.0 - (30 - $i) * 0.05, // very slow weight loss
                    'unit'            => 'kg',
                    'context'         => 'morning',
                    'logged_at'       => $date->copy()->setTime(7, 0),
                ]);
            }
        }

        // ── Dinesh — 14 days of BP + pulse (cardiac patient) ────────────────
        for ($i = 14; $i >= 0; $i--) {
            $date = now()->subDays($i);

            HealthLog::create([
                'patient_user_id' => $dinesh->id,
                'log_type'        => 'bp',
                'value_1'         => rand(134, 148),
                'value_2'         => rand(84, 94),
                'unit'            => 'mmHg',
                'context'         => 'morning',
                'logged_at'       => $date->copy()->setTime(8, 0),
            ]);

            HealthLog::create([
                'patient_user_id' => $dinesh->id,
                'log_type'        => 'pulse',
                'value_1'         => rand(74, 92),
                'unit'            => 'bpm',
                'context'         => 'morning',
                'logged_at'       => $date->copy()->setTime(8, 5),
            ]);

            if ($i % 3 === 0) {
                HealthLog::create([
                    'patient_user_id' => $dinesh->id,
                    'log_type'        => 'oxygen',
                    'value_1'         => rand(96, 99),
                    'unit'            => '%',
                    'context'         => 'random',
                    'logged_at'       => $date->copy()->setTime(20, 0),
                ]);
            }
        }

        // ── Ananya — weight logs during pregnancy ────────────────────────────
        $pregnancyWeights = [
            [150, 58.0], [120, 59.2], [90, 60.8],
            [60, 62.5], [30, 65.0], [10, 67.2], [0, 67.8],
        ];

        foreach ($pregnancyWeights as [$daysAgo, $weight]) {
            HealthLog::create([
                'patient_user_id' => $ananya->id,
                'log_type'        => 'weight',
                'value_1'         => $weight,
                'unit'            => 'kg',
                'context'         => 'morning',
                'notes'           => 'Pregnancy weight record',
                'logged_at'       => now()->subDays($daysAgo)->setTime(8, 0),
            ]);
        }

        // Blood pressure during pregnancy
        $bpReadings = [
            [90, 108, 68], [70, 110, 70], [50, 112, 72],
            [30, 114, 74], [10, 118, 76], [0, 118, 76],
        ];

        foreach ($bpReadings as [$daysAgo, $sys, $dia]) {
            HealthLog::create([
                'patient_user_id' => $ananya->id,
                'log_type'        => 'bp',
                'value_1'         => $sys,
                'value_2'         => $dia,
                'unit'            => 'mmHg',
                'context'         => 'morning',
                'logged_at'       => now()->subDays($daysAgo)->setTime(9, 0),
            ]);
        }

        $this->command->info('✔ Health logs seeded: 30 days for Suresh (BP+sugar), 14 days for Dinesh (BP+pulse), Ananya (pregnancy weight+BP)');
    }
}
