<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessPaymentRequest;
use App\Http\Resources\PaymentCollection;
use App\Http\Resources\PaymentResource;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\PaymentGateway\PaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class PaymentController extends Controller
{
    protected PaymentService $paymentService;
    protected OrderService $orderService;
    protected PaymentGatewayManager $gatewayManager;

    public function __construct(
        PaymentService $paymentService,
        OrderService $orderService,
        PaymentGatewayManager $gatewayManager
    ) {
        $this->paymentService = $paymentService;
        $this->orderService = $orderService;
        $this->gatewayManager = $gatewayManager;
    }

    /**
     * Process a payment for an order.
     *
     * POST /api/orders/{id}/payments
     *
     * Headers:
     * - Idempotency-Key: Optional UUID for retry safety (if repeated, same result is returned)
     */
    public function process(ProcessPaymentRequest $request, int $orderId)
    {
        try {
            $order = $this->orderService->getById($orderId);

            // Extract idempotency key from request header (standard practice for safe retries)
            $idempotencyKey = $request->header('Idempotency-Key');

            $payment = $this->paymentService->processPayment(
                $order,
                Auth::id(),
                $request->validated(),
                $idempotencyKey
            );

            $statusCode = $payment->status === 'successful' ? 201 : 422;

            return response()->json([
                'message' => $payment->status === 'successful'
                    ? 'Payment processed successfully'
                    : 'Payment processing failed',
                'data' => new PaymentResource($payment),
            ], $statusCode);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Payment processing error',
                'error' => $e->getMessage(),
            ], $e->getMessage() === 'Unauthorized to pay for this order' ? 403 : 422);
        }
    }

    /**
     * Get all payments for a specific order.
     */
    public function indexByOrder(int $orderId, Request $request)
    {
        try {
            $order = $this->orderService->getById($orderId);

            // Authorization check
            if (!$this->orderService->userCanAccess($order, Auth::id())) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'error' => 'You can only view payments for your own orders',
                ], 403);
            }

            $payments = $this->paymentService->getOrderPayments(
                $order,
                $request->status,
                min((int) $request->per_page ?? 15, 100)
            );

            return response()->json([
                'message' => 'Payments retrieved successfully',
                'data' => PaymentResource::collection($payments),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving payments',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get all payments for the authenticated user.
     *
     * GET /api/payments
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $payments = $this->paymentService->getUserPayments(
            Auth::id(),
            $request->status,
            min((int) $request->per_page ?? 15, 100)
        );

        return response()->json([
            'message' => 'Payments retrieved successfully',
            'data' => PaymentResource::collection($payments),
        ]);
    }

    /**
     * Get a specific payment details.
     *
     * GET /api/payments/{id}
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        try {
            $payment = $this->paymentService->getById($id);

            // Authorization check
            if (!$this->paymentService->userCanAccess($payment, Auth::id())) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'error' => 'You can only view your own payments',
                ], 403);
            }

            return response()->json([
                'message' => 'Payment retrieved successfully',
                'data' => new PaymentResource($payment),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Payment not found',
                'error' => 'The specified payment does not exist',
            ], 404);
        }
    }

    /**
     * Get available payment methods.
     *
     * GET /api/payments/methods
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableMethods()
    {
        return response()->json([
            'data' => $this->gatewayManager->getAvailableMethodsForAPI(),
        ]);
    }

}
