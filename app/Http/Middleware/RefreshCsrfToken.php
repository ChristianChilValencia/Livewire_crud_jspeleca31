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
        // Always ensure there's a valid CSRF token in the session
        // This prevents "page expired" errors by being proactive
        if (!session()->has('_token')) {
            session()->regenerateToken();
        }
        
        // For Livewire requests, always refresh the CSRF token to ensure it's valid
        if ($request->hasHeader('X-Livewire')) {
            // Store the token in a variable to make sure it's accessible
            $token = session()->token();
            // Update the request with the current token
            $request->headers->set('X-CSRF-TOKEN', $token);
        }
        
        // Handle the response
        $response = $next($request);
        
        // If this is a Livewire response, make sure it has the latest token
        if ($request->hasHeader('X-Livewire') && method_exists($response, 'header')) {
            $response->header('X-CSRF-TOKEN', session()->token());
        }
        
        return $response;
    }
}
