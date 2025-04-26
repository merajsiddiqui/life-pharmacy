<?php

return [
    'attributes' => [
        'name' => 'name',
        'description' => 'description',
        'price' => 'price',
        'stock' => 'stock',
        'category_id' => 'category',
    ],
    'custom' => [
        'name' => [
            'required' => 'The product name is required.',
            'string' => 'The product name must be a string.',
            'max' => 'The product name must not exceed :max characters.',
        ],
        'description' => [
            'string' => 'The product description must be a string.',
        ],
        'price' => [
            'required' => 'The product price is required.',
            'numeric' => 'The product price must be a number.',
            'min' => 'The product price must be at least :min.',
        ],
        'stock' => [
            'required' => 'The product stock is required.',
            'integer' => 'The product stock must be an integer.',
            'min' => 'The product stock must be at least :min.',
        ],
        'category_id' => [
            'required' => 'The product category is required.',
            'exists' => 'The selected category does not exist.',
        ],
    ],
    'required' => 'The :attribute field is required',
    'string' => 'The :attribute field must be a string',
    'max' => [
        'string' => 'The :attribute field must not exceed :max characters',
        'file' => 'The :attribute field must not exceed :max kilobytes',
        'array' => 'The :attribute field must not exceed :max items',
    ],
    'numeric' => 'The :attribute field must be a number',
    'min' => [
        'numeric' => 'The :attribute field must be at least :min',
        'string' => 'The :attribute field must be at least :min characters',
        'array' => 'The :attribute field must be at least :min items',
    ],
    'exists' => 'The selected :attribute is invalid',
    'image' => 'The :attribute field must be an image',
    'mimes' => 'The :attribute field must be a file of type: :values',
    'array' => 'The :attribute field must be an array',
    'regex' => 'The :attribute format is invalid',
]; 