<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;
use App\Policies\CartItemPolicy;

class AddToCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && app(CartItemPolicy::class)->create($this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $product = Product::find($this->product_id);
                    if ($product && $value > $product->stock) {
                        $fail(__('validation.quantity.insufficient', ['available' => $product->stock]));
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
            'product_id.required' => __('validation.product_id.required'),
            'product_id.exists' => __('validation.product_id.exists'),
            'quantity.required' => __('validation.quantity.required'),
            'quantity.integer' => __('validation.quantity.integer'),
            'quantity.min' => __('validation.quantity.min')
        ];
    }
}