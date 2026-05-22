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
use Illuminate\Support\Facades\Log;

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
        \Illuminate\Support\Facades\Log::info('=== ORDER STORE STARTED ===');
        \Illuminate\Support\Facades\Log::info('Request Data:', $request->all());
        \Illuminate\Support\Facades\Log::info('User ID:', ['id' => Auth::id()]);
        \Illuminate\Support\Facades\Log::info('Authenticated:', ['check' => Auth::check()]);
        
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
        
        \Illuminate\Support\Facades\Log::info('Validation passed:', $validated);

        $userId = Auth::id();
        
        // Check if user is authenticated
        if (!$userId) {
            \Illuminate\Support\Facades\Log::error('User not authenticated!');
            return back()->with('error', 'You must be logged in to place an order');
        }

        // Query cart
        $cartItems = Cart::where('user_id', $userId)
                            ->with(['product', 'vendor'])
                            ->get();

        \Illuminate\Support\Facades\Log::info('Cart query executed', [
            'user_id' => $userId,
            'cart_count' => $cartItems->count(),
        ]);

        if ($cartItems->isEmpty()) {
            \Illuminate\Support\Facades\Log::warning('CART EMPTY ERROR', [
                'user_id' => $userId,
            ]);
            return back()->with('error', 'Your cart is empty');
        }

        DB::beginTransaction();
        try {
            \Illuminate\Support\Facades\Log::info('Starting order creation process');
            
            // Group cart items by vendor
            $itemsByVendor = $cartItems->groupBy('vendor_id');
            \Illuminate\Support\Facades\Log::info('Items grouped by vendor', ['grouped_count' => $itemsByVendor->count()]);
            
            $orders = []; // Store created orders for response
            
            foreach ($itemsByVendor as $vendorId => $items) {
                \Illuminate\Support\Facades\Log::info('Processing vendor', ['vendor_id' => $vendorId, 'items_count' => $items->count()]);
                
                $vendor = Vendor::findOrFail($vendorId);
                \Illuminate\Support\Facades\Log::info('Vendor found', ['vendor_id' => $vendor->id, 'business_name' => $vendor->business_name]);
                
                // Check if vendor is open
                if (!$vendor->is_open) {
                    DB::rollBack();
                    return back()->with('error', $vendor->business_name . ' is currently closed and cannot accept orders. Please remove their items from your cart.');
                }
                
                // Calculate totals
                $subtotal = $items->sum(function($item) {
                    return $item->price * $item->quantity;
                });
                
                \Illuminate\Support\Facades\Log::info('Calculating delivery fee', ['vendor_id' => $vendorId, 'lat' => $request->delivery_latitude, 'lng' => $request->delivery_longitude]);
                $deliveryFee = $this->calculateDeliveryFee($vendor, $request->delivery_latitude, $request->delivery_longitude);
                $platformFee = 0; // No platform fee
                $total = $subtotal + $deliveryFee;

            
                // Create order (NO RIDER ASSIGNMENT YET)
                \Illuminate\Support\Facades\Log::info('Creating order', ['customer_id' => Auth::id(), 'vendor_id' => $vendorId]);
                $order = Order::create([
                    'customer_id' => Auth::id(),
                    'vendor_id' => $vendorId,
                    'county' => $request->county,
                    'sub_county' => $request->sub_county,
                    'ward' => $request->ward,
                    'delivery_address' => $request->delivery_address,
                    'delivery_latitude' => (string) $request->delivery_latitude,
                    'delivery_longitude' => (string) $request->delivery_longitude,
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'platform_fee' => $platformFee,
                    'total' => $total,
                    'payment_method' => $request->payment_method,
                    'phone' => $request->phone,
                    'mpesa_number' => $request->payment_method === 'mpesa' ? $request->mpesa_number : null,
                    'special_instructions' => $request->special_instructions,
                    'status' => 'pending',
                    // 'rider_id' => null, // Explicitly set rider_id to null
                ]);
                
                \Illuminate\Support\Facades\Log::info('Order created successfully', ['order_id' => $order->id, 'order_number' => $order->order_number]);

                // Create order items with admin pricing
                foreach ($items as $cartItem) {
                    // Re-verify the customer price at time of order using admin pricing
                    $product = Product::find($cartItem->product_id);
                    $finalPrice = $product->getCustomerPriceForSizeAndVendor(
                        $cartItem->size ?? null, 
                        $vendorId
                    );
                    
                    \Illuminate\Support\Facades\Log::info('Creating order item', [
                        'cart_item_id' => $cartItem->id, 
                        'product_id' => $cartItem->product_id,
                        'price_used' => $finalPrice,
                        'original_price' => $cartItem->price
                    ]);
                    
                    $order->items()->create([
                        'product_id' => $cartItem->product_id,
                        'quantity' => $cartItem->quantity,
                        'unit_price' => $finalPrice,
                        'size' => $cartItem->size,
                        'total' => $finalPrice * $cartItem->quantity,
                    ]);
                }
                
                \Illuminate\Support\Facades\Log::info('Order items created successfully', ['order_id' => $order->id]);

                // Create initial tracking record with customer's delivery location
                $order->tracking()->create([
                    'status' => 'pending',
                    'notes' => 'Order placed successfully. Waiting for vendor confirmation.',
                    'latitude' => $request->delivery_latitude,
                    'longitude' => $request->delivery_longitude,
                    'updated_by' => Auth::id(),
                    'updated_by_type' => 'customer',
                ]);

                // ============================================================
                // IMPORTANT: RIDER ASSIGNMENT REMOVED FROM THIS LOCATION
                // Riders will only be assigned when the order status becomes 'ready_for_pickup'
                // and a rider manually accepts the order from their dashboard.
                // ============================================================

                // Dispatch OrderPlaced event to notify vendor
                $order->load(['vendor.user', 'customer', 'items.product']);
                event(new OrderPlaced($order));

                \Illuminate\Support\Facades\Log::info('OrderPlaced event dispatched', [
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
            \Illuminate\Support\Facades\Log::info('Clearing cart for user', ['user_id' => Auth::id()]);
            Cart::where('user_id', Auth::id())->delete();

            DB::commit();
            
            \Illuminate\Support\Facades\Log::info('Order placement successful, committing transaction');
            return redirect()->route('customer.orders')
                        ->with('success', 'Order placed successfully!');
                        
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Exception in order placement', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to place order. Please try again. Error: ' . $e->getMessage());
        }
    }

    public function track(Order $order)
    {
        // Ensure the customer can only track their own orders
        if ($order->customer_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Load necessary relationships
        $order->load(['vendor.user', 'rider.user', 'customer', 'items.product', 'tracking']);
        
        // Calculate delivery progress (FIX: Move this BEFORE using it)
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
        
        // Add Google Maps URLs for vendor and customer (helpful for tracking page)
        $order->vendor_location_url = $order->vendor && $order->vendor->latitude && $order->vendor->longitude 
            ? "https://www.google.com/maps/search/?api=1&query={$order->vendor->latitude},{$order->vendor->longitude}" 
            : null;
        
        $order->customer_location_url = $order->delivery_latitude && $order->delivery_longitude 
            ? "https://www.google.com/maps/search/?api=1&query={$order->delivery_latitude},{$order->delivery_longitude}" 
            : null;
        
        // Make sure deliveryProgress is passed to the view
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
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$order->rider) {
            return response()->json(['error' => 'No rider assigned'], 404);
        }

        $order->rider->load('user');
        
        // Generate live tracking URL
        $trackingUrl = null;
        if ($order->rider->current_latitude && $order->rider->current_longitude && 
            $order->delivery_latitude && $order->delivery_longitude) {
            $trackingUrl = $this->generateDirectionsUrl(
                $order->rider->current_latitude,
                $order->rider->current_longitude,
                $order->delivery_latitude,
                $order->delivery_longitude,
                'Track Your Delivery'
            );
        }

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
            'status' => $order->status,
            'tracking_url' => $trackingUrl,
        ]);
    }

    /**
     * Generate Google Maps directions URL
     */
    private function generateDirectionsUrl($fromLat, $fromLng, $toLat, $toLng, $label = 'Destination')
    {
        if (!$toLat || !$toLng) {
            return null;
        }
        
        if ($fromLat && $fromLng) {
            return "https://www.google.com/maps/dir/{$fromLat},{$fromLng}/{$toLat},{$toLng}/?travelmode=driving";
        }
        
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

    private function getOrderTimeline($order)
    {
        $timeline = [];

        if ($order->created_at) {
            $timeline[] = [
                'status' => 'Order Placed',
                'time' => $order->created_at,
                'icon' => 'bi bi-clock-history',
                'completed' => true
            ];
        }

        if ($order->confirmed_at) {
            $timeline[] = [
                'status' => 'Order Confirmed',
                'time' => $order->confirmed_at,
                'icon' => 'bi bi-check-circle',
                'completed' => true
            ];
        }

        if ($order->prepared_at) {
            $timeline[] = [
                'status' => 'Order Prepared',
                'time' => $order->prepared_at,
                'icon' => 'bi bi-box-seam',
                'completed' => true
            ];
        }

        if ($order->picked_up_at) {
            $timeline[] = [
                'status' => 'Order Picked Up',
                'time' => $order->picked_up_at,
                'icon' => 'bi bi-truck',
                'completed' => true
            ];
        }

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


    protected function calculateDeliveryFee($vendor, $customerLat, $customerLng)
    {
        // No delivery fee - return 0
        return 0;
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