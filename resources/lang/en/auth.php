<?php

return [
    'validation' => [
        'name_required' => 'The name field is required.',
        'name_string' => 'The name must be a string.',
        'name_max' => 'The name may not be greater than 255 characters.',
        'email_required' => 'The email field is required.',
        'email_string' => 'The email must be a string.',
        'email_email' => 'The email must be a valid email address.',
        'email_max' => 'The email may not be greater than 255 characters.',
        'email_unique' => 'The email has already been taken.',
        'password_required' => 'The password field is required.',
        'password_string' => 'The password must be a string.',
        'password_min' => 'The password must be at least 8 characters.',
        'password_confirmed' => 'The password confirmation does not match.',
    ],
    'messages' => [
        'registration_failed' => 'Registration failed. Please try again.',
    ],
];