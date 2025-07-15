<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ApiPostController;
use App\Http\Controllers\Api\ApiCommentController;
use App\Http\Controllers\Api\ProductApiController;

// 🔓 Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/debug-token', [\App\Http\Controllers\Api\TokenDebugController::class, 'debug']);

// Simple test route to verify token authentication
Route::get('/test-auth', function() {
    return response()->json([
        'message' => 'You are authenticated!',
        'user' => auth()->user()
    ]);
})->middleware('auth:api');

// 🔐 Protected Routes (requires API token)
Route::middleware(['auth:api'])->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // 📌 Posts
    Route::apiResource('posts', ApiPostController::class);

    // 💬 Comments
    Route::post('/comments', [ApiCommentController::class, 'store']);
    Route::put('/comments/{comment}', [ApiCommentController::class, 'update']);
    Route::delete('/comments/{comment}', [ApiCommentController::class, 'destroy']);

    // 📦 Products
    Route::apiResource('products', ProductApiController::class);
});
