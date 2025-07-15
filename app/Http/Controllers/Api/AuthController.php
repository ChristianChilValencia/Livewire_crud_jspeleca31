<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Create token with expiration date
        $expiresAt = now()->addDays(7);
        $tokenResult = $user->createToken('auth_token', ['*'], $expiresAt);
        
        // Get the token model and ID
        $newTokenId = $tokenResult->accessToken->id;
        $tokenModel = $user->tokens()->where('id', $newTokenId)->first();
        
        // Create a custom token format that's easier to use
        $customToken = 'custom_token_' . $newTokenId;
        
        // Store original sanctum token and the custom format
        if ($tokenModel) {
            $tokenModel->original_token = $tokenResult->plainTextToken;
            $tokenModel->token_name = $customToken;
            $tokenModel->save();
        }
        
        // Use the actual token value instead of the custom format
        $responseToken = $tokenResult->plainTextToken;
        
        // Still store the custom token in session for web usage
        session(['auth_token' => $customToken]);

        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
            'token' => $responseToken,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s')
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check for existing valid token
        $existingToken = $user->tokens()
            ->where('name', 'auth_token')
            ->where(function($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();
            
        if ($existingToken) {
            // Use existing token - no need to create a new one
            $expiresAt = $existingToken->expires_at;
            
            // Use the custom token format that was stored
            $customToken = $existingToken->token_name;
            
            // If we don't have a custom token name (older tokens), create one
            if (empty($customToken)) {
                $customToken = 'custom_token_' . $existingToken->id;
                $existingToken->token_name = $customToken;
                $existingToken->save();
            }
            
            // Log token reuse for debugging
            \Log::info("Reusing existing token: {$customToken} for user {$user->email}");
            
            // Use the original token value instead of the custom format
            $responseToken = $existingToken->original_token ?? $customToken;
        } else {
            // Create a new token with expiration date
            $expiresAt = now()->addDays(7);
            $tokenResult = $user->createToken('auth_token', ['*'], $expiresAt);
            
            // Get the token model and ID
            $newTokenId = $tokenResult->accessToken->id;
            $tokenModel = $user->tokens()->where('id', $newTokenId)->first();
            
            // Create a custom token format that's easier to use
            $customToken = 'custom_token_' . $newTokenId;
            
            // Store original sanctum token and the custom format
            if ($tokenModel) {
                $tokenModel->original_token = $tokenResult->plainTextToken;
                $tokenModel->token_name = $customToken;
                $tokenModel->save();
            }
            
            // Return the actual token value instead of the custom format
            $responseToken = $tokenResult->plainTextToken;
        }
        
        // Store in session for web usage
        session(['auth_token' => $responseToken]);

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $responseToken,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s')
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
