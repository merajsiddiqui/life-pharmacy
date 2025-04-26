<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Eloquent implementation of the ProductRepositoryInterface
 */
class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @var Product
     */
    protected $model;

    /**
     * Create a new ProductRepository instance
     *
     * @param Product $model
     */
    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    /**
     * Get all products with optional filters
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with('category');

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->paginate(10);
    }

    /**
     * Find a product by its ID
     *
     * @param int $id
     * @return Product|null
     */
    public function findById(int $id): ?Product
    {
        return $this->model->with('category')->find($id);
    }

    /**
     * Create a new product
     *
     * @param array $data
     * @return Product
     */
    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing product
     *
     * @param Product $product
     * @param array $data
     * @return Product
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product;
    }

    /**
     * Delete a product
     *
     * @param Product $product
     * @return bool
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }
} 