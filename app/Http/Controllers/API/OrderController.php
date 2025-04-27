<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\CartService;
use App\Traits\ApiResponse;
use App\Traits\SanitizesInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
     * @param \App\Services\CartService $cartService
     */
    public function __construct(
        protected OrderService $orderService,
        protected CartService $cartService
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
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter orders by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "processing", "completed", "cancelled"})
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort orders by field (created_at, total_amount, status)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Sort order (asc, desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
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
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=75)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(ref="#/components/schemas/ForbiddenResponse")
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Order::class);
        $orders = $this->orderService->getOrders(auth()->id());

        Log::info('Orders retrieved successfully', ['user_id' => auth()->id()]);

        return $this->successResponse(
            OrderResource::collection($orders),
            __('orders.messages.list_retrieved')
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
     *         @OA\JsonContent(ref="#/components/schemas/OrderRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Order created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/OrderResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     *
     * @param \App\Http\Requests\Api\CreateOrderRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        $this->authorize('create', Order::class);
        
        $cart = $this->cartService->getCart(auth()->id());
        
        if ($cart->items->isEmpty()) {
            return $this->errorResponse(
                __('orders.validation.cart_empty'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // Validate stock availability for all items
        foreach ($cart->items as $item) {
            $product = $item->product;
            if ($product->stock === 0) {
                return $this->errorResponse(
                    __('orders.validation.product_out_of_stock'),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
            
            if ($product->stock < $item->quantity) {
                return $this->errorResponse(
                    __('orders.validation.insufficient_stock'),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        }

        $sanitizedData = $this->sanitizeInput($request->validated());
        $order = $this->orderService->createOrder([
            'user_id' => auth()->id(),
            'shipping_address' => $sanitizedData['shipping_address'],
            'phone_number' => $sanitizedData['phone_number'],
            'notes' => $sanitizedData['notes'] ?? null,
            'payment_method' => $sanitizedData['payment_method'],
            'payment_status' => $sanitizedData['payment_status'],
            'shipping_method' => $sanitizedData['shipping_method'],
            'discount_code' => $sanitizedData['discount_code'] ?? null
        ], $cart->items);

        // Clear the cart after successful order creation
        $this->cartService->clearCart($cart);

        Log::info('Order created successfully', [
            'order_id' => $order->id,
            'user_id' => auth()->id()
        ]);

        return $this->createdResponse(
            new OrderResource($order),
            __('orders.messages.created')
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
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Order retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/OrderResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(ref="#/components/schemas/ForbiddenResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundResponse")
     *     )
     * )
     *
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

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
     *         description="Order ID",
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
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(ref="#/components/schemas/ForbiddenResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     *
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Order $order): JsonResponse
    {
        if ($order->status === 'cancelled') {
            return $this->errorResponse(
                __('orders.validation.already_cancelled'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->authorize('cancel', $order);
        
        if ($order->status !== 'pending') {
            return $this->errorResponse(
                __('orders.validation.cannot_cancel'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        
        $this->orderService->cancelOrder($order);

        Log::info('Order cancelled successfully', [
            'order_id' => $order->id,
            'user_id' => auth()->id()
        ]);

        return $this->successResponse(
            new OrderResource($order),
            __('orders.messages.cancelled')
        );
    }

    /**
     * Update the specified order's status.
     * 
     * @OA\Put(
     *     path="/api/orders/{id}",
     *     summary="Update order status",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"pending", "processing", "completed", "cancelled"},
     *                 example="processing",
     *                 description="New status of the order"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Order updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/OrderResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(ref="#/components/schemas/ForbiddenResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     *
     * @param \App\Models\Order $order
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Order $order, Request $request): JsonResponse
    {
        $this->authorize('update', $order);
        
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,processing,completed,cancelled']
        ]);
        
        $order = $this->orderService->updateOrderStatus($order, $validated['status']);

        Log::info('Order status updated successfully', [
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'status' => $validated['status']
        ]);

        return $this->successResponse(
            new OrderResource($order),
            __('order.messages.updated')
        );
    }
}
