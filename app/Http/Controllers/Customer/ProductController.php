<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Vendor;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)->get();
        $category = request('category');
        $search = request('search');
        $nearby = request('nearby'); // 0 for all, 1 for nearby only
        $userLat = request('lat');
        $userLng = request('lng');
        
        // Only show products from vendors that are open
        $query = Product::where('is_active', true)
            ->whereHas('vendors', function($q) {
                $q->where('is_open', true);
            });
        
        if ($category) {
            $query->where('category_id', $category);
        }
        
        // Search by product name or description
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Filter by nearby vendors (within ~1km radius)
        if ($nearby && $userLat && $userLng) {
            $query->whereHas('vendors', function($q) use ($userLat, $userLng) {
                $q->where('is_open', true)
                  ->whereRaw("(6371 * acos(cos(radians($userLat)) * cos(radians(latitude)) * cos(radians(longitude) - radians($userLng)) + sin(radians($userLat)) * sin(radians(latitude)))) <= 1");
            });
        }
        
        $products = $query->paginate(12);
        
        return view('customer.products.index', [
            'products' => $products,
            'categories' => $categories,
            'selectedCategory' => $category,
            'searchQuery' => $search,
            'nearbyOnly' => $nearby,
        ]);
    }

    public function show(Product $product)
    {
        // Check if the product's vendor is open
        $vendor = $product->vendors()->first();
        
        if (!$vendor || !$vendor->is_open) {
            return redirect()->route('customer.products.index')
                ->with('error', 'This product is currently unavailable.');
        }
        
        return view('customer.products.show', [
            'product' => $product,
        ]);
    }

    public function vendorShop(Vendor $vendor)
    {
        // If shop is closed, show closed message
        if (!$vendor->is_open) {
            return view('customer.vendor-shop-closed', [
                'vendor' => $vendor,
            ]);
        }
        
        $products = $vendor->products()
            ->where('is_active', true)
            ->paginate(12);
        
        return view('customer.vendor-shop', [
            'vendor' => $vendor,
            'products' => $products,
        ]);
    }
}