<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class UserRepository
 * 
 * @package App\Repositories
 */
class UserRepository implements UserRepositoryInterface
{
    /**
     * UserRepository constructor.
     *
     * @param \App\Models\User $model
     */
    public function __construct(protected User $model)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * {@inheritDoc}
     */
    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function all(): Collection
    {
        return $this->model->with('roles')->get();
    }

    /**
     * {@inheritDoc}
     */
    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }
} 