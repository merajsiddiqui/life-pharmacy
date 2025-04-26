<?php

namespace App\Http\Requests\Api;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * RegisterRequest Class
 * 
 * Handles the validation and authorization for user registration requests.
 */
class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'user_type' => ['nullable', new Enum(UserType::class)],
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
            'name.required' => __('auth.validation.name_required'),
            'name.string' => __('auth.validation.name_string'),
            'name.max' => __('auth.validation.name_max'),
            'email.required' => __('auth.validation.email_required'),
            'email.string' => __('auth.validation.email_string'),
            'email.email' => __('auth.validation.email_email'),
            'email.max' => __('auth.validation.email_max'),
            'email.unique' => __('auth.validation.email_unique'),
            'password.required' => __('auth.validation.password_required'),
            'password.string' => __('auth.validation.password_string'),
            'password.min' => __('auth.validation.password_min'),
            'password.confirmed' => __('auth.validation.password_confirmed'),
            'user_type.enum' => __('auth.validation.user_type_invalid'),
        ];
    }

    /**
     * Prepare the data for validation.
     * Sets default user type to CUSTOMER if not provided.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->user_type === null) {
            $this->merge(['user_type' => UserType::CUSTOMER->value]);
        }
    }
}