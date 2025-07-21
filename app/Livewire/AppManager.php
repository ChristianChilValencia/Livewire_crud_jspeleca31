<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class AppManager extends Component
{
    use WithPagination, WithFileUploads;

    public $name, $email, $password, $password_confirmation;
    public $authMode = 'login';
    public $authError = null;
    
    public $productId, $code, $quantity, $price, $description, $image, $oldImage;
    public $isEdit = false, $showModal = false, $showViewModal = false, $showDeleteModal = false, $deleteId = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'password' => 'required|min:8',
        
        'code' => 'required|string|max:255',
        'name' => 'required|string|max:255',
        'quantity' => 'required|integer',
        'price' => 'required|numeric',
        'description' => 'nullable|string',
        'image' => 'nullable|image|max:2048',
    ];

    public function render()
    {
        if (!Auth::check()) {
            return view('livewire.auth-manager');
        }

        return view('livewire.product-crud', [
            'products' => Product::latest()->paginate(4)
        ]);
    }

    public function switchAuthMode($mode)
    {
        $this->authMode = $mode;
        $this->resetValidation();
        $this->authError = null;
        $this->reset(['name', 'email', 'password', 'password_confirmation']);
    }

    public function login()
    {
        $this->authError = null;
        
        $credentials = $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check if the email exists in the database
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            $this->authError = 'This email is not registered in our system. Please register first.';
            return;
        }

        if (Auth::attempt($credentials)) {
            session()->regenerate();
            return redirect('/');
        }

        $this->addError('email', 'The provided credentials do not match our records.');
    }

    public function register()
    {
        $this->authError = null;
        
        try {
            $validated = $this->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|same:password_confirmation',
                'password_confirmation' => 'required',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            Auth::login($user);
            return redirect('/');
        } catch (\Exception $e) {
            $this->authError = 'Registration failed. Please check your information and try again.';
            return;
        }
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
        $validated = $this->validate([
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);
        
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
        $validated = $this->validate([
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);
        
        $product = Product::findOrFail($this->productId);
        
        if ($this->image) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $product->image = $this->image->store('products', 'public');
        } else {
            $product->image = $this->oldImage;
        }
        
        $product->code = $validated['code'];
        $product->name = $validated['name'];
        $product->quantity = $validated['quantity'];
        $product->price = $validated['price'];
        $product->description = $validated['description'];
        $product->save();
        
        session()->flash('success', 'Product updated successfully.');
        $this->showModal = false;
        $this->resetFields();
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $product = Product::findOrFail($this->deleteId);
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();
        session()->flash('success', 'Product deleted successfully.');
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
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
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->resetFields();
    }
}
