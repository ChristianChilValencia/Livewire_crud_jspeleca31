<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.guest')]
class Register extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function register()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        // Create token with 7 days expiration
        $expiresAt = now()->addDays(7);
        $tokenResult = $user->createToken('auth_token', ['*'], $expiresAt);
        $token = $tokenResult->plainTextToken;
        
        session(['auth_token' => $token]);
        session(['token_expires_at' => $expiresAt]);
        Auth::login($user);
        
        return redirect()->route('products.index');
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
