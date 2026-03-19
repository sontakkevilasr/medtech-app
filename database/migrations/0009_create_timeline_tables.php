<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timeline_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_user_id')->nullable()     // null = system template
                  ->constrained('users')->nullOnDelete();
            $table->enum('specialty_type', [
                'obstetrics', 'pediatrics', 'ivf',
                'dental', 'orthopedic', 'oncology', 'custom'
            ]);
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->integer('total_duration_days')->nullable();  // 280 days for pregnancy
            $table->string('duration_unit', 20)->default('week'); // week | day | month
            $table->boolean('is_system_template')->default(false);// pre-built templates
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('timeline_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('timeline_templates')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->integer('offset_value');                     // e.g. 8 (week 8)
            $table->string('offset_unit', 10)->default('week'); // week | day | month
            $table->enum('milestone_type', [
                'visit', 'vaccination', 'test', 'scan',
                'medication', 'procedure', 'reminder', 'info'
            ])->default('visit');
            $table->text('precautions')->nullable();
            $table->text('diet_advice')->nullable();
            $table->text('exercise_advice')->nullable();
            $table->json('reminder_days_before')->nullable();    // [7, 3, 1] days before
            $table->string('icon')->nullable();                  // icon name for UI
            $table->string('color', 10)->nullable();             // hex color for timeline
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('patient_timelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('timeline_templates')->cascadeOnDelete();
            $table->foreignId('patient_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('family_member_id')->nullable()
                  ->constrained('family_members')->nullOnDelete();
            $table->foreignId('assigned_by_doctor_id')->constrained('users')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('expected_end_date')->nullable();
            $table->json('custom_notes')->nullable();            // per-milestone custom notes
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_timelines');
        Schema::dropIfExists('timeline_milestones');
        Schema::dropIfExists('timeline_templates');
    }
};
