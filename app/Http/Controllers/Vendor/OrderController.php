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
            ->withCount('items') // Add this
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
        $order->loadCount('items'); // Add this

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
            } elseif ($validated['status'] === 'cancelled') {
                $order->update(['cancelled_at' => now()]);
            }

            // FIX: Update vendor wallet when order is delivered
            if ($validated['status'] === 'delivered' && $oldStatus !== 'delivered') {
                // Use subtotal (vendor's earnings before fees)
                $amountToAdd = $order->subtotal;
                
                // Update vendor wallet and total orders
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
            }

            // Create tracking record
            \App\Models\OrderTracking::create([
                'order_id' => $order->id,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'updated_by' => auth()->id(),
                'updated_by_type' => 'vendor',
            ]);

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