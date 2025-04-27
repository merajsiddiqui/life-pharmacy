<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CartResource",
 *     title="Cart Resource",
 *     description="Shopping cart resource representation",
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(
 *             property="items",
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/CartItemResource")
 *         ),
 *         @OA\Property(
 *             property="total_amount",
 *             type="number",
 *             format="float",
 *             example=299.97
 *         )
 *     )
 * )
 */
class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => [
                'items' => CartItemResource::collection($this->items),
                'total_amount' => $this->items->sum(function ($item) {
                    return $item->quantity * $item->unit_price;
                })
            ]
        ];
    }
} 