@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="fw-bold mb-4">
        <i class="bi bi-shop"></i> Vendor Dashboard
    </h2>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #05bb14;">
                        <i class="bi bi-box"></i>
                    </div>
                    <h6 class="text-muted mb-2">Total Orders</h6>
                    <h3 class="fw-bold mb-0">{{ $totalOrders ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #237bdd;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h6 class="text-muted mb-2">Pending Orders</h6>
                    <h3 class="fw-bold mb-0">{{ $pendingOrders ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #28a745;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h6 class="text-muted mb-2">Completed</h6>
                    <h3 class="fw-bold mb-0">{{ $completedOrders ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #dc3545;">
                        <i class="bi bi-wallet"></i>
                    </div>
                    <h6 class="text-muted mb-2">Today's Revenue</h6>
                    <h3 class="fw-bold mb-0">KES {{ number_format($todayRevenue ?? 0, 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Recent Orders</h6>
                        <a href="{{ route('vendor.orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    @if($recentOrders && $recentOrders->count())
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                        <tr>
                                            <td><strong>#{{ $order->order_number }}</strong></td>
                                            <td>{{ $order->customer->name }}</td>
                                            <td>{{ $order->items_count ?? 0 }}</td>
                                            <td>
                                                <span class="badge badge-{{ $order->status === 'delivered' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td>KES {{ number_format($order->total ?? $order->subtotal, 0) }}</td>
                                            <td>
                                                <a href="{{ route('vendor.orders.show', $order->id) }}" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4 mb-0">No orders yet</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('vendor.orders.index') }}" class="btn btn-primary">
                            <i class="bi bi-box"></i> Manage Orders
                        </a>
                        <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-basket"></i> My Products
                        </a>
                        <a href="{{ route('vendor.earnings') }}" class="btn btn-outline-primary">
                            <i class="bi bi-graph-up"></i> Earnings
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Shop Status</h6>
                </div>
                <div class="card-body">
                    @php
                        $vendor = Auth::user()->vendor;
                        $isOpen = $vendor->is_open ?? false;
                    @endphp
                    <p class="mb-3">
                        Status: 
                        <span class="badge badge-{{ $isOpen ? 'success' : 'danger' }}">
                            {{ $isOpen ? 'Open' : 'Closed' }}
                        </span>
                    </p>
                    <form method="POST" action="{{ route('vendor.toggle-status') }}" class="d-inline">
                        @csrf
                        @method('POST')
                        <button type="submit" class="btn btn-{{ $isOpen ? 'outline-danger' : 'success' }} btn-sm w-100">
                            <i class="bi bi-{{ $isOpen ? 'lock' : 'unlock' }}"></i> 
                            {{ $isOpen ? 'Close Shop' : 'Open Shop' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
