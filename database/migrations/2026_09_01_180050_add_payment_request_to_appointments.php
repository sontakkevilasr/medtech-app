<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('payment_requested_at')->nullable()->after('payment_preference');
            $table->unsignedBigInteger('payment_requested_by')->nullable()->after('payment_requested_at');

            $table->index('payment_requested_at');
            $table->foreign('payment_requested_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['payment_requested_by']);
            $table->dropIndex(['payment_requested_at']);
            $table->dropColumn(['payment_requested_at', 'payment_requested_by']);
        });
    }
};
