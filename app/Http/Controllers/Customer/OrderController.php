<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\Cart;
use App\Events\OrderPlaced;  
use App\Services\OrderMatchingService;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected $matchingService;
    protected $mpesaService;

    public function __construct(OrderMatchingService $matchingService, MpesaService $mpesaService)
    {
        $this->matchingService = $matchingService;
        $this->mpesaService = $mpesaService;
    }

    public function index()
    {
        $orders = Order::where('customer_id', Auth::id())
                      ->with(['vendor.user', 'rider.user', 'items.product'])
                      ->latest()
                      ->paginate(10);
        
        return view('customer.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);
        
        $order->load(['vendor.user', 'rider.user', 'items.product', 'tracking']);
        
        $deliveryProgress = $this->calculateDeliveryProgress($order->status);
        
        return view('customer.orders.show', compact('order', 'deliveryProgress'));
    }

    public function checkout()
    {
        $cartItems = Cart::where('user_id', Auth::id())
                        ->with(['product', 'vendor'])
                        ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('customer.products.index')
                        ->with('error', 'Your cart is empty');
        }

        // Check if any vendor is closed
        $closedVendors = $cartItems->filter(function($item) {
            return !$item->vendor->is_open;
        });

        if ($closedVendors->isNotEmpty()) {
            $vendorNames = $closedVendors->pluck('vendor.business_name')->unique()->join(', ');
            return redirect()->route('customer.cart')
                        ->with('error', "The following shops are currently closed: {$vendorNames}. Please remove their items to proceed.");
        }

        $total = $cartItems->sum(function($item) {
            return $item->price * $item->quantity;
        });

        return view('customer.checkout', [
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }

    public function store(Request $request)
    {   
        // Log incoming request
        \Log::info('=== ORDER STORE STARTED ===');
        \Log::info('Request Data:', $request->all());
        \Log::info('User ID:', ['id' => Auth::id()]);
        \Log::info('Authenticated:', ['check' => Auth::check()]);
        
        // Validate with conditional M-Pesa number validation
        $validated = $request->validate([
            'county' => 'required|string',
            'sub_county' => 'required|string',
            'ward' => 'required|string',
            'delivery_address' => 'required|string',
            'delivery_latitude' => 'required|numeric',
            'delivery_longitude' => 'required|numeric',
            'phone' => 'required|string',
            'payment_method' => 'required|in:cash,mpesa',
            'mpesa_number' => 'required_if:payment_method,mpesa|nullable|string|regex:/^254\d{9}$/',
            'special_instructions' => 'nullable|string',
        ], [
            'mpesa_number.required_if' => 'M-Pesa number is required when selecting M-Pesa payment',
            'mpesa_number.regex' => 'M-Pesa number must start with 254 and have exactly 12 digits',
        ]);
        
        \Log::info('Validation passed:', $validated);

        $userId = Auth::id();
        
        // Check if user is authenticated
        if (!$userId) {
            \Log::error('User not authenticated!');
            return back()->with('error', 'You must be logged in to place an order');
        }

        // Query cart
        $cartItems = Cart::where('user_id', $userId)
                        ->with(['product', 'vendor'])
                        ->get();

        \Log::info('Cart query executed', [
            'user_id' => $userId,
            'cart_count' => $cartItems->count(),
        ]);

        if ($cartItems->isEmpty()) {
            \Log::warning('CART EMPTY ERROR', [
                'user_id' => $userId,
            ]);
            return back()->with('error', 'Your cart is empty');
        }

        DB::beginTransaction();
        try {
            \Log::info('Starting order creation process');
            
            // Group cart items by vendor
            $itemsByVendor = $cartItems->groupBy('vendor_id');
            \Log::info('Items grouped by vendor', ['grouped_count' => $itemsByVendor->count()]);
            
            $orders = []; // Store created orders for response
            
            foreach ($itemsByVendor as $vendorId => $items) {
                \Log::info('Processing vendor', ['vendor_id' => $vendorId, 'items_count' => $items->count()]);
                
                $vendor = Vendor::findOrFail($vendorId);
                \Log::info('Vendor found', ['vendor_id' => $vendor->id, 'business_name' => $vendor->business_name]);
                
                // Check if vendor is open
                if (!$vendor->is_open) {
                    DB::rollBack();
                    return back()->with('error', $vendor->business_name . ' is currently closed and cannot accept orders. Please remove their items from your cart.');
                }
                
                // Calculate totals
                $subtotal = $items->sum(function($item) {
                    return $item->price * $item->quantity;
                });
                
                \Log::info('Calculating delivery fee', ['vendor_id' => $vendorId, 'lat' => $request->delivery_latitude, 'lng' => $request->delivery_longitude]);
                $deliveryFee = $this->calculateDeliveryFee($vendor, $request->delivery_latitude, $request->delivery_longitude);
                $platformFee = $subtotal * 0.05; // 5% platform fee
                $total = $subtotal + $deliveryFee + $platformFee;

                \Log::info('Order totals calculated', ['subtotal' => $subtotal, 'delivery_fee' => $deliveryFee, 'platform_fee' => $platformFee, 'total' => $total]);

                // Create order
                \Log::info('Creating order', ['customer_id' => Auth::id(), 'vendor_id' => $vendorId]);
                $order = Order::create([
                    'customer_id' => Auth::id(),
                    'vendor_id' => $vendorId,
                    'county' => $request->county,
                    'sub_county' => $request->sub_county,
                    'ward' => $request->ward,
                    'delivery_address' => $request->delivery_address,
                    'delivery_latitude' => $request->delivery_latitude,
                    'delivery_longitude' => $request->delivery_longitude,
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'platform_fee' => $platformFee,
                    'total' => $total,
                    'payment_method' => $request->payment_method,
                    'phone' => $request->phone,
                    'mpesa_number' => $request->payment_method === 'mpesa' ? $request->mpesa_number : null,
                    'special_instructions' => $request->special_instructions,
                    'status' => 'pending',
                ]);
                
                \Log::info('Order created successfully', ['order_id' => $order->id, 'order_number' => $order->order_number]);

                // Create order items
                foreach ($items as $cartItem) {
                    \Log::info('Creating order item', ['cart_item_id' => $cartItem->id, 'product_id' => $cartItem->product_id]);
                    $order->items()->create([
                        'product_id' => $cartItem->product_id,
                        'quantity' => $cartItem->quantity,
                        'unit_price' => $cartItem->price,
                        'size' => $cartItem->size,
                        'total' => $cartItem->price * $cartItem->quantity,
                    ]);
                }
                
                \Log::info('Order items created successfully', ['order_id' => $order->id]);

                // Create initial tracking record with customer's delivery location
                $order->tracking()->create([
                    'status' => 'pending',
                    'notes' => 'Order placed successfully',
                    'latitude' => $request->delivery_latitude,
                    'longitude' => $request->delivery_longitude,
                    'updated_by' => Auth::id(),
                    'updated_by_type' => 'customer',
                ]);

                // Match with nearest available rider based on vendor location
                $rider = $this->matchingService->findNearestRider($vendor);
                if ($rider) {
                    $order->update(['rider_id' => $rider->id]);
                    
                    // Create tracking record for rider assignment with rider's location
                    $trackingData = [
                        'status' => 'rider_assigned',
                        'notes' => "Rider {$rider->user->name} has been assigned to your order",
                        'updated_by' => Auth::id(),
                        'updated_by_type' => 'system',
                    ];
                    
                    if ($rider->current_latitude && $rider->current_longitude) {
                        $trackingData['latitude'] = $rider->current_latitude;
                        $trackingData['longitude'] = $rider->current_longitude;
                    }
                    
                    $order->tracking()->create($trackingData);
                    
                    \Log::info('Rider assigned', [
                        'order_id' => $order->id, 
                        'rider_id' => $rider->id,
                        'rider_name' => $rider->user->name,
                        'vendor_location' => $vendor->latitude . ',' . $vendor->longitude,
                        'rider_location' => $rider->current_latitude . ',' . $rider->current_longitude
                    ]);
                } else {
                    \Log::warning('No rider available for assignment', [
                        'order_id' => $order->id,
                        'vendor_id' => $vendor->id,
                        'vendor_location' => $vendor->latitude . ',' . $vendor->longitude
                    ]);
                    
                    // Create tracking record for no rider available
                    $order->tracking()->create([
                        'status' => 'pending',
                        'notes' => 'Looking for an available rider...',
                        'latitude' => $vendor->latitude,
                        'longitude' => $vendor->longitude,
                        'updated_by' => Auth::id(),
                        'updated_by_type' => 'system',
                    ]);
                }

                // Dispatch OrderPlaced event
                $order->load(['vendor.user', 'customer', 'items.product']);
                event(new OrderPlaced($order));

                \Log::info('OrderPlaced event dispatched', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'vendor_id' => $vendor->id
                ]);

                // Send M-Pesa prompt if payment method is M-Pesa
                if ($request->payment_method === 'mpesa' && $request->mpesa_number) {
                    $this->sendMpesaPrompt($order, $request->mpesa_number);
                }
                
                $orders[] = $order;
            }

            // Clear cart
            \Log::info('Clearing cart for user', ['user_id' => Auth::id()]);
            Cart::where('user_id', Auth::id())->delete();

            DB::commit();
            
            \Log::info('Order placement successful, committing transaction');
            return redirect()->route('customer.orders')
                           ->with('success', 'Order placed successfully!');
                           
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Exception in order placement', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to place order. Please try again. Error: ' . $e->getMessage());
        }
    }

    // Add this method to your Customer OrderController
    public function track(Order $order)
    {
        // Ensure the customer can only track their own orders
        if ($order->customer_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Load necessary relationships
        $order->load(['vendor.user', 'rider.user', 'customer', 'items.product', 'tracking']);
        
        // Calculate delivery progress
        $deliveryProgress = $this->calculateDeliveryProgress($order->status);
        
        // Add status flags for the progress bar
        $order->order_placed = true;
        $order->confirmed = in_array($order->status, ['confirmed', 'preparing', 'ready_for_pickup', 'picked_up', 'on_the_way', 'delivered']);
        $order->preparing = in_array($order->status, ['preparing', 'ready_for_pickup', 'picked_up', 'on_the_way', 'delivered']);
        $order->ready_for_pickup = in_array($order->status, ['ready_for_pickup', 'picked_up', 'on_the_way', 'delivered']);
        $order->on_the_way = in_array($order->status, ['picked_up', 'on_the_way', 'delivered']);
        $order->delivered = $order->status === 'delivered';
        
        // Human-readable status label
        $order->status_label = $this->getStatusLabel($order->status);
        
        // Get the latest rider location with user details
        $riderLocation = null;
        if ($order->rider && $order->rider->current_latitude) {
            $order->rider->load('user');
            $riderLocation = [
                'lat' => $order->rider->current_latitude,
                'lng' => $order->rider->current_longitude,
                'updated_at' => $order->rider->last_location_update ?? now(),
                'name' => $order->rider->user->name,
                'phone' => $order->rider->user->phone,
                'email' => $order->rider->user->email,
                'profile_pic' => $order->rider->user->profile_picture,
                'rating' => $order->rider->rating,
                'total_deliveries' => $order->rider->total_deliveries,
            ];
        }
        
        // Get order timeline
        $timeline = $this->getOrderTimeline($order);
        
        return view('customer.orders.track', compact('order', 'riderLocation', 'timeline', 'deliveryProgress'));
    }

    /**
     * Calculate delivery progress percentage based on order status
     */
    private function calculateDeliveryProgress($status)
    {
        $progressMap = [
            'pending' => 0,
            'confirmed' => 20,
            'preparing' => 40,
            'ready_for_pickup' => 60,
            'picked_up' => 80,
            'in_transit' => 85,
            'on_the_way' => 85,
            'delivered' => 100
        ];
        
        return $progressMap[$status] ?? 0;
    }

    /**
     * Get human-readable status label
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'pending' => 'Order Placed - Awaiting Confirmation',
            'confirmed' => 'Order Confirmed - Preparing Your Items',
            'preparing' => 'Preparing Your Order',
            'ready_for_pickup' => 'Ready for Pickup',
            'picked_up' => 'Rider Has Picked Up Your Order',
            'in_transit' => 'On The Way to You',
            'on_the_way' => 'On The Way to You',
            'delivered' => 'Delivered - Enjoy Your Order!'
        ];
        
        return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    public function getRiderLocation(Order $order)
    {
        // Ensure the customer can only track their own orders
        if ($order->customer_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$order->rider) {
            return response()->json(['error' => 'No rider assigned'], 404);
        }

        $order->rider->load('user');

        return response()->json([
            'lat' => $order->rider->current_latitude,
            'lng' => $order->rider->current_longitude,
            'updated_at' => $order->rider->last_location_update ? $order->rider->last_location_update->toIso8601String() : null,
            'name' => $order->rider->user->name,
            'phone' => $order->rider->user->phone,
            'email' => $order->rider->user->email,
            'profile_pic' => $order->rider->user->profile_picture,
            'rating' => $order->rider->rating,
            'total_deliveries' => $order->rider->total_deliveries,
        ]);
    }

    public function downloadInvoice(Order $order)
    {
        // Ensure the customer can only download their own invoices
        if ($order->customer_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $order->load(['vendor.user', 'rider.user', 'customer', 'items.product']);

        $data = [
            'order' => $order,
            'customer' => $order->customer,
            'vendor' => $order->vendor,
            'items' => $order->items,
        ];

        return view('customer.orders.invoice', $data);
    }

    private function getOrderTimeline($order)
    {
        $timeline = [];

        // Order Placed
        if ($order->created_at) {
            $timeline[] = [
                'status' => 'Order Placed',
                'time' => $order->created_at,
                'icon' => 'bi bi-clock-history',
                'completed' => true
            ];
        }

        // Order Confirmed
        if ($order->confirmed_at) {
            $timeline[] = [
                'status' => 'Order Confirmed',
                'time' => $order->confirmed_at,
                'icon' => 'bi bi-check-circle',
                'completed' => true
            ];
        }

        // Order Prepared
        if ($order->prepared_at) {
            $timeline[] = [
                'status' => 'Order Prepared',
                'time' => $order->prepared_at,
                'icon' => 'bi bi-box-seam',
                'completed' => true
            ];
        }

        // Order Picked Up
        if ($order->picked_up_at) {
            $timeline[] = [
                'status' => 'Order Picked Up',
                'time' => $order->picked_up_at,
                'icon' => 'bi bi-truck',
                'completed' => true
            ];
        }

        // Delivered
        if ($order->delivered_at) {
            $timeline[] = [
                'status' => 'Delivered',
                'time' => $order->delivered_at,
                'icon' => 'bi bi-check-circle-fill',
                'completed' => true
            ];
        }

        return $timeline;
    }

    protected function calculateDeliveryFee($vendor, $customerLat, $customerLng)
    {
        // Check if vendor has coordinates
        if (!$vendor->latitude || !$vendor->longitude) {
            \Log::warning('Vendor missing coordinates for delivery fee calculation', [
                'vendor_id' => $vendor->id,
                'business_name' => $vendor->business_name
            ]);
            return 100; // Default delivery fee
        }
        
        $distance = $this->calculateDistance(
            $vendor->latitude,
            $vendor->longitude,
            $customerLat,
            $customerLng
        );
        
        // Base fee + per km charge
        $baseFee = 50;
        $perKmRate = 20;
        
        $deliveryFee = $baseFee + ($distance * $perKmRate);
        
        \Log::info('Delivery fee calculated', [
            'vendor_id' => $vendor->id,
            'distance_km' => round($distance, 2),
            'delivery_fee' => round($deliveryFee, 2)
        ]);
        
        return round($deliveryFee);
    }

    protected function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // kilometers
        
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }

    protected function sendMpesaPrompt($order, $mpesaNumber)
    {
        try {
            \Log::info('Initiating M-Pesa STK Push', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'mpesa_number' => $mpesaNumber,
                'amount' => $order->total,
            ]);

            $result = $this->mpesaService->initiateStkPush(
                $mpesaNumber,
                $order->total,
                $order->order_number
            );

            if ($result['success']) {
                \Log::info('M-Pesa STK Push sent successfully', [
                    'order_id' => $order->id,
                    'response' => $result['data'],
                ]);

                $order->update([
                    'payment_reference' => $result['data']['CheckoutRequestID'] ?? 'MPESA-' . $order->id . '-' . time(),
                    'payment_status' => 'pending',
                ]);

            } else {
                \Log::error('Failed to send M-Pesa STK Push', [
                    'order_id' => $order->id,
                    'error' => $result['message'],
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Error in sendMpesaPrompt', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}