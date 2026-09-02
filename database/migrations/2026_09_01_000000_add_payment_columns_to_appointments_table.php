<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Step 5: patient states their payment preference at booking (not actual payment)
            $table->enum('payment_preference', ['online', 'cash'])
                  ->default('online')
                  ->after('payment_status');

            // Step 2: note stored when doctor marks "Complete Anyway" (no payment record created)
            $table->text('completion_note')
                  ->nullable()
                  ->after('payment_preference');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['payment_preference', 'completion_note']);
        });
    }
};
