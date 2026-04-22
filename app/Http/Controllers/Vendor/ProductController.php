<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVendorProductRequest;
use App\Http\Requests\UpdateVendorProductRequest;
use App\Models\Product;
use App\Models\Category;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of vendor's products.
     */
    public function index()
    {
        $vendor = Auth::user()->vendor;
        $products = $vendor->products()
                    ->with('category')
                    ->paginate(15);
        
        return view('vendor.products.index', compact('products', 'vendor'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('vendor.products.create', compact('categories'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreVendorProductRequest $request)
    {
        $vendor = Auth::user()->vendor;

        $validated = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        }

        // Set base_price and final_price from price field
        $validated['base_price'] = $request->input('price');
        $validated['final_price'] = $request->input('price');
        $validated['is_active'] = $request->boolean('is_active', true);
        
        // Generate slug
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);

        // Create product
        $product = Product::create($validated);

        // Attach to vendor with stock quantity
        $vendor->products()->attach($product->id, [
            'stock_quantity' => $request->input('stock_quantity', 0),
            'is_available' => $request->boolean('is_available', true),
            'custom_price' => $request->input('custom_price'),
        ]);

        return redirect()
            ->route('vendor.products.show', $product)
            ->with('success', 'Product created successfully!');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $vendor = Auth::user()->vendor;
        
        // Check if vendor owns this product
        if (!$vendor->products->contains($product->id)) {
            abort(403);
        }

        $productData = $vendor->products()
                       ->where('product_id', $product->id)
                       ->first();

        return view('vendor.products.show', [
            'product' => $product,
            'productData' => $productData,
            'vendor' => $vendor,
        ]);
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $vendor = Auth::user()->vendor;
        
        // Check if vendor owns this product
        if (!$vendor->products->contains($product->id)) {
            abort(403);
        }

        $categories = Category::where('is_active', true)->get();
        $productData = $vendor->products()
                       ->where('product_id', $product->id)
                       ->first();

        return view('vendor.products.edit', compact('product', 'productData', 'categories', 'vendor'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateVendorProductRequest $request, Product $product)
    {
        $vendor = Auth::user()->vendor;
        
        if (!$vendor->products->contains($product->id)) {
            abort(403);
        }

        $validated = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        }

        // Update prices
        $validated['base_price'] = $request->input('price');
        $validated['final_price'] = $request->input('price');
        $validated['is_active'] = $request->boolean('is_active', true);
        
        // Update slug if name changed
        if ($product->name !== $validated['name']) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        $product->update($validated);

        // Update pivot data
        $vendor->products()->updateExistingPivot($product->id, [
            'stock_quantity' => $validated['stock_quantity'],
            'is_available' => $request->boolean('is_available', true),
            'custom_price' => $validated['custom_price'] ?? null,
        ]);

        return redirect()
            ->route('vendor.products.show', $product)
            ->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        $vendor = Auth::user()->vendor;
        
        // Check if vendor owns this product
        if (!$vendor->products->contains($product->id)) {
            abort(403);
        }

        // Delete image if exists
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        // Detach from vendor
        $vendor->products()->detach($product->id);

        // Delete product if no other vendors have it
        if ($product->vendors()->count() === 0) {
            $product->delete();
        }

        return redirect()
            ->route('vendor.products.index')
            ->with('success', 'Product deleted successfully!');
    }

    /**
     * Bulk update product availability
     */
    public function bulkToggleAvailability(Request $request)
    {
        $vendor = Auth::user()->vendor;

        $validated = $request->validate([
            'product_id' => 'required|array',
            'product_id.*' => 'exists:products,id',
            'is_available' => 'required|boolean',
        ]);

        foreach ($validated['product_id'] as $productId) {
            $vendor->products()->updateExistingPivot($productId, [
                'is_available' => $validated['is_available'],
            ]);
        }

        return redirect()->back()->with('success', 'Products updated successfully!');
    }

    /**
     * Export products to CSV
     */
    public function export()
    {
        $vendor = Auth::user()->vendor;
        $products = $vendor->products()->get();

        $filename = 'products_' . date('Y-m-d') . '.csv';
        $handle = fopen('php://memory', 'r+');

        fputcsv($handle, ['ID', 'Name', 'Category', 'Price', 'Stock', 'Available', 'Status']);

        foreach ($products as $product) {
            fputcsv($handle, [
                $product->id,
                $product->name,
                $product->category->name,
                $product->price,
                $product->pivot->stock_quantity,
                $product->pivot->is_available ? 'Yes' : 'No',
                $product->is_active ? 'Active' : 'Inactive',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }

    /**
     * Toggle product availability via AJAX
     */
    public function toggleAvailability(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'is_available' => 'required|boolean',
        ]);

        $vendor = Auth::user()->vendor;
        
        $vendor->products()->updateExistingPivot($request->product_id, [
            'is_available' => $request->is_available,
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Product availability updated successfully'
        ]);
    }
}
