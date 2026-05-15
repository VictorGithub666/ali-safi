@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    <h1 class="mb-4"><i class="bi bi-person-badge"></i> Assign Riders to Orders</h1>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4"><input type="text" name="search" class="form-control" placeholder="Search order or customer..." value="{{ request('search') }}"></div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Order ID</th><th>Customer</th><th>Vendor</th><th>Total</th><th>Items</th><th>Date</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td><strong>#{{ $order->id }}</strong></td>
                            <td>{{ $order->customer->name }}</td>
                            <td>{{ $order->vendor->business_name }}</td>
                            <td>KES {{ number_format($order->total_price, 2) }}</td>
                            <td><span class="badge bg-info">{{ $order->items->count() }}</span></td>
                            <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                            <td><a href="{{ route('admin.orders.select-rider') }}?order_id={{ $order->id }}" class="btn btn-sm btn-success"><i class="bi bi-person-check"></i> Assign</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4">No orders ready for pickup</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-center mt-4">{{ $orders->links() }}</div>
</div>
@endsection