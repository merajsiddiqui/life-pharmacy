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
        $cart = $this->cartRepository->getOrCreateCart($userId);
        $this->updateCartTotals($cart);
        return $cart;
    }

    /**
     * Update cart totals.
     *
     * @param \App\Models\Cart $cart
     * @return void
     */
    protected function updateCartTotals(Cart $cart): void
    {
        $cart->load('items');
        $cart->total_amount = $cart->items->sum('subtotal');
        $cart->save();
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
        
        // Check if there's enough stock
        if ($product->stock < $quantity) {
            throw new \Exception('Not enough stock available');
        }
        
        $existingItem = $cart->items()
            ->where('product_id', $productId)
            ->first();

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $quantity;
            if ($product->stock < $newQuantity) {
                throw new \Exception('Not enough stock available');
            }
            $item = $this->updateCartItem($existingItem, $newQuantity);
        } else {
            $item = $this->cartRepository->addItem($cart, [
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'subtotal' => $product->price * $quantity
            ]);
        }

        $this->updateCartTotals($cart);
        return $item;
    }

    /**
     * Update a cart item's quantity.
     *
     * @param \App\Models\CartItem $item
     * @param int $quantity
     * @return \App\Models\CartItem
     */
    public function updateCartItem(CartItem $item, int $quantity): CartItem
    {
        // Reload the cart item with its relationships
        $item = CartItem::with(['product', 'cart'])->findOrFail($item->id);

        // Check if there's enough stock
        if ($item->product->stock < $quantity) {
            throw new \Exception('Not enough stock available');
        }

        $item->quantity = $quantity;
        $item->updateSubtotal();
        $this->updateCartTotals($item->cart);

        return $item;
    }

    /**
     * Remove an item from the cart.
     *
     * @param \App\Models\CartItem $item
     * @return bool
     */
    public function removeFromCart(CartItem $item): bool
    {
        $cart = $item->cart;
        $result = $this->cartRepository->removeItem($item);
        if ($result) {
            $this->updateCartTotals($cart);
        }
        return $result;
    }

    /**
     * Clear all items from the cart.
     *
     * @param \App\Models\Cart $cart
     * @return bool
     */
    public function clearCart(Cart $cart): bool
    {
        $result = $this->cartRepository->clearCart($cart);
        if ($result) {
            $this->updateCartTotals($cart);
        }
        return $result;
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