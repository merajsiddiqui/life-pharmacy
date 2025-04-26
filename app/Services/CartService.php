<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Repositories\Interfaces\CartRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

/**
 * Class CartService
 * 
 * @package App\Services
 */
class CartService
{
    /**
     * CartService constructor.
     *
     * @param \App\Repositories\Interfaces\CartRepositoryInterface $cartRepository
     */
    public function __construct(
        protected CartRepositoryInterface $cartRepository
    ) {
    }

    /**
     * Get or create a cart for the user.
     *
     * @param int $userId
     * @return \App\Models\Cart
     */
    public function getCart(int $userId): Cart
    {
        return $this->cartRepository->getOrCreateCart($userId);
    }

    /**
     * Get all items in the cart.
     *
     * @param \App\Models\Cart $cart
     * @return array
     */
    public function getCartItems(Cart $cart): array
    {
        $items = $this->cartRepository->getCartItems($cart);
        return $this->formatCartItems($items);
    }

    /**
     * Add a product to the cart.
     *
     * @param \App\Models\Cart $cart
     * @param int $productId
     * @param int $quantity
     * @return \App\Models\CartItem
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function addToCart(Cart $cart, int $productId, int $quantity): CartItem
    {
        $product = Product::findOrFail($productId);
        
        $existingItem = $cart->items()
            ->where('product_id', $productId)
            ->first();

        if ($existingItem) {
            return $this->updateCartItem($existingItem, $existingItem->quantity + $quantity);
        }

        return $this->cartRepository->addItem($cart, [
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => $product->price,
            'subtotal' => $product->price * $quantity
        ]);
    }

    /**
     * Update a cart item's quantity.
     *
     * @param \App\Models\CartItem $item
     * @param int $quantity
     * @return bool
     */
    public function updateCartItem(CartItem $item, int $quantity): bool
    {
        return $this->cartRepository->updateItem($item, $quantity);
    }

    /**
     * Remove an item from the cart.
     *
     * @param \App\Models\CartItem $item
     * @return bool
     */
    public function removeFromCart(CartItem $item): bool
    {
        return $this->cartRepository->removeItem($item);
    }

    /**
     * Clear all items from the cart.
     *
     * @param \App\Models\Cart $cart
     * @return bool
     */
    public function clearCart(Cart $cart): bool
    {
        return $this->cartRepository->clearCart($cart);
    }

    /**
     * Format cart items for response.
     *
     * @param \Illuminate\Support\Collection $items
     * @return array
     */
    protected function formatCartItems(Collection $items): array
    {
        return $items->map(function ($item) {
            return [
                'id' => $item->id,
                'product' => [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'price' => $item->product->price,
                    'image' => $item->product->image
                ],
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal
            ];
        })->toArray();
    }
} 