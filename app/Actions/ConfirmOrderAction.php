<?php

namespace App\Actions;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

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
            // Log details to help debugging when a valid token seems to be present
            Log::warning('ConfirmOrderAction: unauthorized attempt to confirm order', [
                'order_id' => $order->id,
                'order_user_id' => $order->user_id,
                'requesting_user_id' => $userId,
            ]);

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
