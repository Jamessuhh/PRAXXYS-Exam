<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Protected routes that require authentication
    Route::apiResource('products', ProductController::class);
});

// Public routes that don't require authentication
Route::get('/categories', [CategoryController::class, 'index']);