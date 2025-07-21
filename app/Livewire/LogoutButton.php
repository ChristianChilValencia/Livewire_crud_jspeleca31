<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class LogoutButton extends Component
{
    public $showLogoutModal = false;

    public function render()
    {
        return view('livewire.logout-button');
    }

    public function confirmLogout()
    {
        $this->showLogoutModal = true;
    }

    public function cancelLogout()
    {
        $this->showLogoutModal = false;
    }

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        
        return redirect('/');
    }
}
