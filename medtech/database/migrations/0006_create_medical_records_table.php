<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('family_member_id')->nullable()
                  ->constrained('family_members')->nullOnDelete();
            $table->foreignId('doctor_user_id')->constrained('users')->cascadeOnDelete();
            $table->date('visit_date');
            $table->string('visit_type', 50)->default('consultation'); // consultation | follow_up | emergency
            $table->text('chief_complaint')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('examination_notes')->nullable();
            $table->json('vitals')->nullable();                  // height, weight, BP, temp, SpO2
            $table->text('treatment_plan')->nullable();
            $table->text('doctor_notes')->nullable();            // private doctor notes
            $table->date('follow_up_date')->nullable();
            $table->json('attachments')->nullable();             // lab reports, scans (file paths)
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
