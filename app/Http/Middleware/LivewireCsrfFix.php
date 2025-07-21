<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class LivewireCsrfFix
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
        // Check if this is a Livewire request
        if ($request->hasHeader('X-Livewire')) {
            // If the session doesn't have a CSRF token, regenerate it
            if (!session()->has('_token')) {
                session()->regenerateToken();
            }
        }
        
        return $next($request);
    }
}
