<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $vendor = Auth::user()->vendor;
        
        // Today's statistics
        $todayOrders = Order::where('vendor_id', $vendor->id)
                       ->whereDate('created_at', today())
                       ->count();
                       
        $todayRevenue = Order::where('vendor_id', $vendor->id)
                        ->whereDate('created_at', today())
                        ->where('status', 'delivered')
                        ->sum('subtotal');
        
        // Total statistics
        $totalOrders = Order::where('vendor_id', $vendor->id)->count();
        $totalRevenue = Order::where('vendor_id', $vendor->id)
                        ->where('status', 'delivered')
                        ->sum('subtotal');
                        
        $pendingOrders = Order::where('vendor_id', $vendor->id)
                         ->whereIn('status', ['pending', 'confirmed', 'preparing'])
                         ->count();

        $completedOrders = Order::where('vendor_id', $vendor->id)
                           ->where('status', 'delivered')
                           ->count();
                       
        $recentOrders = Order::where('vendor_id', $vendor->id)
                        ->with(['customer'])
                        ->withCount('items')
                        ->latest()
                        ->take(10)
                        ->get();
                            
        $popularProducts = Product::whereHas('vendors', function($q) use ($vendor) {
                                $q->where('vendor_id', $vendor->id);
                            })
                            ->withCount(['orders as orders_count' => function($q) use ($vendor) {
                                $q->where('vendor_id', $vendor->id);
                            }])
                            ->orderBy('orders_count', 'desc')
                            ->take(5)
                            ->get();

        $totalProducts = $vendor->products()->count();
        $lowStockProducts = $vendor->products()
                              ->wherePivot('stock_quantity', '<', 5)
                              ->count();
        
        return view('vendor.dashboard', compact(
            'todayOrders',
            'totalRevenue',
            'pendingOrders',
            'recentOrders',
            'popularProducts',
            'vendor',
            'todayRevenue',
            'totalOrders',
            'completedOrders',
            'totalProducts',
            'lowStockProducts'
        ));
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
        
        return response()->json(['success' => true, 'message' => 'Stock updated successfully']);
    }

    public function toggleStatus(Request $request)
    {
        $vendor = Auth::user()->vendor;
        
        $vendor->update([
            'is_open' => !$vendor->is_open
        ]);

        return redirect()
            ->back()
            ->with('success', $vendor->is_open ? 'Shop is now open!' : 'Shop is now closed!');
    }

    /**
     * Update vendor shop location (latitude, longitude, and address)
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'business_address' => 'nullable|string|max:500',
        ]);

        $vendor = Auth::user()->vendor;
        
        // Store as string to preserve all decimal places
        $vendor->update([
            'latitude' => (string) $request->latitude,
            'longitude' => (string) $request->longitude,
            'business_address' => $request->business_address ?? $vendor->business_address,
        ]);

        \Log::info('Vendor location updated', [
            'vendor_id' => $vendor->id,
            'business_name' => $vendor->business_name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'precision' => strlen(substr(strrchr((string)$request->latitude, "."), 1))
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shop location updated successfully!',
            'data' => [
                'latitude' => (string) $vendor->latitude,
                'longitude' => (string) $vendor->longitude,
                'business_address' => $vendor->business_address,
            ]
        ]);
    }

    public function earnings(Request $request)
    {
        $vendor = Auth::user()->vendor;

        // Date range (default last 30 days)
        $dateFrom = $request->input('date_from', today()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', today()->format('Y-m-d'));

        // Build the query
        $earningsQuery = Order::where('vendor_id', $vendor->id)
                            ->where('status', 'delivered')
                            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        // Get daily earnings
        $earnings = (clone $earningsQuery)
                    ->selectRaw('DATE(created_at) as date, SUM(subtotal) as total')
                    ->groupBy('date')
                    ->orderBy('date', 'asc')
                    ->get();

        // Get totals
        $totalEarnings = (clone $earningsQuery)->sum('subtotal');
        $totalOrders = (clone $earningsQuery)->count();

        // Debug log
        \Log::info('Vendor Earnings Query', [
            'vendor_id' => $vendor->id,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'total_earnings' => $totalEarnings,
            'total_orders' => $totalOrders,
            'earnings_count' => $earnings->count(),
            'wallet_balance' => $vendor->wallet_balance,
            'raw_query' => Order::where('vendor_id', $vendor->id)
                            ->where('status', 'delivered')
                            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                            ->toSql()
        ]);

        return view('vendor.earnings', compact(
            'earnings',
            'totalEarnings',
            'totalOrders',
            'vendor',
            'dateFrom',
            'dateTo'
        ));
    }

    public function analytics(Request $request)
    {
        $vendor = Auth::user()->vendor;

        // Date range
        $dateFrom = $request->input('date_from', today()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', today()->format('Y-m-d'));

        // Orders by status
        $ordersByStatus = Order::where('vendor_id', $vendor->id)
                              ->whereBetween('created_at', [$dateFrom, $dateTo])
                              ->selectRaw('status, COUNT(*) as count')
                              ->groupBy('status')
                              ->get();

        // Top products
        $topProducts = Product::whereHas('vendors', function($q) use ($vendor) {
                            $q->where('vendor_id', $vendor->id);
                        })
                        ->withCount(['orders as orders_count' => function($q) use ($vendor, $dateFrom, $dateTo) {
                            $q->where('vendor_id', $vendor->id)
                              ->whereBetween('orders.created_at', [$dateFrom, $dateTo]);
                        }])
                        ->orderBy('orders_count', 'desc')
                        ->take(10)
                        ->get();

        // Revenue trend
        $revenueTrend = Order::where('vendor_id', $vendor->id)
                            ->where('status', 'delivered')
                            ->whereBetween('created_at', [$dateFrom, $dateTo])
                            ->selectRaw('DATE(created_at) as date, SUM(subtotal) as revenue')
                            ->groupBy('date')
                            ->orderBy('date')
                            ->get();

        // Summary stats
        $stats = [
            'total_orders' => Order::where('vendor_id', $vendor->id)
                                  ->whereBetween('created_at', [$dateFrom, $dateTo])
                                  ->count(),
            'completed_orders' => Order::where('vendor_id', $vendor->id)
                                      ->where('status', 'delivered')
                                      ->whereBetween('created_at', [$dateFrom, $dateTo])
                                      ->count(),
            'pending_orders' => Order::where('vendor_id', $vendor->id)
                                    ->whereIn('status', ['pending', 'confirmed', 'preparing'])
                                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                                    ->count(),
            'total_revenue' => Order::where('vendor_id', $vendor->id)
                                   ->where('status', 'delivered')
                                   ->whereBetween('created_at', [$dateFrom, $dateTo])
                                   ->sum('subtotal'),
            'avg_order_value' => Order::where('vendor_id', $vendor->id)
                                     ->where('status', 'delivered')
                                     ->whereBetween('created_at', [$dateFrom, $dateTo])
                                     ->avg('subtotal') ?? 0,
        ];

        return view('vendor.analytics', compact(
            'vendor',
            'ordersByStatus',
            'topProducts',
            'revenueTrend',
            'stats',
            'dateFrom',
            'dateTo'
        ));
    }
}