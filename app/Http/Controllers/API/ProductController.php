<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateProductRequest;
use App\Http\Requests\Api\UpdateProductRequest;
use App\Http\Requests\Api\ListProductRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductCollection;
use App\Models\Product;
use App\Services\ProductService;
use App\Traits\ApiResponse;
use App\Traits\SanitizesInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="API Endpoints for product management"
 * )
 */
class ProductController extends Controller
{
    use ApiResponse, SanitizesInput;

    /**
     * ProductController constructor.
     *
     * @param \App\Services\ProductService $productService
     */
    public function __construct(
        protected ProductService $productService
    ) {
    }

    /**
     * Display a listing of products.
     * 
     * @OA\Get(
     *     path="/api/products",
     *     summary="Get all products",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter products by category",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search products by name or description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort products by field (price, name, created_at)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Sort order (asc, desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Products retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Products retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ProductResource")
     *             )
     *         )
     *     )
     * )
     *
     * @param ListProductRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(ListProductRequest $request): JsonResponse
    {
        $products = $this->productService->getAllProducts($request->validated());

        Log::info('Products retrieved successfully');

        return $this->collectionResponse(
            ProductResource::collection($products),
            __('products.messages.retrieved')
        );
    }

    /**
     * Store a newly created product.
     * 
     * @OA\Post(
     *     path="/api/products",
     *     summary="Create a new product",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description", "price", "stock", "category_id"},
     *             @OA\Property(property="name", type="string", example="Product Name"),
     *             @OA\Property(property="description", type="string", example="Product Description"),
     *             @OA\Property(property="price", type="number", format="float", example=99.99),
     *             @OA\Property(property="stock", type="integer", example=100),
     *             @OA\Property(property="category_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     *
     * @param \App\Http\Requests\Api\CreateProductRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);
        
        $sanitizedData = $this->sanitizeInput($request->validated());
        $product = $this->productService->createProduct($sanitizedData);

        Log::info('Product created successfully', ['product_id' => $product->id]);

        return $this->createdResponse(
            new ProductResource($product),
            __('products.messages.created')
        );
    }

    /**
     * Display the specified product.
     * 
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Get product details",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Product retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/ProductResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Product not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID format",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid ID format. Please provide a valid numeric ID.")
     *         )
     *     )
     * )
     *
     * @param string|int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Product $product): JsonResponse
    {
        Log::info('Product retrieved successfully', ['product_id' => $product->id]);

        return $this->successResponse(
            new ProductResource($product),
            __('products.messages.retrieved')
        );
    }

    /**
     * Update the specified product.
     * 
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Update a product",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description", "price", "stock", "category_id"},
     *             @OA\Property(property="name", type="string", example="Updated Product Name"),
     *             @OA\Property(property="description", type="string", example="Updated Product Description"),
     *             @OA\Property(property="price", type="number", format="float", example=149.99),
     *             @OA\Property(property="stock", type="integer", example=50),
     *             @OA\Property(property="category_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Product updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/ProductResource"
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
     *                 @OA\Property(property="name", type="array", @OA\Items(type="string", example="The name field is required")),
     *                 @OA\Property(property="price", type="array", @OA\Items(type="string", example="The price must be a number"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID format",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid ID format. Please provide a valid numeric ID.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Product not found")
     *         )
     *     )
     * )
     *
     * @param \App\Http\Requests\Api\UpdateProductRequest $request
     * @param string|int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);
        
        $sanitizedData = $this->sanitizeInput($request->validated());
        $product = $this->productService->updateProduct($product, $sanitizedData);

        Log::info('Product updated successfully', ['product_id' => $product->id]);

        return $this->successResponse(
            new ProductResource($product),
            __('products.messages.updated')
        );
    }

    /**
     * Remove the specified product.
     * 
     * @OA\Delete(
     *     path="/api/products/{product}",
     *     summary="Delete a product",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Product deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Product not found"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);
        
        $this->productService->deleteProduct($product);

        Log::info('Product deleted successfully', ['product_id' => $product->id]);

        return $this->deletedResponse(__('products.messages.deleted'));
    }
}
