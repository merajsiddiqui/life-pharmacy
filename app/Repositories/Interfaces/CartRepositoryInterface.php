<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface CartRepositoryInterface
 * 
 * @package App\Repositories\Interfaces
 */
interface CartRepositoryInterface
{
    public function getOrCreateCart(int $userId): Cart;
    public function getCartItems(Cart $cart): Collection;
    public function addItem(Cart $cart, array $itemData): CartItem;
    public function updateItem(CartItem $item, int $quantity): bool;
    public function removeItem(CartItem $item): bool;
    public function clearCart(Cart $cart): bool;
} 