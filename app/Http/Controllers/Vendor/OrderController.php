<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $vendor = auth()->user()->vendor;
        
        $orders = $vendor->orders()
            ->with(['customer', 'rider.user', 'items.product'])
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

        $order->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $order->notes,
        ]);

        // Create tracking record
        \App\Models\OrderTracking::create([
            'order_id' => $order->id,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'updated_by' => auth()->id(),
            'updated_by_type' => 'vendor',
        ]);

        return redirect()
            ->route('vendor.orders.show', $order)
            ->with('success', 'Order status updated successfully');
    }
}
