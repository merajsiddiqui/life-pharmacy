<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

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
        ];
    }
} 