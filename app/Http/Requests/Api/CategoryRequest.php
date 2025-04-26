<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CategoryRequest
 * 
 * @package App\Http\Requests\Api
 */
class CategoryRequest extends FormRequest
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
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];

        // Add unique rule for name when updating
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['name'][] = 'unique:categories,name,' . $this->category->id;
        } else {
            $rules['name'][] = 'unique:categories,name';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('categories.validation.name_required'),
            'name.string' => __('categories.validation.name_string'),
            'name.max' => __('categories.validation.name_max'),
            'name.unique' => __('categories.validation.name_unique'),
            'description.string' => __('categories.validation.description_string'),
            'description.max' => __('categories.validation.description_max'),
        ];
    }
}