<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserProfile;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $patients = [
            [
                'mobile' => '9200000001',
                'email'  => 'ananya.mehta@gmail.com',
                'name'   => 'Ananya Mehta',
                'dob'    => '1995-06-14',
                'gender' => 'female',
                'blood'  => 'A+',
                'city'   => 'Mumbai',
                'state'  => 'Maharashtra',
                'pin'    => '400001',
                'lang'   => 'en',
                'emergency_name'   => 'Rohit Mehta',
                'emergency_mobile' => '9200100001',
            ],
            [
                'mobile' => '9200000002',
                'email'  => 'vikram.desai@gmail.com',
                'name'   => 'Vikram Desai',
                'dob'    => '1988-02-28',
                'gender' => 'male',
                'blood'  => 'O+',
                'city'   => 'Surat',
                'state'  => 'Gujarat',
                'pin'    => '395001',
                'lang'   => 'hi',
                'emergency_name'   => 'Priya Desai',
                'emergency_mobile' => '9200100002',
            ],
            [
                'mobile' => '9200000003',
                'email'  => 'sneha.kulkarni@gmail.com',
                'name'   => 'Sneha Kulkarni',
                'dob'    => '1993-09-03',
                'gender' => 'female',
                'blood'  => 'B+',
                'city'   => 'Pune',
                'state'  => 'Maharashtra',
                'pin'    => '411001',
                'lang'   => 'mr',
                'emergency_name'   => 'Suresh Kulkarni',
                'emergency_mobile' => '9200100003',
            ],
            [
                'mobile' => '9200000004',
                'email'  => 'aryan.kapoor@gmail.com',
                'name'   => 'Aryan Kapoor',
                'dob'    => '1975-12-20',
                'gender' => 'male',
                'blood'  => 'AB+',
                'city'   => 'New Delhi',
                'state'  => 'Delhi',
                'pin'    => '110001',
                'lang'   => 'hi',
                'emergency_name'   => 'Neha Kapoor',
                'emergency_mobile' => '9200100004',
            ],
            [
                'mobile' => '9200000005',
                'email'  => 'lakshmi.rajan@gmail.com',
                'name'   => 'Lakshmi Rajan',
                'dob'    => '1998-04-17',
                'gender' => 'female',
                'blood'  => 'O-',
                'city'   => 'Chennai',
                'state'  => 'Tamil Nadu',
                'pin'    => '600001',
                'lang'   => 'en',
                'emergency_name'   => 'Rajan Subramaniam',
                'emergency_mobile' => '9200100005',
            ],
            [
                'mobile' => '9200000006',
                'email'  => 'suresh.nayak@gmail.com',
                'name'   => 'Suresh Nayak',
                'dob'    => '1968-07-31',
                'gender' => 'male',
                'blood'  => 'A-',
                'city'   => 'Bengaluru',
                'state'  => 'Karnataka',
                'pin'    => '560001',
                'lang'   => 'en',
                'emergency_name'   => 'Geetha Nayak',
                'emergency_mobile' => '9200100006',
            ],
            [
                'mobile' => '9200000007',
                'email'  => 'pooja.mishra@gmail.com',
                'name'   => 'Pooja Mishra',
                'dob'    => '2001-11-09',
                'gender' => 'female',
                'blood'  => 'B-',
                'city'   => 'Lucknow',
                'state'  => 'Uttar Pradesh',
                'pin'    => '226001',
                'lang'   => 'hi',
                'emergency_name'   => 'Ramesh Mishra',
                'emergency_mobile' => '9200100007',
            ],
            [
                'mobile' => '9200000008',
                'email'  => 'dinesh.reddy@gmail.com',
                'name'   => 'Dinesh Reddy',
                'dob'    => '1980-08-14',
                'gender' => 'male',
                'blood'  => 'O+',
                'city'   => 'Hyderabad',
                'state'  => 'Telangana',
                'pin'    => '500001',
                'lang'   => 'en',
                'emergency_name'   => 'Swapna Reddy',
                'emergency_mobile' => '9200100008',
            ],
            [
                'mobile' => '9200000009',
                'email'  => 'fatima.khan@gmail.com',
                'name'   => 'Fatima Khan',
                'dob'    => '1991-03-22',
                'gender' => 'female',
                'blood'  => 'AB-',
                'city'   => 'Nagpur',
                'state'  => 'Maharashtra',
                'pin'    => '440001',
                'lang'   => 'hi',
                'emergency_name'   => 'Imran Khan',
                'emergency_mobile' => '9200100009',
            ],
            [
                'mobile' => '9200000010',
                'email'  => 'harpreet.singh@gmail.com',
                'name'   => 'Harpreet Singh',
                'dob'    => '1972-05-05',
                'gender' => 'male',
                'blood'  => 'A+',
                'city'   => 'Amritsar',
                'state'  => 'Punjab',
                'pin'    => '143001',
                'lang'   => 'en',
                'emergency_name'   => 'Gurpreet Kaur',
                'emergency_mobile' => '9200100010',
            ],
        ];

        foreach ($patients as $data) {
            $user = User::create([
                'mobile_number'      => $data['mobile'],
                'country_code'       => '+91',
                'email'              => $data['email'],
                'password'           => Hash::make('Patient@1234'),
                'role'               => 'patient',
                'is_verified'        => true,
                'is_active'          => true,
                'preferred_language' => $data['lang'],
            ]);

            UserProfile::create([
                'user_id'                  => $user->id,
                'full_name'                => $data['name'],
                'dob'                      => $data['dob'],
                'gender'                   => $data['gender'],
                'blood_group'              => $data['blood'],
                'city'                     => $data['city'],
                'state'                    => $data['state'],
                'pincode'                  => $data['pin'],
                'emergency_contact_name'   => $data['emergency_name'],
                'emergency_contact_number' => $data['emergency_mobile'],
            ]);
        }

        $this->command->info('✔ 10 patient users created → password: Patient@1234');
    }
}
