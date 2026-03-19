<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserProfile;

class DoctorSeeder extends Seeder
{
    /**
     * 8 doctors covering the key specializations used by the timeline system.
     */
    public function run(): void
    {
        $doctors = [
            [
                'mobile'   => '9100000001',
                'email'    => 'dr.priya.sharma@medtech.in',
                'name'     => 'Dr. Priya Sharma',
                'gender'   => 'female',
                'dob'      => '1982-04-15',
                'city'     => 'Mumbai',
                'state'    => 'Maharashtra',
                'blood'    => 'B+',
            ],
            [
                'mobile'   => '9100000002',
                'email'    => 'dr.rajesh.patel@medtech.in',
                'name'     => 'Dr. Rajesh Patel',
                'gender'   => 'male',
                'dob'      => '1978-09-22',
                'city'     => 'Ahmedabad',
                'state'    => 'Gujarat',
                'blood'    => 'O+',
            ],
            [
                'mobile'   => '9100000003',
                'email'    => 'dr.sunita.rao@medtech.in',
                'name'     => 'Dr. Sunita Rao',
                'gender'   => 'female',
                'dob'      => '1985-11-08',
                'city'     => 'Bengaluru',
                'state'    => 'Karnataka',
                'blood'    => 'A+',
            ],
            [
                'mobile'   => '9100000004',
                'email'    => 'dr.amit.verma@medtech.in',
                'name'     => 'Dr. Amit Verma',
                'gender'   => 'male',
                'dob'      => '1980-03-30',
                'city'     => 'Delhi',
                'state'    => 'Delhi',
                'blood'    => 'AB+',
            ],
            [
                'mobile'   => '9100000005',
                'email'    => 'dr.kavitha.nair@medtech.in',
                'name'     => 'Dr. Kavitha Nair',
                'gender'   => 'female',
                'dob'      => '1987-07-12',
                'city'     => 'Chennai',
                'state'    => 'Tamil Nadu',
                'blood'    => 'O-',
            ],
            [
                'mobile'   => '9100000006',
                'email'    => 'dr.sanjay.gupta@medtech.in',
                'name'     => 'Dr. Sanjay Gupta',
                'gender'   => 'male',
                'dob'      => '1975-01-19',
                'city'     => 'Lucknow',
                'state'    => 'Uttar Pradesh',
                'blood'    => 'A-',
            ],
            [
                'mobile'   => '9100000007',
                'email'    => 'dr.meera.iyer@medtech.in',
                'name'     => 'Dr. Meera Iyer',
                'gender'   => 'female',
                'dob'      => '1990-05-25',
                'city'     => 'Pune',
                'state'    => 'Maharashtra',
                'blood'    => 'B-',
            ],
            [
                'mobile'   => '9100000008',
                'email'    => 'dr.arjun.singh@medtech.in',
                'name'     => 'Dr. Arjun Singh',
                'gender'   => 'male',
                'dob'      => '1983-12-05',
                'city'     => 'Hyderabad',
                'state'    => 'Telangana',
                'blood'    => 'AB-',
            ],
        ];

        foreach ($doctors as $data) {
            $user = User::create([
                'mobile_number'      => $data['mobile'],
                'country_code'       => '+91',
                'email'              => $data['email'],
                'password'           => Hash::make('Doctor@1234'),
                'role'               => 'doctor',
                'is_verified'        => true,
                'is_active'          => true,
                'preferred_language' => 'en',
            ]);

            UserProfile::create([
                'user_id'     => $user->id,
                'full_name'   => $data['name'],
                'dob'         => $data['dob'],
                'gender'      => $data['gender'],
                'blood_group' => $data['blood'],
                'city'        => $data['city'],
                'state'       => $data['state'],
            ]);
        }

        $this->command->info('✔ 8 doctor users created → password: Doctor@1234');
    }
}
