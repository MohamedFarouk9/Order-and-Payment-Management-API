<?php

namespace App\Services\PaymentGateway;

/**
 * CreditCardGateway
 *
 * Concrete implementation of PaymentGatewayInterface for credit card processing.
 * This is a simulation - in production, you'd integrate with Stripe, Square, etc.
 *
 * Required configuration: CREDIT_CARD_API_KEY in .env
 */
class CreditCardGateway implements PaymentGatewayInterface
{
    /**
     * API key for the credit card processor
     */
    protected string $apiKey;

    /**
     * Gateway name identifier
     */
    private const NAME = 'credit_card';

    public function __construct()
    {
        $this->apiKey = config('services.credit_card.api_key', '');
    }

    /**
     * Process payment via credit card.
     *
     * @param float $amount
     * @param array $details Must include: card_number, card_holder, expiry, cvv
     * @return PaymentGatewayResponse
     * @throws PaymentGatewayException
     */
    public function process(float $amount, array $details): PaymentGatewayResponse
    {
        $this->validateDetails($details);

        try {
            // Simulate credit card validation
            if (!$this->validateCard($details)) {
                return PaymentGatewayResponse::failed(
                    'Credit card validation failed',
                    'CARD_INVALID'
                );
            }

            // Simulate API call - in production, call actual processor
            $transactionId = $this->generateTransactionId();

            // Simulate random success (90% success rate for demo)
            if (rand(1, 100) <= 90) {
                return PaymentGatewayResponse::success(
                    $transactionId,
                    'Credit card payment processed successfully',
                    [
                        'gateway' => self::NAME,
                        'amount' => $amount,
                        'card_last_four' => substr($details['card_number'], -4),
                    ]
                );
            } else {
                return PaymentGatewayResponse::failed(
                    'Credit card was declined',
                    'CARD_DECLINED'
                );
            }
        } catch (\Exception $e) {
            throw new PaymentGatewayException(
                'Credit card payment processing failed',
                0,
                'GATEWAY_ERROR',
                []
            );
        }
    }

    /**
     * Refund a credit card payment.
     *
     * @param string $transactionId
     * @param float|null $amount
     * @return PaymentGatewayResponse
     * @throws PaymentGatewayException
     */
    public function refund(string $transactionId, ?float $amount = null): PaymentGatewayResponse
    {
        // Simulate refund processing
        return PaymentGatewayResponse::success(
            $transactionId . '_refund',
            'Refund processed successfully',
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
     * Validate credit card details.
     *
     * @param array $details
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function validateDetails(array $details): bool
    {
        $required = ['card_number', 'card_holder', 'expiry', 'cvv'];

        foreach ($required as $field) {
            if (empty($details[$field] ?? null)) {
                throw new \InvalidArgumentException("Missing required field: $field");
            }
        }

        // Basic card number validation (Luhn algorithm)
        if (!$this->luhnCheck($details['card_number'])) {
            throw new \InvalidArgumentException('Invalid card number');
        }

        // Validate expiry format (MM/YY)
        if (!preg_match('/^\d{2}\/\d{2}$/', $details['expiry'])) {
            throw new \InvalidArgumentException('Invalid expiry format (use MM/YY)');
        }

        // Validate CVV (3-4 digits)
        if (!preg_match('/^\d{3,4}$/', $details['cvv'])) {
            throw new \InvalidArgumentException('Invalid CVV');
        }

        return true;
    }

    /**
     * Validate credit card using Luhn algorithm.
     *
     * @param string $cardNumber
     * @return bool
     */
    private function luhnCheck(string $cardNumber): bool
    {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);

        if (empty($cardNumber) || strlen($cardNumber) < 13) {
            return false;
        }

        $sum = 0;
        $isEven = false;

        for ($i = strlen($cardNumber) - 1; $i >= 0; --$i) {
            $digit = (int) $cardNumber[$i];

            if ($isEven) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $isEven = !$isEven;
        }

        return $sum % 10 === 0;
    }

    /**
     * Validate card details locally before sending to gateway.
     *
     * @param array $details
     * @return bool
     */
    private function validateCard(array $details): bool
    {
        // Check if card is expired
        $expiry = $details['expiry'];
        [$month, $year] = explode('/', $expiry);
        $expiryDate = \DateTime::createFromFormat('m/y', $expiry);

        return $expiryDate > new \DateTime();
    }

    /**
     * Generate unique transaction ID.
     *
     * @return string
     */
    private function generateTransactionId(): string
    {
        return 'CC_' . time() . '_' . uniqid();
    }
}
