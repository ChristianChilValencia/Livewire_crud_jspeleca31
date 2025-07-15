<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Logout extends Component
{
    public function logout()
    {
        // Revoke the current token
        if (Auth::user()) {
            Auth::user()->currentAccessToken()->delete();
        }
        
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        
        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.auth.logout');
    }
}
