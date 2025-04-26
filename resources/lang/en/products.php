<?php

return [
    'messages' => [
        'created' => 'Product created successfully',
        'creation_failed' => 'Failed to create product',
        'retrieved' => 'Product retrieved successfully',
        'list_retrieved' => 'Products retrieved successfully',
        'updated' => 'Product updated successfully',
        'update_failed' => 'Failed to update product',
        'deleted' => 'Product deleted successfully',
        'delete_failed' => 'Failed to delete product',
        'not_found' => 'Product not found',
        'retrieval_failed' => 'Failed to retrieve product',
    ],
    'validation' => [
        'name_required' => 'Product name is required',
        'name_string' => 'Product name must be a string',
        'name_max' => 'Product name cannot exceed 255 characters',
        'description_required' => 'Product description is required',
        'description_string' => 'Product description must be a string',
        'price_required' => 'Product price is required',
        'price_numeric' => 'Product price must be a number',
        'price_min' => 'Product price must be at least 0',
        'stock_required' => 'Product stock is required',
        'stock_integer' => 'Product stock must be an integer',
        'stock_min' => 'Product stock cannot be negative',
        'category_id_required' => 'Category is required',
        'category_id_exists' => 'Selected category does not exist',
    ],
    'attributes' => [
        'name' => 'Name',
        'description' => 'Description',
        'price' => 'Price',
        'stock' => 'Stock',
        'category_id' => 'Category',
        'images' => 'Images',
    ],
]; 