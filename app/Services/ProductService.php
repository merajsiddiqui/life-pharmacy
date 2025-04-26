<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;

/**
 * Service class for handling product-related business logic
 */
class ProductService
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Cache duration in seconds
     */
    const CACHE_DURATION = 3600; // 1 hour

    /**
     * Create a new ProductService instance
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Get all products with optional filters
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getAllProducts(array $filters = []): LengthAwarePaginator
    {
        $cacheKey = $this->generateCacheKey($filters);

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($filters) {
            return $this->productRepository->getAll($filters);
        });
    }

    /**
     * Get a product by its ID
     *
     * @param int $id
     * @return Product|null
     */
    public function getProductById(int $id): ?Product
    {
        $cacheKey = "product:{$id}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($id) {
            return $this->productRepository->findById($id);
        });
    }

    /**
     * Create a new product
     *
     * @param array $data
     * @return Product
     */
    public function createProduct(array $data): Product
    {
        $product = $this->productRepository->create($data);
        $this->clearProductCache();
        return $product;
    }

    /**
     * Update an existing product
     *
     * @param Product $product
     * @param array $data
     * @return Product
     */
    public function updateProduct(Product $product, array $data): Product
    {
        $product = $this->productRepository->update($product, $data);
        $this->clearProductCache($product->id);
        return $product;
    }

    /**
     * Delete a product
     *
     * @param Product $product
     * @return bool
     */
    public function deleteProduct(Product $product): bool
    {
        $result = $this->productRepository->delete($product);
        $this->clearProductCache($product->id);
        return $result;
    }

    /**
     * Generate a cache key for product listing
     *
     * @param array $filters
     * @return string
     */
    protected function generateCacheKey(array $filters): string
    {
        return 'products:' . md5(json_encode($filters));
    }

    /**
     * Clear product cache
     *
     * @param int|null $productId
     * @return void
     */
    protected function clearProductCache(?int $productId = null): void
    {
        if ($productId) {
            Cache::forget("product:{$productId}");
        }
        
        if (App::environment('testing')) {
            // In testing environment, just clear all cache
            Cache::flush();
        } else {
            // In production/development, use cache tags
            Cache::tags(['products'])->flush();
        }
    }
} 