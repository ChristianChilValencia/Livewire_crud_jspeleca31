<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.guest')]
class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;
    public $token = null;

    protected function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $user = User::where('email', $this->email)->first();
            
            // Check if user already has a valid token
            $existingToken = $user->tokens()
                ->where('name', 'auth_token')
                ->where(function($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->first();
            
            if ($existingToken) {
                // Use existing token
                $expiresAt = $existingToken->expires_at;
                
                // Get the original token that was stored during creation
                $originalToken = $existingToken->original_token;
                
                if (!empty($originalToken)) {
                    $this->token = $originalToken;
                } else {
                    // If original token isn't stored, create a new format but keep the same ID
                    $tokenId = $existingToken->id;
                    $this->token = $tokenId . '|' . \Illuminate\Support\Str::random(40);
                    
                    // Store it for future use
                    $existingToken->original_token = $this->token;
                    $existingToken->save();
                }
            } else {
                // Create a new token with expiration of 7 days
                $expiresAt = now()->addDays(7);
                $tokenResult = $user->createToken('auth_token', ['*'], $expiresAt);
                $this->token = $tokenResult->plainTextToken;
                
                // Store the original token format
                $newTokenId = explode('|', $this->token)[0];
                $tokenModel = $user->tokens()->where('id', $newTokenId)->first();
                if ($tokenModel) {
                    $tokenModel->original_token = $this->token;
                    $tokenModel->save();
                }
            }
            
            session(['auth_token' => $this->token]);
            session(['token_expires_at' => $expiresAt]);
            session()->flash('message', 'Successfully logged in!');
            
            return redirect()->intended(route('products.index'));
        }
        
        $this->addError('email', 'The provided credentials do not match our records.');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
