<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function __construct()
    {
        $this->middleware('user.type:rider');
    }

    public function index()
    {
        $user = Auth::user();
        $rider = $user->rider;
        
        // Check if rider record exists
        if (!$rider) {
            // Create rider record if it doesn't exist
            $rider = Rider::create([
                'user_id' => $user->id,
                'vehicle_type' => 'motorcycle',
                'vehicle_number' => 'PENDING',
                'license_number' => 'PENDING',
                'is_available' => false,
                'is_verified' => false,
                'total_deliveries' => 0,
                'wallet_balance' => 0,
            ]);
            
            // Refresh the user relationship
            $user->refresh();
            $rider = $user->rider;
        }
        
        // If still no rider record, redirect with error
        if (!$rider) {
            return redirect()->route('profile.edit')
                ->with('error', 'Rider profile not properly configured. Please contact support.');
        }
        
        $availableOrders = Order::whereNull('rider_id')
                               ->where('status', 'ready_for_pickup')
                               ->with(['vendor.user', 'customer'])
                               ->latest()
                               ->get();
                               
        $myDeliveries = Order::where('rider_id', $rider->id)
                            ->with(['vendor.user', 'customer'])
                            ->latest()
                            ->get();
                            
        $completedToday = Order::where('rider_id', $rider->id)
                              ->where('status', 'delivered')
                              ->whereDate('delivered_at', today())
                              ->count();
        
        return view('rider.dashboard', compact(
            'availableOrders',
            'myDeliveries',
            'rider',
            'completedToday'
        ));
    }

    public function acceptOrder(Request $request, Order $order)
    {
        $rider = Auth::user()->rider;
        
        if (!$rider->is_available) {
            return response()->json(['error' => 'You must be available to accept orders'], 400);
        }
        
        if ($order->rider_id) {
            return response()->json(['error' => 'Order already assigned'], 400);
        }
        
        $order->update([
            'rider_id' => $rider->id,
            'status' => 'picked_up',
            'picked_up_at' => now(),
        ]);
        
        $order->tracking()->create([
            'status' => 'picked_up',
            'notes' => 'Order picked up by rider',
        ]);
        
        return response()->json(['success' => true, 'message' => 'Order accepted successfully']);
    }

    public function completeDelivery(Request $request, Order $order)
    {
        $request->validate([
            'payment_received' => 'required|boolean',
            'notes' => 'nullable|string|max:500',
        ]);
        
        $rider = Auth::user()->rider;
        
        if ($order->rider_id !== $rider->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        DB::transaction(function () use ($request, $order, $rider) {
            $order->update([
                'status' => 'delivered',
                'delivered_at' => now(),
                'payment_status' => $request->payment_received ? 'paid' : 'pending',
            ]);
            
            $order->tracking()->create([
                'status' => 'delivered',
                'notes' => $request->notes,
            ]);
            
            if ($request->payment_received) {
                // Create transaction
                if (!$order->transaction) {
                    $order->transaction()->create([
                        'user_id' => $order->customer_id,
                        'type' => 'payment',
                        'amount' => $order->total,
                        'status' => 'completed',
                        'payment_method' => $order->payment_method,
                    ]);
                }
                
                // Update vendor and rider wallet
                $order->vendor->increment('wallet_balance', $order->subtotal);
            }
            
            // Always increment rider wallet with delivery fee
            $rider->increment('wallet_balance', $order->delivery_fee);
            $rider->increment('total_deliveries');
        });
        
        return response()->json(['success' => true, 'message' => 'Delivery completed successfully']);
    }

    public function toggleAvailability()
    {
        $rider = Auth::user()->rider;
        $rider->update(['is_available' => !$rider->is_available]);
        
        return response()->json([
            'success' => true,
            'is_available' => $rider->is_available,
            'message' => $rider->is_available ? 'You are now available for deliveries' : 'You are now offline'
        ]);
    }

    public function earnings()
    {
        $rider = Auth::user()->rider;
        
        $totalEarnings = Order::where('rider_id', $rider->id)
                            ->where('status', 'delivered')
                            ->sum('delivery_fee');
        
        $todayEarnings = Order::where('rider_id', $rider->id)
                             ->where('status', 'delivered')
                             ->whereDate('delivered_at', today())
                             ->sum('delivery_fee');
        
        $weekEarnings = Order::where('rider_id', $rider->id)
                            ->where('status', 'delivered')
                            ->whereDate('delivered_at', '>=', now()->startOfWeek())
                            ->sum('delivery_fee');
        
        $monthEarnings = Order::where('rider_id', $rider->id)
                             ->where('status', 'delivered')
                             ->whereMonth('delivered_at', now()->month)
                             ->sum('delivery_fee');
        
        $earningsChart = Order::where('rider_id', $rider->id)
                             ->where('status', 'delivered')
                             ->select(
                                 DB::raw('DATE(delivered_at) as date'),
                                 DB::raw('SUM(delivery_fee) as earnings'),
                                 DB::raw('COUNT(*) as deliveries')
                             )
                             ->groupBy('date')
                             ->orderBy('date', 'desc')
                             ->take(30)
                             ->get();
        
        return view('rider.earnings', compact(
            'totalEarnings',
            'todayEarnings',
            'weekEarnings',
            'monthEarnings',
            'earningsChart',
            'rider'
        ));
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);
        
        $rider = Auth::user()->rider;
        $rider->updateLocation($request->latitude, $request->longitude);
        
        return response()->json(['success' => true]);
    }
}