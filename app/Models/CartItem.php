<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CartItem model representing an item in a shopping cart
 * 
 * @property int $id
 * @property int $cart_id
 * @property int $product_id
 * @property int $quantity
 * @property float $unit_price
 * @property float $subtotal
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Cart $cart
 * @property-read \App\Models\Product $product
 */
class CartItem extends Model
{
    /**
     * The attributes that are mass assignable
     *
     * @var array<string>
     */
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal'
    ];

    /**
     * The attributes that should be cast
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    /**
     * Get the cart that owns the cart item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product associated with the cart item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Update the subtotal of the cart item based on quantity and unit price
     *
     * @return void
     */
    public function updateSubtotal(): void
    {
        $this->subtotal = $this->quantity * $this->unit_price;
        $this->save();
    }
}
