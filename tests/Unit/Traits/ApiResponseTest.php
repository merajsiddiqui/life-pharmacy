<?php

namespace Tests\Unit\Traits;

use App\Traits\ApiResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Tests\TestCase;

/**
 * Class ApiResponseTest
 * 
 * This test suite covers the ApiResponse trait functionality including:
 * - Success response formatting
 * - Resource response formatting
 * - Collection response formatting
 * - Error response formatting
 */
class ApiResponseTest extends TestCase
{
    use ApiResponse;

    /**
     * Test success response formatting
     * 
     * This test verifies that:
     * - The response has the correct status code
     * - The response contains the expected data
     * - The response includes the success message
     * - The response structure is correct
     */
    public function test_success_response()
    {
        $data = ['key' => 'value'];
        $message = 'Success message';
        $code = Response::HTTP_OK;

        $response = $this->successResponse($data, $message, $code);

        $this->assertEquals($code, $response->getStatusCode());
        $this->assertEquals([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], json_decode($response->getContent(), true));
    }

    /**
     * Test resource response formatting
     * 
     * This test verifies that:
     * - The response has the correct status code
     * - The resource data is properly formatted
     * - The response includes the success message
     * - The response structure is correct
     */
    public function test_resource_response()
    {
        $resource = new JsonResource(['key' => 'value']);
        $message = 'Resource message';
        $code = Response::HTTP_OK;

        $response = $this->resourceResponse($resource, $message, $code);

        $this->assertEquals($code, $response->getStatusCode());
        $this->assertEquals([
            'status' => 'success',
            'message' => $message,
            'data' => ['key' => 'value']
        ], json_decode($response->getContent(), true));
    }

    /**
     * Test collection response formatting
     * 
     * This test verifies that:
     * - The response has the correct status code
     * - The collection data is properly formatted
     * - The response includes the success message
     * - The response structure is correct
     */
    public function test_collection_response()
    {
        $collection = new ResourceCollection([
            new JsonResource(['key' => 'value1']),
            new JsonResource(['key' => 'value2'])
        ]);
        $message = 'Collection message';
        $code = Response::HTTP_OK;

        $response = $this->collectionResponse($collection, $message, $code);

        $this->assertEquals($code, $response->getStatusCode());
        $this->assertEquals([
            'status' => 'success',
            'message' => $message,
            'data' => [
                ['key' => 'value1'],
                ['key' => 'value2']
            ]
        ], json_decode($response->getContent(), true));
    }

    /**
     * Test error response formatting
     * 
     * This test verifies that:
     * - The response has the correct status code
     * - The error message is properly formatted
     * - The response structure is correct
     * - The error status is set
     */
    public function test_error_response()
    {
        $message = 'Error message';
        $code = Response::HTTP_BAD_REQUEST;

        $response = $this->errorResponse($message, $code);

        $this->assertEquals($code, $response->getStatusCode());
        $this->assertEquals([
            'status' => 'error',
            'message' => $message
        ], json_decode($response->getContent(), true));
    }
} 