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
        // Store current auth state
        $wasLoggedIn = Auth::check();
        
        // Get the current CSRF token before logout
        $token = csrf_token();
        
        // Perform logout
        Auth::logout();
        
        // Manually set the token back to what it was before logout
        session()->put('_token', $token);
        
        // Close the modal
        $this->showLogoutModal = false;
        
        // Emit event to tell other components to refresh
        if ($wasLoggedIn) {
            $this->dispatch('loggedOut');
        }
    }
}
