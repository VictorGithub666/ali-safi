<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $vendor = auth()->user()->vendor;
        
        $orders = $vendor->orders()
            ->with(['customer', 'rider.user', 'items.product'])
            ->withCount('items')
            ->when(request('status'), function($q) {
                return $q->where('status', request('status'));
            })
            ->when(request('search'), function($q) {
                return $q->where('order_number', 'like', '%' . request('search') . '%')
                         ->orWhereHas('customer', function($sq) {
                             $sq->where('name', 'like', '%' . request('search') . '%');
                         });
            })
            ->orderByDesc('created_at')
            ->paginate(15);

        $statuses = ['pending', 'confirmed', 'preparing', 'ready_for_pickup', 'picked_up', 'delivered', 'cancelled'];

        return view('vendor.orders.index', [
            'orders' => $orders,
            'statuses' => $statuses,
        ]);
    }

    public function show(Order $order)
    {
        $vendor = auth()->user()->vendor;
        
        if ($order->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this order');
        }

        $order->load(['customer', 'rider.user', 'items.product', 'tracking']);
        $order->loadCount('items');

        return view('vendor.orders.show', [
            'order' => $order,
            'vendor' => $vendor,
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $vendor = auth()->user()->vendor;
        
        if ($order->vendor_id !== $vendor->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,preparing,ready_for_pickup,picked_up,delivered,cancelled'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $order->status;
            
            $order->update([
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? $order->notes,
            ]);

            // Set timestamp based on status
            if ($validated['status'] === 'confirmed') {
                $order->update(['confirmed_at' => now()]);
            } elseif ($validated['status'] === 'preparing') {
                $order->update(['prepared_at' => now()]);
            } elseif ($validated['status'] === 'picked_up') {
                $order->update(['picked_up_at' => now()]);
            } elseif ($validated['status'] === 'delivered') {
                $order->update(['delivered_at' => now()]);
                
                // ========================================
                // FIX 1: Reduce product stock
                // ========================================
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
                        
                        \Log::info('Product stock updated after delivery', [
                            'order_id' => $order->id,
                            'product_id' => $item->product_id,
                            'old_stock' => $currentStock,
                            'quantity_sold' => $item->quantity,
                            'new_stock' => $newStock,
                        ]);
                    }
                }
                
                // Update vendor wallet (existing code)
                $amountToAdd = $order->subtotal;
                $vendor->wallet_balance = $vendor->wallet_balance + $amountToAdd;
                $vendor->total_orders = $vendor->total_orders + 1;
                $vendor->save();
                
                \Log::info('Vendor wallet updated - Order Delivered', [
                    'vendor_id' => $vendor->id,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'amount_added' => $amountToAdd,
                    'old_balance' => $vendor->getOriginal('wallet_balance'),
                    'new_balance' => $vendor->wallet_balance
                ]);
            } elseif ($validated['status'] === 'cancelled') {
                $order->update(['cancelled_at' => now()]);
            }

            // Create tracking record (existing code)
            $trackingData = [
                'order_id' => $order->id,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'updated_by' => auth()->id(),
                'updated_by_type' => 'vendor',
            ];

            if ($order->rider && $order->rider->current_latitude && $order->rider->current_longitude) {
                $trackingData['latitude'] = $order->rider->current_latitude;
                $trackingData['longitude'] = $order->rider->current_longitude;
            } elseif ($vendor->latitude && $vendor->longitude) {
                $trackingData['latitude'] = $vendor->latitude;
                $trackingData['longitude'] = $vendor->longitude;
            }

            \App\Models\OrderTracking::create($trackingData);

            DB::commit();

            return redirect()
                ->route('vendor.orders.show', $order)
                ->with('success', 'Order status updated successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update order status', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to update order status. Please try again.');
        }
    }
}