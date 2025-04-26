<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

/**
 * Class AuthService
 * 
 * @package App\Services
 */
class AuthService
{
    /**
     * AuthService constructor.
     *
     * @param \App\Repositories\Interfaces\UserRepositoryInterface $userRepository
     */
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * Register a new user
     *
     * @param array $data
     * @return array{user: \App\Models\User, token: string}
     * @throws \Exception
     */
    public function register(array $data): array
    {
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Assign customer role by default
        $customerRole = Role::where('slug', 'customer')->first();
        $user->roles()->attach($customerRole);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    /**
     * Authenticate a user
     *
     * @param string $email
     * @param string $password
     * @return array{user: \App\Models\User, token: string}|null
     */
    public function login(string $email, string $password): ?array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    /**
     * Logout the current user
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function logout(User $user): bool
    {
        return $user->currentAccessToken()->delete();
    }
} 