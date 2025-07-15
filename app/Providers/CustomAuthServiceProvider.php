<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Auth\TokenGuard;

class CustomAuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        // Register our custom token guard
        Auth::extend('token', function ($app, $name, array $config) {
            // Return an instance of our custom token guard
            return new TokenGuard(
                Auth::createUserProvider($config['provider']),
                $app['request']
            );
        });
    }
}
