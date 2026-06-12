<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE prescription_medicines MODIFY COLUMN timing ENUM('before_food','after_food','with_food','empty_stomach','bed_time','any_time') NOT NULL DEFAULT 'after_food'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE prescription_medicines MODIFY COLUMN timing ENUM('before_food','after_food','with_food','any_time') NOT NULL DEFAULT 'after_food'");
    }
};
