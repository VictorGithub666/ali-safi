@extends('layouts.app')

@section('content')
<div class="container-fluid py-5">
    <h2 class="fw-bold mb-4">
        <i class="bi bi-speedometer2"></i> Admin Dashboard
    </h2>

    @if(session()->has('admin_notifications'))
    @foreach(session('admin_notifications') as $notification)
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-whatsapp me-2"></i>
        <strong>New Order Alert!</strong> 
        <a href="{{ $notification }}" target="_blank" class="alert-link">
            Click here to view order details on WhatsApp
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endforeach
    @endif

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
                        <i class="bi bi-shop"></i>
                    </div>
                    <h6 class="text-muted mb-2">Active Vendors</h6>
                    <h3 class="fw-bold mb-0">{{ $activeVendors ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #28a745;">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h6 class="text-muted mb-2">Active Riders</h6>
                    <h3 class="fw-bold mb-0">{{ $activeRiders ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #dc3545;">
                        <i class="bi bi-wallet"></i>
                    </div>
                    <h6 class="text-muted mb-2">Total Revenue</h6>
                    <h3 class="fw-bold mb-0">KES {{ number_format($totalRevenue ?? 0, 0) }}</h3>
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
                        <a href="{{ route('admin.orders') }}" class="btn btn-sm btn-outline-primary">View All</a>
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
                                        <th>Vendor</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                        <tr>
                                            <td><strong>#{{ $order->order_number }}</strong></td>
                                            <td>{{ $order->customer->name ?? 'N/A' }}</td>
                                            <td>{{ $order->vendor->business_name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                                </span>
                                            </td>
                                            <td>KES {{ number_format($order->total, 0) }}</td>
                                            <td>
                                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </td>
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
                        <a href="{{ route('admin.orders') }}" class="btn btn-primary">
                            <i class="bi bi-box"></i> Manage Orders
                        </a>
                        <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-shop"></i> Manage Vendors
                        </a>
                        <a href="{{ route('admin.riders.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-truck"></i> Manage Riders
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Platform Stats</h6>
                </div>
                <div class="card-body small">
                    <p class="mb-2">
                        <strong>Total Users:</strong> {{ $totalUsers ?? 0 }}
                    </p>
                    <p class="mb-2">
                        <strong>Total Orders:</strong> {{ $stats['total_orders'] ?? 0 }}
                    </p>
                    <p class="mb-2">
                        <strong>Completed Orders:</strong> {{ $completedOrders ?? 0 }}
                    </p>
                    <p class="mb-2">
                        <strong>Active Vendors:</strong> {{ $activeVendors ?? 0 }}
                    </p>
                    <p class="mb-2">
                        <strong>Active Riders:</strong> {{ $stats['active_riders'] ?? 0 }}
                    </p>
                    <p class="mb-0">
                        <strong>Commission Rate:</strong> {{ $commissionRate ?? 5 }}%
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
