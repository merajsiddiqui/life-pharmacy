<?php

namespace Tests\Feature\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Config;

/**
 * Test suite for the CartController API endpoints.
 * 
 * This test class verifies the functionality of the shopping cart API,
 * including adding/removing items, updating quantities, and cart management.
 * It uses Laravel's testing framework and Sanctum for authentication.
 */
class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->setTestDatabaseConfig();
    }

    /**
     * Configure the test database connection.
     */
    protected function setTestDatabaseConfig(): void
    {
        Config::set('database.default', 'mysql_test');
        Config::set('database.connections.mysql_test', [
            'driver' => 'mysql',
            'host' => env('DB_TEST_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_PORT', '3306'),
            'database' => env('DB_TEST_DATABASE', 'life_pharmacy_test'),
            'username' => env('DB_TEST_USERNAME', 'root'),
            'password' => env('DB_TEST_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);
    }

    /**
     * Clean up after each test.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Test the cart retrieval endpoint.
     * Verifies that:
     * - The endpoint returns a 200 status code
     * - The response contains the correct JSON structure
     * - Cart items are properly listed with their details
     */
    public function test_can_get_cart()
    {
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/cart');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => [
                            'product_id',
                            'quantity',
                            'price',
                            'product' => [
                                'id',
                                'name',
                                'price'
                            ]
                        ]
                    ],
                    'total_amount'
                ]
            ]);
    }

    /**
     * Test adding an item to the cart.
     * Verifies that:
     * - An item can be added to the cart successfully
     * - The response contains the success message
     * - The cart data is correctly updated in the database
     */
    public function test_can_add_item_to_cart()
    {
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $product = Product::first();
        $cartData = [
            'product_id' => $product->id,
            'quantity' => 2
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/cart/items', $cartData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Item added to cart successfully'
            ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);
    }

    /**
     * Test updating cart item quantity.
     * Verifies that:
     * - The quantity of a cart item can be updated
     * - The response contains the success message
     * - The cart data is correctly updated in the database
     */
    public function test_can_update_cart_item_quantity()
    {
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $cartItem = Cart::where('user_id', $user->id)->first();
        $updateData = [
            'quantity' => 3
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/cart/items/{$cartItem->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Cart item updated successfully'
            ]);

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => $updateData['quantity']
        ]);
    }

    /**
     * Test removing an item from the cart.
     * Verifies that:
     * - An item can be removed from the cart
     * - The response returns a 204 status code
     * - The item is removed from the database
     */
    public function test_can_remove_cart_item()
    {
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $cartItem = Cart::where('user_id', $user->id)->first();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/cart/items/{$cartItem->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id
        ]);
    }

    /**
     * Test clearing the entire cart.
     * Verifies that:
     * - All items can be removed from the cart
     * - The response returns a 204 status code
     * - All cart items are removed from the database
     */
    public function test_can_clear_cart()
    {
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/cart');

        $response->assertStatus(204);

        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id
        ]);
    }

    /**
     * Test validation of required fields when adding items.
     * Verifies that:
     * - The API returns validation errors for missing required fields
     * - The response contains validation errors for product_id and quantity
     */
    public function test_validates_required_fields_on_add_item()
    {
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/cart/items', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id', 'quantity']);
    }

    /**
     * Test validation of product availability.
     * Verifies that:
     * - The API returns validation errors for insufficient stock
     * - The response contains appropriate error messages
     */
    public function test_validates_product_availability()
    {
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $product = Product::first();
        $cartData = [
            'product_id' => $product->id,
            'quantity' => $product->stock + 1
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/cart/items', $cartData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    /**
     * Test adding multiple items to cart at once.
     */
    public function test_can_add_multiple_items_to_cart(): void
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(3)->create([
            'stock_quantity' => 10
        ]);

        $response = $this->actingAs($user)->postJson('/api/cart/bulk-add', [
            'items' => $products->map(fn($product) => [
                'product_id' => $product->id,
                'quantity' => 2
            ])->toArray()
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'cart' => [
                    'items' => [
                        '*' => [
                            'id',
                            'product_id',
                            'quantity'
                        ]
                    ]
                ]
            ]);

        $this->assertDatabaseCount('cart_items', 3);
    }

    /**
     * Test attempting to add item with quantity exceeding stock.
     */
    public function test_cannot_add_item_exceeding_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'stock_quantity' => 5
        ]);

        $response = $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 10
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);

        $this->assertDatabaseCount('cart_items', 0);
    }

    /**
     * Test cart total calculation with multiple items.
     */
    public function test_cart_total_calculation(): void
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(3)->create([
            'price' => 100.00,
            'stock_quantity' => 10
        ]);

        foreach ($products as $product) {
            $this->actingAs($user)->postJson('/api/cart/add', [
                'product_id' => $product->id,
                'quantity' => 2
            ]);
        }

        $response = $this->actingAs($user)->getJson('/api/cart');

        $response->assertStatus(200)
            ->assertJson([
                'cart' => [
                    'total' => 600.00,
                    'items_count' => 6
                ]
            ]);
    }

    /**
     * Test cart session persistence across requests.
     */
    public function test_cart_session_persistence(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'stock_quantity' => 10
        ]);

        $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $this->app->instance('request', request());

        $response = $this->actingAs($user)->getJson('/api/cart');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'cart' => [
                    'items' => [
                        '*' => [
                            'id',
                            'product_id',
                            'quantity'
                        ]
                    ]
                ]
            ]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2
        ]);
    }

    /**
     * Test concurrent cart updates.
     */
    public function test_concurrent_cart_updates(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'stock_quantity' => 10
        ]);

        $cart = Cart::factory()->create([
            'user_id' => $user->id
        ]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $responses = collect(range(1, 3))->map(function () use ($user, $product) {
            return $this->actingAs($user)->putJson("/api/cart/items/{$product->id}", [
                'quantity' => 3
            ]);
        });

        $responses->each->assertStatus(200);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 3
        ]);
    }
} 