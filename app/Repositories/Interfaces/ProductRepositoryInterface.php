<?php

namespace App\Repositories\Interfaces;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface for product repository operations
 */
interface ProductRepositoryInterface
{
    /**
     * Get all products with optional filters
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getAll(array $filters = []): LengthAwarePaginator;

    /**
     * Find a product by its ID
     *
     * @param int $id
     * @return Product|null
     */
    public function findById(int $id): ?Product;

    /**
     * Create a new product
     *
     * @param array $data
     * @return Product
     */
    public function create(array $data): Product;

    /**
     * Update an existing product
     *
     * @param Product $product
     * @param array $data
     * @return Product
     */
    public function update(Product $product, array $data): Product;

    /**
     * Delete a product
     *
     * @param Product $product
     * @return bool
     */
    public function delete(Product $product): bool;
} 