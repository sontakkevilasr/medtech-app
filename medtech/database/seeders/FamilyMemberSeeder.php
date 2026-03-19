<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\FamilyMember;

class FamilyMemberSeeder extends Seeder
{
    public function run(): void
    {
        // Ananya Mehta (9200000001) — pregnant, add husband & in-laws
        $ananya = User::where('mobile_number', '9200000001')->first();
        FamilyMember::create([
            'primary_user_id' => $ananya->id,
            'sub_id'          => 'MED-00101-B',
            'full_name'       => 'Rohit Mehta',
            'dob'             => '1992-03-10',
            'gender'          => 'male',
            'relation'        => 'spouse',
            'blood_group'     => 'B+',
        ]);
        FamilyMember::create([
            'primary_user_id' => $ananya->id,
            'sub_id'          => 'MED-00101-C',
            'full_name'       => 'Kavya Mehta',        // newborn
            'dob'             => null,
            'gender'          => 'female',
            'relation'        => 'child',
            'blood_group'     => null,
        ]);

        // Vikram Desai (9200000002) — adds wife and son
        $vikram = User::where('mobile_number', '9200000002')->first();
        FamilyMember::create([
            'primary_user_id' => $vikram->id,
            'sub_id'          => 'MED-00201-B',
            'full_name'       => 'Priya Desai',
            'dob'             => '1990-07-22',
            'gender'          => 'female',
            'relation'        => 'spouse',
            'blood_group'     => 'A+',
        ]);
        FamilyMember::create([
            'primary_user_id' => $vikram->id,
            'sub_id'          => 'MED-00201-C',
            'full_name'       => 'Aarav Desai',
            'dob'             => '2018-01-15',
            'gender'          => 'male',
            'relation'        => 'child',
            'blood_group'     => 'O+',
        ]);

        // Aryan Kapoor (9200000004) — adds elderly parents
        $aryan = User::where('mobile_number', '9200000004')->first();
        FamilyMember::create([
            'primary_user_id' => $aryan->id,
            'sub_id'          => 'MED-00401-B',
            'full_name'       => 'Rakesh Kapoor',
            'dob'             => '1950-04-18',
            'gender'          => 'male',
            'relation'        => 'parent',
            'blood_group'     => 'O+',
        ]);
        FamilyMember::create([
            'primary_user_id' => $aryan->id,
            'sub_id'          => 'MED-00401-C',
            'full_name'       => 'Sunita Kapoor',
            'dob'             => '1953-11-30',
            'gender'          => 'female',
            'relation'        => 'parent',
            'blood_group'     => 'A+',
        ]);

        // Sneha Kulkarni (9200000003) — add a delinked member (demo of delink feature)
        $sneha = User::where('mobile_number', '9200000003')->first();
        FamilyMember::create([
            'primary_user_id'     => $sneha->id,
            'sub_id'              => 'MED-00301-B',
            'full_name'           => 'Amit Kulkarni',
            'dob'                 => '1991-08-08',
            'gender'              => 'male',
            'relation'            => 'spouse',
            'blood_group'         => 'O+',
            'is_delinked'         => true,               // demo delinked sub-ID
            'linked_mobile'       => '9300000099',
            'linked_country_code' => '+91',
            'delinked_at'         => now()->subMonths(2),
        ]);

        // Suresh Nayak (9200000006) — diabetic, adds wife
        $suresh = User::where('mobile_number', '9200000006')->first();
        FamilyMember::create([
            'primary_user_id' => $suresh->id,
            'sub_id'          => 'MED-00601-B',
            'full_name'       => 'Geetha Nayak',
            'dob'             => '1971-02-14',
            'gender'          => 'female',
            'relation'        => 'spouse',
            'blood_group'     => 'B+',
        ]);

        $this->command->info('✔ Family members created with sub-IDs (including 1 delinked demo)');
    }
}
