<?php

use Illuminate\Support\Facades\Route;

// Livewire Components
use App\Livewire\Auth\UserAuth;
use App\Livewire\ProductCrud;
use App\Livewire\UploadCrud;

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', UserAuth::class)->name('login');
    Route::get('/register', function() {
        return view('livewire.auth.user-auth', ['isLogin' => false]);
    })->name('register');
});

Route::middleware('auth')->group(function () {
    Route::get('/products', ProductCrud::class)->name('products');
    Route::get('/uploads', UploadCrud::class)->name('uploads.index');
});

Route::get('/', function () {
    return redirect(route('products')); 
});