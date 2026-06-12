<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('medicine_name', 150);
            $table->string('generic_name', 150)->nullable();
            $table->string('form', 30)->nullable();
            $table->string('dosage', 50)->nullable();
            $table->string('frequency', 100)->nullable();
            $table->string('timing', 30)->nullable();
            $table->unsignedSmallInteger('duration_days')->nullable();
            $table->text('special_instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['doctor_user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_medicines');
    }
};
