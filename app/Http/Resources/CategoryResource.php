<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CategoryResource",
 *     title="Category Resource",
 *     description="Product category resource representation",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Electronics"),
 *     @OA\Property(property="slug", type="string", example="electronics"),
 *     @OA\Property(property="description", type="string", example="Electronic devices and accessories"),
 *     @OA\Property(
 *         property="products",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/ProductResource")
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource?->id,
            'name' => $this->resource?->name,
            'slug' => $this->resource?->slug,
            'description' => $this->resource?->description,
            // 'products' => ProductResource::collection($this->whenLoaded('products')),
            'created_at' => $this->resource?->created_at,
            'updated_at' => $this->resource?->updated_at,
        ];
    }
} 