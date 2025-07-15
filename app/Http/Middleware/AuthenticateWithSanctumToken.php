<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class AuthenticateWithSanctumToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If there's a session token but user is not authenticated, try to authenticate
        if (Session::has('auth_token') && !Auth::check()) {
            $token = Session::get('auth_token');
            $tokenModel = null;
            $user = null;
            
            // Extract token parts
            $tokenParts = explode('|', $token);
            
            if (count($tokenParts) === 2) {
                $tokenId = $tokenParts[0];
                
                // Find token directly by ID for better performance
                $tokenModel = PersonalAccessToken::where('id', $tokenId)
                    ->where(function($query) {
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    })
                    ->first();
                    
                if ($tokenModel) {
                    // If we have an original token stored, verify it matches the session token
                    // or update it if it's not set yet
                    if ($tokenModel->original_token === $token || !$tokenModel->original_token) {
                        if (!$tokenModel->original_token) {
                            $tokenModel->original_token = $token;
                            $tokenModel->save();
                        }
                        
                        $user = $tokenModel->tokenable;
                    }
                }
            }
            
            if ($user) {
                // Store the expiration date in the session
                if ($tokenModel && $tokenModel->expires_at) {
                    Session::put('token_expires_at', $tokenModel->expires_at);
                }
                
                Auth::login($user);
            } else {
                // Clear invalid or expired token
                Session::forget('auth_token');
                Session::forget('token_expires_at');
            }
        }
        
        return $next($request);
    }
}
