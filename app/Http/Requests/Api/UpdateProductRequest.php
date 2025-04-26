<?php

namespace App\Http\Requests\Api;

use App\Models\Product;
use App\Policies\ProductPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'image_url' => ['sometimes', 'string', 'url'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (empty($this->all())) {
                $validator->errors()->add('fields', 'At least one field must be present for update.');
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
            'name.string' => __('products.validation.name_string'),
            'name.max' => __('products.validation.name_max'),
            'description.string' => __('products.validation.description_string'),
            'price.numeric' => __('products.validation.price_numeric'),
            'price.min' => __('products.validation.price_min'),
            'stock.integer' => __('products.validation.stock_integer'),
            'stock.min' => __('products.validation.stock_min'),
            'category_id.exists' => __('products.validation.category_not_found'),
            'image_url.string' => __('products.validation.image_string'),
            'image_url.url' => __('products.validation.image_url'),
            'fields' => 'At least one field must be present for update.',
        ];
    }
} 