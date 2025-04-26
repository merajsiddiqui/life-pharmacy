<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class CategoryRepository
 * 
 * @package App\Repositories
 */
class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * CategoryRepository constructor.
     *
     * @param \App\Models\Category $model
     */
    public function __construct(protected Category $model)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function all(): Collection
    {
        return $this->model->newQuery()->with('products')->get();
    }

    /**
     * {@inheritDoc}
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with('products')
            ->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function findById(int $id): ?Category
    {
        return $this->model->newQuery()->with('products')->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): Category
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(Category $category, array $data): bool
    {
        return $category->update($data);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(Category $category): bool
    {
        return $category->delete();
    }
} 