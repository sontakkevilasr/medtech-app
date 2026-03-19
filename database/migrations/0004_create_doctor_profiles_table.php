<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('specialization', 100);
            $table->string('sub_specialization', 100)->nullable();
            $table->string('registration_number', 50)->unique();  // MCI / State council
            $table->string('registration_council', 100)->nullable();
            $table->string('qualification', 255);                 // MBBS, MD, etc.
            $table->integer('experience_years')->default(0);
            $table->string('clinic_name', 150)->nullable();
            $table->text('clinic_address')->nullable();
            $table->string('clinic_city', 100)->nullable();
            $table->string('clinic_state', 100)->nullable();
            $table->string('clinic_pincode', 10)->nullable();
            $table->decimal('consultation_fee', 8, 2)->default(0);
            $table->string('upi_id')->nullable();                 // for QR payments
            $table->string('upi_qr_image')->nullable();
            $table->json('languages_spoken')->nullable();         // ['en','hi','mr']
            $table->json('available_slots')->nullable();          // default weekly slots
            $table->text('bio')->nullable();
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_verified')->default(false);       // admin verification
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->date('premium_expires_at')->nullable();
            $table->boolean('accept_online_appointments')->default(true);
            $table->string('whatsapp_number', 15)->nullable();
            $table->string('whatsapp_country_code', 5)->default('+91');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_profiles');
    }
};
