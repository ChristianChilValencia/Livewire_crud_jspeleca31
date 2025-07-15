<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Check for token in session and use it for authentication if available
        $this->app->booted(function () {
            if (Session::has('auth_token') && !Auth::check()) {
                $token = Session::get('auth_token');
                $tokenModel = null;
                
                $user = \App\Models\User::whereHas('tokens', function ($query) use ($token, &$tokenModel) {
                    $plainTextToken = explode('|', $token)[1] ?? $token;
                    $query->where('token', hash('sha256', $plainTextToken))
                          ->where(function($q) {
                              $q->whereNull('expires_at')
                                ->orWhere('expires_at', '>', now());
                          });
                          
                    // Store the token model for expiration info
                    $tokenModel = $query->first();
                })->first();
                
                if ($user) {
                    // Store the expiration date in the session
                    if ($tokenModel && $tokenModel->expires_at) {
                        Session::put('token_expires_at', $tokenModel->expires_at);
                    }
                    
                    Auth::login($user);
                }
            }
        });
    }
}
