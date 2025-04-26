<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

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
            'payment_method' => ['required', 'string', 'in:credit_card,cash_on_delivery'],
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
            'payment_method.required' => __('orders.validation.payment_method_required'),
            'payment_method.string' => __('orders.validation.payment_method_string'),
            'payment_method.in' => __('orders.validation.payment_method_invalid'),
        ];
    }
}
