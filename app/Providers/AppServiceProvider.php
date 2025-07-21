<?php

namespace App\Providers;

use App\Helpers\CsrfHelper;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        // Make sure a CSRF token exists for every request
        CsrfHelper::ensureTokenExists();
        
        // When Livewire component hydrates, make sure we have a valid CSRF token
        Livewire::listen('component.hydrate', function () {
            CsrfHelper::ensureTokenExists();
        });
        
        // Hook into Laravel's CSRF token validation process to always allow Livewire requests
        $this->app->singleton(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, function ($app) {
            return new class($app) extends \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken {
                public function handle($request, \Closure $next)
                {
                    // If this is a Livewire request, bypass CSRF validation
                    if ($request->hasHeader('X-Livewire')) {
                        return $next($request);
                    }
                    
                    // Otherwise, proceed with normal CSRF validation
                    return parent::handle($request, $next);
                }
            };
        });
    }
}
