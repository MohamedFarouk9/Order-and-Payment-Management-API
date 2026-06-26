<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration creates the payments table which stores payment transaction records.
     * Key features:
     * - Tracks payment status (pending, successful, failed)
     * - Records payment method used (credit_card, paypal, etc.)
     * - Stores gateway-specific response data for debugging
     * - Links payments to orders
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('payment_method', 50); // e.g., 'credit_card', 'paypal', 'stripe'
            $table->enum('status', ['pending', 'successful', 'failed'])->default('pending');
            $table->decimal('amount', 10, 2);
            $table->string('idempotency_key')->nullable(); // Idempotency key for retry safety
            $table->string('gateway_transaction_id')->nullable()->unique();
            $table->json('gateway_response')->nullable(); // Store full gateway response for audit trail
            $table->text('failure_reason')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Indexes for common queries and uniqueness
            $table->index('order_id');
            $table->index('status');
            $table->index('payment_method');
            $table->index('created_at');
            $table->unique(['order_id', 'idempotency_key']); // Ensure same idempotency key per order is unique
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
