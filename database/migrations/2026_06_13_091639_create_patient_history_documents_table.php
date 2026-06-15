<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_history_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('family_member_id')->nullable()->constrained('family_members')->cascadeOnDelete();
            $table->string('title', 200);
            $table->enum('document_type', [
                'prescription', 'lab_report', 'discharge_summary',
                'scan_xray', 'vaccination', 'insurance', 'other',
            ])->default('other');

            $table->text('notes')->nullable();
            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('file_size')->nullable();     // bytes
            $table->date('document_date')->nullable();
            $table->timestamps();

            $table->index(['patient_user_id', 'family_member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_history_documents');
    }
};
