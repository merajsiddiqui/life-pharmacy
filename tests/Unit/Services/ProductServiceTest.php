<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\ProductService;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

/**
 * Class ProductServiceTest
 * 
 * This test suite covers the ProductService functionality including:
 * - Product listing with filters
 * - Single product retrieval
 * - Product creation
 * - Product updates
 * - Product deletion
 */
class ProductServiceTest extends TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductService
     */
    protected $productService;

    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->productRepository = Mockery::mock(ProductRepositoryInterface::class);
        $this->productService = new ProductService($this->productRepository);
    }

    /**
     * Clean up the test environment
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test retrieving all products with filters
     * 
     * This test verifies that:
     * - The service correctly passes filters to the repository
     * - The returned data is properly paginated
     * - The response matches the expected format
     */
    public function test_get_all_products()
    {
        $filters = ['category_id' => 1];
        $expectedProducts = new LengthAwarePaginator([], 0, 10);

        $this->productRepository
            ->shouldReceive('getAll')
            ->with($filters)
            ->once()
            ->andReturn($expectedProducts);

        $result = $this->productService->getAllProducts($filters);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals($expectedProducts, $result);
    }

    /**
     * Test retrieving a single product by ID
     * 
     * This test verifies that:
     * - The service correctly passes the ID to the repository
     * - The returned product matches the expected format
     * - The response is properly typed
     */
    public function test_get_product_by_id()
    {
        $productId = 1;
        $expectedProduct = new Product();

        $this->productRepository
            ->shouldReceive('findById')
            ->with($productId)
            ->once()
            ->andReturn($expectedProduct);

        $result = $this->productService->getProductById($productId);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertEquals($expectedProduct, $result);
    }

    /**
     * Test creating a new product
     * 
     * This test verifies that:
     * - The service correctly passes product data to the repository
     * - The created product matches the input data
     * - The response is properly typed
     */
    public function test_create_product()
    {
        $productData = [
            'name' => 'Test Product',
            'price' => 100,
            'stock' => 10
        ];
        $expectedProduct = new Product($productData);

        $this->productRepository
            ->shouldReceive('create')
            ->with($productData)
            ->once()
            ->andReturn($expectedProduct);

        $result = $this->productService->createProduct($productData);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertEquals($expectedProduct, $result);
    }

    /**
     * Test updating an existing product
     * 
     * This test verifies that:
     * - The service correctly passes update data to the repository
     * - The updated product reflects the changes
     * - The response is properly typed
     */
    public function test_update_product()
    {
        $product = new Product();
        $updateData = [
            'name' => 'Updated Product',
            'price' => 200
        ];
        $expectedProduct = new Product($updateData);

        $this->productRepository
            ->shouldReceive('update')
            ->with($product, $updateData)
            ->once()
            ->andReturn($expectedProduct);

        $result = $this->productService->updateProduct($product, $updateData);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertEquals($expectedProduct, $result);
    }

    /**
     * Test deleting a product
     * 
     * This test verifies that:
     * - The service correctly passes the product to the repository
     * - The deletion operation returns the expected result
     * - The response is properly typed
     */
    public function test_delete_product()
    {
        $product = new Product();
        $expectedResult = true;

        $this->productRepository
            ->shouldReceive('delete')
            ->with($product)
            ->once()
            ->andReturn($expectedResult);

        $result = $this->productService->deleteProduct($product);

        $this->assertTrue($result);
    }
} 