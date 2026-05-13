@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('customer.orders') }}">My Orders</a></li>
                    <li class="breadcrumb-item active">Track Order #{{ $order->order_number }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Order Tracking Progress -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title fw-bold mb-0">
                        <i class="bi bi-geo-alt-fill me-2" style="color: var(--primary-green);"></i>
                        Order Tracking
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Progress Percentage -->
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <svg width="120" height="120" viewBox="0 0 120 120">
                                <circle cx="60" cy="60" r="54" fill="none" stroke="#e9ecef" stroke-width="8"/>
                                <circle cx="60" cy="60" r="54" fill="none" stroke="#05bb14" stroke-width="8"
                                        stroke-dasharray="{{ 2 * pi() * 54 }}"
                                        stroke-dashoffset="{{ (2 * pi() * 54) - (($deliveryProgress / 100) * (2 * pi() * 54)) }}"
                                        transform="rotate(-90 60 60)"
                                        stroke-linecap="round"/>
                            </svg>
                            <div class="position-absolute top-50 start-50 translate-middle text-center">
                                <span class="fw-bold" style="font-size: 1.8rem; color: var(--primary-green);">
                                    {{ $deliveryProgress }}%
                                </span>
                            </div>
                        </div>
                        <h6 class="mt-3 mb-0">Delivery Progress</h6>
                        <small class="text-muted">{{ $order->status_label ?? ucfirst($order->status) }}</small>
                    </div>

                    <!-- Progress Bar with Motorcycle Animation -->
                    <div class="tracking-progress mb-5">
                        <div class="progress-line">
                            <div class="progress-track"></div>
                            <div class="progress-fill" style="width: {{ $deliveryProgress }}%;"></div>
                            <div class="motorcycle-icon" style="left: {{ $deliveryProgress }}%;">
                                <i class="bi bi-motorcycle"></i>
                            </div>
                        </div>
                        
                        <div class="progress-stops">
                            <div class="stop-point" data-status="Order Placed">
                                <div class="stop-dot {{ $order->order_placed ? 'active' : '' }}"></div>
                                <div class="stop-label">Order Placed</div>
                            </div>
                            <div class="stop-point" data-status="Confirmed">
                                <div class="stop-dot {{ $order->confirmed ? 'active' : '' }}"></div>
                                <div class="stop-label">Confirmed</div>
                            </div>
                            <div class="stop-point" data-status="Preparing">
                                <div class="stop-dot {{ $order->preparing ? 'active' : '' }}"></div>
                                <div class="stop-label">Preparing</div>
                            </div>
                            <div class="stop-point" data-status="Ready for Pickup">
                                <div class="stop-dot {{ $order->ready_for_pickup ? 'active' : '' }}"></div>
                                <div class="stop-label">Ready</div>
                            </div>
                            <div class="stop-point" data-status="On The Way">
                                <div class="stop-dot {{ $order->on_the_way ? 'active' : '' }}"></div>
                                <div class="stop-label">On The Way</div>
                            </div>
                            <div class="stop-point" data-status="Delivered">
                                <div class="stop-dot {{ $order->delivered ? 'active' : '' }}"></div>
                                <div class="stop-label">Delivered</div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Status Timeline -->
                    <div class="order-timeline mt-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Order Timeline</h6>
                        <div class="timeline-items">
                            @foreach($timeline as $event)
                                <div class="timeline-item {{ $event['completed'] ? 'completed' : '' }}">
                                    <div class="timeline-icon">
                                        <i class="{{ $event['icon'] }}"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">{{ $event['status'] }}</h6>
                                        <small class="text-muted">{{ $event['time']->format('M d, Y H:i A') }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Rider Location Map (if rider assigned and on the way) -->
                    @if($order->rider && in_array($order->status, ['picked_up', 'in_transit', 'on_the_way']))
                        <div class="rider-location mt-4">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-truck me-2" style="color: var(--primary-green);"></i>
                                Rider Location
                            </h6>
                            <div id="riderMap" style="height: 300px; border-radius: 10px; overflow: hidden;"></div>
                            <div class="rider-info mt-3 p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $riderLocation['name'] ?? 'Rider' }}</strong>
                                        <p class="mb-0 small text-muted">
                                            <i class="bi bi-telephone"></i> {{ $riderLocation['phone'] ?? 'Not available' }}
                                        </p>
                                    </div>
                                    <div>
                                        <span class="badge bg-success">
                                            <i class="bi bi-star-fill"></i> {{ $riderLocation['rating'] ?? 'New' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-2 small text-muted">
                                    <i class="bi bi-geo-alt"></i> 
                                    <span id="distanceToCustomer">Calculating distance...</span>
                                    <span id="eta"> | ETA: Calculating...</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Order Summary -->
            <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 100px;">
                <div class="card-header bg-light">
                    <h5 class="card-title fw-bold mb-0">Order Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Order Number</label>
                        <p class="fw-bold mb-0">#{{ $order->order_number }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-muted small">Order Date</label>
                        <p class="mb-0">{{ $order->created_at->format('M d, Y H:i A') }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-muted small">Delivery Address</label>
                        <p class="mb-0">{{ $order->delivery_address }}</p>
                        <small class="text-muted">{{ $order->ward }}, {{ $order->sub_county }}, {{ $order->county }}</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-muted small">Payment Method</label>
                        <p class="mb-0">{{ ucfirst($order->payment_method) }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-muted small">Payment Status</label>
                        <p>
                            <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </p>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <strong>KES {{ number_format($order->subtotal, 0) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Delivery Fee</span>
                            <strong>KES {{ number_format($order->delivery_fee, 0) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Platform Fee</span>
                            <strong>KES {{ number_format($order->platform_fee, 0) }}</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Total</span>
                            <strong class="fw-bold" style="color: var(--primary-green); font-size: 1.2rem;">
                                KES {{ number_format($order->total, 0) }}
                            </strong>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label class="text-muted small">Need Help?</label>
                        <div class="d-grid gap-2 mt-2">
                            <a href="tel:{{ $order->vendor->business_phone }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-telephone"></i> Call Vendor
                            </a>
                            @if($order->rider && $order->rider->user->phone)
                                <a href="tel:{{ $order->rider->user->phone }}" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-truck"></i> Call Rider
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Tracking Progress Bar Styles */
.tracking-progress {
    position: relative;
    padding: 20px 0;
}

.progress-line {
    position: relative;
    height: 6px;
    background: #e9ecef;
    border-radius: 10px;
    margin: 30px 0;
}

.progress-track {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #e9ecef;
    border-radius: 10px;
}

.progress-fill {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: linear-gradient(90deg, #05bb14, #237bdd);
    border-radius: 10px;
    transition: width 0.5s ease;
}

.motorcycle-icon {
    position: absolute;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 40px;
    height: 40px;
    background: white;
    border: 2px solid #05bb14;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: left 0.5s ease;
    z-index: 10;
    box-shadow: 0 2px 10px rgba(5, 187, 20, 0.3);
}

.motorcycle-icon i {
    font-size: 20px;
    color: #05bb14;
}

.progress-stops {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
    position: relative;
}

.stop-point {
    text-align: center;
    flex: 1;
    position: relative;
}

.stop-dot {
    width: 12px;
    height: 12px;
    background: #dee2e6;
    border: 2px solid #adb5bd;
    border-radius: 50%;
    margin: 0 auto 8px;
    transition: all 0.3s ease;
}

.stop-dot.active {
    background: #05bb14;
    border-color: #05bb14;
    box-shadow: 0 0 0 3px rgba(5, 187, 20, 0.2);
    transform: scale(1.2);
}

.stop-label {
    font-size: 0.7rem;
    color: #6c757d;
    font-weight: 500;
}

.stop-dot.active + .stop-label {
    color: #05bb14;
    font-weight: bold;
}

/* Timeline Styles */
.order-timeline {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
}

.timeline-items {
    position: relative;
}

.timeline-item {
    display: flex;
    margin-bottom: 20px;
    position: relative;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 16px;
    top: 32px;
    bottom: -20px;
    width: 2px;
    background: #dee2e6;
}

.timeline-icon {
    width: 32px;
    height: 32px;
    background: white;
    border: 2px solid #dee2e6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    z-index: 1;
    background: white;
}

.timeline-item.completed .timeline-icon {
    border-color: #05bb14;
    background: #05bb14;
    color: white;
}

.timeline-item.completed .timeline-icon i {
    color: white;
}

.timeline-content {
    flex: 1;
}

.timeline-content h6 {
    font-size: 0.9rem;
    margin: 0;
}

.timeline-item.completed .timeline-content h6 {
    color: #05bb14;
    font-weight: bold;
}

/* Pulse Animation for Motorcycle */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(5, 187, 20, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(5, 187, 20, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(5, 187, 20, 0);
    }
}

.motorcycle-icon {
    animation: pulse 2s infinite;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<script>
let map = null;
let riderMarker = null;
let customerMarker = null;
let updateInterval = null;

// Calculate delivery progress based on order status
function getDeliveryProgress(orderStatus) {
    const progressMap = {
        'pending': 0,
        'confirmed': 20,
        'preparing': 40,
        'ready_for_pickup': 60,
        'picked_up': 80,
        'in_transit': 85,
        'on_the_way': 85,
        'delivered': 100
    };
    return progressMap[orderStatus] || 0;
}

// Initialize map if rider is assigned
@if($order->rider && in_array($order->status, ['picked_up', 'in_transit', 'on_the_way']))
    function initMap() {
        const vendorLat = {{ $order->vendor->latitude ?? 0 }};
        const vendorLng = {{ $order->vendor->longitude ?? 0 }};
        const customerLat = {{ $order->delivery_latitude ?? 0 }};
        const customerLng = {{ $order->delivery_longitude ?? 0 }};
        const riderLat = {{ $riderLocation['lat'] ?? 0 }};
        const riderLng = {{ $riderLocation['lng'] ?? 0 }};

        if (riderLat && riderLng) {
            map = L.map('riderMap').setView([riderLat, riderLng], 13);
            
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; CartoDB'
            }).addTo(map);
            
            // Rider marker (moving)
            const riderIcon = L.divIcon({
                className: 'custom-div-icon',
                html: '<div style="background-color: #237bdd; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.2);"><i class="bi bi-motorcycle" style="color: white; font-size: 16px;"></i></div>',
                iconSize: [30, 30],
                popupAnchor: [0, -15]
            });
            
            riderMarker = L.marker([riderLat, riderLng], { icon: riderIcon }).addTo(map);
            riderMarker.bindPopup('<strong>Rider</strong><br>{{ $riderLocation['name'] ?? 'Rider' }}');
            
            // Vendor marker
            if (vendorLat && vendorLng) {
                const vendorIcon = L.divIcon({
                    className: 'custom-div-icon',
                    html: '<div style="background-color: #ffc107; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white;"><i class="bi bi-shop" style="color: white; font-size: 14px;"></i></div>',
                    iconSize: [30, 30]
                });
                L.marker([vendorLat, vendorLng], { icon: vendorIcon }).addTo(map)
                    .bindPopup('<strong>Vendor</strong><br>{{ $order->vendor->business_name }}');
            }
            
            // Customer marker
            if (customerLat && customerLng) {
                const customerIcon = L.divIcon({
                    className: 'custom-div-icon',
                    html: '<div style="background-color: #05bb14; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white;"><i class="bi bi-house" style="color: white; font-size: 14px;"></i></div>',
                    iconSize: [30, 30]
                });
                customerMarker = L.marker([customerLat, customerLng], { icon: customerIcon }).addTo(map);
                customerMarker.bindPopup('<strong>Your Location</strong><br>{{ $order->delivery_address }}');
            }
            
            // Draw route line between vendor and customer
            if (vendorLat && vendorLng && customerLat && customerLng) {
                const routePoints = [[vendorLat, vendorLng], [customerLat, customerLng]];
                const routeLine = L.polyline(routePoints, {
                    color: '#05bb14',
                    weight: 3,
                    opacity: 0.6,
                    dashArray: '5, 10'
                }).addTo(map);
                
                // Calculate and display distance
                const distance = calculateDistance(vendorLat, vendorLng, customerLat, customerLng);
                const eta = Math.round(distance / 30 * 60); // Assuming 30 km/h average speed
                document.getElementById('distanceToCustomer').innerHTML = `${distance.toFixed(1)} km away`;
                document.getElementById('eta').innerHTML = ` | ETA: ${eta} min`;
            }
        }
    }
    
    // Calculate distance between two points
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }
    
    // Update rider location in real-time
    function updateRiderLocation() {
        fetch('{{ route("customer.orders.rider-location", $order) }}')
            .then(response => response.json())
            .then(data => {
                if (data.lat && data.lng && map && riderMarker) {
                    const newLat = data.lat;
                    const newLng = data.lng;
                    riderMarker.setLatLng([newLat, newLng]);
                    map.setView([newLat, newLng], map.getZoom());
                    
                    // Update distance to customer
                    const customerLat = {{ $order->delivery_latitude ?? 0 }};
                    const customerLng = {{ $order->delivery_longitude ?? 0 }};
                    if (customerLat && customerLng) {
                        const distance = calculateDistance(newLat, newLng, customerLat, customerLng);
                        const eta = Math.round(distance / 30 * 60);
                        document.getElementById('distanceToCustomer').innerHTML = `${distance.toFixed(1)} km away`;
                        document.getElementById('eta').innerHTML = ` | ETA: ${eta} min`;
                    }
                    
                    // Update last update time
                    if (data.updated_at) {
                        const updateTime = new Date(data.updated_at);
                        document.getElementById('lastUpdateTime').innerHTML = updateTime.toLocaleTimeString();
                    }
                }
            })
            .catch(error => console.error('Error updating rider location:', error));
    }
    
    // Initialize map when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initMap();
        
        // Update rider location every 5 seconds
        if ('{{ $order->status }}' === 'on_the_way' || '{{ $order->status }}' === 'picked_up' || '{{ $order->status }}' === 'in_transit') {
            updateInterval = setInterval(updateRiderLocation, 5000);
        }
    });
    
    // Clean up interval on page unload
    window.addEventListener('beforeunload', function() {
        if (updateInterval) {
            clearInterval(updateInterval);
        }
    });
@endif
</script>
@endsection