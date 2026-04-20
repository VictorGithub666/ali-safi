<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Vendor;

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
        
        $query = Product::where('is_active', true);
        
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
                $q->whereRaw("(6371 * acos(cos(radians($userLat)) * cos(radians(latitude)) * cos(radians(longitude) - radians($userLng)) + sin(radians($userLat)) * sin(radians(latitude)))) <= 1");
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
        return view('customer.products.show', [
            'product' => $product,
        ]);
    }

    public function vendorShop(Vendor $vendor)
    {
        $products = $vendor->products()
            ->where('is_active', true)
            ->paginate(12);
        
        return view('customer.vendor-shop', [
            'vendor' => $vendor,
            'products' => $products,
        ]);
    }
}
