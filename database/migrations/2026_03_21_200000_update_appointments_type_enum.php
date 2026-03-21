<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE appointments MODIFY COLUMN `type` ENUM('in_person','online','consultation','follow_up','emergency') NOT NULL DEFAULT 'consultation'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE appointments MODIFY COLUMN `type` ENUM('in_person','online') NOT NULL DEFAULT 'in_person'");
    }
};
