<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'image_url' => ['nullable', 'string', 'url'],
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
            'name.required' => __('products.validation.name_required'),
            'name.string' => __('products.validation.name_string'),
            'name.max' => __('products.validation.name_max'),
            'description.required' => __('products.validation.description_required'),
            'description.string' => __('products.validation.description_string'),
            'price.required' => __('products.validation.price_required'),
            'price.numeric' => __('products.validation.price_numeric'),
            'price.min' => __('products.validation.price_min'),
            'stock.required' => __('products.validation.stock_required'),
            'stock.integer' => __('products.validation.stock_integer'),
            'stock.min' => __('products.validation.stock_min'),
            'category_id.required' => __('products.validation.category_required'),
            'category_id.exists' => __('products.validation.category_not_found'),
            'image_url.string' => __('products.validation.image_string'),
            'image_url.url' => __('products.validation.image_url'),
        ];
    }
}
