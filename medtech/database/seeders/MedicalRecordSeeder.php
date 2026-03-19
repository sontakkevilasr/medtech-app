<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;

class MedicalRecordSeeder extends Seeder
{
    public function run(): void
    {
        $drPriya   = User::where('mobile_number', '9100000001')->first(); // OBG
        $drRajesh  = User::where('mobile_number', '9100000002')->first(); // Pediatrics
        $drKavitha = User::where('mobile_number', '9100000005')->first(); // General
        $drArjun   = User::where('mobile_number', '9100000008')->first(); // Cardiology

        $ananya  = User::where('mobile_number', '9200000001')->first();   // Pregnant patient
        $vikram  = User::where('mobile_number', '9200000002')->first();   // Has child
        $suresh  = User::where('mobile_number', '9200000006')->first();   // Diabetic
        $dinesh  = User::where('mobile_number', '9200000008')->first();   // Cardiac

        // ── Ananya with Dr. Priya (OBG — 3 visits) ──────────────────────────
        $this->createVisit(
            $drPriya, $ananya, null,
            now()->subMonths(5)->toDateString(),
            'follow_up',
            'Nausea, fatigue, missed period × 6 weeks',
            'Pregnancy 6 weeks. Viable intrauterine pregnancy confirmed on USG. EDD calculated.',
            ['height' => '162cm', 'weight' => '58kg', 'bp' => '110/70', 'spo2' => '99%'],
            'Continue folic acid 5mg. Avoid raw meat, alcohol, smoking.',
            now()->subMonths(4)->toDateString(),
            [
                ['Folic Acid', null, '5mg', 'tablet', '0-0-1', 90, 'after_food', 'Continue till 12 weeks'],
                ['Vitamin D3', null, '60000 IU', 'capsule', '0-0-0', 4, 'after_food', 'Once a week × 4 weeks'],
                ['Iron + Folic Acid', 'Ferrous Fumarate', '150mg/0.5mg', 'tablet', '1-0-1', 90, 'after_food', 'Take with OJ. Not with tea/milk.'],
            ]
        );

        $this->createVisit(
            $drPriya, $ananya, null,
            now()->subMonths(3)->toDateString(),
            'follow_up',
            'Routine antenatal visit. Mild lower back pain.',
            'Pregnancy 16 weeks. FHR 148 bpm. Fundal height adequate. NT scan normal.',
            ['weight' => '61kg', 'bp' => '112/72', 'spo2' => '99%'],
            'Begin calcium supplementation. Left lateral position for sleep.',
            now()->subMonths(2)->toDateString(),
            [
                ['Calcium Carbonate + Vitamin D3', null, '500mg/200IU', 'tablet', '1-0-1', 90, 'after_food', null],
                ['Pyridoxine (B6)', null, '25mg', 'tablet', '0-1-0', 30, 'after_food', 'For nausea'],
            ]
        );

        $this->createVisit(
            $drPriya, $ananya, null,
            now()->subDays(10)->toDateString(),
            'follow_up',
            'Routine check. Mild leg swelling.',
            'Pregnancy 28 weeks. Growth scan — fetal weight 1.1kg (appropriate for gestational age). AFI normal.',
            ['weight' => '67kg', 'bp' => '118/76', 'spo2' => '98%'],
            'Reduce salt intake. Elevate feet. Count fetal movements.',
            now()->addDays(21)->toDateString(),
            [
                ['Ferrous Ascorbate + Folic Acid', null, '100mg/1.5mg', 'tablet', '1-0-0', 60, 'before_food', 'On empty stomach'],
                ['Calcium Carbonate + Vitamin D3', null, '500mg/250IU', 'tablet', '0-0-1', 60, 'after_food', null],
                ['Aspirin', null, '75mg', 'tablet', '0-0-1', 60, 'after_food', 'Low dose, continue till 36 weeks'],
            ]
        );

        // ── Vikram's child (Aarav) with Dr. Rajesh (Pediatrics) ─────────────
        $aarav = $vikram->familyMembers()->where('full_name', 'Aarav Desai')->first();
        $this->createVisit(
            $drRajesh, $vikram, $aarav,
            now()->subMonths(1)->toDateString(),
            'consultation',
            'High fever 103°F × 2 days. Runny nose. Mild cough.',
            'Viral URTI. Throat — mild congestion. Lungs — clear. No bacterial signs.',
            ['temp' => '102.8°F', 'weight' => '14.2kg', 'spo2' => '98%'],
            'Plenty of fluids. Rest. Return if fever persists > 5 days or child becomes lethargic.',
            now()->subDays(21)->toDateString(),
            [
                ['Paracetamol Syrup', null, '250mg/5ml', 'syrup', '3-0-3', 5, 'after_food', '10ml per dose. Only if temp > 38.5°C'],
                ['Cetirizine Syrup', null, '5mg/5ml', 'syrup', '0-0-1', 5, 'after_food', '5ml at bedtime'],
                ['Ambroxol Syrup', null, '15mg/5ml', 'syrup', '1-1-1', 5, 'after_food', '5ml × 3 times/day'],
            ]
        );

        // ── Suresh with Dr. Kavitha (General — Diabetic) ─────────────────────
        $this->createVisit(
            $drKavitha, $suresh, null,
            now()->subMonths(2)->toDateString(),
            'consultation',
            'Routine diabetes check. Increased thirst, fatigue.',
            'Type 2 Diabetes Mellitus — uncontrolled. HbA1c: 8.6%. BP: 148/94 (Stage 2 hypertension).',
            ['weight' => '82kg', 'bp' => '148/94', 'sugar_fasting' => '186 mg/dL', 'sugar_pp' => '268 mg/dL'],
            'Low carb diet. Walk 30 minutes/day. Stop sugar, white rice, maida, aerated drinks.',
            now()->subMonths(1)->toDateString(),
            [
                ['Metformin', null, '500mg', 'tablet', '1-0-1', 90, 'after_food', 'Take after meals to reduce GI upset'],
                ['Glimepiride', null, '2mg', 'tablet', '1-0-0', 90, 'before_food', 'Take 30 min before breakfast'],
                ['Amlodipine', null, '5mg', 'tablet', '0-0-1', 30, 'after_food', 'For blood pressure'],
                ['Telmisartan', null, '40mg', 'tablet', '1-0-0', 30, 'after_food', 'Do not skip'],
                ['Atorvastatin', null, '10mg', 'tablet', '0-0-1', 30, 'after_food', 'Night dose — for cholesterol'],
            ]
        );

        // ── Dinesh with Dr. Arjun (Cardiology) ───────────────────────────────
        $this->createVisit(
            $drArjun, $dinesh, null,
            now()->subWeeks(3)->toDateString(),
            'consultation',
            'Chest discomfort on exertion, occasional palpitations.',
            'Stable angina. ECG: mild ST changes. Echo: EF 52%. Referred for stress test.',
            ['bp' => '142/88', 'pulse' => '88 bpm', 'weight' => '78kg', 'spo2' => '97%'],
            'No strenuous activity till stress test. Low salt, low fat diet. No smoking.',
            now()->addWeeks(2)->toDateString(),
            [
                ['Aspirin', null, '75mg', 'tablet', '0-1-0', 30, 'after_food', 'Do not stop without doctor advice'],
                ['Atorvastatin', null, '40mg', 'tablet', '0-0-1', 30, 'after_food', 'For cholesterol — night dose'],
                ['Metoprolol Succinate', null, '25mg', 'tablet', '1-0-0', 30, 'after_food', null],
                ['Isosorbide Mononitrate', null, '30mg', 'tablet', '1-0-0', 30, 'after_food', 'For chest pain'],
            ]
        );

        $this->command->info('✔ Medical records & prescriptions seeded (4 patients, 6 visits)');
    }

