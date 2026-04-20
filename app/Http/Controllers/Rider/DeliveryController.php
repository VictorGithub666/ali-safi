<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryController extends Controller
{
    public function index()
    {
        $rider = Auth::user()->rider;
        
        $availableOrders = Order::whereNull('rider_id')
                               ->where('status', 'ready_for_pickup')
                               ->with(['vendor.user', 'customer'])
                               ->get();
                               
        $myDeliveries = Order::where('rider_id', $rider->id)
                            ->whereIn('status', ['picked_up', 'in_transit'])
                            ->with(['vendor.user', 'customer'])
                            ->get();
                            
        $completedDeliveries = Order::where('rider_id', $rider->id)
                                   ->where('status', 'delivered')
                                   ->whereDate('delivered_at', today())
                                   ->with(['vendor.user', 'customer'])
                                   ->get();
        
        return view('rider.deliveries.index', compact(
            'availableOrders',
            'myDeliveries',
            'completedDeliveries'
        ));
    }

    public function acceptOrder(Order $order)
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
        
        event(new OrderPickedUp($order));
        
        return response()->json(['success' => true]);
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
        
        $rider = Auth::user()->rider;
        $rider->updateLocation($request->latitude, $request->longitude);
        
        // If rider has active delivery, update order tracking
        $activeOrder = Order::where('rider_id', $rider->id)
                           ->whereIn('status', ['picked_up', 'in_transit'])
                           ->first();
                           
        if ($activeOrder) {
            $activeOrder->tracking()->create([
                'status' => 'in_transit',
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);
            
            broadcast(new RiderLocationUpdated($activeOrder, $request->latitude, $request->longitude));
        }
        
        return response()->json(['success' => true]);
    }

    public function completeDelivery(Request $request, Order $order)
    {
        $this->authorize('update', $order);
        
        $request->validate([
            'payment_received' => 'required|boolean',
            'payment_reference' => 'required_if:payment_received,true',
        ]);
        
        $order->updateStatus('delivered');
        
        if ($request->payment_received) {
            $order->update([
                'payment_status' => 'paid',
                'payment_reference' => $request->payment_reference,
            ]);
            
            // Create transaction
            $order->transaction()->create([
                'user_id' => $order->customer_id,
                'type' => 'payment',
                'amount' => $order->total,
                'status' => 'completed',
                'payment_method' => $order->payment_method,
                'reference' => $request->payment_reference,
            ]);
            
            // Update vendor wallet
            $order->vendor->increment('wallet_balance', $order->subtotal);
            
            // Update rider wallet
            $order->rider->increment('wallet_balance', $order->delivery_fee);
        }
        
        // Update rider stats
        $order->rider->increment('total_deliveries');
        
        event(new OrderDelivered($order));
        
        return response()->json(['success' => true]);
    }

    public function toggleAvailability()
    {
        $rider = Auth::user()->rider;
        $rider->update(['is_available' => !$rider->is_available]);
        
        return response()->json([
            'success' => true,
            'is_available' => $rider->is_available
        ]);
    }
}