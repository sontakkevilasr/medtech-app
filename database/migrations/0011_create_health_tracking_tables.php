<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('family_member_id')->nullable()
                  ->constrained('family_members')->nullOnDelete();
            $table->enum('log_type', ['bp', 'sugar', 'weight', 'oxygen', 'temperature', 'pulse'])
                  ->index();
            // Flexible value columns to support various metric types
            $table->decimal('value_1', 6, 2)->nullable();       // systolic BP / sugar / weight
            $table->decimal('value_2', 6, 2)->nullable();       // diastolic BP
            $table->string('unit', 20)->nullable();              // mmHg, mg/dL, kg, %
            $table->enum('context', ['fasting', 'post_meal', 'random', 'morning', 'night', 'other'])
                  ->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('logged_at');
            $table->timestamps();
            $table->index(['patient_user_id', 'log_type', 'logged_at']);
        });

        Schema::create('medication_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('family_member_id')->nullable()
                  ->constrained('family_members')->nullOnDelete();
            $table->foreignId('prescription_id')->nullable()
                  ->constrained('prescriptions')->nullOnDelete();
            $table->string('medicine_name', 150);
            $table->string('dosage', 50);
            $table->json('reminder_times');                      // ["08:00","14:00","20:00"]
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('channel', ['whatsapp', 'sms', 'in_app', 'all'])->default('whatsapp');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_reminders');
        Schema::dropIfExists('health_logs');
    }
};
