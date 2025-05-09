<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class CategoryService
 * 
 * @package App\Services
 */
class CategoryService
{
    /**
     * CategoryService constructor.
     *
     * @param \App\Repositories\Interfaces\CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository
    ) {
    }

    /**
     * Get all categories
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllCategories(): Collection
    {
        return $this->categoryRepository->all();
    }

    /**
     * Get paginated categories
     *
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginatedCategories(int $perPage = 15): LengthAwarePaginator
    {
        return $this->categoryRepository->paginate($perPage);
    }

    /**
     * Get a category by ID
     *
     * @param int $id
     * @return \App\Models\Category|null
     */
    public function getCategoryById(int $id): ?Category
    {
        return $this->categoryRepository->findById($id);
    }

    /**
     * Create a new category
     *
     * @param array $data
     * @return \App\Models\Category
     */
    public function createCategory(array $data): Category
    {
        return $this->categoryRepository->create($data);
    }

    /**
     * Update an existing category
     *
     * @param \App\Models\Category $category
     * @param array $data
     * @return bool
     */
    public function updateCategory(Category $category, array $data): bool
    {
        return $this->categoryRepository->update($category, $data);
    }

    /**
     * Delete a category
     *
     * @param \App\Models\Category $category
     * @return bool
     */
    public function deleteCategory(Category $category): bool
    {
        return $this->categoryRepository->delete($category);
    }
} 