<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_number')->unique();      // APT-2024-000001
            $table->foreignId('doctor_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('patient_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('family_member_id')->nullable()
                  ->constrained('family_members')->nullOnDelete();
            $table->dateTime('slot_datetime');
            $table->integer('duration_minutes')->default(15);
            $table->enum('type', ['in_person', 'online'])->default('in_person');
            $table->enum('status', [
                'booked', 'confirmed', 'completed',
                'cancelled', 'no_show', 'rescheduled'
            ])->default('booked');
            $table->text('reason')->nullable();                  // patient's reason for visit
            $table->text('cancellation_reason')->nullable();
            $table->boolean('reminder_24h_sent')->default(false);
            $table->boolean('reminder_1h_sent')->default(false);
            $table->boolean('follow_up_reminder_sent')->default(false);
            $table->decimal('fee', 8, 2)->default(0);
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->foreignId('rescheduled_from')->nullable()
                  ->constrained('appointments')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
