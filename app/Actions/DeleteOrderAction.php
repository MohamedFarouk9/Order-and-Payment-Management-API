<?php

namespace App\Actions;

use App\Models\Order;

/**
 * DeleteOrderAction
 *
 * Deletes an order.
 * Orders with payments cannot be deleted.
 */
class DeleteOrderAction
{
    /**
     * Execute the action.
     *
     * @param Order $order The order to delete
     * @param int $userId User ID for authorization
     * @return bool
     * @throws \Exception
     */
    public function execute(Order $order, int $userId): bool
    {
        // Authorization check
        if ($order->user_id !== $userId) {
            throw new \Exception('Unauthorized to delete this order');
        }

        // Cannot delete orders with payments
        if (!$order->canBeDeleted()) {
            throw new \Exception('Cannot delete orders with associated payments');
        }

        return $order->delete();
    }
}
