<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
            'items' => ['required', 'array'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000']
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => __('orders.validation.items_required'),
            'items.array' => __('orders.validation.items_array'),
            'items.*.product_id.required' => __('orders.validation.product_id_required'),
            'items.*.product_id.exists' => __('orders.validation.product_not_found'),
            'items.*.quantity.required' => __('orders.validation.quantity_required'),
            'items.*.quantity.integer' => __('orders.validation.quantity_integer'),
            'items.*.quantity.min' => __('orders.validation.quantity_min'),
            'shipping_address.required' => __('orders.validation.shipping_address_required'),
            'shipping_address.string' => __('orders.validation.shipping_address_string'),
            'shipping_address.max' => __('orders.validation.shipping_address_max'),
            'phone_number.required' => __('orders.validation.phone_required'),
            'phone_number.string' => __('orders.validation.phone_string'),
            'phone_number.max' => __('orders.validation.phone_max'),
            'notes.string' => __('orders.validation.notes_string'),
            'notes.max' => __('orders.validation.notes_max')
        ];
    }
}
