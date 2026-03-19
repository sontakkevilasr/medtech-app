<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\PatientAccessPermission;

class AccessPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Full-access patients — doctor can view without OTP
        $fullAccessMobiles = [
            '9200000001', // Ananya — trusts her OBG doctor fully
            '9200000002', // Vikram
            '9200000006', // Suresh — elderly, wants easy access
            '9200000008', // Dinesh
            '9200000010', // Harpreet
        ];

        // OTP-required patients — doctor must request access every time
        $otpRequiredMobiles = [
            '9200000003', // Sneha — privacy-conscious
            '9200000004', // Aryan
            '9200000005', // Lakshmi
            '9200000007', // Pooja
            '9200000009', // Fatima
        ];

        foreach ($fullAccessMobiles as $mobile) {
            $user = User::where('mobile_number', $mobile)->first();
            if ($user) {
                PatientAccessPermission::create([
                    'patient_user_id'  => $user->id,
                    'family_member_id' => null,
                    'access_type'      => 'full',
                ]);
            }
        }

        foreach ($otpRequiredMobiles as $mobile) {
            $user = User::where('mobile_number', $mobile)->first();
            if ($user) {
                PatientAccessPermission::create([
                    'patient_user_id'  => $user->id,
                    'family_member_id' => null,
                    'access_type'      => 'otp_required',
                ]);
            }
        }

        $this->command->info('✔ Access permissions set: 5 full-access, 5 OTP-required');
    }
}
