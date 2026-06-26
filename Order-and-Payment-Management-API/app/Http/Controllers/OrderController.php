<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmOrderRequest;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }


    public function store(CreateOrderRequest $request)
    {
        try {
            $order = $this->orderService->create(Auth::id(), $request->validated());

            return response()->json([
                'message' => 'Order created successfully',
                'data' => new OrderResource($order),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Order creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all orders for authenticated user.
     */
    public function index(Request $request)
    {
        $orders = $this->orderService->getUserOrders(
            Auth::id(),
            $request->status,
            min((int) $request->per_page ?? 15, 100)
        );

        return response()->json([
            'message' => 'Orders retrieved successfully',
            'data' => OrderResource::collection($orders),
        ]);
    }

    /**
     * Get a specific order by ID.
     */
    public function show(int $id)
    {
        try {
            $order = $this->orderService->getById($id);

            // Authorization check
            if (!$this->orderService->userCanAccess($order, Auth::id())) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'error' => 'You can only view your own orders',
                ], 403);
            }

            return response()->json([
                'message' => 'Order retrieved successfully',
                'data' => new OrderResource($order),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Order not found',
                'error' => 'The specified order does not exist',
            ], 404);
        }
    }

    /**
     * Update an order.
     */
    public function update(UpdateOrderRequest $request, int $id)
    {
        try {
            $order = $this->orderService->getById($id);

            $updated = $this->orderService->update(
                $order,
                Auth::id(),
                $request->validated()
            );

            return response()->json([
                'message' => 'Order updated successfully',
                'data' => new OrderResource($updated),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Update failed',
                'error' => $e->getMessage(),
            ], $e->getMessage() === 'Unauthorized to update this order' ? 403 : 422);
        }
    }

    /**
     * Confirm an order.
     *
     * POST /api/orders/{id}/confirm
     *
     * @param ConfirmOrderRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirm(ConfirmOrderRequest $request, int $id)
    {
        try {
            $order = $this->orderService->getById($id);

            $confirmed = $this->orderService->confirm($order, Auth::id());

            return response()->json([
                'message' => 'Order confirmed successfully',
                'data' => new OrderResource($confirmed),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Confirmation failed',
                'error' => $e->getMessage(),
            ], $e->getMessage() === 'Unauthorized to confirm this order' ? 403 : 422);
        }
    }

    /**
     * Delete an order.
     *
     * DELETE /api/orders/{id}
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        try {
            $order = $this->orderService->getById($id);

            $this->orderService->delete($order, Auth::id());

            return response()->json([
                'message' => 'Order deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Deletion failed',
                'error' => $e->getMessage(),
            ], $e->getMessage() === 'Unauthorized to delete this order' ? 403 : 422);
        }
    }
}
