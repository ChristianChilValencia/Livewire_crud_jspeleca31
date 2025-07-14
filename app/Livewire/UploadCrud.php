<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\Image;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class UploadCrud extends Component
{
    use WithPagination, WithFileUploads;

    public $image, $name;
    public $showModal = false;
    public $deleteId = null;

    public function render()
    {
        return view('livewire.upload-crud', [
            'images' => Image::latest()->paginate(8)
        ]);
    }

    public function openModal()
    {
        $this->reset(['image', 'name']);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|max:2048',
        ]);
        $path = $this->image->store('uploads', 'public');
        Image::create([
            'name' => $this->name,
            'path' => $path,
        ]);
        session()->flash('success', 'Image uploaded successfully.');
        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
    }

    public function delete()
    {
        $image = Image::findOrFail($this->deleteId);
        \Storage::disk('public')->delete($image->path);
        $image->delete();
        $this->deleteId = null;
        session()->flash('success', 'Image deleted successfully.');
    }
}
