<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Payment Model
 *
 * Represents a payment transaction for an order.
 * Implements Idempotency for safe retry logic:
 * - Use idempotency_key to prevent duplicate charges
 * - Same request (same idempotency_key) always returns same payment result
 * 
 * - Payments can only be processed for orders in 'confirmed' status
 * - Payment status: pending, successful, failed
 * - Stores gateway-specific transaction data for audit trail
 */
class Payment extends Model
{
    use HasFactory;


    protected $fillable = [
        'order_id',
        'payment_method',
        'status',
        'amount',
        'idempotency_key',
        'gateway_transaction_id',
        'gateway_response',
        'failure_reason',
        'processed_at',
    ];


    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'json',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who made this payment (through the order).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function user()
    {
        return $this->hasOneThrough(User::class, Order::class);
    }

    /**
     * Mark payment as successful with gateway details.
     *
     * @param string $gatewayTransactionId
     * @param array $gatewayResponse
     * @return bool
     */
    public function markSuccessful(string $gatewayTransactionId, array $gatewayResponse = []): bool
    {
        return $this->update([
            'status' => 'successful',
            'gateway_transaction_id' => $gatewayTransactionId,
            'gateway_response' => $gatewayResponse,
            'processed_at' => now(),
        ]);
    }


    public function markFailed(string $failureReason, array $gatewayResponse = []): bool
    {
        return $this->update([
            'status' => 'failed',
            'failure_reason' => $failureReason,
            'gateway_response' => $gatewayResponse,
            'processed_at' => now(),
        ]);
    }


    public function isPending(): bool
    {
        return $this->status === 'pending';
    }


    public function isSuccessful(): bool
    {
        return $this->status === 'successful';
    }


    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if associated order can accept this payment.
     * Business rule: payments only allowed for confirmed orders.
     *
     * @return bool
     */
    public function canBeProcessed(): bool
    {
        return $this->order && $this->order->status === 'confirmed';
    }
}
