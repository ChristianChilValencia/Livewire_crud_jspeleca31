<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RefreshCsrfToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // If the user is authenticated and the session doesn't have a CSRF token
        // or we're coming from an API authentication
        if (Auth::check() && (!session()->has('_token') || session()->get('auth_source') === 'api')) {
            // Regenerate the CSRF token
            session()->regenerateToken();
            
            // Mark that we've refreshed the token
            session()->put('auth_source', 'web');
        }
        
        return $next($request);
    }
}
