@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-box"></i> Orders
            </h2>
            <p class="text-muted mb-0">Manage and track your orders</p>
        </div>
        <a href="{{ route('vendor.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-house"></i> Dashboard
        </a>
    </div>

    <!-- Filter & Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Search by order number or customer name..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($orders->count() > 0)
        <!-- Orders Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Rider</th>
                            <th>Ordered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>
                                    <strong>#{{ $order->order_number }}</strong>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $order->customer->name }}</strong><br>
                                        <small class="text-muted">{{ $order->customer->phone }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $order->items->count() }} item{{ $order->items->count() !== 1 ? 's' : '' }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $baseTotal = $order->items->sum(function($item) {
                                            return $item->product->base_price * $item->quantity;
                                        });
                                    @endphp
                                    <strong>KES {{ number_format($baseTotal, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-{{ 
                                        $order->status == 'delivered' ? 'success' : 
                                        ($order->status == 'cancelled' ? 'danger' : 
                                        ($order->status == 'pending' ? 'light text-dark' : 'warning'))
                                    }}">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($order->rider)
                                        <small>
                                            {{ Str::limit($order->rider->user->name, 15) }}<br>
                                            <span class="text-muted">{{ $order->rider->user->phone }}</span>
                                        </small>
                                    @else
                                        <span class="text-muted small">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $order->created_at->format('M d') }}</small><br>
                                    <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('vendor.orders.show', $order) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
       <div class="d-flex justify-content-center mt-4">
                        {{ $orders->links('pagination::bootstrap-5') }}
                    </div>
    @else
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            <strong>No orders found!</strong> 
            @if(request('search') || request('status'))
                Try adjusting your filters.
            @else
                Orders will appear here once customers place them.
            @endif
        </div>
    @endif
</div>

@endsection
