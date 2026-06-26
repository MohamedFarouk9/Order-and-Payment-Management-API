<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ProcessPaymentRequest
 *
 * Validates payment processing input.
 * Ensures payment method and details are correct.
 * Payment details validation depends on the payment method selected.
 */
class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'string', 'in:credit_card,paypal'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'details' => ['required', 'array'],
            'details.card_number' => ['required_if:payment_method,credit_card', 'string'],
            'details.card_holder' => ['required_if:payment_method,credit_card', 'string'],
            'details.expiry' => ['required_if:payment_method,credit_card', 'string'],
            'details.cvv' => ['required_if:payment_method,credit_card', 'string'],
            'details.email' => ['required_if:payment_method,paypal', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.in' => 'Selected payment method is not supported.',
            'amount.min' => 'Payment amount must be greater than 0.',
            'details.required' => 'Payment details are required.',
        ];
    }
}
