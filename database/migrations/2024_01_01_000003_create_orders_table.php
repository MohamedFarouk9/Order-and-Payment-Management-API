<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration creates the orders table which stores all customer orders.
     * Each order tracks:
     * - user_id: The customer who placed the order
     * - status: Current order state (pending, confirmed, cancelled)
     * - total_amount: Calculated sum of all items
     * - items_count: Number of items in the order
     * - timestamps: Track creation and modification times
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->decimal('total_amount', 10, 2);
            $table->integer('items_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for common queries
            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
