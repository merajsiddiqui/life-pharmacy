<?php

namespace App\Traits;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Lang;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Trait for handling API responses in a consistent format
 * 
 * This trait provides standardized methods for generating API responses
 * with consistent structure and status codes.
 */
trait ApiResponse
{
    /**
     * Return a success response
     *
     * @param mixed $data The data to be included in the response
     * @param string|null $message Optional success message to be translated
     * @param int $code HTTP status code (defaults to 200)
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($data, ?string $message = null, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message ? Lang::get($message) : null,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return a resource response
     *
     * @param \Illuminate\Http\Resources\Json\JsonResource $resource The resource to be transformed
     * @param string|null $message Optional success message to be translated
     * @param int $code HTTP status code (defaults to 200)
     * @return \Illuminate\Http\JsonResponse
     */
    protected function resourceResponse(JsonResource $resource, ?string $message = null, int $code = Response::HTTP_OK): JsonResponse
    {
        return $resource
            ->additional([
                'status' => 'success',
                'message' => $message ? Lang::get($message) : null
            ])
            ->response()
            ->setStatusCode($code)
            ->header('Content-Type', 'application/json');
    }

    /**
     * Return a collection response
     *
     * @param \Illuminate\Http\Resources\Json\ResourceCollection $collection The collection to be transformed
     * @param string|null $message Optional success message to be translated
     * @param int $code HTTP status code (defaults to 200)
     * @return \Illuminate\Http\JsonResponse
     */
    protected function collectionResponse(ResourceCollection $collection, ?string $message = null, int $code = Response::HTTP_OK): JsonResponse
    {
        return $collection
            ->additional([
                'status' => 'success',
                'message' => $message ? Lang::get($message) : null
            ])
            ->response()
            ->setStatusCode($code)
            ->header('Content-Type', 'application/json');
    }

    /**
     * Return an error response
     *
     * @param string $message Error message to be translated
     * @param int $code HTTP status code (defaults to 400)
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(string $message, int $statusCode): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], $statusCode);
    }

    /**
     * Return a created response (201)
     *
     * @param mixed $data The data to be included in the response
     * @param string|null $message Optional success message to be translated
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createdResponse($data, ?string $message = null): JsonResponse
    {
        return $this->successResponse($data, $message, Response::HTTP_CREATED);
    }

    /**
     * Return a not found response (404)
     *
     * @param string $message Error message to be translated (defaults to 'Resource not found')
     * @return \Illuminate\Http\JsonResponse
     */
    protected function notFoundResponse($message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Return an unauthorized response (403)
     *
     * @param string $message Error message to be translated (defaults to 'Unauthorized')
     * @return \Illuminate\Http\JsonResponse
     */
    protected function unauthorizedResponse($message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Return a validation error response (422)
     *
     * @param array $errors Validation errors to be included in the response
     * @return \Illuminate\Http\JsonResponse
     */
    protected function validationErrorResponse($errors): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => Lang::get('auth.messages.validation_failed'),
            'data' => null,
            'errors' => $errors
        ], Response::HTTP_UNPROCESSABLE_ENTITY)->header('Content-Type', 'application/json');
    }

    /**
     * Respond with a single resource
     *
     * @param JsonResource $resource
     * @param string|null $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function respondWithResource(JsonResource $resource, ?string $message = null, int $statusCode = 200): JsonResponse
    {
        return $this->successResponse($resource, $message, $statusCode);
    }

    /**
     * Respond with a collection of resources
     *
     * @param ResourceCollection $collection
     * @param string|null $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function respondWithResourceCollection(ResourceCollection $collection, ?string $message = null, int $statusCode = 200): JsonResponse
    {
        $response = [
            'status' => 'success',
            'message' => $message ? Lang::get($message) : null,
            'data' => $collection,
        ];

        // Add pagination meta if the collection is paginated
        if ($collection->resource instanceof LengthAwarePaginator) {
            $response['meta'] = [
                'current_page' => $collection->currentPage(),
                'from' => $collection->firstItem(),
                'last_page' => $collection->lastPage(),
                'per_page' => $collection->perPage(),
                'to' => $collection->lastItem(),
                'total' => $collection->total(),
                'path' => $collection->path(),
                'has_more_pages' => $collection->hasMorePages(),
            ];

            $response['links'] = [
                'first' => $collection->url(1),
                'last' => $collection->url($collection->lastPage()),
                'prev' => $collection->previousPageUrl(),
                'next' => $collection->nextPageUrl(),
            ];
        }

        return response()->json($response, $statusCode);
    }
    /**
     * Return a success response for deleted resources
     *
     * @param string|null $message Optional success message to be translated
     * @return \Illuminate\Http\JsonResponse
     */
    protected function deletedResponse(?string $message = null): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message ? Lang::get($message) : null,
            'data' => null,
        ], Response::HTTP_NO_CONTENT);
    }
}