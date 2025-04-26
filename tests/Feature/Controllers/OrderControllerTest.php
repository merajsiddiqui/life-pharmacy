<?php

namespace Tests\Feature\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

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
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'total_amount',
                        'status',
                        'items' => [
                            '*' => [
                                'product_id',
                                'quantity',
                                'price'
                            ]
                        ],
                        'created_at',
                        'updated_at'
                    ]
                ],
                'pagination' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                    'from',
                    'to'
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
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $product = Product::first();
        $orderData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2
                ]
            ]
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Order created successfully'
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending'
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
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $order = Order::first();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $order->id,
                    'user_id' => $order->user_id
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
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $order = Order::first();
        $updateData = [
            'status' => 'processing'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/orders/{$order->id}/status", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Order status updated successfully'
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => $updateData['status']
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
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    /**
     * Test validation of product availability during order creation.
     * Verifies that:
     * - The API returns validation errors for insufficient stock
     * - The response contains appropriate error messages
     */
    public function test_validates_product_availability()
    {
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $product = Product::first();
        $orderData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => $product->stock + 1
                ]
            ]
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);
    }
} 