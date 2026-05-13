@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('customer.orders') }}" class="text-decoration-none">My Orders</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('customer.orders.show', $order) }}" class="text-decoration-none">Order #{{ $order->order_number }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Track Delivery</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-1">Track Your Order</h2>
                    <p class="text-muted">Order #{{ $order->order_number }}</p>
                </div>
                <span class="badge bg-warning text-dark fs-6" id="status-badge">
                    <i class="bi bi-truck"></i> {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Tracking Map -->
        <div class="col-lg-8">
            <!-- Live Map -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-0">
                    <div style="background-color: #e9ecef; border-radius: 8px; height: 400px; display: flex; align-items: center; justify-content: center; position: relative;">
                        <!-- Map Placeholder -->
                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <div class="text-center text-white">
                                <i class="bi bi-map" style="font-size: 64px;"></i>
                                <p class="mt-3 mb-0">Live Tracking Map</p>
                                <p class="small">Real-time map would display here with rider location</p>
                            </div>
                        </div>

                        <!-- Map Legend -->
                        <div style="position: absolute; bottom: 15px; left: 15px; background: white; border-radius: 8px; padding: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                            <p class="small fw-bold mb-2">Legend</p>
                            <div class="mb-1">
                                <span class="badge bg-success me-2"></span>
                                <small>Pickup Point</small>
                            </div>
                            <div class="mb-1">
                                <span class="badge bg-info me-2"></span>
                                <small>Current Location</small>
                            </div>
                            <div>
                                <span class="badge bg-success me-2"></span>
                                <small>Delivery Point</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery Timeline -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Delivery Timeline</h5>
                </div>
                <div class="card-body">
                    @foreach($timeline as $item)
                        <div class="d-flex mb-4 {{ !$item['completed'] ? 'p-3 rounded' : '' }}" style="{{ !$item['completed'] ? 'background-color: #f8f9fa;' : '' }}">
                            <div class="text-center me-3" style="min-width: 60px;">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold" style="width: 40px; height: 40px; background-color: {{ $item['completed'] ? 'var(--primary-green)' : 'var(--primary-blue)' }};">
                                    <i class="bi {{ $item['icon'] }}"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">{{ $item['status'] }}</h6>
                                <p class="text-muted small mb-1">{{ $item['time']->format('F j, Y') }} at {{ $item['time']->format('g:i A') }}</p>
                                @if(!$item['completed'])
                                    <p class="small mb-0">Your delivery is on the way! Your rider is currently out for delivery.</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Order Summary -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold small mb-2">Delivery Address</h6>
                            <p class="small mb-0">
                                <strong>{{ $order->customer->name }}</strong><br>
                                {{ $order->delivery_address }}<br>
                                {{ $order->ward }}, {{ $order->sub_county }}, {{ $order->county }}<br>
                                <i class="bi bi-phone"></i> {{ $order->customer->phone }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold small mb-2">Items in Order</h6>
                            @foreach($order->items as $item)
                                <p class="small mb-1">• {{ $item->product->name }} (Qty: {{ $item->quantity }}) - KES {{ number_format($item->total, 2) }}</p>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Delivery Coordinates -->
                    <hr>
                    <h6 class="fw-bold small mb-2">Delivery Coordinates</h6>
                    <div class="row g-2">
                        <div class="col-6">
                            <p class="small text-muted mb-1">Latitude</p>
                            <p class="small fw-bold">{{ $order->delivery_latitude }}</p>
                        </div>
                        <div class="col-6">
                            <p class="small text-muted mb-1">Longitude</p>
                            <p class="small fw-bold">{{ $order->delivery_longitude }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Rider Information -->
            <div class="card border-0 shadow-sm mb-4" id="rider-card">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Delivery Rider</h5>
                </div>
                <div class="card-body text-center">
                    @if($riderLocation)
                        <div class="mb-3">
                            @if($riderLocation['profile_pic'])
                                <img src="{{ asset('storage/' . $riderLocation['profile_pic']) }}" alt="{{ $riderLocation['name'] }}" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                            @else
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold" style="width: 80px; height: 80px; background-color: var(--primary-green); font-size: 32px;">
                                    <i class="bi bi-person"></i>
                                </div>
                            @endif
                        </div>
                        <h6 class="fw-bold mb-1" id="rider-name">{{ $riderLocation['name'] }}</h6>
                        <div class="d-flex justify-content-center gap-1 mb-3">
                            @for($i = 0; $i < floor($riderLocation['rating']); $i++)
                                <i class="bi bi-star-fill text-warning" style="font-size: 14px;"></i>
                            @endfor
                            @if($riderLocation['rating'] - floor($riderLocation['rating']) > 0)
                                <i class="bi bi-star-half text-warning" style="font-size: 14px;"></i>
                            @endif
                        </div>
                        <p class="small text-muted mb-3" id="rider-rating">{{ number_format($riderLocation['rating'], 1) }} rating ({{ $riderLocation['total_deliveries'] }} deliveries)</p>

                        <div class="d-grid gap-2">
                            <button class="btn btn-sm btn-outline-secondary" type="button">
                                <i class="bi bi-telephone me-1"></i>
                                <a href="tel:{{ $riderLocation['phone'] }}" class="text-decoration-none text-reset">Call Rider</a>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" type="button">
                                <i class="bi bi-chat-dots me-1"></i> Send Message
                            </button>
                        </div>

                        <hr>

                        <h6 class="fw-bold small mb-2">Rider Details</h6>
                        <p class="small text-muted mb-1">
                            <i class="bi bi-telephone"></i> <a href="tel:{{ $riderLocation['phone'] }}" class="text-decoration-none text-reset">{{ $riderLocation['phone'] }}</a>
                        </p>
                        <p class="small text-muted mb-1">
                            <i class="bi bi-envelope"></i> <a href="mailto:{{ $riderLocation['email'] }}" class="text-decoration-none text-reset">{{ $riderLocation['email'] }}</a>
                        </p>
                        <p class="small text-muted mb-1">
                            <i class="bi bi-bicycle"></i> {{ ucfirst($order->rider->vehicle_type) }} Rider
                        </p>
                        <p class="small text-muted mb-0">
                            <i class="bi bi-shield-check"></i> {{ $order->rider->is_verified ? 'Verified & Insured' : 'Pending Verification' }}
                        </p>
                        <p class="small text-muted mt-2" id="location-update-time">
                            @if($riderLocation['updated_at'])
                                Location updated {{ $riderLocation['updated_at']->diffForHumans() }}
                            @else
                                Location tracking not yet started
                            @endif
                        </p>
                    @else
                        <p class="text-muted">No rider assigned yet. We'll update you once a rider is assigned to your order.</p>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Actions</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('customer.orders.invoice', $order) }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                        <i class="bi bi-download me-1"></i> Download Invoice
                    </a>
                </div>
            </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderId = {{ $order->id }};
    const riderCardElement = document.getElementById('rider-card');
    
    // Function to update rider location and details
    function updateRiderLocation() {
        fetch(`/customer/orders/${orderId}/rider-location`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.log('No rider assigned yet');
                    return;
                }
                
                // Update rider name
                const riderNameEl = document.getElementById('rider-name');
                if (riderNameEl) {
                    riderNameEl.textContent = data.name;
                }
                
                // Update rider rating
                const riderRatingEl = document.getElementById('rider-rating');
                if (riderRatingEl) {
                    riderRatingEl.textContent = `${parseFloat(data.rating).toFixed(1)} rating (${data.total_deliveries} deliveries)`;
                }
                
                // Update location update time
                const locationUpdateEl = document.getElementById('location-update-time');
                if (locationUpdateEl) {
                    if (data.updated_at) {
                        const updatedAt = new Date(data.updated_at);
                        const now = new Date();
                        const diffMs = now - updatedAt;
                        const diffMins = Math.floor(diffMs / 60000);
                        
                        if (diffMins < 1) {
                            locationUpdateEl.textContent = 'Location updated now';
                        } else if (diffMins < 60) {
                            locationUpdateEl.textContent = `Location updated ${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
                        } else {
                            const diffHours = Math.floor(diffMins / 60);
                            locationUpdateEl.textContent = `Location updated ${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
                        }
                    } else {
                        locationUpdateEl.textContent = 'Location tracking not yet started';
                    }
                }
                
                console.log('Rider location updated:', data);
            })
            .catch(error => console.error('Error updating rider location:', error));
    }
    
    // Update rider location immediately and then every 30 seconds
    updateRiderLocation();
    setInterval(updateRiderLocation, 30000);
});
</script>
@endsection
