<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    {{ $isLogin ? 'Login' : 'Register' }}
                </div>
                <div class="card-body">
                    @if($isLogin)
                        <form wire:submit="login">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" wire:model="email">
                                @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" wire:model="password">
                                @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <button type="submit" class="btn btn-primary">Login</button>
                                <a href="#" wire:click.prevent="toggleForm" class="text-decoration-none">Need an account?</a>
                            </div>
                        </form>
                    @else
                        <form wire:submit="register">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" wire:model="name">
                                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" wire:model="email">
                                @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" wire:model="password">
                                @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="password_confirmation" wire:model="password_confirmation">
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <button type="submit" class="btn btn-primary">Register</button>
                                <a href="#" wire:click.prevent="toggleForm" class="text-decoration-none">Already have an account?</a>
                            </div>
                        </form>
                    @endif
                </div>
                @if($authToken)
                <div class="card-footer">
                    <small class="text-muted">Your Token: {{ $authToken }}</small>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
