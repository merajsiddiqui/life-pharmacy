<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddToCartRequest;
use App\Http\Requests\Api\UpdateCartItemRequest;
use App\Http\Resources\CartItemResource;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use App\Traits\ApiResponse;
use App\Traits\SanitizesInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Cart",
 *     description="API Endpoints for shopping cart management"
 * )
 */
class CartController extends Controller
{
    use ApiResponse, SanitizesInput;

    /**
     * CartController constructor.
     *
     * @param \App\Services\CartService $cartService
     */
    public function __construct(
        protected CartService $cartService
    ) {
    }

    /**
     * Display the user's cart.
     * 
     * @OA\Get(
     *     path="/api/cart",
     *     summary="Get user's cart",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cart retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Cart retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/CartResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', CartItem::class);
        $cart = $this->cartService->getCart(auth()->id());

        Log::info('Cart retrieved successfully', ['user_id' => auth()->id()]);

        return $this->successResponse([
            'items' => CartItemResource::collection($cart->items),
            'total_amount' => $cart->total
        ], __('cart.messages.retrieved'));
    }

    /**
     * Add a product to the cart.
     * 
     * @OA\Post(
     *     path="/api/cart/items",
     *     summary="Add product to cart",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id", "quantity"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product added to cart successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Product added to cart successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/CartItemResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="product_id", type="array", @OA\Items(type="string", example="The product id field is required")),
     *                 @OA\Property(property="quantity", type="array", @OA\Items(type="string", example="The quantity must be at least 1"))
     *             )
     *         )
     *     )
     * )
     *
     * @param \App\Http\Requests\Api\AddToCartRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addItem(AddToCartRequest $request): JsonResponse
    {
        $this->authorize('create', CartItem::class);
        
        $sanitizedData = $this->sanitizeInput($request->validated());
        $cart = $this->cartService->getCart(auth()->id());
        
        $item = $this->cartService->addToCart(
            $cart,
            $sanitizedData['product_id'],
            $sanitizedData['quantity']
        );

        Log::info('Product added to cart successfully', [
            'user_id' => auth()->id(),
            'product_id' => $sanitizedData['product_id']
        ]);

        return $this->successResponse(
            new CartItemResource($item),
            __('cart.messages.item_added')
        );
    }

    /**
     * Update a cart item's quantity.
     * 
     * @OA\Put(
     *     path="/api/cart/items/{product_id}",
     *     summary="Update cart item quantity",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quantity"},
     *             @OA\Property(property="quantity", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cart item updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Cart item updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/CartItemResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cart item not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Cart item not found")
     *         )
     *     )
     * )
     *
     * @param \App\Http\Requests\Api\UpdateCartItemRequest $request
     * @param int $product_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateItem(UpdateCartItemRequest $request, int $product_id): JsonResponse
    {
        try {
            $cart = $this->cartService->getCart(auth()->id());
            
            // Find the cart item by product ID
            $cartItem = $cart->items()->where('product_id', $product_id)->first();
            
            if (!$cartItem) {
                return $this->errorResponse(
                    'Cart item not found',
                    Response::HTTP_NOT_FOUND
                );
            }

            // Load the cart item with its relationships
            $cartItem->load(['product', 'cart']);
            
            $sanitizedData = $this->sanitizeInput($request->validated());
            
            $updatedItem = $this->cartService->updateCartItem($cartItem, $sanitizedData['quantity']);

            Log::info('Cart item updated successfully', [
                'user_id' => auth()->id(),
                'cart_item_id' => $cartItem->id,
                'product_id' => $product_id
            ]);

            return $this->successResponse(
                new CartItemResource($updatedItem),
                __('cart.messages.item_updated')
            );
        } catch (\Exception $e) {
            Log::error('Failed to update cart item', [
                'error' => $e->getMessage(),
                'product_id' => $product_id,
                'user_id' => auth()->id()
            ]);

            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Remove an item from the cart.
     * 
     * @OA\Delete(
     *     path="/api/cart/items/{id}",
     *     summary="Remove item from cart",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Item removed from cart successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to remove this cart item")
     *         )
     *     )
     * )
     *
     * @param \App\Models\CartItem $item
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeItem(CartItem $item): JsonResponse
    {
        $this->authorize('delete', $item);
        $this->cartService->removeFromCart($item);

        Log::info('Cart item removed successfully', [
            'user_id' => auth()->id(),
            'cart_item_id' => $item->id
        ]);

        return $this->successResponse(null, __('cart.messages.item_removed'), Response::HTTP_NO_CONTENT);
    
    }

    /**
     * Clear the entire cart.
     * 
     * @OA\Delete(
     *     path="/api/cart",
     *     summary="Clear cart",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=204,
     *         description="Cart cleared successfully"
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear(): JsonResponse
    {
        $this->authorize('viewAny', CartItem::class);
        
        $cart = $this->cartService->getCart(auth()->id());
        $this->cartService->clearCart($cart);

        Log::info('Cart cleared successfully', ['user_id' => auth()->id()]);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
} 