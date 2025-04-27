<?php

namespace Tests\Feature\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

/**
 * Test suite for the ProductController API endpoints.
 * 
 * This test class verifies the functionality of the product management API,
 * including CRUD operations, validation, and localization features.
 * It uses Laravel's testing framework and Sanctum for authentication.
 */
class ProductControllerTest extends TestCase
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
     * Test the product listing endpoint.
     * Verifies that:
     * - The endpoint returns a 200 status code
     * - The response contains the correct JSON structure
     * - Pagination information is included
     */
    public function test_can_list_products()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'price',
                        'stock',
                        'category_id',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }

    /**
     * Test the product listing endpoint with Arabic localization.
     * Verifies that:
     * - The endpoint returns a 200 status code
     * - The response message is correctly translated to Arabic
     */
    public function test_can_list_products_in_arabic()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept-Language' => 'ar'
        ])->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'price',
                        'stock',
                        'category_id',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }

    /**
     * Test the product creation endpoint.
     * Verifies that:
     * - A new product can be created successfully
     * - The response contains the success message
     * - The product data is correctly stored in the database
     */
    public function test_can_create_product()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $category = Category::factory()->create();
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100,
            'stock' => 10,
            'category_id' => $category->id
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/products', $productData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'stock',
                    'category_id',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    /**
     * Test the product retrieval endpoint.
     * Verifies that:
     * - A specific product can be retrieved by ID
     * - The response contains the correct product data
     */
    public function test_can_show_product()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $product = Product::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'stock',
                    'category_id',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    /**
     * Test the product update endpoint.
     * Verifies that:
     * - An existing product can be updated
     * - The response contains the success message
     * - The updated data is correctly stored in the database
     */
    public function test_can_update_product()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $product = Product::factory()->create();
        $updateData = [
            'name' => 'Updated Product',
            'description' => 'Updated Description',
            'price' => 200,
            'stock' => 20
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/products/{$product->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'stock',
                    'category_id',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    /**
     * Test the product deletion endpoint.
     * Verifies that:
     * - A product can be soft deleted
     * - The response returns a 204 status code
     * - The product is marked as deleted in the database
     */
    public function test_can_delete_product()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $product = Product::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id
        ]);
    }

    /**
     * Test validation of required fields during product creation.
     * Verifies that:
     * - The API returns validation errors for missing required fields
     * - The response contains validation errors for name, price, stock, and category_id
     */
    public function test_validates_required_fields_on_create()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'stock', 'category_id']);
    }

    /**
     * Test validation of numeric fields during product creation.
     * Verifies that:
     * - The API returns validation errors for non-numeric values
     * - The response contains validation errors for price and stock fields
     */
    public function test_validates_numeric_fields()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/products', [
                'name' => 'Test Product',
                'description' => 'Test Description',
                'price' => 'not-a-number',
                'stock' => 'not-a-number',
                'category_id' => 1
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price', 'stock']);
    }

    /**
     * Test validation of category existence during product creation.
     * Verifies that:
     * - The API returns validation errors for non-existent category IDs
     * - The response contains validation errors for invalid category_id
     */
    public function test_validates_category_exists()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/products', [
                'name' => 'Test Product',
                'description' => 'Test Description',
                'price' => 100,
                'stock' => 10,
                'category_id' => 999
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }
} 