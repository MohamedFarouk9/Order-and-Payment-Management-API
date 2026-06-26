<?php

namespace App\Actions;

use App\Models\Order;

/**
 * ConfirmOrderAction
 *
 * Changes order status from pending to confirmed.
 * Once confirmed, the order can accept payments.
 */
class ConfirmOrderAction
{
    /**
     * Execute the action.
     *
     * @param Order $order The order to confirm
     * @param int $userId User ID for authorization
     * @return Order
     * @throws \Exception
     */
    public function execute(Order $order, int $userId): Order
    {
        // Authorization check
        if ($order->user_id !== $userId) {
            throw new \Exception('Unauthorized to confirm this order');
        }

        // Check if order can be confirmed
        if (!$order->canBeConfirmed()) {
            throw new \Exception('Only pending orders can be confirmed');
        }

        $order->confirm();

        return $order;
    }
}
