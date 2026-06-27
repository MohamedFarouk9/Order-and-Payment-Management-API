<?php

namespace App\Actions;

use App\Models\Order;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * UpdateOrderAction
 *
 * Updates an existing order with new details and items.
 * Validates authorization and order status before updating.
 */
class UpdateOrderAction
{
    /**
     * Execute the action.
     *
     * @param Order $order The order to update
     * @param int $userId User ID for authorization
     * @param array $data New order data
     * @return Order
     * @throws \Exception
     */
    public function execute(Order $order, int $userId, array $data): Order
    {
        // Authorization check
        if ($order->user_id !== $userId) {
            Log::warning('UpdateOrderAction: unauthorized update attempt', [
                'order_id' => $order->id,
                'order_user_id' => $order->user_id,
                'requesting_user_id' => $userId,
            ]);

            throw new \Exception('Unauthorized to update this order');
        }

        // Can only update pending orders
        if ($order->status !== 'pending') {
            Log::info('UpdateOrderAction: update prevented because order is not pending', [
                'order_id' => $order->id,
                'status' => $order->status,
                'requesting_user_id' => $userId,
            ]);

            throw new \Exception('Can only update pending orders');
        }

        // Update notes if provided
        if (Arr::has($data, 'notes')) {
            $order->update(['notes' => $data['notes']]);
        }

        // Update items if provided
        if (Arr::has($data, 'items')) {
            $order->items()->delete();  // Remove existing items

            foreach ($data['items'] as $item) {
                $order->addItem(
                    $item['product_name'],
                    $item['quantity'],
                    $item['price']
                );
            }
        }

        return $order->refresh();
    }
}
