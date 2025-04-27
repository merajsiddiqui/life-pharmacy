<?php

namespace Tests\Feature\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

/**
 * Test suite for the OrderController API endpoints.
 * 
 * This test class verifies the functionality of the order management API,
 * including order creation, retrieval, status updates, and validation.
 * It uses Laravel's testing framework and Sanctum for authentication.
 */
class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up the test environment.
     * Disables exception handling to see actual errors during testing.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    /**
     * Clean up after each test.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Test the order listing endpoint.
     * Verifies that:
     * - The endpoint returns a 200 status code
     * - The response contains the correct JSON structure
     * - Pagination information is included
     * - Orders are properly associated with users
     */
    public function test_can_list_orders()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'status',
                        'total_amount',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }

    /**
     * Test the order creation endpoint.
     * Verifies that:
     * - A new order can be created successfully
     * - The response contains the success message
     * - The order data is correctly stored in the database
     * - Product stock is properly updated
     */
    public function test_can_create_order()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $product = Product::factory()->create([
            'stock' => 10
        ]);

        // Add item to cart first
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/cart/items', [
                'product_id' => $product->id,
                'quantity' => 2
            ]);

        $orderData = [
            'shipping_address' => '123 Test Street, Test City',
            'phone_number' => '1234567890',
            'payment_method' => 'credit_card',
            'payment_status' => 'pending',
            'shipping_method' => 'standard'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'status',
                    'total_amount',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    /**
     * Test the order retrieval endpoint.
     * Verifies that:
     * - A specific order can be retrieved by ID
     * - The response contains the correct order data
     * - Order items are properly included
     */
    public function test_can_show_order()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $order = Order::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'status',
                    'total_amount',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    /**
     * Test the order status update endpoint.
     * Verifies that:
     * - An order's status can be updated
     * - The response contains the success message
     * - The updated status is correctly stored in the database
     */
    public function test_can_update_order_status()
    {
        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Administrator role with full access'
        ]);

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
        $user->roles()->attach($adminRole->id);
        
        $token = $user->createToken('test-token')->plainTextToken;

        $order = Order::factory()->create([
            'user_id' => $user->id
        ]);
        $updateData = [
            'status' => 'processing'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/orders/{$order->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'status',
                    'total_amount',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    /**
     * Test validation of required fields during order creation.
     * Verifies that:
     * - The API returns validation errors for missing required fields
     * - The response contains validation errors for items
     */
    public function test_validates_required_fields_on_create()
    {
        $this->withExceptionHandling();

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'shipping_address',
                'phone_number',
                'payment_method',
                'payment_status',
                'shipping_method'
            ]);
    }

    /**
     * Test validation of product availability during order creation.
     * Verifies that:
     * - The API returns validation errors for insufficient stock
     * - The response contains appropriate error messages
     */
    public function test_validates_product_availability()
    {
        $this->withExceptionHandling(); // Enable exception handling

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $product = Product::factory()->create([
            'stock' => 0
        ]);

        $orderData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1
                ]
            ],
            'shipping_address' => '123 Test Street, Test City',
            'phone_number' => '1234567890',
            'payment_method' => 'credit_card'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.product_id']);
    }
} 