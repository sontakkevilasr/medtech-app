<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_number')->unique();     // RX-2024-000001
            $table->foreignId('medical_record_id')->nullable()
                  ->constrained('medical_records')->nullOnDelete();
            $table->foreignId('doctor_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('patient_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('family_member_id')->nullable()
                  ->constrained('family_members')->nullOnDelete();
            $table->date('prescribed_date');
            $table->text('diagnosis_summary')->nullable();
            $table->text('general_instructions')->nullable();
            $table->text('diet_advice')->nullable();
            $table->text('follow_up_instructions')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->string('pdf_path')->nullable();
            $table->boolean('is_sent_whatsapp')->default(false);
            $table->timestamp('whatsapp_sent_at')->nullable();
            $table->enum('status', ['draft', 'issued', 'cancelled'])->default('issued');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('prescription_medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
            $table->string('medicine_name', 150);
            $table->string('generic_name', 150)->nullable();
            $table->string('dosage', 50);                        // 500mg, 10ml
            $table->string('form', 30)->nullable();              // tablet, syrup, injection
            $table->string('frequency', 100);                    // 1-0-1, TDS, OD
            $table->integer('duration_days')->nullable();
            $table->enum('timing', ['before_food','after_food','with_food','any_time','empty_stomach','bed_time', ])
                  ->default('after_food');
            $table->text('special_instructions')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_medicines');
        Schema::dropIfExists('prescriptions');
    }
};
