@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-receipt"></i> Order #{{ $order->order_number }}</h1>
        <div>
            <a href="{{ route('admin.orders') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Order Information -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Order Number</dt>
                                <dd class="col-sm-8">{{ $order->order_number }}</dd>
                                
                                <dt class="col-sm-4">Status</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-{{ $order->status == 'delivered' ? 'success' : ($order->status == 'cancelled' ? 'danger' : 'warning') }}">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </dd>
                                
                                <dt class="col-sm-4">Payment Status</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : 'warning' }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </dd>
                                
                                <dt class="col-sm-4">Payment Method</dt>
                                <dd class="col-sm-8">{{ ucfirst($order->payment_method) }}</dd>
                                
                                <dt class="col-sm-4">Created At</dt>
                                <dd class="col-sm-8">{{ $order->created_at->format('Y-m-d H:i:s') }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Customer</dt>
                                <dd class="col-sm-8">{{ $order->customer->name ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-4">Customer Email</dt>
                                <dd class="col-sm-8">{{ $order->customer->email ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-4">Customer Phone</dt>
                                <dd class="col-sm-8">{{ $order->customer->phone ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-4">Vendor</dt>
                                <dd class="col-sm-8">{{ $order->vendor->business_name ?? 'N/A' }}</dd>
                                
                                <dt class="col-sm-4">Rider</dt>
                                <dd class="col-sm-8">{{ $order->rider->user->name ?? 'Unassigned' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Size</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->size ?? '-' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>KES {{ number_format($item->unit_price, 2) }}</td>
                                <td>KES {{ number_format($item->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                <td>KES {{ number_format($order->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Delivery Fee:</strong></td>
                                <td>KES {{ number_format($order->delivery_fee, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Platform Fee:</strong></td>
                                <td>KES {{ number_format($order->platform_fee, 2) }}</td>
                            </tr>
                            <tr class="table-active">
                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                <td><strong>KES {{ number_format($order->total, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Delivery Address -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Delivery Address</h5>
                </div>
                <div class="card-body">
                    <p><strong>Address:</strong> {{ $order->delivery_address }}</p>
                    <p><strong>Location:</strong> {{ $order->ward }}, {{ $order->sub_county }}, {{ $order->county }}</p>
                    @if($order->delivery_latitude && $order->delivery_longitude)
                        <p><strong>Coordinates:</strong> {{ $order->delivery_latitude }}, {{ $order->delivery_longitude }}</p>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $order->delivery_latitude }},{{ $order->delivery_longitude }}" 
                           target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-geo-alt"></i> View on Map
                        </a>
                    @endif
                    @if($order->special_instructions)
                        <div class="mt-3">
                            <strong>Special Instructions:</strong>
                            <p class="text-muted mb-0">{{ $order->special_instructions }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Order Timeline -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Timeline</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @if($order->created_at)
                            <div class="list-group-item">
                                <small class="text-muted">{{ $order->created_at->format('Y-m-d H:i:s') }}</small>
                                <p class="mb-0"><i class="bi bi-check-circle-fill text-success"></i> Order Placed</p>
                            </div>
                        @endif
                        @if($order->confirmed_at)
                            <div class="list-group-item">
                                <small class="text-muted">{{ $order->confirmed_at->format('Y-m-d H:i:s') }}</small>
                                <p class="mb-0"><i class="bi bi-check-circle-fill text-success"></i> Order Confirmed</p>
                            </div>
                        @endif
                        @if($order->prepared_at)
                            <div class="list-group-item">
                                <small class="text-muted">{{ $order->prepared_at->format('Y-m-d H:i:s') }}</small>
                                <p class="mb-0"><i class="bi bi-box-seam-fill text-info"></i> Order Prepared</p>
                            </div>
                        @endif
                        @if($order->picked_up_at)
                            <div class="list-group-item">
                                <small class="text-muted">{{ $order->picked_up_at->format('Y-m-d H:i:s') }}</small>
                                <p class="mb-0"><i class="bi bi-truck text-primary"></i> Picked Up by Rider</p>
                            </div>
                        @endif
                        @if($order->delivered_at)
                            <div class="list-group-item">
                                <small class="text-muted">{{ $order->delivered_at->format('Y-m-d H:i:s') }}</small>
                                <p class="mb-0"><i class="bi bi-flag-checkered-fill text-success"></i> Delivered</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection