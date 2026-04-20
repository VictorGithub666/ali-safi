<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $vendor = Auth::user()->vendor;
        
        $todayOrders = Order::where('vendor_id', $vendor->id)
                           ->whereDate('created_at', today())
                           ->count();
                           
        $totalRevenue = Order::where('vendor_id', $vendor->id)
                            ->where('status', 'delivered')
                            ->sum('subtotal');
                            
        $pendingOrders = Order::where('vendor_id', $vendor->id)
                             ->whereIn('status', ['pending', 'confirmed', 'preparing'])
                             ->count();
                             
        $recentOrders = Order::where('vendor_id', $vendor->id)
                            ->with(['customer', 'items.product'])
                            ->latest()
                            ->take(10)
                            ->get();
                            
        $popularProducts = Product::whereHas('orders', function($q) use ($vendor) {
                                $q->where('vendor_id', $vendor->id);
                            })
                            ->withCount(['orders as orders_count' => function($q) use ($vendor) {
                                $q->where('vendor_id', $vendor->id);
                            }])
                            ->orderBy('orders_count', 'desc')
                            ->take(5)
                            ->get();
        
        return view('vendor.dashboard', compact(
            'todayOrders',
            'totalRevenue',
            'pendingOrders',
            'recentOrders',
            'popularProducts'
        ));
    }

    public function orders(Request $request)
    {
        $vendor = Auth::user()->vendor;
        
        $orders = Order::where('vendor_id', $vendor->id)
                      ->with(['customer', 'rider.user', 'items.product'])
                      ->when($request->status, function($q) use ($request) {
                          return $q->where('status', $request->status);
                      })
                      ->latest()
                      ->paginate(20);
        
        return view('vendor.orders.index', compact('orders'));
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $this->authorize('update', $order);
        
        $request->validate([
            'status' => 'required|in:confirmed,preparing,ready_for_pickup',
        ]);
        
        $order->updateStatus($request->status);
        
        return response()->json(['success' => true]);
    }

    public function products()
    {
        $vendor = Auth::user()->vendor;
        $products = $vendor->products()->with('category')->paginate(15);
        
        return view('vendor.products.index', compact('products'));
    }

    public function updateStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'stock_quantity' => 'required|integer|min:0',
            'is_available' => 'boolean',
        ]);
        
        $vendor = Auth::user()->vendor;
        
        $vendor->products()->updateExistingPivot($request->product_id, [
            'stock_quantity' => $request->stock_quantity,
            'is_available' => $request->is_available ?? true,
        ]);
        
        return response()->json(['success' => true]);
    }
}