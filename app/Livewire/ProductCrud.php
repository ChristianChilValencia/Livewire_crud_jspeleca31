<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ProductCrud extends Component
{
    use WithPagination, WithFileUploads;

    public $productId, $code, $name, $quantity, $price, $description, $image, $oldImage;
    public $isEdit = false, $showModal = false, $showViewModal = false;

    protected $rules = [
        'code' => 'required|string|max:255',
        'name' => 'required|string|max:255',
        'quantity' => 'required|integer',
        'price' => 'required|numeric',
        'description' => 'nullable|string',
        'image' => 'nullable|image|max:2048',
    ];

    public function render()
    {
        return view('livewire.product-crud', [
            'products' => Product::latest()->paginate(4)
        ]);
    }

    public function resetFields()
    {
        $this->productId = null;
        $this->code = '';
        $this->name = '';
        $this->quantity = '';
        $this->price = '';
        $this->description = '';
        $this->image = null;
        $this->oldImage = null;
        $this->isEdit = false;
    }

    public function create()
    {
        $this->resetFields();
        $this->showModal = true;
    }

    public function store()
    {
        $validated = $this->validate();
        if ($this->image) {
            $validated['image'] = $this->image->store('products', 'public');
        }
        Product::create($validated);
        session()->flash('success', 'New product is added successfully.');
        $this->showModal = false;
        $this->resetFields();
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $this->productId = $product->id;
        $this->code = $product->code;
        $this->name = $product->name;
        $this->quantity = $product->quantity;
        $this->price = $product->price;
        $this->description = $product->description;
        $this->oldImage = $product->image;
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function update()
    {
        $validated = $this->validate();
        $product = Product::findOrFail($this->productId);
        if ($this->image) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $validated['image'] = $this->image->store('products', 'public');
        } else {
            $validated['image'] = $this->oldImage;
        }
        $product->update($validated);
        session()->flash('success', 'Product updated successfully.');
        $this->showModal = false;
        $this->resetFields();
    }

    public function delete($id)
    {
        $product = Product::findOrFail($id);
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();
        session()->flash('success', 'Product deleted successfully.');
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        $this->productId = $product->id;
        $this->code = $product->code;
        $this->name = $product->name;
        $this->quantity = $product->quantity;
        $this->price = $product->price;
        $this->description = $product->description;
        $this->oldImage = $product->image;
        $this->showViewModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showViewModal = false;
        $this->resetFields();
    }
}
