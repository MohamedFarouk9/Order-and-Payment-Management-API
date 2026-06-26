<?php

namespace App\Services\PaymentGateway;

/**
 * PaymentGatewayResponse
 *
 * Standardized response object from any payment gateway.
 * This ensures all gateways return data in a consistent format,
 * making it easy to handle responses uniformly.
 */
class PaymentGatewayResponse
{
    /**
     * Whether the payment was successful.
     */
    public bool $success;

    /**
     * Unique transaction ID from the gateway.
     */
    public string $transactionId;

    /**
     * Message describing the result (for UI display).
     */
    public string $message;

    /**
     * Raw response data from the gateway (for logging/audit).
     */
    public array $rawData;

    /**
     * Error code from the gateway (if failed).
     */
    public ?string $errorCode;

    /**
     * Create a successful response.
     *
     * @param string $transactionId
     * @param string $message
     * @param array $rawData
     * @return self

    */

    public static function success(
        string $transactionId,
        string $message = 'Payment processed successfully',
        array $rawData = []
    ): self {
        $response = new self();
        $response->success = true;
        $response->transactionId = $transactionId;
        $response->message = $message;
        $response->rawData = $rawData;
        $response->errorCode = null;

        return $response;
    }

    /**
     * Create a failed response.
     *
     * @param string $message
     * @param string $errorCode
     * @param array $rawData
     * @return self
     */
    public static function failed(
        string $message = 'Payment processing failed',
        string $errorCode = 'UNKNOWN',
        array $rawData = []
    ): self {
        $response = new self();
        $response->success = false;
        $response->transactionId = '';
        $response->message = $message;
        $response->rawData = $rawData;
        $response->errorCode = $errorCode;

        return $response;
    }

    /**
     * Convert response to array (useful for API responses).
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'transaction_id' => $this->transactionId,
            'message' => $this->message,
            'error_code' => $this->errorCode,
        ];
    }
}
