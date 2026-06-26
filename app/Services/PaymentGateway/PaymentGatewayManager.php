<?php

namespace App\Services\PaymentGateway;

/**
 * PaymentGatewayManager
 *
 * Central manager for payment gateways using the Strategy Pattern.
 *
 * This class is the key to extensibility:
 * - Register new gateways without modifying existing code
 * - Route payments to the appropriate gateway
 * - Handle gateway configuration
 *
 * To add a new payment method:
 * 1. Create a new class implementing PaymentGatewayInterface
 * 2. Register it in the registerGateways() method
 * 3. That's it! No other changes needed.
 */
class PaymentGatewayManager
{
    /**
     * Available payment gateways.
     *
     * @var array<string, PaymentGatewayInterface>
     */
    protected array $gateways = [];

    /**
     * Constructor - registers all available gateways.
     */
    public function __construct()
    {
        $this->registerGateways();
    }

    /**
     * Register all available payment gateways.
     *
     * This is where you add new payment methods.
     * Each gateway must implement PaymentGatewayInterface.
     *
     * @return void
     */
    protected function registerGateways(): void
    {
        $this->register('credit_card', new CreditCardGateway());
        $this->register('paypal', new PayPalGateway());

        // Example: To add Stripe
        // $this->register('stripe', new StripeGateway());

        // Example: To add Apple Pay
        // $this->register('apple_pay', new ApplePayGateway());
    }

    /**
     * Register a new payment gateway.
     * Can be used for runtime registration or testing.
     *
     * @param string $name The gateway identifier (e.g., 'credit_card', 'paypal')
     * @param PaymentGatewayInterface $gateway The gateway implementation
     * @return self For method chaining
     */
    public function register(string $name, PaymentGatewayInterface $gateway): self
    {
        $this->gateways[$name] = $gateway;
        return $this;
    }

    /**
     * Get a specific payment gateway.
     *
     * @param string $name The gateway name/identifier
     * @return PaymentGatewayInterface
     * @throws \InvalidArgumentException If gateway not found
     */
    public function gateway(string $name): PaymentGatewayInterface
    {
        if (!isset($this->gateways[$name])) {
            throw new \InvalidArgumentException(
                "Payment gateway '{$name}' not found. Available: " .
                implode(', ', array_keys($this->gateways))
            );
        }

        return $this->gateways[$name];
    }

    /**
     * Get all available gateways.
     *
     * @return array<string, PaymentGatewayInterface>
     */
    public function all(): array
    {
        return $this->gateways;
    }

    /**
     * Get all available and enabled gateways.
     *
     * @return array<string, PaymentGatewayInterface>
     */
    public function available(): array
    {
        return array_filter(
            $this->gateways,
            fn(PaymentGatewayInterface $gateway) => $gateway->isAvailable()
        );
    }

    /**
     * Process a payment using the specified gateway.
     * This is the main entry point for payment processing.
     *
     * @param string $gatewayName The payment method to use
     * @param float $amount The amount to charge
     * @param array $details Payment details (varies by gateway)
     * @return PaymentGatewayResponse
     * @throws PaymentGatewayException
     * @throws \InvalidArgumentException If gateway not found or not available
     */
    public function process(string $gatewayName, float $amount, array $details): PaymentGatewayResponse
    {
        $gateway = $this->gateway($gatewayName);

        if (!$gateway->isAvailable()) {
            throw new \InvalidArgumentException(
                "Payment gateway '{$gatewayName}' is not currently available"
            );
        }

        return $gateway->process($amount, $details);
    }

    /**
     * Refund a payment using the original gateway.
     *
     * @param string $gatewayName
     * @param string $transactionId
     * @param float|null $amount
     * @return PaymentGatewayResponse
     * @throws \InvalidArgumentException
     */
    public function refund(string $gatewayName, string $transactionId, ?float $amount = null): PaymentGatewayResponse
    {
        $gateway = $this->gateway($gatewayName);

        if (!$gateway->isAvailable()) {
            throw new \InvalidArgumentException(
                "Payment gateway '{$gatewayName}' is not currently available"
            );
        }

        return $gateway->refund($transactionId, $amount);
    }

    /**
     * Get list of available payment methods (for API responses).
     *
     * @return array
     */
    public function getAvailableMethodsForAPI(): array
    {
        return array_map(
            fn(PaymentGatewayInterface $gateway) => [
                'id' => $gateway->getName(),
                'name' => ucfirst(str_replace('_', ' ', $gateway->getName())),
            ],
            $this->available()
        );
    }

    /**
     * Check if a specific gateway is available.
     *
     * @param string $name
     * @return bool
     */
    public function isAvailable(string $name): bool
    {
        return isset($this->gateways[$name]) && $this->gateways[$name]->isAvailable();
    }
}
