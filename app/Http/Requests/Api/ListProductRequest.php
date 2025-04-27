<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ListProductRequest extends FormRequest
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
            'category_id' => 'nullable|integer',
            'search' => 'nullable|string|max:255',
            'sort' => 'nullable|string|in:price,name,created_at',
            'order' => 'nullable|string|in:asc,desc',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('category_id')) {
            $this->merge([
                'category_id' => (int) $this->category_id
            ]);
        }

        // Set default pagination values if not provided
        if (!$this->has('page')) {
            $this->merge(['page' => 1]);
        }

        if (!$this->has('per_page')) {
            $this->merge(['per_page' => 15]);
        }
    }
} 