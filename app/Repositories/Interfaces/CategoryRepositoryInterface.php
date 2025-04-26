<?php

namespace App\Repositories\Interfaces;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface CategoryRepositoryInterface
 * 
 * @package App\Repositories\Interfaces
 */
interface CategoryRepositoryInterface
{
    /**
     * Get all categories with their products
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(): Collection;

    /**
     * Find a category by ID with its products
     *
     * @param int $id
     * @return \App\Models\Category|null
     */
    public function findById(int $id): ?Category;

    /**
     * Create a new category
     *
     * @param array $data
     * @return \App\Models\Category
     */
    public function create(array $data): Category;

    /**
     * Update an existing category
     *
     * @param \App\Models\Category $category
     * @param array $data
     * @return bool
     */
    public function update(Category $category, array $data): bool;

    /**
     * Delete a category
     *
     * @param \App\Models\Category $category
     * @return bool
     */
    public function delete(Category $category): bool;
} 