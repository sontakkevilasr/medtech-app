<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The form's third "Notify via" pill sends channel='both' (WhatsApp + SMS),
     * but the original ENUM only allowed: whatsapp, sms, in_app, all.
     * This migration adds 'both' to the ENUM so the value is accepted.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE medication_reminders MODIFY COLUMN channel ENUM('whatsapp','sms','in_app','all','both') NOT NULL DEFAULT 'whatsapp'");
    }

    public function down(): void
    {
        // Convert any 'both' rows to 'all' so the column can be safely narrowed back.
        DB::table('medication_reminders')
            ->where('channel', 'both')
            ->update(['channel' => 'all']);

        DB::statement("ALTER TABLE medication_reminders MODIFY COLUMN channel ENUM('whatsapp','sms','in_app','all') NOT NULL DEFAULT 'whatsapp'");
    }
};
