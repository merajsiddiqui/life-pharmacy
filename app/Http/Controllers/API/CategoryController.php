<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use App\Traits\ApiResponse;
use App\Traits\SanitizesInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Class CategoryController
 * 
 * Handles category-related API endpoints with proper validation, caching, and error handling.
 * 
 * @package App\Http\Controllers\Api
 * 
 * @OA\Tag(
 *     name="Categories",
 *     description="API Endpoints for category management"
 * )
 */
class CategoryController extends Controller
{
    use ApiResponse, SanitizesInput;

    /**
     * CategoryController constructor.
     *
     * @param \App\Services\CategoryService $categoryService
     */
    public function __construct(
        protected CategoryService $categoryService
    ) {
    }

    /**
     * Display a listing of categories.
     * 
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Get all categories",
     *     tags={"Categories"},
     *     @OA\Response(
     *         response=200,
     *         description="Categories retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Categories retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/CategoryResource")
     *             )
     *         )
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $categories = Cache::remember('categories.all', 3600, function () {
                return $this->categoryService->getAllCategories();
            });

            Log::info('Categories retrieved successfully');

            return $this->successResponse(
                CategoryResource::collection($categories),
                __('categories.messages.list_retrieved')
            );
        } catch (\Exception $e) {
            Log::error('Error retrieving categories', [
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse(
                __('categories.messages.retrieval_failed'),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Store a newly created category.
     * 
     * @OA\Post(
     *     path="/api/categories",
     *     summary="Create a new category",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description"},
     *             @OA\Property(property="name", type="string", example="Electronics"),
     *             @OA\Property(property="description", type="string", example="Electronic devices and accessories")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Category created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/CategoryResource"
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
     *                 @OA\Property(property="description", type="array", @OA\Items(type="string", example="The description field is required"))
     *             )
     *         )
     *     )
     * )
     *
     * @param \App\Http\Requests\Api\CategoryRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        try {
            $sanitizedData = $this->sanitizeInput($request->validated());
            $category = $this->categoryService->createCategory($sanitizedData);

            Cache::tags(['categories'])->flush();

            Log::info('Category created successfully', ['category_id' => $category->id]);

            return $this->createdResponse(
                new CategoryResource($category),
                __('categories.messages.created')
            );
        } catch (\Exception $e) {
            Log::error('Error creating category', [
                'error' => $e->getMessage(),
                'data' => $sanitizedData ?? []
            ]);

            return $this->errorResponse(
                __('categories.messages.creation_failed'),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Display the specified category.
     * 
     * @OA\Get(
     *     path="/api/categories/{id}",
     *     summary="Get category details",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Category retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/CategoryResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Category not found")
     *         )
     *     )
     * )
     *
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Category $category): JsonResponse
    {
        try {
            Log::info('Category retrieved successfully', ['category_id' => $category->id]);

            return $this->successResponse(
                new CategoryResource($category),
                __('categories.messages.retrieved')
            );
        } catch (\Exception $e) {
            Log::error('Error retrieving category', [
                'error' => $e->getMessage(),
                'category_id' => $category->id
            ]);

            return $this->errorResponse(
                __('categories.messages.retrieval_failed'),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Update the specified category.
     * 
     * @OA\Put(
     *     path="/api/categories/{id}",
     *     summary="Update category",
     *     tags={"Categories"},
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
     *             @OA\Property(property="name", type="string", example="Updated Category Name"),
     *             @OA\Property(property="description", type="string", example="Updated category description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Category updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/CategoryResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Category not found")
     *         )
     *     )
     * )
     *
     * @param \App\Http\Requests\Api\CategoryRequest $request
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        try {
            $sanitizedData = $this->sanitizeInput($request->validated());
            $category = $this->categoryService->updateCategory($category, $sanitizedData);

            Cache::tags(['categories'])->flush();

            Log::info('Category updated successfully', ['category_id' => $category->id]);

            return $this->successResponse(
                new CategoryResource($category),
                __('categories.messages.updated')
            );
        } catch (\Exception $e) {
            Log::error('Error updating category', [
                'error' => $e->getMessage(),
                'category_id' => $category->id,
                'data' => $sanitizedData ?? []
            ]);

            return $this->errorResponse(
                __('categories.messages.update_failed'),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Remove the specified category.
     * 
     * @OA\Delete(
     *     path="/api/categories/{id}",
     *     summary="Delete category",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Category deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Category not found")
     *         )
     *     )
     * )
     *
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Category $category): JsonResponse
    {
        try {
            $this->categoryService->deleteCategory($category);

            Cache::tags(['categories'])->flush();

            Log::info('Category deleted successfully', ['category_id' => $category->id]);

            return $this->successResponse(
                null,
                __('categories.messages.deleted'),
                Response::HTTP_NO_CONTENT
            );
        } catch (\Exception $e) {
            Log::error('Error deleting category', [
                'error' => $e->getMessage(),
                'category_id' => $category->id
            ]);

            return $this->errorResponse(
                __('categories.messages.delete_failed'),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
} 