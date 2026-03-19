<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()
                  ->constrained('appointments')->nullOnDelete();
            $table->string('razorpay_order_id')->nullable()->unique();
            $table->string('razorpay_payment_id')->nullable()->unique();
            $table->string('razorpay_signature')->nullable();
            $table->enum('payment_method', ['razorpay', 'upi_qr', 'cash', 'other'])
                  ->default('razorpay');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 5)->default('INR');
            $table->enum('status', ['created', 'paid', 'failed', 'refunded'])->default('created');
            $table->enum('purpose', ['appointment', 'subscription', 'other'])->default('appointment');
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('doctor_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->enum('plan', ['basic', 'premium', 'enterprise'])->default('basic');
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('features_unlocked')->nullable();       // list of enabled features
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_subscriptions');
        Schema::dropIfExists('payments');
    }
};
