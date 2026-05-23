@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="fw-bold mb-4">
        <i class="bi bi-speedometer2"></i> Customer Dashboard
    </h2>

    @if(auth()->user()->google_id)
    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-google me-2"></i>
        <strong>Welcome Google User!</strong> Please complete your profile by adding your phone number and address.
        <a href="{{ route('profile.edit') }}" class="alert-link">Complete Profile</a>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
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
                        <i class="bi bi-cart"></i>
                    </div>
                    <h6 class="text-muted mb-2">Total Spent</h6>
                    <h3 class="fw-bold mb-0">KES {{ number_format($totalSpent ?? 0, 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Recent Orders</h6>
                </div>
                <div class="card-body">
                    @if($recentOrders && $recentOrders->count())
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                        <tr>
                                            <td><strong>#{{ $order->order_number }}</strong></td>
                                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <span class="badge badge-{{ $order->status === 'delivered' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td>KES {{ number_format($order->total_amount, 0) }}</td>
                                            <td>
                                                <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4 mb-0">No orders yet. <a href="{{ route('customer.products') }}">Start shopping!</a></p>
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
                        <a href="{{ route('customer.products') }}" class="btn btn-primary">
                            <i class="bi bi-shop"></i> Browse Products
                        </a>
                        <a href="{{ route('customer.cart') }}" class="btn btn-outline-primary">
                            <i class="bi bi-cart"></i> View Cart
                        </a>
                        <a href="{{ route('customer.orders') }}" class="btn btn-outline-primary">
                            <i class="bi bi-box"></i> All Orders
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Account  Info</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Name:</strong> {{ Auth::user()->name }}
                    </p>
                    <p class="mb-2">
                        <strong>Email:</strong> {{ Auth::user()->email }}
                    </p>
                    <p class="mb-0">
                        <strong>Phone:</strong> {{ Auth::user()->phone ?? 'Not set' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
