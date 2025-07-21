<ul class="navbar-nav ms-auto">
    @if(!$isLoggedIn)
        <li class="nav-item">
            <span class="nav-link">Please login to continue</span>
        </li>
    @else
        <li class="nav-item">
            <span class="nav-link">Welcome, {{ $userName }}</span>
        </li>
        <li class="nav-item">
            <livewire:logout-button />
        </li>
    @endif
</ul>
