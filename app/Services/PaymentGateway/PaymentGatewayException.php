<?php

namespace App\Services\PaymentGateway;

use Exception;

/**
 * PaymentGatewayException
 *
 * Thrown when payment gateway processing fails.
 * Used to distinguish payment-related exceptions from general exceptions.
 */
class PaymentGatewayException extends Exception
{
    /**
     * Gateway error code
     */
    protected string $gatewayErrorCode;

    /**
     * Gateway error response data
     */
    protected array $gatewayResponse;

    public function __construct(
        string $message = '',
        int $code = 0,
        string $gatewayErrorCode = '',
        array $gatewayResponse = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->gatewayErrorCode = $gatewayErrorCode;
        $this->gatewayResponse = $gatewayResponse;
    }

    public function getGatewayErrorCode(): string
    {
        return $this->gatewayErrorCode;
    }

    public function getGatewayResponse(): array
    {
        return $this->gatewayResponse;
    }
}
