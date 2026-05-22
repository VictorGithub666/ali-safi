<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\Rider;
use App\Models\AdminRiderFee;
use App\Models\OrderTracking;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminOrderAssignmentController extends Controller
{
    /**
     * Display list of orders ready for pickup
     */
    public function index(Request $request)
    {
        Log::info('=== AdminOrderAssignmentController@index START ===', [
            'timestamp' => now()->toDateTimeString(),
            'user_id' => auth()->id(),
            'request_params' => $request->all()
        ]);

        try {
            $query = Order::where('status', 'ready_for_pickup')->with('customer', 'vendor', 'items');

            Log::debug('Initial query built', [
                'base_query' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            if ($request->filled('search')) {
                $search = $request->get('search');
                Log::info('Applying search filter', ['search_term' => $search]);
                
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%$search%")
                      ->orWhereHas('customer', function ($cq) use ($search) {
                          $cq->where('name', 'like', "%$search%");
                      });
                });
            }

            if ($request->filled('vendor_id')) {
                $vendorId = $request->get('vendor_id');
                Log::info('Applying vendor filter', ['vendor_id' => $vendorId]);
                $query->where('vendor_id', $vendorId);
            }

            $orders = $query->latest()->paginate(15);
            
            Log::info('Orders retrieved successfully', [
                'total_orders' => $orders->total(),
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'orders_count' => $orders->count()
            ]);

            return view('admin.orders.assignment', compact('orders'));
            
        } catch (\Exception $e) {
            Log::error('Error in index method', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Failed to load orders: ' . $e->getMessage());
        }
    }

    /**
     * Get available riders for a specific order
     */
    public function getAvailableRiders(Request $request)
    {
        Log::info('=== AdminOrderAssignmentController@getAvailableRiders START ===', [
            'timestamp' => now()->toDateTimeString(),
            'user_id' => auth()->id(),
            'full_url' => $request->fullUrl(),
            'all_params' => $request->all(),
            'query_params' => $request->query(),
            'method' => $request->method(),
            'path' => $request->path()
        ]);

        try {
            $orderId = $request->query('order_id');
            Log::info('Order ID extracted', ['order_id' => $orderId, 'type' => gettype($orderId)]);
            
            if (!$orderId) {
                Log::warning('No order_id provided in request', [
                    'available_params' => array_keys($request->all())
                ]);
                
                return redirect()->route('admin.orders.assignment')
                    ->with('error', 'No order specified for rider assignment. Please select an order first.');
            }

            $order = Order::with(['customer', 'vendor'])->find($orderId);
            Log::info('Order lookup result', [
                'order_found' => !is_null($order),
                'order_id' => $orderId,
                'order_exists' => Order::where('id', $orderId)->exists()
            ]);
            
            if (!$order) {
                Log::error('Order not found', ['order_id' => $orderId]);
                
                return redirect()->route('admin.orders.assignment')
                    ->with('error', "The specified order (ID: {$orderId}) was not found.");
            }

            Log::info('Order details retrieved', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'order_status' => $order->status,
                'customer_name' => $order->customer->name ?? 'N/A',
                'vendor_name' => $order->vendor->business_name ?? 'N/A',
                'order_total' => $order->total
            ]);

            // Query available riders
            Log::info('Querying available riders');
            $ridersQuery = Rider::where('is_verified', true)
                ->where('is_available', true)
                ->with('user');
            
            Log::debug('Riders query SQL', [
                'sql' => $ridersQuery->toSql(),
                'bindings' => $ridersQuery->getBindings()
            ]);
            
            $riders = $ridersQuery->withCount('orders')->get();
            
            Log::info('Available riders retrieved', [
                'total_riders' => $riders->count(),
                'riders_list' => $riders->map(function($rider) {
                    return [
                        'id' => $rider->id,
                        'name' => $rider->user->name ?? 'N/A',
                        'vehicle_type' => $rider->vehicle_type,
                        'is_verified' => $rider->is_verified,
                        'is_available' => $rider->is_available,
                        'orders_count' => $rider->orders_count
                    ];
                })->toArray()
            ]);

            if ($riders->isEmpty()) {
                Log::warning('No available riders found', [
                    'verification_status' => Rider::where('is_verified', true)->count(),
                    'availability_status' => Rider::where('is_available', true)->count(),
                    'total_riders' => Rider::count()
                ]);
            }

            // Log view data being passed
            $viewData = [
                'riders' => $riders,
                'order' => $order
            ];
            
            Log::info('Rendering select-rider view', [
                'view_path' => 'admin.orders.select-rider',
                'data_keys' => array_keys($viewData),
                'riders_count' => $riders->count(),
                'order_id' => $order->id
            ]);

            return view('admin.orders.select-rider', compact('riders', 'order'));
            
        } catch (\Exception $e) {
            Log::error('CRITICAL ERROR in getAvailableRiders', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'request_url' => $request->fullUrl(),
                'request_params' => $request->all()
            ]);
            
            return redirect()->route('admin.orders.assignment')
                ->with('error', 'Failed to load riders: ' . $e->getMessage());
        }
    }

    /**
     * Assign a single order to a rider
     */
    public function assign(Request $request)
    {
        Log::info('=== AdminOrderAssignmentController@assign START ===', [
            'timestamp' => now()->toDateTimeString(),
            'user_id' => auth()->id(),
            'request_data' => $request->all()
        ]);

        try {
            $validated = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'rider_id' => 'required|exists:riders,id',
                'distance_km' => 'required|numeric|min:0',
                'base_fee' => 'required|numeric|min:0',
                'per_km_fee' => 'required|numeric|min:0',
                'bonus' => 'nullable|numeric|min:0'
            ]);

            Log::info('Validation passed', ['validated_data' => $validated]);

            $order = Order::with(['customer', 'vendor.user'])->find($validated['order_id']);
            $rider = Rider::with('user')->find($validated['rider_id']);

            if (!$order) {
                Log::error('Order not found during assignment', ['order_id' => $validated['order_id']]);
                return back()->with('error', 'Order not found');
            }

            if (!$rider) {
                Log::error('Rider not found during assignment', ['rider_id' => $validated['rider_id']]);
                return back()->with('error', 'Rider not found');
            }

            // Calculate total rider fee
            $totalRiderFee = $validated['base_fee'] + 
                            ($validated['distance_km'] * $validated['per_km_fee']) + 
                            ($validated['bonus'] ?? 0);

            Log::info('Order and Rider found', [
                'order_id' => $order->id,
                'order_status' => $order->status,
                'rider_id' => $rider->id,
                'rider_name' => $rider->user->name ?? 'N/A',
                'calculated_fee' => $totalRiderFee
            ]);

            // Create AdminRiderFee record
            $adminFee = AdminRiderFee::create([
                'rider_id' => $rider->id,
                'order_id' => $order->id,
                'base_fee' => $validated['base_fee'],
                'per_km_fee' => $validated['per_km_fee'],
                'distance_km' => $validated['distance_km'],
                'bonus' => $validated['bonus'] ?? 0,
                'status' => 'pending'
            ]);

            Log::info('AdminRiderFee created', ['admin_fee_id' => $adminFee->id]);

            // Update order - FIX: Update delivery_fee to the calculated rider fee
            $order->update([
                'rider_id' => $rider->id,
                'status' => 'picked_up',
                'delivery_fee' => $totalRiderFee  // ← THIS IS THE FIX
            ]);

            Log::info('Order updated', [
                'order_id' => $order->id,
                'new_status' => 'picked_up',
                'assigned_rider_id' => $rider->id,
                'delivery_fee_updated_to' => $totalRiderFee
            ]);

            // Create tracking record
            $tracking = OrderTracking::create([
                'order_id' => $order->id,
                'status' => 'picked_up',
                'notes' => 'Assigned to rider: ' . ($rider->user->name ?? 'Rider #' . $rider->id)
            ]);

            Log::info('OrderTracking created', ['tracking_id' => $tracking->id]);

            return redirect()->route('admin.orders.assignment')
                ->with('success', "Rider {$rider->user->name} assigned to Order #{$order->order_number} successfully with delivery fee of KES " . number_format($totalRiderFee, 2));
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation failed in assign method', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            Log::error('CRITICAL ERROR in assign method', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return back()->with('error', 'Failed to assign rider: ' . $e->getMessage());
        }
    }

    /**
     * Batch assign multiple orders to a single rider
     */
   public function batchAssign(Request $request)
    {
        Log::info('=== AdminOrderAssignmentController@batchAssign START ===', [
            'timestamp' => now()->toDateTimeString(),
            'user_id' => auth()->id(),
            'order_ids_count' => count($request->input('order_ids', [])),
            'rider_id' => $request->input('rider_id')
        ]);

        try {
            $validated = $request->validate([
                'order_ids' => 'required|array',
                'order_ids.*' => 'exists:orders,id',
                'rider_id' => 'required|exists:riders,id',
                'base_fee' => 'required|numeric|min:0',
                'per_km_fee' => 'required|numeric|min:0',
                'distance_km' => 'required|numeric|min:0',
                'bonus' => 'nullable|numeric|min:0'
            ]);

            Log::info('Validation passed for batch assign', [
                'order_ids' => $validated['order_ids'],
                'rider_id' => $validated['rider_id']
            ]);

            $rider = Rider::with('user')->find($validated['rider_id']);
            
            if (!$rider) {
                Log::error('Rider not found for batch assignment', ['rider_id' => $validated['rider_id']]);
                return back()->with('error', 'Rider not found');
            }

            // Calculate total rider fee for each order
            $totalRiderFee = $validated['base_fee'] + 
                            ($validated['distance_km'] * $validated['per_km_fee']) + 
                            ($validated['bonus'] ?? 0);

            $assignedCount = 0;
            $failedOrders = [];

            foreach ($validated['order_ids'] as $order_id) {
                try {
                    $order = Order::find($order_id);
                    
                    if (!$order) {
                        Log::warning('Order not found in batch', ['order_id' => $order_id]);
                        $failedOrders[] = $order_id;
                        continue;
                    }

                    AdminRiderFee::create([
                        'rider_id' => $rider->id,
                        'order_id' => $order->id,
                        'base_fee' => $validated['base_fee'],
                        'per_km_fee' => $validated['per_km_fee'],
                        'distance_km' => $validated['distance_km'],
                        'bonus' => $validated['bonus'] ?? 0,
                        'status' => 'pending'
                    ]);

                    // FIX: Update delivery_fee when batch assigning
                    $order->update([
                        'rider_id' => $rider->id, 
                        'status' => 'picked_up',
                        'delivery_fee' => $totalRiderFee  // ← ADD THIS LINE
                    ]);

                    OrderTracking::create([
                        'order_id' => $order->id,
                        'status' => 'picked_up',
                        'notes' => 'Batch assigned to rider: ' . ($rider->user->name ?? 'Rider #' . $rider->id)
                    ]);

                    $assignedCount++;
                    Log::info('Order assigned in batch', ['order_id' => $order_id, 'delivery_fee' => $totalRiderFee]);
                    
                } catch (\Exception $e) {
                    Log::error('Failed to assign order in batch', [
                        'order_id' => $order_id,
                        'error' => $e->getMessage()
                    ]);
                    $failedOrders[] = $order_id;
                }
            }

            $message = "{$assignedCount} orders assigned to rider {$rider->user->name} successfully with delivery fee of KES " . number_format($totalRiderFee, 2);
            if (!empty($failedOrders)) {
                $message .= ". Failed orders: " . implode(', ', $failedOrders);
            }

            Log::info('Batch assign completed', [
                'assigned_count' => $assignedCount,
                'failed_count' => count($failedOrders),
                'failed_orders' => $failedOrders
            ]);

            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            Log::error('CRITICAL ERROR in batchAssign', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Failed to batch assign orders: ' . $e->getMessage());
        }
    }

    /**
     * Reassign an order to a different rider
     */
    public function reassign(Request $request, Order $order)
    {
        Log::info('=== AdminOrderAssignmentController@reassign START ===', [
            'timestamp' => now()->toDateTimeString(),
            'user_id' => auth()->id(),
            'order_id' => $order->id,
            'current_rider_id' => $order->rider_id,
            'request_data' => $request->all()
        ]);

        try {
            $validated = $request->validate([
                'rider_id' => 'required|exists:riders,id',
                'distance_km' => 'required|numeric|min:0',
                'base_fee' => 'required|numeric|min:0',
                'per_km_fee' => 'required|numeric|min:0',
                'bonus' => 'nullable|numeric|min:0'
            ]);

            $oldRiderId = $order->rider_id;
            $rider = Rider::with('user')->find($validated['rider_id']);

            // Calculate new rider fee
            $newRiderFee = $validated['base_fee'] + 
                        ($validated['distance_km'] * $validated['per_km_fee']) + 
                        ($validated['bonus'] ?? 0);

            Log::info('Reassign details', [
                'old_rider_id' => $oldRiderId,
                'new_rider_id' => $rider->id,
                'new_rider_name' => $rider->user->name ?? 'N/A',
                'new_delivery_fee' => $newRiderFee
            ]);

            // Delete old fee records
            $deletedCount = AdminRiderFee::where('order_id', $order->id)->delete();
            Log::info('Deleted old rider fee records', ['deleted_count' => $deletedCount]);

            // Create new fee record
            $adminFee = AdminRiderFee::create([
                'rider_id' => $rider->id,
                'order_id' => $order->id,
                'base_fee' => $validated['base_fee'],
                'per_km_fee' => $validated['per_km_fee'],
                'distance_km' => $validated['distance_km'],
                'bonus' => $validated['bonus'] ?? 0,
                'status' => 'pending'
            ]);

            Log::info('New AdminRiderFee created', ['admin_fee_id' => $adminFee->id]);

            // Update order - FIX: Update delivery_fee when reassigning
            $order->update([
                'rider_id' => $rider->id,
                'delivery_fee' => $newRiderFee  // ← ADD THIS LINE
            ]);

            // Create tracking record
            $tracking = OrderTracking::create([
                'order_id' => $order->id,
                'status' => 'picked_up',
                'notes' => 'Reassigned from rider ID: ' . ($oldRiderId ?? 'none') . ' to: ' . ($rider->user->name ?? 'Rider #' . $rider->id) . ' with new delivery fee of KES ' . number_format($newRiderFee, 2)
            ]);

            Log::info('Order reassigned successfully', [
                'order_id' => $order->id,
                'old_rider_id' => $oldRiderId,
                'new_rider_id' => $rider->id,
                'tracking_id' => $tracking->id,
                'delivery_fee_updated' => $newRiderFee
            ]);

            return back()->with('success', "Order #{$order->order_number} reassigned to {$rider->user->name} with delivery fee of KES " . number_format($newRiderFee, 2));
            
        } catch (\Exception $e) {
            Log::error('CRITICAL ERROR in reassign', [
                'order_id' => $order->id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Failed to reassign rider: ' . $e->getMessage());
        }
    }

    /**
     * Cancel rider assignment for an order
     */
    public function cancelAssignment(Request $request, Order $order)
    {
        Log::info('=== AdminOrderAssignmentController@cancelAssignment START ===', [
            'timestamp' => now()->toDateTimeString(),
            'user_id' => auth()->id(),
            'order_id' => $order->id,
            'current_rider_id' => $order->rider_id,
            'current_status' => $order->status
        ]);

        try {
            $oldRiderId = $order->rider_id;
            
            // Delete fee records
            $deletedCount = AdminRiderFee::where('order_id', $order->id)->delete();
            Log::info('Deleted rider fee records', ['deleted_count' => $deletedCount]);

            // Update order
            $order->update([
                'rider_id' => null,
                'status' => 'ready_for_pickup'
            ]);

            Log::info('Order updated', [
                'order_id' => $order->id,
                'new_status' => 'ready_for_pickup',
                'rider_id_set_to_null' => true
            ]);

            // Create tracking record
            $tracking = OrderTracking::create([
                'order_id' => $order->id,
                'status' => 'ready_for_pickup',
                'notes' => 'Rider assignment cancelled for rider ID: ' . ($oldRiderId ?? 'none')
            ]);

            Log::info('Assignment cancelled successfully', [
                'order_id' => $order->id,
                'old_rider_id' => $oldRiderId,
                'tracking_id' => $tracking->id
            ]);

            return back()->with('success', "Rider assignment for Order #{$order->order_number} has been cancelled");
            
        } catch (\Exception $e) {
            Log::error('CRITICAL ERROR in cancelAssignment', [
                'order_id' => $order->id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Failed to cancel rider assignment: ' . $e->getMessage());
        }
    }
}