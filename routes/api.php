<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Order routes
    Route::apiResource('orders', OrderController::class)->only(['store', 'index', 'show']);
    
    // Product routes (protected by policies)
    Route::apiResource('products', ProductController::class);
    
    // Category routes (protected by policies)
    Route::apiResource('categories', CategoryController::class);
}); 