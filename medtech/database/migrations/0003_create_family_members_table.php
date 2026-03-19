<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('primary_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('sub_id', 20)->unique();              // e.g. MED-00123-A
            $table->string('full_name', 100);
            $table->date('dob')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->enum('relation', [
                'self', 'spouse', 'child', 'parent',
                'sibling', 'grandparent', 'other'
            ])->default('other');
            $table->string('blood_group', 5)->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('aadhaar_number', 255)->nullable();   // stored encrypted

            // Delink / relink support
            $table->boolean('is_delinked')->default(false);
            $table->string('linked_mobile', 15)->nullable();     // mobile after delink
            $table->string('linked_country_code', 5)->nullable();
            $table->foreignId('linked_user_id')->nullable()      // user after delink
                  ->constrained('users')->nullOnDelete();
            $table->timestamp('delinked_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};
