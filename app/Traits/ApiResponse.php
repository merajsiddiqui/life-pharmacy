<?php

namespace App\Traits;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

/**
 * Trait for handling API responses in a consistent format
 */
trait ApiResponse
{
    /**
     * Return a success response
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($data, $message = null, $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Return a resource response
     *
     * @param JsonResource $resource
     * @param string|null $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function resourceResponse(JsonResource $resource, ?string $message = null, int $code = Response::HTTP_OK)
    {
        return $resource
            ->additional([
                'status' => 'success',
                'message' => $message
            ])
            ->response()
            ->setStatusCode($code);
    }

    /**
     * Return a collection response
     *
     * @param ResourceCollection $collection
     * @param string|null $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function collectionResponse(ResourceCollection $collection, ?string $message = null, int $code = Response::HTTP_OK)
    {
        return $collection
            ->additional([
                'status' => 'success',
                'message' => $message
            ])
            ->response()
            ->setStatusCode($code);
    }

    /**
     * Return an error response
     *
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse($message, $code = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $code);
    }

    protected function createdResponse($data, $message = null): JsonResponse
    {
        return $this->successResponse($data, $message, Response::HTTP_CREATED);
    }

    protected function notFoundResponse($message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_NOT_FOUND);
    }

    protected function unauthorizedResponse($message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_FORBIDDEN);
    }

    protected function validationErrorResponse($errors): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $errors
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
} 