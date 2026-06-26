<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentGateway\PaymentGatewayException;
use App\Services\PaymentGateway\PaymentGatewayManager;
use Illuminate\Support\Str;

/**
 * ProcessPaymentAction
 *
 * Processes a payment for an order using the appropriate gateway.
 * Implements Idempotency: Safe retry logic prevents duplicate charges.
 */
class ProcessPaymentAction
{
    protected PaymentGatewayManager $gatewayManager;

    public function __construct(PaymentGatewayManager $gatewayManager)
    {
        $this->gatewayManager = $gatewayManager;
    }

    /**
     * Execute the action.
     *
     * @param Order $order The order to pay for
     * @param int $userId User ID for authorization
     * @param array $data Payment data
     * @param string|null $idempotencyKey Optional idempotency key for retry safety
     * @return Payment
     * @throws \Exception
     */
    public function execute(Order $order, int $userId, array $data, ?string $idempotencyKey = null): Payment
    {
        // Authorization check
        if ($order->user_id !== $userId) {
            throw new \Exception('Unauthorized to pay for this order');
        }

        //Only confirmed orders can be paid
        if ($order->status !== 'confirmed') {
            throw new \Exception('Only confirmed orders can be paid');
        }

        // Generate idempotency key if not provided
        $idempotencyKey = $idempotencyKey ?? Str::uuid()->toString();

        // **IDEMPOTENCY CHECK**: If this key was already processed, return the existing payment
        // This prevents duplicate charges if client retries the request
        $existingPayment = Payment::where('order_id', $order->id)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existingPayment) {
            // Same request received again - return the previous result
            // This is safe and idempotent - no new charge will be made
            return $existingPayment;
        }

        // Create new payment record (initially pending)
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => $data['payment_method'],
            'status' => 'pending',
            'amount' => $data['amount'],
            'idempotency_key' => $idempotencyKey, // Store the idempotency key
        ]);

        try {
            // Process payment through the appropriate gateway
            $response = $this->gatewayManager->process(
                $data['payment_method'],
                $data['amount'],
                $data['details']
            );

            if ($response->success) {
                // Payment successful
                $payment->markSuccessful(
                    $response->transactionId,
                    $response->rawData
                );
            } else {
                // Payment failed
                $payment->markFailed(
                    $response->message,
                    $response->rawData
                );
            }
        } catch (PaymentGatewayException $e) {
            // Gateway error
            $payment->markFailed(
                $e->getMessage(),
                $e->getGatewayResponse()
            );
        }

        return $payment;
    }
}
