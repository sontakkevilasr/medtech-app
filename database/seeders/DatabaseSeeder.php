<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Order matters — FK dependencies must be seeded before dependents.
     */
    public function run(): void
    {
        $this->call([
            // 1. Core users (admin first, then doctors, then patients)
            AdminUserSeeder::class,
            DoctorSeeder::class,
            PatientSeeder::class,

            // 2. Doctor profiles & specializations
            DoctorProfileSeeder::class,

            // 3. Family members under patient accounts
            FamilyMemberSeeder::class,

            // 4. Access permissions for patients
            AccessPermissionSeeder::class,

            // 5. System-wide timeline templates (OBG, Pediatrics, etc.)
            TimelineTemplateSeeder::class,

            // 6. Sample medical records, prescriptions, appointments
            MedicalRecordSeeder::class,
            AppointmentSeeder::class,

            // 7. Health logs for patients
            HealthLogSeeder::class,
        ]);

        $this->command->info('✅ All seeders completed successfully!');
        $this->command->table(
            ['Role', 'Count', 'Sample Login'],
            [
                ['Admin',   '1',  '+91 9000000000 | OTP: 123456'],
                ['Doctors', '8',  '+91 9100000001 to 9100000008'],
                ['Patients','10', '+91 9200000001 to 9200000010'],
            ]
        );
    }
}
