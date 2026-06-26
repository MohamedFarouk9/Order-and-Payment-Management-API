<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdateOrderRequest
 *
 * Validates order update input.
 * Allows updating notes and items.
 */
class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // User can only update their own orders
        return auth()->user()->id === (int) $this->route('order');
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['nullable', 'array'],
            'items.*.id' => ['required_if:items,null', 'integer'],
            'items.*.product_name' => ['required', 'string', 'min:1', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
