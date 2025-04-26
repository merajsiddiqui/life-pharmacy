<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('auth.validation.email_required'),
            'email.string' => __('auth.validation.email_string'),
            'email.email' => __('auth.validation.email_email'),
            'password.required' => __('auth.validation.password_required'),
            'password.string' => __('auth.validation.password_string'),
        ];
    }
} 