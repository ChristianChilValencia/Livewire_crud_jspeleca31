<?php

use Illuminate\Support\Facades\Route;

// Single Livewire Component for all functionality
Route::get('/', \App\Livewire\AppManager::class)->name('app.manager');

// Redirect any other routes to the main component
Route::any('/{any}', function () {
    return redirect()->route('app.manager');
})->where('any', '.*');