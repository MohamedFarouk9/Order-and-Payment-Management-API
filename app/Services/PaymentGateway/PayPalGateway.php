<?php

namespace App\Services\PaymentGateway;

/**
 * PayPalGateway
 *
 * Concrete implementation for PayPal payment processing.
 * Required configuration: PAYPAL_API_KEY in .env
 */
class PayPalGateway implements PaymentGatewayInterface
{
    /**
     * PayPal API key
     */
    protected string $apiKey;

    /**
     * Gateway name identifier
     */
    private const NAME = 'paypal';

    public function __construct()
    {
        $this->apiKey = config('services.paypal.api_key', '');
    }

    /**
     * Process payment via PayPal.
     *
     * @param float $amount
     * @param array $details Must include: email
     * @return PaymentGatewayResponse
     * @throws PaymentGatewayException
     */
    public function process(float $amount, array $details): PaymentGatewayResponse
    {
        $this->validateDetails($details);

        try {
            // Simulate PayPal API call
            $transactionId = $this->generateTransactionId();

            // Simulate success (95% success rate for PayPal)
            if (rand(1, 100) <= 95) {
                return PaymentGatewayResponse::success(
                    $transactionId,
                    'PayPal payment processed successfully',
                    [
                        'gateway' => self::NAME,
                        'amount' => $amount,
                        'paypal_email' => $details['email'],
                        'timestamp' => now()->toDateTimeString(),
                    ]
                );
            } else {
                return PaymentGatewayResponse::failed(
                    'PayPal payment could not be completed',
                    'PAYPAL_ERROR'
                );
            }
        } catch (\Exception $e) {
            throw new PaymentGatewayException(
                'PayPal payment processing failed: ' . $e->getMessage(),
                0,
                'GATEWAY_ERROR',
                []
            );
        }
    }

    /**
     * Refund a PayPal payment.
     *
     * @param string $transactionId
     * @param float|null $amount
     * @return PaymentGatewayResponse
     * @throws PaymentGatewayException
     */
    public function refund(string $transactionId, ?float $amount = null): PaymentGatewayResponse
    {
        // Simulate PayPal refund
        return PaymentGatewayResponse::success(
            $transactionId . '_refund',
            'PayPal refund processed successfully',
            [
                'original_transaction_id' => $transactionId,
                'gateway' => self::NAME,
            ]
        );
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Validate PayPal payment details.
     *
     * @param array $details Must contain 'email'
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function validateDetails(array $details): bool
    {
        if (empty($details['email'] ?? null)) {
            throw new \InvalidArgumentException('PayPal email is required');
        }

        if (!filter_var($details['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address');
        }

        return true;
    }

    /**
     * Generate unique transaction ID in PayPal format.
     *
     * @return string
     */
    private function generateTransactionId(): string
    {
        return 'PP_' . strtoupper(bin2hex(random_bytes(8)));
    }
}
