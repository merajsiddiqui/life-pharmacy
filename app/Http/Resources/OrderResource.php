<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="OrderResource",
 *     title="Order Resource",
 *     description="Order resource representation",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="status", type="string", example="pending"),
 *     @OA\Property(property="total_amount", type="number", format="float", example=299.99),
 *     @OA\Property(property="shipping_address", type="string", example="123 Main St, City, Country"),
 *     @OA\Property(property="phone_number", type="string", example="+1234567890"),
 *     @OA\Property(property="notes", type="string", nullable=true, example="Please deliver in the evening"),
 *     @OA\Property(property="payment_method", type="string", example="credit_card"),
 *     @OA\Property(property="payment_status", type="string", example="paid"),
 *     @OA\Property(property="shipping_method", type="string", example="standard"),
 *     @OA\Property(property="discount_code", type="string", nullable=true, example="SUMMER2024"),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/OrderItemResource")
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class OrderResource extends JsonResource
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
            'id' => $this->id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'shipping_address' => $this->shipping_address,
            'phone_number' => $this->phone_number,
            'notes' => $this->notes,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'shipping_method' => $this->shipping_method,
            'discount_code' => $this->discount_code,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
