<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;

class CreateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'shipping_address' => ['required', 'string'],
            'phone_number' => ['required', 'string'],
            'payment_method' => ['required', 'string', 'in:credit_card,cash_on_delivery'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            
            foreach ($items as $index => $item) {
                if (!isset($item['product_id']) || !isset($item['quantity'])) {
                    continue;
                }
                
                $product = Product::find($item['product_id']);
                if ($product && $product->stock < $item['quantity']) {
                    $validator->errors()->add(
                        "items.{$index}.quantity",
                        __('orders.validation.insufficient_stock')
                    );
                }
                
                if ($product && $product->stock === 0) {
                    $validator->errors()->add(
                        "items.{$index}.product_id",
                        __('orders.validation.product_out_of_stock')
                    );
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => __('orders.validation.items_required'),
            'items.array' => __('orders.validation.items_array'),
            'items.min' => __('orders.validation.items_min'),
            'items.*.product_id.required' => __('orders.validation.product_id_required'),
            'items.*.product_id.exists' => __('orders.validation.product_not_found'),
            'items.*.quantity.required' => __('orders.validation.quantity_required'),
            'items.*.quantity.integer' => __('orders.validation.quantity_integer'),
            'items.*.quantity.min' => __('orders.validation.quantity_min'),
            'shipping_address.required' => __('orders.validation.shipping_address_required'),
            'shipping_address.string' => __('orders.validation.shipping_address_string'),
            'phone_number.required' => __('orders.validation.phone_number_required'),
            'phone_number.string' => __('orders.validation.phone_number_string'),
            'payment_method.required' => __('orders.validation.payment_method_required'),
            'payment_method.string' => __('orders.validation.payment_method_string'),
            'payment_method.in' => __('orders.validation.payment_method_invalid'),
        ];
    }
}
