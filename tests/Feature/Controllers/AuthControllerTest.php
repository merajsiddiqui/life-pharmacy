<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::factory()->create([
            'name' => 'Administrator',
            'slug' => 'admin',
            'description' => 'System Administrator'
        ]);

        Role::factory()->create([
            'name' => 'Customer',
            'slug' => 'customer',
            'description' => 'Regular Customer'
        ]);
    }

    public function test_user_can_register_as_customer()
    {
        $userData = [
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'user_type' => 'customer'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at'
                    ],
                    'token'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test Customer',
            'email' => 'customer@example.com'
        ]);

        $user = User::where('email', 'customer@example.com')->first();
        $this->assertTrue($user->hasRole('customer'));
    }

    public function test_user_can_register_as_admin()
    {
        $userData = [
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'user_type' => 'admin'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at'
                    ],
                    'token'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test Admin',
            'email' => 'admin@example.com'
        ]);

        $user = User::where('email', 'admin@example.com')->first();
        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at'
                    ],
                    'token'
                ]
            ]);
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data'
            ]);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(401)
            ->assertJsonStructure([
                'status',
                'message'
            ]);
    }

    public function test_user_cannot_register_with_existing_email()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $userData = [
            'name' => 'Another User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'user_type' => 'customer'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Validation failed',
                'errors' => [
                    'email' => ['The email has already been taken.']
                ],
                'status' => 'error',
                'data' => null
            ]);
    }

    public function test_user_cannot_register_with_invalid_role()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'user_type' => 'invalid_role'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_type']);
    }

    public function test_unauthenticated_user_cannot_logout()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'You are not authenticated. Please login to access this resource.',
                'status' => 'error',
                'data' => null
            ]);
    }
} 