<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for storing a new product
 * 
 * This request handles the validation rules for creating a new product
 * including its name, description, price, stock, category, and images.
 */
class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
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
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_]+$/', // Only alphanumeric, spaces, hyphens, and underscores
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'stock' => [
                'required',
                'integer',
                'min:0',
                'max:999999',
            ],
            'category_id' => [
                'required',
                'exists:categories,id',
            ],
            'images' => [
                'nullable',
                'array',
                'max:5', // Maximum 5 images per product
            ],
            'images.*' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048', // Maximum 2MB per image
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return trans('validation.attributes');
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'The product name can only contain letters, numbers, spaces, hyphens, and underscores.',
            'images.max' => 'You can upload a maximum of 5 images.',
            'images.*.max' => 'Each image must not exceed 2MB.',
        ];
    }
}
