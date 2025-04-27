<?php

return [
    'messages' => [
        'created' => 'Order created successfully',
        'creation_failed' => 'Failed to create order',
        'retrieved' => 'Order retrieved successfully',
        'list_retrieved' => 'Orders retrieved successfully',
        'unauthorized' => 'You are not authorized to view this order',
    ],
    'validation' => [
        'items_required' => 'At least one item is required.',
        'items_array' => 'Order items must be an array',
        'product_id_required' => 'Product ID is required',
        'product_not_found' => 'Product not found.',
        'quantity_required' => 'Quantity is required',
        'quantity_integer' => 'Quantity must be an integer',
        'quantity_min' => 'Quantity must be at least 1',
        'shipping_address_required' => 'Shipping address is required',
        'shipping_address_string' => 'Shipping address must be a string',
        'shipping_address_max' => 'Shipping address cannot exceed 255 characters',
        'phone_required' => 'Phone number is required',
        'phone_string' => 'Phone number must be a string',
        'phone_max' => 'Phone number cannot exceed 20 characters',
        'notes_string' => 'Notes must be a string',
        'notes_max' => 'Notes cannot exceed 1000 characters',
        'product_out_of_stock' => 'Product is out of stock.',
        'insufficient_stock' => 'Insufficient stock available for the requested quantity.',
        'invalid_quantity' => 'Invalid quantity specified.',
    ],
    'success' => [
        'created' => 'Order created successfully.',
        'updated' => 'Order updated successfully.',
        'deleted' => 'Order deleted successfully.',
    ],
    'error' => [
        'create' => 'Failed to create order.',
        'update' => 'Failed to update order.',
        'delete' => 'Failed to delete order.',
    ],
]; 