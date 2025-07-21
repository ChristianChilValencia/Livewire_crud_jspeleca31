<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NavigationMenu extends Component
{
    protected $listeners = ['loggedIn' => '$refresh', 'loggedOut' => '$refresh'];

    public function render()
    {
        return view('livewire.navigation-menu', [
            'isLoggedIn' => Auth::check(),
            'userName' => Auth::check() ? Auth::user()->name : ''
        ]);
    }
}
