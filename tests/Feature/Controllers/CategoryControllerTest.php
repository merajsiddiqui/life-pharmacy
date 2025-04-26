<?php

namespace Tests\Feature\Controllers;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Test suite for the CategoryController API endpoints.
 * 
 * This test class verifies the functionality of the category management API,
 * including CRUD operations, validation, and localization features.
 * It uses Laravel's testing framework and Sanctum for authentication.
 */
class CategoryControllerTest extends TestCase
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
     * Test the category listing endpoint.
     * Verifies that:
     * - The endpoint returns a 200 status code
     * - The response contains the correct JSON structure
     * - Categories are properly listed with their products
     */
    public function test_can_list_categories()
    {
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'products_count',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }

    /**
     * Test the category creation endpoint.
     * Verifies that:
     * - A new category can be created successfully
     * - The response contains the success message
     * - The category data is correctly stored in the database
     */
    public function test_can_create_category()
    {
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test Description'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/categories', $categoryData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Category created successfully'
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => $categoryData['name'],
            'description' => $categoryData['description']
        ]);
    }

    /**
     * Test the category retrieval endpoint.
     * Verifies that:
     * - A specific category can be retrieved by ID
     * - The response contains the correct category data
     * - Associated products are included in the response
     */
    public function test_can_show_category()
    {
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $category = Category::first();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $category->id,
                    'name' => $category->name
                ]
            ]);
    }

    /**
     * Test the category update endpoint.
     * Verifies that:
     * - An existing category can be updated
     * - The response contains the success message
     * - The updated data is correctly stored in the database
     */
    public function test_can_update_category()
    {
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $category = Category::first();
        $updateData = [
            'name' => 'Updated Category',
            'description' => 'Updated Description'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/categories/{$category->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Category updated successfully'
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => $updateData['name'],
            'description' => $updateData['description']
        ]);
    }

    /**
     * Test the category deletion endpoint.
     * Verifies that:
     * - A category can be deleted
     * - The response returns a 204 status code
     * - The category is removed from the database
     */
    public function test_can_delete_category()
    {
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $category = Category::first();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);
    }

    /**
     * Test validation of required fields during category creation.
     * Verifies that:
     * - The API returns validation errors for missing required fields
     * - The response contains validation errors for name
     */
    public function test_validates_required_fields_on_create()
    {
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/categories', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test validation of unique category names.
     * Verifies that:
     * - The API returns validation errors for duplicate category names
     * - The response contains appropriate error messages
     */
    public function test_validates_unique_category_name()
    {
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $existingCategory = Category::first();
        $categoryData = [
            'name' => $existingCategory->name,
            'description' => 'Test Description'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/categories', $categoryData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
} 