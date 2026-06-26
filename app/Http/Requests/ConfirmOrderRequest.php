<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ConfirmOrderRequest
 * 
 * Validates order confirmation.
 * Currently minimal validation as it's a state change endpoint.
 */
class ConfirmOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
