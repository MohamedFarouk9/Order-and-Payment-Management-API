<?php

namespace App\Services;

use App\Actions\CreateOrderAction;
use App\Actions\UpdateOrderAction;
use App\Actions\ConfirmOrderAction;
use App\Actions\DeleteOrderAction;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * OrderService
 *
 * Service layer for order operations.
 * Coordinates order-related actions and provides a clean API for controllers.
 *
 * Architecture Pattern:
 * Controller -> Service -> Actions -> Models
 */
class OrderService
{
    protected CreateOrderAction $createOrderAction;
    protected UpdateOrderAction $updateOrderAction;
    protected ConfirmOrderAction $confirmOrderAction;
    protected DeleteOrderAction $deleteOrderAction;

    public function __construct(
        CreateOrderAction $createOrderAction,
        UpdateOrderAction $updateOrderAction,
        ConfirmOrderAction $confirmOrderAction,
        DeleteOrderAction $deleteOrderAction
    ) {
        $this->createOrderAction = $createOrderAction;
        $this->updateOrderAction = $updateOrderAction;
        $this->confirmOrderAction = $confirmOrderAction;
        $this->deleteOrderAction = $deleteOrderAction;
    }

    /**
     * Create a new order.
     *
     * @param int $userId User ID
     * @param array $data Order data with items
     * @return Order
     */
    public function create(int $userId, array $data): Order
    {
        return $this->createOrderAction->execute($userId, $data);
    }

    /**
     * Get all orders for a user with optional filtering.
     */
    public function getUserOrders(int $userId, ?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Order::with(['items', 'payments'])->where('user_id', $userId);

        if ($status && in_array($status, ['pending', 'confirmed', 'cancelled'])) {
            $query->where('status', $status);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get a specific order by ID.
     */
    public function getById(int $orderId): Order
    {
        return Order::with(['items', 'payments'])->findOrFail($orderId);
    }

    /**
     * Update an order.
     */
    public function update(Order $order, int $userId, array $data): Order
    {
        return $this->updateOrderAction->execute($order, $userId, $data);
    }

    /**
     * Confirm an order.
     */
    public function confirm(Order $order, int $userId): Order
    {
        return $this->confirmOrderAction->execute($order, $userId);
    }

    /**
     * Delete an order.
     */
    public function delete(Order $order, int $userId): bool
    {
        return $this->deleteOrderAction->execute($order, $userId);
    }

    /**
     * Check if user can access an order.
     */
    public function userCanAccess(Order $order, int $userId): bool
    {
        return $order->user_id === $userId;
    }
}
