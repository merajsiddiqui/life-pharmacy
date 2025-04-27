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
            'shipping_address' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'string', 'in:credit_card,cash_on_delivery,wallet'],
            'payment_status' => ['required', 'string', 'in:pending,paid,failed'],
            'shipping_method' => ['required', 'string', 'in:standard,express'],
            'discount_code' => ['nullable', 'string', 'max:50'],
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
            'shipping_address.required' => __('orders.validation.shipping_address_required'),
            'shipping_address.string' => __('orders.validation.shipping_address_string'),
            'shipping_address.max' => __('orders.validation.shipping_address_max'),
            'phone_number.required' => __('orders.validation.phone_required'),
            'phone_number.string' => __('orders.validation.phone_string'),
            'phone_number.max' => __('orders.validation.phone_max'),
            'notes.string' => __('orders.validation.notes_string'),
            'notes.max' => __('orders.validation.notes_max'),
            'payment_method.required' => __('orders.validation.payment_method_required'),
            'payment_method.string' => __('orders.validation.payment_method_string'),
            'payment_method.in' => __('orders.validation.payment_method_invalid'),
            'payment_status.required' => __('orders.validation.payment_status_required'),
            'payment_status.string' => __('orders.validation.payment_status_string'),
            'payment_status.in' => __('orders.validation.payment_status_invalid'),
            'shipping_method.required' => __('orders.validation.shipping_method_required'),
            'shipping_method.string' => __('orders.validation.shipping_method_string'),
            'shipping_method.in' => __('orders.validation.shipping_method_invalid'),
            'discount_code.string' => __('orders.validation.discount_code_string'),
            'discount_code.max' => __('orders.validation.discount_code_max'),
        ];
    }
}
