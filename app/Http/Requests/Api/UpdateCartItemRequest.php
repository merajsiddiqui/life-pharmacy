<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\CartItem;
use App\Policies\CartItemPolicy;
use Illuminate\Support\Facades\Log;

class UpdateCartItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isCustomer();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $cartItem = $this->route('cartItem');
                    if ($cartItem->product->stock < $value) {
                        $fail(__('cart.messages.insufficient_stock'));
                    }
                }
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quantity.required' => __('cart.validation.quantity_required'),
            'quantity.integer' => __('cart.validation.quantity_integer'),
            'quantity.min' => __('cart.validation.quantity_min')
        ];
    }
} 