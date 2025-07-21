<?php

use Illuminate\Support\Facades\Route;

// Single Livewire Component for all functionality
Route::get('/', \App\Livewire\AppManager::class)->name('app.manager');

// Route to get a fresh CSRF token without refreshing the page
Route::get('/csrf-token', function () {
    return response()->json([
        'token' => csrf_token(),
    ]);
});

// Redirect any other routes to the main component
Route::any('/{any}', function () {
    return redirect()->route('app.manager');
})->where('any', '.*');