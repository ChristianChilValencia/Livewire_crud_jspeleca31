<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ApiPostController;
use App\Http\Controllers\Api\ApiCommentController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('posts', ApiPostController::class);
    Route::post('comments', [ApiCommentController::class, 'store']);
    Route::put('comments/{comment}', [ApiCommentController::class, 'update']);
    Route::delete('comments/{comment}', [ApiCommentController::class,'destroy']);
});
Route::get('/posts', [ApiPostController::class, 'index']);
