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
        $nearby = request('nearby');
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
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        if ($nearby && $userLat && $userLng) {
            $query->whereHas('vendors', function($q) use ($userLat, $userLng) {
                $q->where('is_open', true)
                ->whereRaw("(6371 * acos(cos(radians($userLat)) * cos(radians(latitude)) * cos(radians(longitude) - radians($userLng)) + sin(radians($userLat)) * sin(radians(latitude)))) <= 1");
            });
        }
        
        $products = $query->paginate(12);
        
        // Get the first vendor for each product and calculate customer price
        foreach ($products as $product) {
            $vendor = $product->vendors()->first();
            if ($vendor) {
                $product->customer_price = $product->getCustomerPriceForVendor($vendor->id);
                $product->vendor_id_for_price = $vendor->id;
            } else {
                $product->customer_price = $product->final_price;
                $product->vendor_id_for_price = null;
            }
        }
        
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
        
        // Get customer price from admin pricing
        $customerPrice = $product->getCustomerPriceForVendor($vendor->id);
        
        // Also get size-based prices with admin markup if applicable
        $sizePricesWithMarkup = [];
        
        // Decode the JSON string to array
        $sizePrices = json_decode($product->size_prices, true);
        $sizesArray = json_decode($product->sizes, true);
        
        if ($sizePrices && is_array($sizePrices)) {
            foreach ($sizePrices as $size => $price) {
                $adminPrice = \App\Models\AdminPrice::where('product_id', $product->id)
                    ->where('vendor_id', $vendor->id)
                    ->where('is_active', true)
                    ->first();
                
                if ($adminPrice && $adminPrice->customer_visible_price) {
                    // If admin has set a fixed customer price, use that for all sizes
                    $sizePricesWithMarkup[$size] = $adminPrice->customer_visible_price;
                } else {
                    $sizePricesWithMarkup[$size] = $price;
                }
            }
        }
        
        return view('customer.products.show', [
            'product' => $product,
            'vendor' => $vendor,
            'customerPrice' => $customerPrice,
            'sizePricesWithMarkup' => $sizePricesWithMarkup,
            'sizesArray' => $sizesArray,
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

    /**
     * Get shops within 1km radius
     */
    public function getNearbyShops(Request $request)
    {
        try {
            $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);

            $latitude = $request->latitude;
            $longitude = $request->longitude;

            // Get vendors within 1km radius using Haversine formula
            $vendors = Vendor::where('is_open', true)
                ->where('is_verified', true)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->selectRaw("*, 
                    (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * sin(radians(latitude)))) AS distance", 
                    [$latitude, $longitude, $latitude])
                ->having('distance', '<=', 1) // 1km radius
                ->orderBy('distance')
                ->get();

            // Always return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                // Store nearby info in session temporarily
                session()->flash('nearby_search', true);
                session()->flash('nearby_count', $vendors->count());
                
                return response()->json([
                    'success' => true,
                    'vendor_count' => $vendors->count(),
                    'message' => $vendors->count() . ' shops found within 1km of your location!',
                    'redirect_url' => route('customer.products.index', [
                        'nearby' => 1,
                        'lat' => $latitude,
                        'lng' => $longitude
                    ])
                ]);
            }

            // For non-AJAX requests, proceed with normal view
            $vendorIds = $vendors->pluck('id');
            
            $products = Product::where('is_active', true)
                ->whereHas('vendors', function($q) use ($vendorIds) {
                    $q->whereIn('vendor_id', $vendorIds)
                    ->where('is_open', true);
                })
                ->with(['vendors' => function($q) use ($vendorIds) {
                    $q->whereIn('vendor_id', $vendorIds);
                }])
                ->paginate(12);

            foreach ($products as $product) {
                $vendor = $product->vendors->first();
                if ($vendor) {
                    $product->customer_price = $product->getCustomerPriceForVendor($vendor->id);
                    $product->vendor_id_for_price = $vendor->id;
                } else {
                    $product->customer_price = $product->final_price;
                    $product->vendor_id_for_price = null;
                }
            }

            $categories = Category::where('is_active', true)->get();

            return view('customer.products.index', [
                'products' => $products,
                'categories' => $categories,
                'selectedCategory' => null,
                'searchQuery' => null,
                'nearbyOnly' => true,
                'nearbyVendors' => $vendors,
                'userLat' => $latitude,
                'userLng' => $longitude,
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid coordinates provided.',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
            
        } catch (\Exception $e) {
            \Log::error('Nearby shops error: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to find nearby shops. Please try again.'
                ], 500);
            }
            
            return back()->with('error', 'Failed to find nearby shops. Please try again.');
        }
    }
}