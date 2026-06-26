<?php

namespace App\Services\PaymentGateway;

/**
 * PaymentGatewayInterface
 *
 * This interface defines the contract that all payment gateways must implement.
 * Using this interface allows us to easily add new payment methods without modifying
 * existing code (Open/Closed Principle).
 */
interface PaymentGatewayInterface
{
    /**
     * Process a payment through the gateway.
     *
     * @param float $amount The amount to charge
     * @param array $details Payment details (credit card, email, etc.)
     * @return PaymentGatewayResponse The response from the gateway
     * @throws PaymentGatewayException If payment processing fails
     */
    public function process(float $amount, array $details): PaymentGatewayResponse;

    /**
     * Refund a previously processed payment.
     *
     * @param string $transactionId The original transaction ID
     * @param float|null $amount The amount to refund (null = full refund)
     * @return PaymentGatewayResponse The response from the gateway
     * @throws PaymentGatewayException If refund processing fails
     */
    public function refund(string $transactionId, ?float $amount = null): PaymentGatewayResponse;

    /**
     * Get the gateway's unique identifier.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Check if the gateway is available/configured.
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Validate payment details before processing.
     *
     * @param array $details
     * @return bool
     * @throws \InvalidArgumentException If details are invalid
     */
    public function validateDetails(array $details): bool;
}
