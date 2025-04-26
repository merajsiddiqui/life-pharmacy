<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface UserRepositoryInterface
 * 
 * @package App\Repositories\Interfaces
 */
interface UserRepositoryInterface
{
    /**
     * Create a new user
     *
     * @param array $data
     * @return \App\Models\User
     */
    public function create(array $data): User;

    /**
     * Find a user by email
     *
     * @param string $email
     * @return \App\Models\User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find a user by ID
     *
     * @param int $id
     * @return \App\Models\User|null
     */
    public function findById(int $id): ?User;

    /**
     * Get all users
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(): Collection;

    /**
     * Update a user
     *
     * @param \App\Models\User $user
     * @param array $data
     * @return bool
     */
    public function update(User $user, array $data): bool;

    /**
     * Delete a user
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function delete(User $user): bool;
} 