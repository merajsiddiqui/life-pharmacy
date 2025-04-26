<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Traits\ApiResponse;
use App\Traits\SanitizesInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="API Endpoints for order management"
 * )
 */
class OrderController extends Controller
{
    use ApiResponse, SanitizesInput;

    /**
     * OrderController constructor.
     *
     * @param \App\Services\OrderService $orderService
     */
    public function __construct(
        protected OrderService $orderService
    ) {
    }

    /**
     * Display a listing of orders.
     * 
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Get all orders",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Orders retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Orders retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/OrderResource")
     *             )
     *         )
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $orders = $this->orderService->getOrders(auth()->id());

        Log::info('Orders retrieved successfully', ['user_id' => auth()->id()]);

        return $this->successResponse(
            OrderResource::collection($orders),
            __('order.messages.retrieved')
        );
    }

    /**
     * Store a newly created order.
     * 
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Create a new order",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"items"},
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"product_id", "quantity"},
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     *
     * @param \App\Http\Requests\Api\CreateOrderRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        $sanitizedData = $this->sanitizeInput($request->validated());
        $order = $this->orderService->createOrder(auth()->id(), $sanitizedData['items']);

        Log::info('Order created successfully', [
            'order_id' => $order->id,
            'user_id' => auth()->id()
        ]);

        return $this->createdResponse(
            new OrderResource($order),
            __('order.messages.created')
        );
    }

    /**
     * Display the specified order.
     * 
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     summary="Get order details",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order retrieved successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     *
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Order $order): JsonResponse
    {
        if ($order->user_id !== auth()->id()) {
            Log::warning('Unauthorized order access attempt', [
                'order_id' => $order->id,
                'user_id' => auth()->id()
            ]);

            throw new HttpException(Response::HTTP_FORBIDDEN, __('order.messages.unauthorized'));
        }

        Log::info('Order retrieved successfully', [
            'order_id' => $order->id,
            'user_id' => auth()->id()
        ]);

        return $this->successResponse(
            new OrderResource($order),
            __('order.messages.retrieved')
        );
    }

    /**
     * Cancel the specified order.
     * 
     * @OA\Post(
     *     path="/api/orders/{id}/cancel",
     *     summary="Cancel an order",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order cancelled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Order cancelled successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/OrderResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Order not found")
     *         )
     *     )
     * )
     *
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Order $order): JsonResponse
    {
        if ($order->user_id !== auth()->id()) {
            Log::warning('Unauthorized order cancellation attempt', [
                'order_id' => $order->id,
                'user_id' => auth()->id()
            ]);

            throw new HttpException(Response::HTTP_FORBIDDEN, __('order.messages.unauthorized'));
        }

        $this->orderService->cancelOrder($order);

        Log::info('Order cancelled successfully', [
            'order_id' => $order->id,
            'user_id' => auth()->id()
        ]);

        return $this->successResponse(
            new OrderResource($order),
            __('order.messages.cancelled')
        );
    }
}
