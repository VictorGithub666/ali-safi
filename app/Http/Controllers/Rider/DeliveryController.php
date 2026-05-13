<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Rider;
use App\Services\DistanceService;
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
        
        Log::info('Rider data retrieved', [
            'rider_id' => $rider?->id,
            'has_rider' => !is_null($rider),
            'user_id' => $user->id,
            'user_name' => $user->name
        ]);
        
        // Check if rider record exists
        if (!$rider) {
            Log::warning('No rider record found, attempting to create', [
                'user_id' => $user->id
            ]);
            
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
            
            Log::info('Rider record created', [
                'rider_id' => $rider->id,
                'user_id' => $user->id
            ]);
            
            // Refresh the user relationship
            $user->refresh();
            $rider = $user->rider;
        }
        
        // If still no rider record, redirect with error
        if (!$rider) {
            Log::error('Failed to create or retrieve rider record', [
                'user_id' => $user->id
            ]);
            return redirect()->route('profile.edit')
                ->with('error', 'Rider profile not properly configured. Please contact support.');
        }
        
        // Get available orders
        $availableOrders = Order::whereNull('rider_id')
                               ->where('status', 'pending')
                               ->with(['vendor.user', 'customer'])
                               ->latest()
                               ->get()
                               ->map(function ($order) use ($rider) {
                                   // Calculate distance from rider to vendor (pickup point)
                                   if ($rider->current_latitude && $rider->current_longitude && 
                                       $order->vendor && $order->vendor->latitude && $order->vendor->longitude) {
                                       $order->distance_to_vendor = DistanceService::calculateDistance(
                                           $rider->current_latitude,
                                           $rider->current_longitude,
                                           $order->vendor->latitude,
                                           $order->vendor->longitude
                                       );
                                       $order->distance_to_vendor_formatted = DistanceService::formatDistance(
                                           $rider->current_latitude,
                                           $rider->current_longitude,
                                           $order->vendor->latitude,
                                           $order->vendor->longitude
                                       );
                                       $order->eta_to_vendor = DistanceService::estimateDeliveryTime($order->distance_to_vendor);
                                   }
                                   
                                   // Calculate distance from vendor to customer (delivery distance)
                                   if ($order->vendor && $order->vendor->latitude && $order->vendor->longitude &&
                                       $order->delivery_latitude && $order->delivery_longitude) {
                                       $order->delivery_distance = DistanceService::calculateDistance(
                                           $order->vendor->latitude,
                                           $order->vendor->longitude,
                                           $order->delivery_latitude,
                                           $order->delivery_longitude
                                       );
                                       $order->delivery_distance_formatted = DistanceService::formatDistance(
                                           $order->vendor->latitude,
                                           $order->vendor->longitude,
                                           $order->delivery_latitude,
                                           $order->delivery_longitude
                                       );
                                       $order->eta_delivery = DistanceService::estimateDeliveryTime($order->delivery_distance);
                                   }
                                   
                                   // Calculate total distance for the entire delivery
                                   if (isset($order->distance_to_vendor) && isset($order->delivery_distance)) {
                                       $order->total_distance = $order->distance_to_vendor + $order->delivery_distance;
                                       $order->total_distance_formatted = number_format($order->total_distance, 1) . ' km';
                                   }
                                   
                                   return $order;
                               });
                               
        $myDeliveries = Order::where('rider_id', $rider->id)
                            ->whereIn('status', ['picked_up', 'on_the_way'])
                            ->with(['vendor.user', 'customer'])
                            ->latest()
                            ->get();
                            
        $completedToday = Order::where('rider_id', $rider->id)
                              ->where('status', 'delivered')
                              ->whereDate('delivered_at', today())
                              ->count();
        
        Log::info('Rider dashboard data loaded', [
            'rider_id' => $rider->id,
            'available_orders_count' => $availableOrders->count(),
            'active_deliveries_count' => $myDeliveries->count(),
            'completed_today' => $completedToday
        ]);
        
        return view('rider.dashboard', compact(
            'availableOrders',
            'myDeliveries',
            'rider',
            'completedToday'
        ));
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
        
        if ($order->rider_id) {
            Log::warning('Order already assigned', [
                'order_id' => $order->id,
                'assigned_rider_id' => $order->rider_id
            ]);
            return redirect()->back()->with('error', 'Order already assigned');
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
        
        Log::info('Order accepted successfully', [
            'order_id' => $order->id,
            'rider_id' => $rider->id
        ]);
        
        return redirect()->route('rider.deliveries.show', $order)
            ->with('success', 'Order picked up successfully! Please deliver to the customer.');
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
        
        \Log::info('Rider location updated', [
            'rider_id' => $rider->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'precision' => strlen(substr(strrchr((string)$request->latitude, "."), 1))
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

            // Calculate distance information
            if ($rider->current_latitude && $rider->current_longitude && 
                $order->vendor && $order->vendor->latitude && $order->vendor->longitude) {
                $order->distance_to_vendor = DistanceService::calculateDistance(
                    $rider->current_latitude,
                    $rider->current_longitude,
                    $order->vendor->latitude,
                    $order->vendor->longitude
                );
                $order->distance_to_vendor_formatted = DistanceService::formatDistance(
                    $rider->current_latitude,
                    $rider->current_longitude,
                    $order->vendor->latitude,
                    $order->vendor->longitude
                );
                
                Log::debug('Distance to vendor calculated', [
                    'distance' => $order->distance_to_vendor_formatted
                ]);
            }

            if ($order->vendor && $order->vendor->latitude && $order->vendor->longitude &&
                $order->delivery_latitude && $order->delivery_longitude) {
                $order->delivery_distance = DistanceService::calculateDistance(
                    $order->vendor->latitude,
                    $order->vendor->longitude,
                    $order->delivery_latitude,
                    $order->delivery_longitude
                );
                $order->delivery_distance_formatted = DistanceService::formatDistance(
                    $order->vendor->latitude,
                    $order->vendor->longitude,
                    $order->delivery_latitude,
                    $order->delivery_longitude
                );
                
                Log::debug('Delivery distance calculated', [
                    'distance' => $order->delivery_distance_formatted
                ]);
            }

            // Generate Google Maps URLs
            $googleMapsUrls = [
                'vendor_location' => $order->vendor && $order->vendor->latitude && $order->vendor->longitude 
                    ? $this->generateNavigationUrl(
                        $rider->current_latitude, 
                        $rider->current_longitude,
                        $order->vendor->latitude, 
                        $order->vendor->longitude
                    ) : null,
                'customer_location' => $order->delivery_latitude && $order->delivery_longitude 
                    ? $this->generateNavigationUrl(
                        $order->vendor->latitude, 
                        $order->vendor->longitude,
                        $order->delivery_latitude, 
                        $order->delivery_longitude
                    ) : null,
                'customer_direct' => $order->delivery_latitude && $order->delivery_longitude 
                    ? $this->generateGoogleMapsUrl($order->delivery_latitude, $order->delivery_longitude) : null,
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
     * Generate Google Maps URL for coordinates
     */
    private function generateGoogleMapsUrl($latitude, $longitude, $locationType = 'destination')
    {
        if (!$latitude || !$longitude) {
            return null;
        }
        
        // Use Google Maps URL scheme that works on both mobile and desktop
        // This will open Google Maps app on mobile or web on desktop
        return "https://www.google.com/maps/search/?api=1&query={$latitude},{$longitude}";
    }

    /**
     * Generate Google Maps navigation URL for directions from current location
     */
    private function generateNavigationUrl($fromLat, $fromLng, $toLat, $toLng, $locationType = 'destination')
    {
        if (!$toLat || !$toLng) {
            return null;
        }
        
        // If rider has current location, use it as starting point
        if ($fromLat && $fromLng) {
            return "https://www.google.com/maps/dir/{$fromLat},{$fromLng}/{$toLat},{$toLng}/";
        }
        
        // Otherwise just show the location
        return "https://www.google.com/maps/search/?api=1&query={$toLat},{$toLng}";
    }
}