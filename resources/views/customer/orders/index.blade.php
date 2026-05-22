@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold mb-1">My Orders</h2>
            <p class="text-muted">Track and manage all your orders</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('customer.products.index') }}" class="btn btn-sm" style="background-color: var(--primary-green); color: white;">
                <i class="bi bi-plus-lg me-1"></i> Continue Shopping
            </a>
        </div>
    </div>

    @if($orders->isEmpty())
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            <strong>No orders yet!</strong> Start by <a href="{{ route('customer.products.index') }}">browsing our products</a>.
        </div>
    @else
        <!-- Filter Tabs -->
        <div class="mb-4">
            @php
                $pendingCount = $orders->where('status', 'pending')->count();
                $deliveredCount = $orders->where('status', 'delivered')->count();
                $cancelledCount = $orders->where('status', 'cancelled')->count();
            @endphp
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active fw-bold" href="#all" data-bs-toggle="tab">All Orders ({{ $orders->count() }})</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold" href="#pending" data-bs-toggle="tab">
                        <i class="bi bi-hourglass-split"></i> Pending ({{ $pendingCount }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold" href="#delivered" data-bs-toggle="tab">
                        <i class="bi bi-check-circle"></i> Delivered ({{ $deliveredCount }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold" href="#cancelled" data-bs-toggle="tab">
                        <i class="bi bi-x-circle"></i> Cancelled ({{ $cancelledCount }})
                    </a>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <!-- All Orders -->
            <div class="tab-pane fade show active" id="all">
                @foreach($orders as $order)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="row g-3">
                                {{-- In the order card loop --}}
                                <div class="col-md-2">
                                    <div class="bg-light rounded p-3" style="height: 100px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                        @php
                                            $firstItem = $order->items->first();
                                        @endphp
                                        @if($firstItem && $firstItem->product->image)
                                            <img src="{{ asset('storage/' . $firstItem->product->image) }}" alt="{{ $firstItem->product->name }}" style="width: 100%; height: 100%; object-fit: fill;">
                                        @else
                                            <p class="text-muted small text-center mb-0">{{ $order->items->count() }} item{{ $order->items->count() !== 1 ? 's' : '' }}</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="col-md-7">
                                    <div class="mb-2">
                                        <h5 class="fw-bold mb-1">Order #{{ $order->order_number }}</h5>
                                        <p class="text-muted small mb-2">Placed on {{ $order->created_at->format('M d, Y \a\t g:i A') }}</p>
                                    </div>

                                    <div class="d-flex flex-wrap gap-3 mb-3">
                                        <div>
                                            <p class="text-muted small mb-1">Items</p>
                                            <p class="fw-bold">{{ $order->items->count() }} item{{ $order->items->count() !== 1 ? 's' : '' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-muted small mb-1">Status</p>
                                            @if($order->status === 'pending')
                                                <span class="badge bg-info text-white">
                                                    <i class="bi bi-clock"></i> Pending
                                                </span>
                                            @elseif($order->status === 'processing')
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-gear"></i> Processing
                                                </span>
                                            @elseif($order->status === 'in_transit')
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-truck"></i> In Transit
                                                </span>
                                            @elseif($order->status === 'delivered')
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Delivered
                                                </span>
                                            @elseif($order->status === 'cancelled')
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle"></i> Cancelled
                                                </span>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-muted small mb-1">Vendor</p>
                                            <p class="fw-bold">{{ $order->vendor->business_name ?? 'N/A' }}</p>
                                        </div>
                                    </div>

                                    <p class="small text-muted mb-0">
                                        <i class="bi bi-map-pin"></i> Delivery to: {{ $order->ward }}, {{ $order->county }}
                                    </p>
                                </div>
                                <div class="col-md-3 text-md-end">
                                    <h5 class="fw-bold" style="color: var(--primary-green);">KES {{ number_format($order->total, 0) }}</h5>
                                    <p class="small text-muted mb-3">Total Amount</p>
                                    <div class="d-grid gap-2">
                                        @if($order->status !== 'cancelled')
                                            <a href="{{ route('customer.orders.track', $order->id) }}" class="btn btn-sm" style="background-color: var(--primary-green); color: white;">
                                                Track Order
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                                Cancelled
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if($orders->isEmpty())
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        No orders found.
                    </div>
                @endif
            </div>

            <!-- Pending Orders -->
            <div class="tab-pane fade" id="pending">
                @php $pendingOrders = $orders->where('status', 'pending'); @endphp
                @forelse($pendingOrders as $order)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <div class="bg-light rounded p-3" style="height: 100px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                        @php
                                            $firstItem = $order->items->first();
                                        @endphp
                                        @if($firstItem && $firstItem->product->image)
                                            <img src="{{ asset('storage/' . $firstItem->product->image) }}" alt="{{ $firstItem->product->name }}" style="width: 100%; height: 100%; object-fit: fill;">
                                        @else
                                            <p class="text-muted small text-center mb-0">{{ $order->items->count() }} item{{ $order->items->count() !== 1 ? 's' : '' }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <h5 class="fw-bold mb-1">Order #{{ $order->order_number }}</h5>
                                    <p class="text-muted small mb-2">Placed on {{ $order->created_at->format('M d, Y \a\t g:i A') }}</p>
                                    <p class="small text-muted mb-0">
                                        <i class="bi bi-map-pin"></i> {{ $order->ward }}, {{ $order->county }}
                                    </p>
                                </div>
                                <div class="col-md-3 text-md-end">
                                    <h5 class="fw-bold" style="color: var(--primary-green);">KES {{ number_format($order->total, 0) }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-info" role="alert">
                        No pending orders.
                    </div>
                @endforelse
            </div>

            <!-- Delivered Orders -->
            <div class="tab-pane fade" id="delivered">
                @php $deliveredOrders = $orders->where('status', 'delivered'); @endphp
                @forelse($deliveredOrders as $order)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <div class="bg-light rounded p-3" style="height: 100px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                        @php
                                            $firstItem = $order->items->first();
                                        @endphp
                                        @if($firstItem && $firstItem->product->image)
                                            <img src="{{ asset('storage/' . $firstItem->product->image) }}" alt="{{ $firstItem->product->name }}" style="width: 100%; height: 100%; object-fit: fill;">
                                        @else
                                            <p class="text-muted small text-center mb-0">{{ $order->items->count() }} item{{ $order->items->count() !== 1 ? 's' : '' }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <h5 class="fw-bold mb-1">Order #{{ $order->order_number }}</h5>
                                    <p class="text-muted small mb-2">Placed on {{ $order->created_at->format('M d, Y \a\t g:i A') }}</p>
                                    <p class="small text-muted mb-0">
                                        <i class="bi bi-map-pin"></i> {{ $order->ward }}, {{ $order->county }}
                                    </p>
                                </div>
                                <div class="col-md-3 text-md-end">
                                    <h5 class="fw-bold" style="color: var(--primary-green);">KES {{ number_format($order->total, 0) }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-info" role="alert">
                        No delivered orders.
                    </div>
                @endforelse
            </div>

            <!-- Cancelled Orders -->
            <div class="tab-pane fade" id="cancelled">
                @php $cancelledOrders = $orders->where('status', 'cancelled'); @endphp
                @forelse($cancelledOrders as $order)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <div class="bg-light rounded p-3" style="height: 100px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                        @php
                                            $firstItem = $order->items->first();
                                        @endphp
                                        @if($firstItem && $firstItem->product->image)
                                            <img src="{{ asset('storage/' . $firstItem->product->image) }}" alt="{{ $firstItem->product->name }}" style="width: 100%; height: 100%; object-fit: fill;">
                                        @else
                                            <p class="text-muted small text-center mb-0">{{ $order->items->count() }} item{{ $order->items->count() !== 1 ? 's' : '' }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <h5 class="fw-bold mb-1">Order #{{ $order->order_number }}</h5>
                                    <p class="text-muted small mb-2">Placed on {{ $order->created_at->format('M d, Y \a\t g:i A') }}</p>
                                    <p class="small text-muted mb-0">
                                        <i class="bi bi-map-pin"></i> {{ $order->ward }}, {{ $order->county }}
                                    </p>
                                </div>
                                <div class="col-md-3 text-md-end">
                                    <h5 class="fw-bold" style="color: var(--primary-green);">KES {{ number_format($order->total, 0) }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-info" role="alert">
                        No cancelled orders.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Pagination -->
        @if($orders->hasPages())
           <div class="d-flex justify-content-center mt-4">
                        {{ $orders->links('pagination::bootstrap-5') }}
                    </div>
        @endif
    @endif
</div>
@endsection
