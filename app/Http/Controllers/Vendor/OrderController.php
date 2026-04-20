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
            ->with(['customer', 'rider', 'items'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('vendor.orders.index', [
            'orders' => $orders,
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
            'notes' => ['nullable', 'string'],
        ]);

        $order->updateStatus($validated['status'], $validated['notes'] ?? null);

        return redirect()->back()->with('success', 'Order status updated');
    }
}
