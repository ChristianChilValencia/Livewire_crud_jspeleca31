<div>
    <a href="#" class="nav-link" wire:click.prevent="confirmLogout">Logout</a>
    
    @if($showLogoutModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Logout Confirmation</h5>
                    <button type="button" class="btn-close" wire:click="cancelLogout"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to log out?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="cancelLogout">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="logout">Logout</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
