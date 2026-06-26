<?php

namespace App\Actions;

use App\Models\Order;
use Illuminate\Support\Arr;

/**
 * CreateOrderAction
 *
 * Single responsibility: Create a new order with items.
 * This action encapsulates the logic for order creation.
 */
class CreateOrderAction
{
    /**
     * Execute the action.
     *
     * @param int $userId User ID
     * @param array $data Order data including items
     * @return Order
     * @throws \Exception
     */
    public function execute(int $userId, array $data): Order
    {
        // Create the order
        $order = Order::create([
            'user_id' => $userId,
            'status' => 'pending',
            'total_amount' => 0,
            'items_count' => 0,
            'notes' => Arr::get($data, 'notes'),
        ]);

        // Add items to the order
        foreach (Arr::get($data, 'items', []) as $item) {
            $order->addItem(
                $item['product_name'],
                $item['quantity'],
                $item['price']
            );
        }

        return $order->refresh();
    }
}
