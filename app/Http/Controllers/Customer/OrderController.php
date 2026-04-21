<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\Cart;
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
        
        return view('customer.orders.show', compact('order'));
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
        \Log::info('User ID (Debug field):', ['id' => $request->input('debug_user_id')]);
        \Log::info('Cart count (Debug field):', ['count' => $request->input('debug_cart_count')]);
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
                        ->with('product')
                        ->get();

        \Log::info('Cart query executed', [
            'user_id' => $userId,
            'cart_count' => $cartItems->count(),
            'total_carts_in_db' => Cart::count(),
        ]);

        if ($cartItems->isEmpty()) {
            \Log::warning('CART EMPTY ERROR', [
                'user_id' => $userId,
                'all_user_carts' => Cart::where('user_id', $userId)->count(),
                'all_carts' => Cart::all()->pluck('user_id')->toArray(),
            ]);
            return back()->with('error', 'Your cart is empty');
        }

        DB::beginTransaction();
        try {
            \Log::info('Starting order creation process');
            
            // Group cart items by vendor
            $itemsByVendor = $cartItems->groupBy('vendor_id');
            \Log::info('Items grouped by vendor', ['grouped_count' => $itemsByVendor->count()]);
            
            foreach ($itemsByVendor as $vendorId => $items) {
                \Log::info('Processing vendor', ['vendor_id' => $vendorId, 'items_count' => $items->count()]);
                
                $vendor = Vendor::findOrFail($vendorId);
                \Log::info('Vendor found', ['vendor_id' => $vendor->id, 'business_name' => $vendor->business_name]);
                
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
                
                \Log::info('Order created successfully', ['order_id' => $order->id]);

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

                // Match with nearest available rider
                $rider = $this->matchingService->findNearestRider($vendor);
                if ($rider) {
                    $order->update(['rider_id' => $rider->id]);
                    \Log::info('Rider assigned', ['order_id' => $order->id, 'rider_id' => $rider->id]);
                    // TODO: Notify rider when event is implemented
                    // event(new NewOrderAssigned($order, $rider));
                }

                // TODO: Notify vendor when event is implemented
                // event(new NewOrderReceived($order, $vendor));

                // Send M-Pesa prompt if payment method is M-Pesa
                if ($request->payment_method === 'mpesa' && $request->mpesa_number) {
                    $this->sendMpesaPrompt($order, $request->mpesa_number);
                }
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
            return back()->with('error', 'Failed to place order. Please try again.');
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
        
        // Get the latest rider location with user details
        $riderLocation = null;
        if ($order->rider && $order->rider->current_latitude) {
            $order->rider->load('user');
            $riderLocation = [
                'lat' => $order->rider->current_latitude,
                'lng' => $order->rider->current_longitude,
                'updated_at' => $order->rider->last_location_update ?? now(), // Default to now if not set
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
        
        return view('customer.orders.track', compact('order', 'riderLocation', 'timeline'));
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

        // Generate PDF using a simple HTML-to-PDF approach
        // For now, we'll return a view that can be printed to PDF
        return view('customer.orders.invoice', $data);
    }

    private function getOrderTimeline($order)
    {
        $timeline = [];

        // Order Confirmed
        if ($order->confirmed_at) {
            $timeline[] = [
                'status' => 'Order Confirmed',
                'time' => $order->confirmed_at,
                'icon' => 'bi bi-check-circle',
                'completed' => true
            ];
        } elseif ($order->created_at) {
            // If not explicitly confirmed, use created time
            $timeline[] = [
                'status' => 'Order Confirmed',
                'time' => $order->created_at,
                'icon' => 'bi bi-check-circle',
                'completed' => true
            ];
        }

        // Order Picked Up
        if ($order->picked_up_at) {
            $timeline[] = [
                'status' => 'Order Picked Up',
                'time' => $order->picked_up_at,
                'icon' => 'bi bi-box-seam',
                'completed' => true
            ];
        }

        // Out for Delivery (In Transit)
        if ($order->status === 'in_transit') {
            $timeline[] = [
                'status' => 'Out for Delivery',
                'time' => now(),
                'icon' => 'bi bi-truck',
                'completed' => false
            ];
        } elseif ($order->picked_up_at && !$order->delivered_at) {
            // If picked up but not delivered, it's in transit
            $timeline[] = [
                'status' => 'Out for Delivery',
                'time' => now(),
                'icon' => 'bi bi-truck',
                'completed' => false
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

        // If no timeline events yet, show order confirmed as pending
        if (empty($timeline) && $order->created_at) {
            $timeline[] = [
                'status' => 'Order Confirmed',
                'time' => $order->created_at,
                'icon' => 'bi bi-check-circle',
                'completed' => false
            ];
        }

        return $timeline;
    }

    protected function calculateDeliveryFee($vendor, $customerLat, $customerLng)
    {
        $distance = $this->calculateDistance(
            $vendor->latitude,
            $vendor->longitude,
            $customerLat,
            $customerLng
        );
        
        // Base fee + per km charge
        $baseFee = 50;
        $perKmRate = 20;
        
        return $baseFee + ($distance * $perKmRate);
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

            // Call M-Pesa service to initiate STK Push
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

                // Update order with payment reference
                $order->update([
                    'payment_reference' => $result['data']['CheckoutRequestID'] ?? 'MPESA-' . $order->id . '-' . time(),
                    'payment_status' => 'pending',
                ]);

                // Notify customer via log (can be extended to send SMS)
                \Log::info('M-Pesa prompt notification', [
                    'customer_phone' => $order->phone,
                    'amount' => $order->total,
                    'order_number' => $order->order_number,
                    'message' => "An M-Pesa prompt has been sent to {$mpesaNumber}. Please enter your M-Pesa PIN to complete the payment.",
                ]);

            } else {
                \Log::error('Failed to send M-Pesa STK Push', [
                    'order_id' => $order->id,
                    'error' => $result['message'],
                    'details' => $result['error'] ?? null,
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Error in sendMpesaPrompt', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}