<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;

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
     * @return array
     */
    public function getAllCategories(): array
    {
        return $this->categoryRepository->all()->toArray();
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