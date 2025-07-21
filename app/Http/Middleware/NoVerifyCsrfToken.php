<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoVerifyCsrfToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // For Livewire requests, we'll skip CSRF verification
        if ($request->hasHeader('X-Livewire')) {
            return $next($request);
        }
        
        // For non-Livewire requests, we'll proceed normally
        return $next($request);
    }
}
