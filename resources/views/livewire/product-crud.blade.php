<div>
    @if (session()->has('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Product List</span>
            <button class="btn btn-success btn-sm" wire:click="create"><i class="bi bi-plus-circle"></i> Add New Product</button>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>S#</th>
                        <th>Image</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $i => $product)
                        <tr>
                            <td>{{ $products->firstItem() + $i }}</td>
                            <td>
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="max-width: 50px;">
                                @else
                                    No Image
                                @endif
                            </td>
                            <td>{{ $product->code }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->quantity }}</td>
                            <td>{{ $product->price }}</td>
                            <td>
                                <button class="btn btn-info btn-sm" wire:click="show({{ $product->id }})">View</button>
                                <button class="btn btn-primary btn-sm" wire:click="edit({{ $product->id }})">Edit</button>
                                <button class="btn btn-danger btn-sm" wire:click="delete({{ $product->id }})" onclick="return confirm('Are you sure?')">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $products->links() }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $isEdit ? 'Edit Product' : 'Add New Product' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Code</label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" wire:model.defer="code">
                                @error('code') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label>Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name">
                                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label>Quantity</label>
                                <input type="number" class="form-control @error('quantity') is-invalid @enderror" wire:model.defer="quantity">
                                @error('quantity') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label>Price</label>
                                <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" wire:model.defer="price">
                                @error('price') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label>Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" wire:model.defer="description"></textarea>
                                @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label>Product Image</label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror" wire:model="image">
                                @if($oldImage && !$image)
                                    <img src="{{ asset('storage/' . $oldImage) }}" alt="Current Image" class="mt-2" style="max-width:100px;">
                                @endif
                                @if($image)
                                    <img src="{{ $image->temporaryUrl() }}" class="mt-2" style="max-width:100px;">
                                @endif
                                @error('image') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update' : 'Add Product' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- View Modal -->
    @if($showViewModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Product Information</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        @if($oldImage)
                            <img src="{{ asset('storage/' . $oldImage) }}" alt="{{ $name }}" class="mb-3" style="max-width: 300px;">
                        @endif
                        <div><strong>Code:</strong> {{ $code }}</div>
                        <div><strong>Name:</strong> {{ $name }}</div>
                        <div><strong>Quantity:</strong> {{ $quantity }}</div>
                        <div><strong>Price:</strong> {{ $price }}</div>
                        <div><strong>Description:</strong> {{ $description }}</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
