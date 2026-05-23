<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Rider;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_orders' => Order::count(),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'total_revenue' => Order::where('status', 'delivered')->sum('total'),
            'platform_revenue' => Order::where('status', 'delivered')->sum('platform_fee'),
            'total_customers' => User::where('user_type', 'customer')->count(),
            'total_vendors' => Vendor::count(),
            'total_riders' => Rider::count(),
            'active_riders' => Rider::where('is_available', true)->count(),
            'pending_orders' => Order::whereIn('status', ['pending', 'confirmed'])->count(),
            'in_transit_orders' => Order::where('status', 'in_transit')->count(),
        ];

         // Extract individual variables for easier access in view
        $totalOrders = $stats['total_orders'];
        $activeRiders = $stats['active_riders'];
        $totalRevenue = $stats['total_revenue'];
        // ... add other variables you need
        
        // Get active vendors (verified + user is active)
        $activeVendors = Vendor::whereHas('user', function($q) {
            $q->where('is_active', true);
        })->where('is_verified', true)->count();
        
        // Get completed orders count
        $completedOrders = Order::where('status', 'delivered')->count();
        
        // Get platform commission rate
        $commissionRate = Setting::get('platform_fee_percentage', 5);
        
        // Get total users count
        $totalUsers = User::count();
        
        $recentOrders = Order::with(['customer', 'vendor.user', 'rider.user'])
                            ->latest()
                            ->take(20)
                            ->get();
                            
        $revenueChart = Order::where('status', 'delivered')
                            ->select(
                                DB::raw('DATE(created_at) as date'),
                                DB::raw('SUM(total) as total'),
                                DB::raw('SUM(platform_fee) as platform_fee')
                            )
                            ->groupBy('date')
                            ->orderBy('date', 'desc')
                            ->take(30)
                            ->get();
        
        return view('admin.dashboard', compact(
            'stats', 
            'recentOrders', 
            'revenueChart',
            'activeVendors',
            'completedOrders',
            'commissionRate',
            'totalUsers',
            'totalOrders',      // Add this
            'activeRiders',     // Add this
            'totalRevenue'  
        ));
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'vendor.user', 'rider.user', 'items.product', 'tracking']);
        
        return view('admin.orders.show', compact('order'));
    }
 
    public function orders(Request $request)
    {
        $orders = Order::with(['customer', 'vendor.user', 'rider.user', 'items.product'])
                    ->when($request->status, function($q) use ($request) {
                        return $q->where('status', $request->status);
                    })
                    ->when($request->vendor, function($q) use ($request) {
                        return $q->where('vendor_id', $request->vendor);
                    })
                    ->when($request->date_from, function($q) use ($request) {
                        return $q->whereDate('created_at', '>=', $request->date_from);
                    })
                    ->when($request->date_to, function($q) use ($request) {
                        return $q->whereDate('created_at', '<=', $request->date_to);
                    })
                    ->latest()
                    ->paginate(20);
        
        $vendors = Vendor::with('user')->get();
        $statuses = ['pending', 'confirmed', 'preparing', 'ready_for_pickup', 'picked_up', 'in_transit', 'delivered', 'cancelled'];
        
        return view('admin.orders.index', compact('orders', 'vendors', 'statuses'));
    }

    public function assignRider(Request $request, Order $order)
    {
        $request->validate([
            'rider_id' => 'required|exists:riders,id',
        ]);
        
        $order->update(['rider_id' => $request->rider_id]);
        
        event(new OrderAssigned($order));
        
        return response()->json(['success' => true]);
    }

    public function vendors()
    {
        $vendors = Vendor::with(['user', 'products'])
                        ->withCount('orders')
                        ->withSum(['orders as total_revenue' => function($q) {
                            $q->where('status', 'delivered');
                        }], 'subtotal')
                        ->paginate(20);
        
        return view('admin.vendors.index', compact('vendors'));
    }

    public function verifyVendor(Vendor $vendor)
    {
        $vendor->update(['is_verified' => true]);
        $vendor->user->update(['is_verified' => true]);
        
        return back()->with('success', 'Vendor verified successfully');
    }

    public function riders()
    {
        $riders = Rider::with('user')
                      ->withCount('orders')
                      ->withAvg('orders as avg_rating', 'rating')
                      ->paginate(20);
        
        return view('admin.riders.index', compact('riders'));
    }

    public function verifyRider(Rider $rider)
    {
        $rider->update(['is_verified' => true]);
        $rider->user->update(['is_verified' => true]);
        
        return back()->with('success', 'Rider verified successfully');
    }

    public function settings()
    {
        return view('admin.settings.index');
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'platform_fee_percentage' => 'required|numeric|min:0|max:100',
            'base_delivery_fee' => 'required|numeric|min:0',
            'per_km_delivery_fee' => 'required|numeric|min:0',
            'vendor_commission_percentage' => 'required|numeric|min:0|max:100',
            'admin_whatsapp_numbers' => 'nullable|string', // Add this line
        ]);
        
        foreach ($request->all() as $key => $value) {
            if (!in_array($key, ['_token', '_method'])) {
                \App\Models\Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
        }
        
        return back()->with('success', 'Settings updated successfully');
    }

    public function reports(Request $request)
    {
        $reportType = $request->get('type', 'orders');
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        $data = [];
        
        switch ($reportType) {
            case 'orders':
                $data = Order::whereBetween('created_at', [$dateFrom, $dateTo])
                            ->select(
                                DB::raw('DATE(created_at) as date'),
                                DB::raw('COUNT(*) as total_orders'),
                                DB::raw('SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as completed_orders'),
                                DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_orders'),
                                DB::raw('SUM(total) as total_revenue'),
                                DB::raw('SUM(platform_fee) as platform_revenue')
                            )
                            ->groupBy('date')
                            ->get();
                break;
                
            case 'vendors':
                $data = Vendor::with('user')
                             ->withCount(['orders' => function($q) use ($dateFrom, $dateTo) {
                                 $q->whereBetween('created_at', [$dateFrom, $dateTo]);
                             }])
                             ->withSum(['orders as total_revenue' => function($q) use ($dateFrom, $dateTo) {
                                 $q->whereBetween('created_at', [$dateFrom, $dateTo])
                                   ->where('status', 'delivered');
                             }], 'subtotal')
                             ->get();
                break;
                
            case 'riders':
                $data = Rider::with('user')
                            ->withCount(['orders' => function($q) use ($dateFrom, $dateTo) {
                                $q->whereBetween('created_at', [$dateFrom, $dateTo]);
                            }])
                            ->withSum(['orders as total_earnings' => function($q) use ($dateFrom, $dateTo) {
                                $q->whereBetween('created_at', [$dateFrom, $dateTo])
                                  ->where('status', 'delivered');
                            }], 'delivery_fee')
                            ->get();
                break;
        }
        
        return view('admin.reports.index', compact('reportType', 'dateFrom', 'dateTo', 'data'));
    }
}