<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class ApiAuthentication
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
        // If user is already authenticated, proceed
        if (Auth::check()) {
            return $next($request);
        }
        
        // Get the token from the Authorization header
        $bearerToken = $request->bearerToken();
        
        if ($bearerToken) {
            // Handle custom token format (custom_token_1)
            if (strpos($bearerToken, 'custom_token_') === 0) {
                $tokenId = (int) str_replace('custom_token_', '', $bearerToken);
                
                // Find the token by ID
                $token = PersonalAccessToken::find($tokenId);
                
                if ($token && 
                    ($token->expires_at === null || $token->expires_at > now())) {
                    
                    // Log the token usage for debugging
                    \Log::info("Using custom token: {$bearerToken}");
                    
                    // Get the user associated with this token
                    $user = $token->tokenable;
                    
                    if ($user) {
                        // Login the user
                        Auth::login($user);
                        return $next($request);
                    }
                }
            }
            // Handle Sanctum token format (1|hash...)
            else if (strpos($bearerToken, '|') !== false) {
                $tokenParts = explode('|', $bearerToken);
                
                if (count($tokenParts) === 2) {
                    $tokenId = $tokenParts[0];
                    
                    // Find the token in the database by ID and check if it's valid
                    $token = PersonalAccessToken::find($tokenId);
                    
                    // If we found a token and it's valid
                    if ($token && 
                        ($token->expires_at === null || $token->expires_at > now()) &&
                        ($token->original_token === $bearerToken || !$token->original_token)) {
                        
                        // Get the user associated with this token
                        $user = $token->tokenable;
                        
                        if ($user) {
                            // Login the user
                            Auth::login($user);
                            return $next($request);
                        }
                    }
                }
            }
        }
        
        // If no bearer token is provided, check if there's a token in the session
        if (session()->has('auth_token')) {
            $sessionToken = session('auth_token');
            
            // Handle custom token format in session
            if (strpos($sessionToken, 'custom_token_') === 0) {
                $tokenId = (int) str_replace('custom_token_', '', $sessionToken);
                
                // Find the token by ID
                $token = PersonalAccessToken::find($tokenId);
                
                if ($token && 
                    ($token->expires_at === null || $token->expires_at > now())) {
                    
                    // Get the user associated with this token
                    $user = $token->tokenable;
                    
                    if ($user) {
                        // Login the user
                        Auth::login($user);
                        return $next($request);
                    }
                }
            }
            // Handle Sanctum token format in session
            else if (strpos($sessionToken, '|') !== false) {
                $tokenParts = explode('|', $sessionToken);
                
                if (count($tokenParts) === 2) {
                    $tokenId = $tokenParts[0];
                    
                    // Find the token by ID
                    $token = PersonalAccessToken::find($tokenId);
                    
                    if ($token && 
                        ($token->expires_at === null || $token->expires_at > now()) &&
                        ($token->original_token === $sessionToken || !$token->original_token)) {
                        
                        // Get the user associated with this token
                        $user = $token->tokenable;
                        
                        if ($user) {
                            // Login the user
                            Auth::login($user);
                            return $next($request);
                        }
                    }
                }
            }
        }
        
        // If we reach here, authentication failed
        return response()->json(['message' => 'Unauthenticated'], 401);
    }
}
