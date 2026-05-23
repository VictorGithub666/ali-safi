<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Rider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryController extends Controller
{


    public function __construct()
    {
        $this->middleware('user.type:rider');
    }

    public function index()
    {
        Log::info('=== Rider Dashboard Accessed ===', [
            'user_id' => Auth::id(),
            'time' => now()->toDateTimeString()
        ]);

        $user = Auth::user();
        $rider = $user->rider;
        
        // Check if rider record exists and create if needed
        if (!$rider) {
            $rider = $this->ensureRiderExists($user);
        }
        
        if (!$rider) {
            return redirect()->route('profile.edit')
                ->with('error', 'Rider profile not properly configured. Please contact support.');
        }
        
        // Check if rider already has an active delivery
        $hasActiveDelivery = Order::where('rider_id', $rider->id)
            ->whereIn('status', ['picked_up', 'on_the_way', 'in_transit'])
            ->exists();
        
        // Only show available orders if rider has NO active delivery
        $availableOrders = collect();
        if (!$hasActiveDelivery) {
            $availableOrders = $this->getAvailableOrders($rider);
        }
        
        // Get active deliveries (should be 0 or 1, but we'll show all)
        $myDeliveries = $this->getActiveDeliveries($rider);
        
        $completedToday = Order::where('rider_id', $rider->id)
                            ->where('status', 'delivered')
                            ->whereDate('delivered_at', today())
                            ->count();
        
        Log::info('Rider dashboard data loaded', [
            'rider_id' => $rider->id,
            'has_active_delivery' => $hasActiveDelivery,
            'available_orders_count' => $availableOrders->count(),
            'active_deliveries_count' => $myDeliveries->count(),
            'completed_today' => $completedToday
        ]);
        
        return view('rider.dashboard', compact(
            'availableOrders',
            'myDeliveries',
            'rider',
            'completedToday',
            'hasActiveDelivery'
        ));
    }

    /**
     * Ensure rider record exists
     */
    private function ensureRiderExists($user)
    {
        Log::warning('No rider record found, attempting to create', [
            'user_id' => $user->id
        ]);
        
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
        
        Log::info('Rider record created', [
            'rider_id' => $rider->id,
            'user_id' => $user->id
        ]);
        
        $user->refresh();
        return $user->rider;
    }

    /**
     * Get available orders for pickup
     */
    private function getAvailableOrders($rider)
    {
        return Order::whereNull('rider_id')
            ->where('status', 'ready_for_pickup')
            ->with(['vendor.user', 'customer'])
            ->latest()
            ->get()
            ->map(function ($order) use ($rider) {
                return $this->addMapsUrls($order, $rider);
            });
    }

    /**
     * Get active deliveries for the rider
     */
    private function getActiveDeliveries($rider)
    {
        return Order::where('rider_id', $rider->id)
            ->whereIn('status', ['picked_up', 'on_the_way', 'in_transit'])
            ->with(['vendor.user', 'customer'])
            ->latest()
            ->get()
            ->map(function ($order) {
                return $this->addMapsUrls($order, null);
            });
    }

    /**
     * Add Google Maps URLs to order
     */
    private function addMapsUrls($order, $rider = null)
    {
        // Add Google Maps URLs for vendor location
        if ($order->vendor && $order->vendor->latitude && $order->vendor->longitude) {
            $vendorLat = (string) $order->vendor->latitude;
            $vendorLng = (string) $order->vendor->longitude;
            
            $order->vendor_maps_url = "https://www.google.com/maps/search/?api=1&query={$vendorLat},{$vendorLng}";
            
            // Generate directions from rider's current location to vendor
            if ($rider && $rider->current_latitude && $rider->current_longitude) {
                $riderLat = (string) $rider->current_latitude;
                $riderLng = (string) $rider->current_longitude;
                $order->vendor_directions_url = "https://www.google.com/maps/dir/{$riderLat},{$riderLng}/{$vendorLat},{$vendorLng}/";
            } else {
                $order->vendor_directions_url = $order->vendor_maps_url;
            }
        } else {
            $order->vendor_maps_url = null;
            $order->vendor_directions_url = null;
        }
        
        // Add Google Maps URLs for customer location
        if ($order->delivery_latitude && $order->delivery_longitude) {
            $customerLat = (string) $order->delivery_latitude;
            $customerLng = (string) $order->delivery_longitude;
            
            $order->customer_maps_url = "https://www.google.com/maps/search/?api=1&query={$customerLat},{$customerLng}";
            
            // Generate directions from vendor to customer
            if ($order->vendor && $order->vendor->latitude && $order->vendor->longitude) {
                $vendorLat = (string) $order->vendor->latitude;
                $vendorLng = (string) $order->vendor->longitude;
                $order->customer_directions_url = "https://www.google.com/maps/dir/{$vendorLat},{$vendorLng}/{$customerLat},{$customerLng}/";
            } else {
                $order->customer_directions_url = $order->customer_maps_url;
            }
        } else {
            $order->customer_maps_url = null;
            $order->customer_directions_url = null;
        }
        
        return $order;
    }

    public function acceptOrder(Request $request, Order $order)
    {
        Log::info('=== Accept Order Called ===', [
            'order_id' => $order->id,
            'user_id' => Auth::id()
        ]);
        
        $rider = Auth::user()->rider;
        
        if (!$rider->is_available) {
            Log::warning('Rider not available to accept order', [
                'rider_id' => $rider->id,
                'is_available' => $rider->is_available
            ]);
            return redirect()->back()->with('error', 'You must be available to accept orders');
        }
        
        // Check if rider already has an active delivery
        $hasActiveDelivery = Order::where('rider_id', $rider->id)
            ->whereIn('status', ['picked_up', 'on_the_way', 'in_transit'])
            ->exists();
        
        if ($hasActiveDelivery) {
            Log::warning('Rider already has an active delivery', [
                'rider_id' => $rider->id
            ]);
            return redirect()->back()->with('error', 'You already have an active delivery. Please complete it before accepting another.');
        }
        
        if ($order->rider_id) {
            Log::warning('Order already assigned', [
                'order_id' => $order->id,
                'assigned_rider_id' => $order->rider_id
            ]);
            return redirect()->back()->with('error', 'Order already assigned to another rider');
        }
        
        // Use transaction to ensure data consistency
        DB::transaction(function () use ($order, $rider) {
            $order->update([
                'rider_id' => $rider->id,
                'status' => 'picked_up',
                'picked_up_at' => now(),
            ]);
            
            $order->tracking()->create([
                'status' => 'picked_up',
                'notes' => 'Order picked up by rider',
                'updated_by' => $rider->user_id,
                'updated_by_type' => 'rider',
            ]);
        });
        
        Log::info('Order accepted successfully', [
            'order_id' => $order->id,
            'rider_id' => $rider->id
        ]);
        
        return redirect()->route('rider.deliveries.show', $order)
            ->with('success', 'Order picked up successfully! Please deliver to the customer.');
    }

    // Add a new API endpoint for checking dashboard status
    public function getDashboardStatus()
    {
        $rider = Auth::user()->rider;
        
        if (!$rider) {
            return response()->json([
                'success' => false,
                'message' => 'Rider profile not found'
            ], 404);
        }
        
        // Check if rider has active delivery
        $hasActiveDelivery = Order::where('rider_id', $rider->id)
            ->whereIn('status', ['picked_up', 'on_the_way', 'in_transit'])
            ->exists();
        
        // Get available orders count (only if no active delivery)
        $availableOrdersCount = 0;
        if (!$hasActiveDelivery) {
            $availableOrdersCount = Order::whereNull('rider_id')
                ->where('status', 'ready_for_pickup')
                ->count();
        }
        
        // Get active delivery details if exists
        $activeDelivery = null;
        if ($hasActiveDelivery) {
            $activeDelivery = Order::where('rider_id', $rider->id)
                ->whereIn('status', ['picked_up', 'on_the_way', 'in_transit'])
                ->with(['vendor.user', 'customer'])
                ->first();
                
            if ($activeDelivery) {
                $activeDelivery->status_label = $this->getStatusLabel($activeDelivery->status);
            }
        }
        
        return response()->json([
            'success' => true,
            'has_active_delivery' => $hasActiveDelivery,
            'available_orders_count' => $availableOrdersCount,
            'active_delivery' => $activeDelivery,
            'is_available' => $rider->is_available,
            'last_location_update' => $rider->last_location_update,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'picked_up' => 'Picked Up - On the way to customer',
            'on_the_way' => 'On The Way to Customer',
            'in_transit' => 'In Transit',
            'delivered' => 'Delivered'
        ];
        
        return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

   

   


    public function completeDelivery(Request $request, Order $order)
    {
        Log::info('=== Complete Delivery Called ===', [
            'order_id' => $order->id,
            'user_id' => Auth::id()
        ]);
        
        $request->validate([
            'payment_received' => 'required|boolean',
            'notes' => 'nullable|string|max:500',
        ]);
        
        $rider = Auth::user()->rider;
        
        if ($order->rider_id !== $rider->id) {
            Log::warning('Unauthorized complete delivery attempt', [
                'order_rider_id' => $order->rider_id,
                'current_rider_id' => $rider->id
            ]);
            return redirect()->back()->with('error', 'Unauthorized action');
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
                
                // Update vendor wallet
                $order->vendor->increment('wallet_balance', $order->subtotal);
            }
            
            // ========================================
            // FIX: Reduce product stock when rider marks as delivered
            // ========================================
            $vendor = $order->vendor;
            foreach ($order->items as $item) {
                // Get the vendor_product pivot record
                $vendorProduct = $vendor->products()
                    ->where('product_id', $item->product_id)
                    ->first();
                
                if ($vendorProduct) {
                    $currentStock = $vendorProduct->pivot->stock_quantity;
                    $newStock = max(0, $currentStock - $item->quantity);
                    
                    $vendor->products()->updateExistingPivot($item->product_id, [
                        'stock_quantity' => $newStock,
                        // If stock becomes 0, mark as unavailable
                        'is_available' => $newStock > 0 ? $vendorProduct->pivot->is_available : false,
                    ]);
                    
                    Log::info('Product stock updated after delivery (by rider)', [
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'old_stock' => $currentStock,
                        'quantity_sold' => $item->quantity,
                        'new_stock' => $newStock,
                    ]);
                }
            }
            
            // Update vendor total orders count
            $vendor->increment('total_orders');
            
            // Always increment rider wallet with delivery fee
            $rider->increment('wallet_balance', $order->delivery_fee);
            $rider->increment('total_deliveries');
            
            Log::info('Delivery completed successfully', [
                'order_id' => $order->id,
                'rider_id' => $rider->id,
                'delivery_fee' => $order->delivery_fee,
                'payment_received' => $request->payment_received
            ]);
        });
        
        // Redirect to earnings page with success message
        return redirect()->route('rider.earnings')
            ->with('success', '🎉 Delivery completed successfully! You earned KES ' . number_format($order->delivery_fee, 2) . '. Thank you for your service!');
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
        
        // Store as string to preserve all decimal places
        $rider->updateLocation(
            (string) $request->latitude, 
            (string) $request->longitude
        );
        
        Log::info('Rider location updated', [
            'rider_id' => $rider->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);
        
        return response()->json(['success' => true]);
    }

    public function show(Order $order)
    {
        Log::info('=== Delivery Details Show Method Called ===', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'user_id' => Auth::id(),
            'timestamp' => now()->toDateTimeString()
        ]);

        try {
            $rider = Auth::user()->rider;
            
            Log::debug('Rider data', [
                'rider_id' => $rider?->id,
                'rider_exists' => !is_null($rider)
            ]);

            // Verify rider owns this order
            if ($order->rider_id !== $rider->id) {
                Log::warning('Unauthorized access attempt', [
                    'order_rider_id' => $order->rider_id,
                    'current_rider_id' => $rider->id,
                    'order_id' => $order->id,
                    'user_id' => Auth::id()
                ]);
                abort(403, 'Unauthorized');
            }

            Log::info('Authorization passed, loading order details');

            // Load all necessary relationships
            $order->load(['vendor.user', 'customer', 'items.product', 'tracking']);
            
            Log::debug('Relationships loaded', [
                'has_vendor' => !is_null($order->vendor),
                'has_customer' => !is_null($order->customer),
                'items_count' => $order->items->count(),
                'tracking_count' => $order->tracking->count()
            ]);

            // Generate Google Maps URLs for navigation
            $googleMapsUrls = [
                'vendor_location' => $order->vendor && $order->vendor->latitude && $order->vendor->longitude 
                    ? $this->generateDirectionsUrl(
                        $rider->current_latitude, 
                        $rider->current_longitude,
                        $order->vendor->latitude, 
                        $order->vendor->longitude,
                        'Vendor Location'
                    ) : null,
                'customer_location' => $order->delivery_latitude && $order->delivery_longitude 
                    ? $this->generateDirectionsUrl(
                        $order->vendor->latitude, 
                        $order->vendor->longitude,
                        $order->delivery_latitude, 
                        $order->delivery_longitude,
                        'Customer Location'
                    ) : null,
                'customer_direct' => $order->delivery_latitude && $order->delivery_longitude 
                    ? $this->generateLocationUrl(
                        $order->delivery_latitude, 
                        $order->delivery_longitude,
                        'Customer Location'
                    ) : null,
                'rider_to_customer_direct' => $order->delivery_latitude && $order->delivery_longitude && $rider->current_latitude && $rider->current_longitude
                    ? $this->generateDirectionsUrl(
                        $rider->current_latitude,
                        $rider->current_longitude,
                        $order->delivery_latitude,
                        $order->delivery_longitude,
                        'Customer Location (From My Location)'
                    ) : null,
            ];

            // Get customer phone number (prioritize orders.phone, fallback to user phone)
            $customerPhone = $order->phone ?? $order->customer->phone ?? null;

            Log::info('Successfully rendering delivery details view', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_phone' => $customerPhone,
                'view' => 'rider.deliveries-show'
            ]);

            return view('rider.deliveries-show', compact('order', 'rider', 'googleMapsUrls', 'customerPhone'));
            
        } catch (\Exception $e) {
            Log::error('Error in show method', [
                'order_id' => $order->id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Generate Google Maps directions URL
     */
    private function generateDirectionsUrl($fromLat, $fromLng, $toLat, $toLng, $label = 'Destination')
    {
        if (!$toLat || !$toLng) {
            return null;
        }
        
        // If we have starting coordinates, create directions URL
        if ($fromLat && $fromLng) {
            return "https://www.google.com/maps/dir/{$fromLat},{$fromLng}/{$toLat},{$toLng}/?travelmode=driving";
        }
        
        // Otherwise just show the location
        return $this->generateLocationUrl($toLat, $toLng, $label);
    }

    /**
     * Generate Google Maps location URL
     */
    private function generateLocationUrl($latitude, $longitude, $label = 'Location')
    {
        if (!$latitude || !$longitude) {
            return null;
        }
        
        return "https://www.google.com/maps/search/?api=1&query={$latitude},{$longitude}";
    }
}