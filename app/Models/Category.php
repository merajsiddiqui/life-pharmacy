<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;

/**
 * Category model representing a product category in the system
 */
class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
        'status'
    ];

    /**
     * Get the products associated with the category
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all categories with their associated products
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllWithProducts(): Collection
    {
        return self::with('products')->get();
    }

}
