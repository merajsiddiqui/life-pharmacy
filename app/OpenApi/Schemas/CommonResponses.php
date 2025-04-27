<?php

namespace App\OpenApi\Schemas;

/**
 * Common response schemas for OpenAPI documentation
 */
class CommonResponses
{
    /**
     * @OA\Schema(
     *     schema="UnauthorizedResponse",
     *     title="Unauthorized Response",
     *     description="Response when user is not authenticated",
     *     @OA\Property(property="status", type="string", example="error"),
     *     @OA\Property(property="message", type="string", example="Unauthorized"),
     *     @OA\Property(property="data", type="null")
     * )
     */
    public function unauthorizedResponse()
    {
    }

    /**
     * @OA\Schema(
     *     schema="ForbiddenResponse",
     *     title="Forbidden Response",
     *     description="Response when user doesn't have permission",
     *     @OA\Property(property="status", type="string", example="error"),
     *     @OA\Property(property="message", type="string", example="You do not have permission to perform this action"),
     *     @OA\Property(property="data", type="null")
     * )
     */
    public function forbiddenResponse()
    {
    }

    /**
     * @OA\Schema(
     *     schema="NotFoundResponse",
     *     title="Not Found Response",
     *     description="Response when resource is not found",
     *     @OA\Property(property="status", type="string", example="error"),
     *     @OA\Property(property="message", type="string", example="Resource not found"),
     *     @OA\Property(property="data", type="null")
     * )
     */
    public function notFoundResponse()
    {
    }

    /**
     * @OA\Schema(
     *     schema="ValidationErrorResponse",
     *     title="Validation Error Response",
     *     description="Response when validation fails",
     *     @OA\Property(property="status", type="string", example="error"),
     *     @OA\Property(property="message", type="string", example="The given data was invalid"),
     *     @OA\Property(
     *         property="errors",
     *         type="object",
     *         @OA\Property(
     *             property="field_name",
     *             type="array",
     *             @OA\Items(type="string", example="The field name field is required")
     *         )
     *     )
     * )
     */
    public function validationErrorResponse()
    {
    }
} 