<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Patient defines default access level for their records
        Schema::create('patient_access_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('family_member_id')->nullable()
                  ->constrained('family_members')->cascadeOnDelete();
            $table->enum('access_type', ['full', 'otp_required'])->default('otp_required');
            $table->timestamps();

            // One permission record per patient / per family member
            $table->unique(['patient_user_id', 'family_member_id']);
        });

        // Doctor raises a request to view patient history
        Schema::create('doctor_access_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('patient_user_id')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->foreignId('family_member_id')->nullable()
                  ->constrained('family_members')->nullOnDelete();
            $table->string('patient_identifier');               // mobile or aadhaar or sub_id
            $table->enum('identifier_type', ['mobile', 'aadhaar', 'sub_id']);
            $table->enum('status', ['pending', 'approved', 'denied', 'expired'])
                  ->default('pending');
            $table->string('otp', 6)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('access_expires_at')->nullable(); // session-based access window
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_access_requests');
        Schema::dropIfExists('patient_access_permissions');
    }
};
