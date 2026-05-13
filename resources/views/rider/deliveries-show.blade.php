@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Main Content Column -->
        <div class="col-lg-8">
            <!-- Order Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-receipt me-2"></i>Order Details
                        </h5>
                        <span class="badge bg-{{ $order->status == 'delivered' ? 'success' : ($order->status == 'cancelled' ? 'danger' : 'warning') }} fs-6">
                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Order Information</h6>
                            <p class="mb-1"><strong>Order #:</strong> {{ $order->order_number }}</p>
                            <p class="mb-1"><strong>Placed:</strong> {{ $order->created_at->format('M d, Y h:i A') }}</p>
                            <p class="mb-1"><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
                            <p class="mb-1">
                                <strong>Payment Status:</strong> 
                                <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : 'warning' }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </p>
                            @if($order->special_instructions)
                                <p class="mb-0"><strong>Special Instructions:</strong> {{ $order->special_instructions }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Customer Information</h6>
                            <p class="mb-1"><strong>Name:</strong> {{ $order->customer->name }}</p>
                            <p class="mb-1">
                                <strong>Phone:</strong> 
                                @if($customerPhone ?? $order->phone ?? $order->customer->phone)
                                    <a href="tel:{{ $order->phone ?? $order->customer->phone }}" class="text-decoration-none">
                                        <i class="bi bi-telephone-fill text-success me-1"></i>
                                        <strong>{{ $order->phone ?? $order->customer->phone }}</strong>
                                    </a>
                                @else
                                    <span class="text-muted">Not provided</span>
                                @endif
                            </p>
                            <p class="mb-1"><strong>Email:</strong> {{ $order->customer->email }}</p>
                            <p class="mb-0"><strong>Delivery Address:</strong> {{ $order->delivery_address }}</p>
                            @if($order->county || $order->sub_county || $order->ward)
                                <p class="mb-0 mt-1">
                                    <strong>Location:</strong> 
                                    {{ $order->county }}{{ $order->sub_county ? ', ' . $order->sub_county : '' }}{{ $order->ward ? ', ' . $order->ward : '' }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pickup Location (Vendor) -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-shop me-2"></i>Pickup Location
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Vendor Details</h6>
                            <p class="mb-1"><strong>{{ $order->vendor->business_name }}</strong></p>
                            <p class="mb-1">{{ $order->vendor->business_address }}</p>
                            <p class="mb-1"><strong>Phone:</strong> {{ $order->vendor->business_phone }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Location Coordinates</h6>
                            @if($order->vendor->latitude && $order->vendor->longitude)
                                <p class="mb-2">
                                    <code>
                                        Lat: {{ $order->vendor->latitude }}, 
                                        Lng: {{ $order->vendor->longitude }}
                                    </code>
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $order->vendor->latitude }},{{ $order->vendor->longitude }}" 
                                       target="_blank" 
                                       class="btn btn-success"
                                       rel="noopener noreferrer">
                                        <i class="bi bi-geo-alt-fill me-1"></i> Open in Google Maps (Pickup)
                                    </a>
                                    @if($rider->current_latitude && $rider->current_longitude)
                                        <a href="https://www.google.com/maps/dir/{{ $rider->current_latitude }},{{ $rider->current_longitude }}/{{ $order->vendor->latitude }},{{ $order->vendor->longitude }}/" 
                                           target="_blank" 
                                           class="btn btn-outline-success"
                                           rel="noopener noreferrer">
                                            <i class="bi bi-navigation me-1"></i> Navigate from My Location
                                        </a>
                                    @endif
                                </div>
                            @else
                                <p class="text-muted">No coordinates available</p>
                            @endif
                            @if(isset($order->distance_to_vendor_formatted))
                                <div class="mt-3 alert alert-info py-2">
                                    <i class="bi bi-rulers me-1"></i> 
                                    <strong>Distance to pickup:</strong> {{ $order->distance_to_vendor_formatted }}
                                    @if(isset($order->eta_to_vendor))
                                        <br><i class="bi bi-clock me-1"></i> 
                                        <strong>ETA:</strong> {{ $order->eta_to_vendor }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery Location (Customer) -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-house-door me-2"></i>Delivery Location
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Delivery Address</h6>
                            <p class="mb-1">{{ $order->delivery_address }}</p>
                            @if($order->county || $order->sub_county || $order->ward)
                                <p class="mb-1">
                                    <strong>Location:</strong> 
                                    {{ $order->county }}{{ $order->sub_county ? ', ' . $order->sub_county : '' }}{{ $order->ward ? ', ' . $order->ward : '' }}
                                </p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Location Coordinates</h6>
                            @if($order->delivery_latitude && $order->delivery_longitude)
                                <p class="mb-2">
                                    <code>
                                        Lat: {{ $order->delivery_latitude }}, 
                                        Lng: {{ $order->delivery_longitude }}
                                    </code>
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $order->delivery_latitude }},{{ $order->delivery_longitude }}" 
                                       target="_blank" 
                                       class="btn btn-primary"
                                       rel="noopener noreferrer">
                                        <i class="bi bi-geo-alt-fill me-1"></i> Open in Google Maps (Delivery)
                                    </a>
                                    @if($order->vendor->latitude && $order->vendor->longitude)
                                        <a href="https://www.google.com/maps/dir/{{ $order->vendor->latitude }},{{ $order->vendor->longitude }}/{{ $order->delivery_latitude }},{{ $order->delivery_longitude }}/" 
                                           target="_blank" 
                                           class="btn btn-outline-primary"
                                           rel="noopener noreferrer">
                                            <i class="bi bi-signpost-2 me-1"></i> Directions from Vendor to Customer
                                        </a>
                                    @endif
                                </div>
                            @else
                                <p class="text-muted">No coordinates available</p>
                            @endif
                            @if(isset($order->delivery_distance_formatted))
                                <div class="mt-3 alert alert-info py-2">
                                    <i class="bi bi-rulers me-1"></i> 
                                    <strong>Delivery distance:</strong> {{ $order->delivery_distance_formatted }}
                                    @if(isset($order->eta_delivery))
                                        <br><i class="bi bi-clock me-1"></i> 
                                        <strong>Estimated delivery time:</strong> {{ $order->eta_delivery }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-box-seam me-2"></i>Order Items
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
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
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="col-lg-4">
            <!-- Delivery Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-truck me-2"></i>Delivery Actions
                    </h5>
                </div>
                <div class="card-body">
                    @if($order->status === 'ready_for_pickup')
                        <form action="{{ route('rider.deliveries.accept', $order) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning w-100 mb-2" onclick="return confirm('Have you picked up the order from the vendor?')">
                                <i class="bi bi-check-circle me-1"></i> Confirm Pickup
                            </button>
                        </form>
                    @elseif($order->status === 'picked_up')
                        <form action="{{ route('rider.deliveries.complete', $order) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">Payment Received?</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="payment_received" id="payment_yes" value="1" autocomplete="off" checked>
                                    <label class="btn btn-outline-success" for="payment_yes">
                                        <i class="bi bi-check-circle me-1"></i> Yes, Paid
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="payment_received" id="payment_no" value="0" autocomplete="off">
                                    <label class="btn btn-outline-danger" for="payment_no">
                                        <i class="bi bi-x-circle me-1"></i> Not Paid
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">Delivery Notes (Optional)</label>
                                <textarea class="form-control" name="notes" rows="2" placeholder="Any issues or notes about this delivery..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Mark this order as delivered?')">
                                <i class="bi bi-flag-checkered me-1"></i> Complete Delivery
                            </button>
                        </form>
                    @elseif($order->status === 'delivered')
                        <div class="alert alert-success mb-0">
                            <i class="bi bi-check-circle-fill me-1"></i> 
                            This delivery has been completed successfully.
                        </div>
                    @elseif($order->status === 'cancelled')
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-x-circle-fill me-1"></i> 
                            This order has been cancelled.
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-clock-history me-1"></i> 
                            Waiting for vendor to prepare the order.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Earnings Summary -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-cash-stack me-2"></i>Earnings Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <h3 class="text-success mb-0">KES {{ number_format($order->delivery_fee, 2) }}</h3>
                        <small class="text-muted">Delivery Fee</small>
                    </div>
                    <hr>
                    <div class="small text-muted">
                        <i class="bi bi-info-circle me-1"></i> 
                        Earnings will be added to your wallet after successful delivery.
                    </div>
                </div>
            </div>

            <!-- Customer Contact -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-telephone me-2"></i>Contact Customer
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @php
                            $customerPhone = $order->phone ?? $order->customer->phone ?? null;
                        @endphp
                        @if($customerPhone)
                            <a href="tel:{{ $customerPhone }}" class="btn btn-outline-primary btn-lg">
                                <i class="bi bi-phone me-2"></i> Call Customer
                            </a>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customerPhone) }}" 
                               target="_blank" class="btn btn-outline-success">
                                <i class="bi bi-whatsapp me-2"></i> WhatsApp
                            </a>
                            <div class="alert alert-info mt-2 mb-0 py-2 text-center">
                                <i class="bi bi-telephone-forward me-1"></i>
                                <strong>Phone:</strong> {{ $customerPhone }}
                            </div>
                        @else
                            <div class="alert alert-warning mb-0 text-center">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                No phone number available for this customer.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Copy Coordinates Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-files me-2"></i>Quick Copy Coordinates
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($order->vendor->latitude && $order->vendor->longitude)
                            <button onclick="copyToClipboard('{{ $order->vendor->latitude }}, {{ $order->vendor->longitude }}', 'Pickup')" 
                                    class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-geo-alt me-1"></i> Copy Pickup Coordinates
                            </button>
                        @endif
                        @if($order->delivery_latitude && $order->delivery_longitude)
                            <button onclick="copyToClipboard('{{ $order->delivery_latitude }}, {{ $order->delivery_longitude }}', 'Delivery')" 
                                    class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-house me-1"></i> Copy Delivery Coordinates
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(coordinates, type) {
    navigator.clipboard.writeText(coordinates).then(function() {
        // Show temporary success message
        const toast = document.createElement('div');
        toast.className = 'alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3';
        toast.style.zIndex = '9999';
        toast.style.minWidth = '300px';
        toast.innerHTML = '<i class="bi bi-check-circle me-2"></i>' + type + ' coordinates copied: ' + coordinates;
        document.body.appendChild(toast);
        setTimeout(function() {
            toast.remove();
        }, 2000);
    }).catch(function(err) {
        console.error('Failed to copy: ', err);
        alert('Failed to copy coordinates. Please copy manually.');
    });
}
</script>
@endsection