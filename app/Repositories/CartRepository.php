<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Repositories\Interfaces\CartRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class CartRepository
 * 
 * @package App\Repositories
 */
class CartRepository implements CartRepositoryInterface
{
    public function __construct(
        protected Cart $cartModel,
        protected CartItem $cartItemModel
    ) {
    }

    public function getOrCreateCart(int $userId): Cart
    {
        return $this->cartModel->firstOrCreate(
            ['user_id' => $userId],
            ['total_amount' => 0]
        );
    }

    public function getCartItems(Cart $cart): Collection
    {
        return $cart->items()->with('product')->get();
    }

    public function addItem(Cart $cart, array $itemData): CartItem
    {
        $item = $cart->items()->create([
            'user_id' => $cart->user_id,
            'product_id' => $itemData['product_id'],
            'quantity' => $itemData['quantity'],
            'unit_price' => $itemData['unit_price'],
            'subtotal' => $itemData['quantity'] * $itemData['unit_price']
        ]);

        $cart->updateTotalAmount();

        return $item;
    }

    public function removeItem(CartItem $item): bool
    {
        $cart = $item->cart;
        $item->delete();
        $cart->updateTotalAmount();

        return true;
    }

    public function clearCart(Cart $cart): bool
    {
        $cart->items()->delete();
        $cart->updateTotalAmount();

        return true;
    }
} 