<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminPrice;
use App\Models\Product;
use App\Models\Vendor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminPriceController extends Controller
{
    public function index(Request $request)
    {
        $query = AdminPrice::with('product', 'vendor');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            })->orWhereHas('vendor', function ($q) use ($search) {
                $q->where('business_name', 'like', "%$search%");
            });
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->get('vendor_id'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }

        $prices = $query->paginate(15);
        $vendors = Vendor::pluck('business_name', 'id');

        return view('admin.prices.index', compact('prices', 'vendors'));
    }

    public function create()
    {
        $products = Product::pluck('name', 'id');
        $vendors = Vendor::pluck('business_name', 'id');
        return view('admin.prices.create', compact('products', 'vendors'));
    }

    /**
     * Get vendor price for a specific product and vendor
     */
    public function getVendorPrice(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'vendor_id' => 'required|exists:vendors,id',
            ]);

            $productId = $request->product_id;
            $vendorId = $request->vendor_id;

            // Check if there's an existing price record
            $existingPrice = AdminPrice::where('product_id', $productId)
                ->where('vendor_id', $vendorId)
                ->first();

            if ($existingPrice) {
                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'id' => $existingPrice->id,
                    'vendor_price' => $existingPrice->vendor_price,
                    'customer_visible_price' => $existingPrice->customer_visible_price,
                    'markup' => $existingPrice->markup,
                    'base_delivery_fee' => $existingPrice->base_delivery_fee,
                    'is_active' => $existingPrice->is_active,
                ]);
            }

            // Get vendor's custom price from vendor_products table
            $vendor = Vendor::find($vendorId);
            $vendorProduct = $vendor ? $vendor->products()->where('product_id', $productId)->first() : null;
            
            $vendorPrice = null;
            if ($vendorProduct && $vendorProduct->pivot->custom_price) {
                $vendorPrice = $vendorProduct->pivot->custom_price;
            } else {
                // Get product base price
                $product = Product::find($productId);
                $vendorPrice = $product ? $product->base_price : 0;
            }

            return response()->json([
                'success' => true,
                'exists' => false,
                'vendor_price' => (float) $vendorPrice,
                'customer_visible_price' => null,
                'markup' => null,
                'base_delivery_fee' => null,
                'is_active' => true,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in getVendorPrice', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'vendor_id' => 'required|exists:vendors,id',
            'vendor_price' => 'required|numeric|min:0',
            'customer_visible_price' => 'required|numeric|min:0',
            'markup' => 'required|numeric|min:0',
            'base_delivery_fee' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        AdminPrice::create($validated);

        return redirect()->route('admin.prices.index')->with('success', 'Price created successfully');
    }

    public function edit(AdminPrice $price)
    {
        $products = Product::pluck('name', 'id');
        $vendors = Vendor::pluck('business_name', 'id');
        return view('admin.prices.edit', compact('price', 'products', 'vendors'));
    }

    public function update(Request $request, AdminPrice $price)
    {
        $validated = $request->validate([
            'vendor_price' => 'required|numeric|min:0',
            'customer_visible_price' => 'required|numeric|min:0',
            'markup' => 'required|numeric|min:0',
            'base_delivery_fee' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $price->update($validated);

        return redirect()->route('admin.prices.index')->with('success', 'Price updated successfully');
    }

    public function destroy(AdminPrice $price)
    {
        $price->delete();
        return redirect()->route('admin.prices.index')->with('success', 'Price deleted successfully');
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'markup_percentage' => 'required|numeric|min:0'
        ]);

        $vendor_id = $validated['vendor_id'];
        $markup_pct = $validated['markup_percentage'] / 100;

        AdminPrice::where('vendor_id', $vendor_id)
            ->where('is_active', true)
            ->each(function ($price) use ($markup_pct) {
                $price->customer_visible_price = $price->vendor_price * (1 + $markup_pct);
                $price->markup = $price->customer_visible_price - $price->vendor_price;
                $price->save();
            });

        return back()->with('success', 'Prices updated successfully');
    }
}