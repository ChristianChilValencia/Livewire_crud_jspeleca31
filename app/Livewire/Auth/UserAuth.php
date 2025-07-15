<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserAuth extends Component
{
    public $email = '';
    public $password = '';
    public $name = '';
    public $password_confirmation = '';
    public $isLogin = true;
    public $authToken = null;
    
    public function mount()
    {
        $this->authToken = session('auth_token');
        
        // Check if already authenticated
        if (Auth::check()) {
            return redirect()->route('products');
        }
    }
    
    public function render()
    {
        return view('livewire.auth.user-auth')
            ->layout('layouts.app');
    }
    
    public function toggleForm()
    {
        $this->reset(['email', 'password', 'name', 'password_confirmation']);
        $this->isLogin = !$this->isLogin;
    }
    
    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        $user = User::where('email', $this->email)->first();
        
        if (!$user || !Hash::check($this->password, $user->password)) {
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
            // Use existing token - reuse the token
            $expiresAt = $existingToken->expires_at;
            
            // Use the custom token format that was stored
            $customToken = $existingToken->token_name;
            
            // If we don't have a custom token name (older tokens), create one
            if (empty($customToken)) {
                $customToken = 'custom_token_' . $existingToken->id;
                $existingToken->token_name = $customToken;
                $existingToken->save();
            }
            
            $this->authToken = $customToken;
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
            
            $this->authToken = $customToken;
        }
        
        // Store in session for web usage
        session(['auth_token' => $this->authToken]);
        
        // Log the user in
        Auth::login($user);
        
        return redirect()->route('products');
    }
    
    public function register()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);
        
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
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
        
        $this->authToken = $customToken;
        
        // Store in session for web usage
        session(['auth_token' => $customToken]);
        
        // Log the user in
        Auth::login($user);
        
        return redirect()->route('products');
    }
    
    public function logout()
    {
        Auth::logout();
        session()->forget('auth_token');
        $this->authToken = null;
        return redirect()->route('login');
    }
}
