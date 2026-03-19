<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DoctorProfile;

class DoctorProfileSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            // Dr. Priya Sharma — Obs & Gynecologist
            '9100000001' => [
                'specialization'            => 'Obstetrics & Gynecology',
                'sub_specialization'        => 'High Risk Pregnancy',
                'registration_number'       => 'MH-OBG-12345',
                'registration_council'      => 'Maharashtra Medical Council',
                'qualification'             => 'MBBS, MD (OBG)',
                'experience_years'          => 15,
                'clinic_name'              => 'Priya Women\'s Clinic',
                'clinic_address'           => '204, Shree Complex, Andheri West',
                'clinic_city'              => 'Mumbai',
                'clinic_state'             => 'Maharashtra',
                'clinic_pincode'           => '400053',
                'consultation_fee'         => 600.00,
                'upi_id'                   => 'drpriya@upi',
                'languages_spoken'         => ['en', 'hi', 'mr'],
                'bio'                      => 'Specialist in high-risk pregnancies and laparoscopic gynecology with 15+ years experience.',
                'is_premium'               => true,
                'is_verified'              => true,
                'whatsapp_number'          => '9100000001',
                'available_slots'          => $this->buildSlots(['Mon','Tue','Wed','Thu','Fri'], '09:00', '13:00', 15),
            ],
            // Dr. Rajesh Patel — Pediatrician
            '9100000002' => [
                'specialization'            => 'Pediatrics',
                'sub_specialization'        => 'Neonatology',
                'registration_number'       => 'GJ-PED-67890',
                'registration_council'      => 'Gujarat Medical Council',
                'qualification'             => 'MBBS, DCH, MD (Pediatrics)',
                'experience_years'          => 18,
                'clinic_name'              => 'Little Stars Children\'s Clinic',
                'clinic_address'           => '12, Navrangpura, Near Law Garden',
                'clinic_city'              => 'Ahmedabad',
                'clinic_state'             => 'Gujarat',
                'clinic_pincode'           => '380009',
                'consultation_fee'         => 500.00,
                'upi_id'                   => 'drrajesh@upi',
                'languages_spoken'         => ['en', 'hi', 'gu'],
                'bio'                      => 'Dedicated pediatrician with expertise in newborn care and childhood vaccination programs.',
                'is_premium'               => true,
                'is_verified'              => true,
                'whatsapp_number'          => '9100000002',
                'available_slots'          => $this->buildSlots(['Mon','Wed','Fri','Sat'], '10:00', '14:00', 20),
            ],
            // Dr. Sunita Rao — IVF Specialist
            '9100000003' => [
                'specialization'            => 'Reproductive Medicine',
                'sub_specialization'        => 'IVF & Infertility',
                'registration_number'       => 'KA-REP-11223',
                'registration_council'      => 'Karnataka Medical Council',
                'qualification'             => 'MBBS, MS (OBG), Fellowship IVF',
                'experience_years'          => 12,
                'clinic_name'              => 'Sunrise IVF Centre',
                'clinic_address'           => '78, Jayanagar 4th Block',
                'clinic_city'              => 'Bengaluru',
                'clinic_state'             => 'Karnataka',
                'clinic_pincode'           => '560041',
                'consultation_fee'         => 1000.00,
                'upi_id'                   => 'drsunita@upi',
                'languages_spoken'         => ['en', 'kn', 'hi'],
                'bio'                      => 'Pioneer in IVF and assisted reproductive technologies with 1200+ successful procedures.',
                'is_premium'               => true,
                'is_verified'              => true,
                'whatsapp_number'          => '9100000003',
                'available_slots'          => $this->buildSlots(['Tue','Wed','Thu','Fri'], '11:00', '15:00', 30),
            ],
            // Dr. Amit Verma — Dentist
            '9100000004' => [
                'specialization'            => 'Dentistry',
                'sub_specialization'        => 'Orthodontics & Implants',
                'registration_number'       => 'DL-DEN-44556',
                'registration_council'      => 'Delhi Dental Council',
                'qualification'             => 'BDS, MDS (Orthodontics)',
                'experience_years'          => 14,
                'clinic_name'              => 'SmileCare Dental Studio',
                'clinic_address'           => 'B-45, Rajouri Garden',
                'clinic_city'              => 'Delhi',
                'clinic_state'             => 'Delhi',
                'clinic_pincode'           => '110027',
                'consultation_fee'         => 400.00,
                'upi_id'                   => 'dramit@upi',
                'languages_spoken'         => ['en', 'hi'],
                'bio'                      => 'Expert in smile makeovers, dental implants, and invisible orthodontic aligners.',
                'is_premium'               => false,
                'is_verified'              => true,
                'whatsapp_number'          => '9100000004',
                'available_slots'          => $this->buildSlots(['Mon','Tue','Thu','Sat'], '09:00', '17:00', 30),
            ],
            // Dr. Kavitha Nair — General Physician
            '9100000005' => [
                'specialization'            => 'General Medicine',
                'sub_specialization'        => 'Diabetology',
                'registration_number'       => 'TN-GEN-77889',
                'registration_council'      => 'Tamil Nadu Medical Council',
                'qualification'             => 'MBBS, MD (General Medicine)',
                'experience_years'          => 10,
                'clinic_name'              => 'Kavitha\'s Family Clinic',
                'clinic_address'           => '23, Anna Nagar East',
                'clinic_city'              => 'Chennai',
                'clinic_state'             => 'Tamil Nadu',
                'clinic_pincode'           => '600102',
                'consultation_fee'         => 350.00,
                'upi_id'                   => 'drkavitha@upi',
                'languages_spoken'         => ['en', 'ta', 'hi'],
                'bio'                      => 'Family physician specialising in diabetes management and preventive healthcare.',
                'is_premium'               => false,
                'is_verified'              => true,
                'whatsapp_number'          => '9100000005',
                'available_slots'          => $this->buildSlots(['Mon','Tue','Wed','Thu','Fri','Sat'], '08:00', '12:00', 10),
            ],
            // Dr. Sanjay Gupta — Orthopedic
            '9100000006' => [
                'specialization'            => 'Orthopedics',
                'sub_specialization'        => 'Joint Replacement',
                'registration_number'       => 'UP-ORT-33221',
                'registration_council'      => 'UP Medical Council',
                'qualification'             => 'MBBS, MS (Ortho), Fellowship (Joint Replacement)',
                'experience_years'          => 22,
                'clinic_name'              => 'Gupta Bone & Joint Clinic',
                'clinic_address'           => '56, Hazratganj',
                'clinic_city'              => 'Lucknow',
                'clinic_state'             => 'Uttar Pradesh',
                'clinic_pincode'           => '226001',
                'consultation_fee'         => 700.00,
                'upi_id'                   => 'drsanjay@upi',
                'languages_spoken'         => ['en', 'hi'],
                'bio'                      => 'Senior orthopedic surgeon with expertise in knee and hip replacement surgery.',
                'is_premium'               => true,
                'is_verified'              => true,
                'whatsapp_number'          => '9100000006',
                'available_slots'          => $this->buildSlots(['Mon','Wed','Fri'], '14:00', '18:00', 20),
            ],
            // Dr. Meera Iyer — Oncologist
            '9100000007' => [
                'specialization'            => 'Oncology',
                'sub_specialization'        => 'Medical Oncology',
                'registration_number'       => 'MH-ONC-55678',
                'registration_council'      => 'Maharashtra Medical Council',
                'qualification'             => 'MBBS, MD, DM (Medical Oncology)',
                'experience_years'          => 8,
                'clinic_name'              => 'Iyer Cancer Care Centre',
                'clinic_address'           => 'FC Road, Shivajinagar',
                'clinic_city'              => 'Pune',
                'clinic_state'             => 'Maharashtra',
                'clinic_pincode'           => '411005',
                'consultation_fee'         => 1200.00,
                'upi_id'                   => 'drmeera@upi',
                'languages_spoken'         => ['en', 'mr', 'hi'],
                'bio'                      => 'Dedicated oncologist providing compassionate, evidence-based cancer treatment.',
                'is_premium'               => true,
                'is_verified'              => true,
                'whatsapp_number'          => '9100000007',
                'available_slots'          => $this->buildSlots(['Tue','Thu','Sat'], '10:00', '16:00', 30),
            ],
            // Dr. Arjun Singh — Cardiologist
            '9100000008' => [
                'specialization'            => 'Cardiology',
                'sub_specialization'        => 'Interventional Cardiology',
                'registration_number'       => 'TS-CAR-99001',
                'registration_council'      => 'Telangana Medical Council',
                'qualification'             => 'MBBS, MD, DM (Cardiology)',
                'experience_years'          => 16,
                'clinic_name'              => 'HeartCare Institute',
                'clinic_address'           => '301, Banjara Hills Road No.10',
                'clinic_city'              => 'Hyderabad',
                'clinic_state'             => 'Telangana',
                'clinic_pincode'           => '500034',
                'consultation_fee'         => 1500.00,
                'upi_id'                   => 'drarjun@upi',
                'languages_spoken'         => ['en', 'hi', 'te'],
                'bio'                      => 'Interventional cardiologist specialising in angioplasty, stenting and heart failure management.',
                'is_premium'               => true,
                'is_verified'              => true,
                'whatsapp_number'          => '9100000008',
                'available_slots'          => $this->buildSlots(['Mon','Tue','Wed','Thu'], '09:00', '13:00', 20),
            ],
        ];

        foreach ($profiles as $mobile => $data) {
            $user = User::where('mobile_number', $mobile)->firstOrFail();

            DoctorProfile::create(array_merge($data, ['user_id' => $user->id]));
        }

        $this->command->info('✔ 8 doctor profiles created with specializations & availability slots');
    }

    /**
     * Build a weekly slot schedule as a JSON-ready array.
     * E.g. Mon-Fri 09:00–13:00 every 15 minutes.
     */
    private function buildSlots(array $days, string $start, string $end, int $intervalMinutes): array
    {
        $slots = [];
        foreach ($days as $day) {
            $times   = [];
            $current = \Carbon\Carbon::createFromFormat('H:i', $start);
            $endTime = \Carbon\Carbon::createFromFormat('H:i', $end);

            while ($current < $endTime) {
                $times[] = $current->format('H:i');
                $current->addMinutes($intervalMinutes);
            }
            $slots[$day] = $times;
        }
        return $slots;
    }
}
