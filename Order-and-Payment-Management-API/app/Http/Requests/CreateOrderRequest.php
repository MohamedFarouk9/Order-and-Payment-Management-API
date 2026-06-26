<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CreateOrderRequest
 * 
 * Validates order creation input.
 * Ensures items are provided and properly formatted.
 */
class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authenticated users can create orders
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_name' => ['required', 'string', 'min:1', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one item is required.',
            'items.min' => 'Order must contain at least one item.',
            'items.*.product_name.required' => 'Product name is required for each item.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.price.min' => 'Price must be greater than 0.',
        ];
    }
}
