<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserProfile;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'mobile_number'      => '9000000000',
            'country_code'       => '+91',
            'email'              => 'admin@medtech.in',
            'password'           => Hash::make('Admin@1234'),
            'role'               => 'admin',
            'is_verified'        => true,
            'is_active'          => true,
            'preferred_language' => 'en',
            'last_login_at'      => now(),
        ]);

        UserProfile::create([
            'user_id'    => $admin->id,
            'full_name'  => 'Platform Administrator',
            'gender'     => 'male',
            'city'       => 'Mumbai',
            'state'      => 'Maharashtra',
        ]);

        $this->command->info('✔ Admin user created → mobile: +91 9000000000 | password: Admin@1234');
    }
}
