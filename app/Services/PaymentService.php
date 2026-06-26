<?php

namespace App\Services;

use App\Actions\ProcessPaymentAction;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\Paginator;

/**
 * PaymentService
 *
 * Service layer for payment operations.
 * Coordinates payment-related actions.
 * Demonstrates the power of service layer for complex operations.
 */
class PaymentService
{
    protected ProcessPaymentAction $processPaymentAction;

    public function __construct(ProcessPaymentAction $processPaymentAction)
    {
        $this->processPaymentAction = $processPaymentAction;
    }

    /**
     * Process a payment for an order.
     *
     * @param Order $order Order to pay for
     * @param int $userId User ID (for authorization)
     * @param array $data Payment data
     * @param string|null $idempotencyKey Optional idempotency key for retry safety
     * @return Payment
     */
    public function processPayment(Order $order, int $userId, array $data, ?string $idempotencyKey = null): Payment
    {
        return $this->processPaymentAction->execute($order, $userId, $data, $idempotencyKey);
    }

    /**
     * Get all payments for a specific order.
     *
     * @param Order $order
     * @param string|null $status Filter by status
     * @param int $perPage
     * @return Paginator
     */
    public function getOrderPayments(Order $order, ?string $status = null, int $perPage = 15): Paginator
    {
        $query = $order->payments();

        if ($status && in_array($status, ['pending', 'successful', 'failed'])) {
            $query->where('status', $status);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get all payments for a user.
     *
     * @param int $userId
     * @param string|null $status Filter by status
     * @param int $perPage
     * @return Paginator
     */
    public function getUserPayments(int $userId, ?string $status = null, int $perPage = 15): Paginator
    {
        $query = Payment::whereHas('order', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });

        if ($status && in_array($status, ['pending', 'successful', 'failed'])) {
            $query->where('status', $status);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get a specific payment.
     *
     * @param int $paymentId
     * @return Payment
     */
    public function getById(int $paymentId): Payment
    {
        return Payment::findOrFail($paymentId);
    }

    /**
     * Check if user can access a payment.
     *
     * @param Payment $payment
     * @param int $userId
     * @return bool
     */
    public function userCanAccess(Payment $payment, int $userId): bool
    {
        return $payment->order->user_id === $userId;
    }
}
