<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductApiController extends Controller
{
    // GET /api/products
    public function index()
    {
        return response()->json(Product::all());
    }

    // POST /api/products
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:products,code',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        return response()->json($product, 201);
    }

    // GET /api/products/{product}
    public function show(Product $product)
    {
        return response()->json($product);
    }

    // PUT /api/products/{product}
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'code' => 'sometimes|required|string|unique:products,code,' . $product->id,
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric',
            'quantity' => 'sometimes|required|integer',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return response()->json($product);
    }

    // DELETE /api/products/{product}
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }
}