    private function createVisit(
        $doctor, $patient, $familyMember,
        string $visitDate, string $visitType,
        string $complaint, string $diagnosis,
        array $vitals,
        string $plan,
        string $followUp,
        array $medicines
    ): void {
        $record = MedicalRecord::create([
            'patient_user_id'  => $patient->id,
            'family_member_id' => $familyMember?->id,
            'doctor_user_id'   => $doctor->id,
            'visit_date'       => $visitDate,
            'visit_type'       => $visitType,
            'chief_complaint'  => $complaint,
            'diagnosis'        => $diagnosis,
            'vitals'           => $vitals,
            'treatment_plan'   => $plan,
            'follow_up_date'   => $followUp,
        ]);

        $prescription = Prescription::create([
            'medical_record_id'      => $record->id,
            'doctor_user_id'         => $doctor->id,
            'patient_user_id'        => $patient->id,
            'family_member_id'       => $familyMember?->id,
            'prescribed_date'        => $visitDate,
            'diagnosis_summary'      => $diagnosis,
            'general_instructions'   => $plan,
            'follow_up_date'         => $followUp,
            'status'                 => 'issued',
        ]);

        foreach ($medicines as $i => $med) {
            PrescriptionMedicine::create([
                'prescription_id'     => $prescription->id,
                'medicine_name'       => $med[0],
                'generic_name'        => $med[1],
                'dosage'              => $med[2],
                'form'                => $med[3],
                'frequency'           => $med[4],
                'duration_days'       => $med[5],
                'timing'              => $med[6],
                'special_instructions'=> $med[7] ?? null,
                'sort_order'          => $i,
            ]);
        }
    }
}
