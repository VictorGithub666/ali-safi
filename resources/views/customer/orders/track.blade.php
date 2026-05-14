@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Auto-refresh status indicator -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info alert-dismissible fade show mb-0" id="autoRefreshAlert">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-arrow-repeat me-2"></i>
                        <span id="refreshStatus">Auto-refreshing</span>
                        <!-- <span id="countdownTimer" class="ms-2 badge bg-light text-dark"></span> -->
                    </div>
                    <div>
                        <!-- <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="refreshNowBtn">
                            <i class="bi bi-arrow-repeat"></i> Refresh Now
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="toggleRefreshBtn">
                            <i class="bi bi-pause-circle"></i> Pause
                        </button> -->
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                    <!-- Last Updated Time -->
                    <div class="text-end mb-3">
                        <small class="text-muted" id="lastUpdatedTime">
                            <i class="bi bi-clock"></i> Last updated: {{ now()->format('H:i:s') }}
                        </small>
                    </div>

                    <!-- Progress Percentage -->
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <svg width="120" height="120" viewBox="0 0 120 120">
                                <circle cx="60" cy="60" r="54" fill="none" stroke="#e9ecef" stroke-width="8"/>
                                <circle cx="60" cy="60" r="54" fill="none" stroke="#05bb14" stroke-width="8"
                                        stroke-dasharray="{{ 2 * pi() * 54 }}"
                                        stroke-dashoffset="{{ (2 * pi() * 54) - (($deliveryProgress / 100) * (2 * pi() * 54)) }}"
                                        transform="rotate(-90 60 60)"
                                        stroke-linecap="round"
                                        id="progressCircle"/>
                            </svg>
                            <div class="position-absolute top-50 start-50 translate-middle text-center">
                                <span class="fw-bold" id="progressPercentage" style="font-size: 1.8rem; color: var(--primary-green);">
                                    {{ $deliveryProgress }}%
                                </span>
                            </div>
                        </div>
                        <h6 class="mt-3 mb-0" id="orderStatusText">{{ $order->status_label ?? ucfirst($order->status) }}</h6>
                        <small class="text-muted" id="statusDescription">
                            @if($order->status == 'pending')
                                Your order has been placed. Waiting for vendor confirmation.
                            @elseif($order->status == 'confirmed')
                                Vendor has confirmed your order. They are preparing your items.
                            @elseif($order->status == 'preparing')
                                Your order is being prepared.
                            @elseif($order->status == 'ready_for_pickup')
                                Your order is ready for pickup by the rider.
                            @elseif($order->status == 'picked_up')
                                Rider has picked up your order and is on the way!
                            @elseif($order->status == 'delivered')
                                Your order has been delivered. Enjoy!
                            @endif
                        </small>
                    </div>

                    <!-- Progress Bar with Motorcycle Animation -->
                    <div class="tracking-progress mb-5">
                        <div class="progress-line">
                            <div class="progress-track"></div>
                            <div class="progress-fill" id="progressFill" style="width: {{ $deliveryProgress }}%;"></div>
                            <div class="motorcycle-icon" id="motorcycleIcon" style="left: {{ $deliveryProgress }}%;">
                                <i class="bi bi-motorcycle"></i>
                            </div>
                        </div>
                        
                        <div class="progress-stops">
                            <div class="stop-point" data-status="Order Placed">
                                <div class="stop-dot {{ $order->order_placed ? 'active' : '' }}" id="stopPlaced"></div>
                                <div class="stop-label">Order Placed</div>
                            </div>
                            <div class="stop-point" data-status="Confirmed">
                                <div class="stop-dot {{ $order->confirmed ? 'active' : '' }}" id="stopConfirmed"></div>
                                <div class="stop-label">Confirmed</div>
                            </div>
                            <div class="stop-point" data-status="Preparing">
                                <div class="stop-dot {{ $order->preparing ? 'active' : '' }}" id="stopPreparing"></div>
                                <div class="stop-label">Preparing</div>
                            </div>
                            <div class="stop-point" data-status="Ready for Pickup">
                                <div class="stop-dot {{ $order->ready_for_pickup ? 'active' : '' }}" id="stopReady"></div>
                                <div class="stop-label">Ready</div>
                            </div>
                            <div class="stop-point" data-status="On The Way">
                                <div class="stop-dot {{ $order->on_the_way ? 'active' : '' }}" id="stopOnTheWay"></div>
                                <div class="stop-label">On The Way</div>
                            </div>
                            <div class="stop-point" data-status="Delivered">
                                <div class="stop-dot {{ $order->delivered ? 'active' : '' }}" id="stopDelivered"></div>
                                <div class="stop-label">Delivered</div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Status Timeline -->
                    <div class="order-timeline mt-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Order Timeline</h6>
                        <div class="timeline-items" id="timelineItems">
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
                                        <strong id="riderName">{{ $riderLocation['name'] ?? 'Rider' }}</strong>
                                        <p class="mb-0 small text-muted">
                                            <i class="bi bi-telephone"></i> <span id="riderPhone">{{ $riderLocation['phone'] ?? 'Not available' }}</span>
                                        </p>
                                    </div>
                                    <div>
                                        <span class="badge bg-success">
                                            <i class="bi bi-star-fill"></i> <span id="riderRating">{{ $riderLocation['rating'] ?? 'New' }}</span>
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
                        <p class="fw-bold mb-0" id="orderNumber">#{{ $order->order_number }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-muted small">Order Date</label>
                        <p class="mb-0" id="orderDate">{{ $order->created_at->format('M d, Y H:i A') }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-muted small">Delivery Address</label>
                        <p class="mb-0" id="deliveryAddress">{{ $order->delivery_address }}</p>
                        <small class="text-muted" id="locationDetails">{{ $order->ward }}, {{ $order->sub_county }}, {{ $order->county }}</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-muted small">Payment Method</label>
                        <p class="mb-0" id="paymentMethod">{{ ucfirst($order->payment_method) }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-muted small">Payment Status</label>
                        <p>
                            <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}" id="paymentStatus">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </p>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <strong id="subtotal">KES {{ number_format($order->subtotal, 0) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Delivery Fee</span>
                            <strong id="deliveryFee">KES {{ number_format($order->delivery_fee, 0) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Platform Fee</span>
                            <strong id="platformFee">KES {{ number_format($order->platform_fee, 0) }}</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Total</span>
                            <strong class="fw-bold" id="totalAmount" style="color: var(--primary-green); font-size: 1.2rem;">
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

/* Fade animation for refresh */
@keyframes fadeIn {
    from { opacity: 0.5; }
    to { opacity: 1; }
}

.refreshing {
    animation: fadeIn 0.5s ease-in-out;
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
let countdownInterval = null;
let secondsRemaining = 10;
let isAutoRefreshEnabled = true;
let currentOrderId = {{ $order->id }};

// Function to refresh page content
function refreshPageContent() {
    if (!isAutoRefreshEnabled) return;
    
    console.log('Refreshing order data...');
    document.getElementById('autoRefreshAlert').classList.add('refreshing');
    
    fetch(window.location.href, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Parse the HTML response
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Update progress elements
        const newProgress = doc.querySelector('#progressPercentage')?.innerText || '0';
        const newProgressPercent = parseInt(newProgress);
        
        // Update progress circle
        const circumference = 2 * Math.PI * 54;
        const offset = circumference - (newProgressPercent / 100 * circumference);
        const progressCircle = document.querySelector('#progressCircle');
        if (progressCircle) {
            progressCircle.style.strokeDashoffset = offset;
        }
        
        // Update progress percentage text
        const progressPercentage = document.querySelector('#progressPercentage');
        if (progressPercentage) progressPercentage.innerText = newProgress;
        
        // Update progress fill
        const progressFill = document.querySelector('#progressFill');
        if (progressFill) progressFill.style.width = newProgressPercent + '%';
        
        // Update motorcycle position
        const motorcycleIcon = document.querySelector('#motorcycleIcon');
        if (motorcycleIcon) motorcycleIcon.style.left = newProgressPercent + '%';
        
        // Update order status text
        const newStatus = doc.querySelector('#orderStatusText')?.innerText;
        if (newStatus) {
            const statusText = document.querySelector('#orderStatusText');
            if (statusText) statusText.innerText = newStatus;
        }
        
        // Update status description
        const newDescription = doc.querySelector('#statusDescription')?.innerHTML;
        if (newDescription) {
            const statusDesc = document.querySelector('#statusDescription');
            if (statusDesc) statusDesc.innerHTML = newDescription;
        }
        
        // Update stop dots
        const stopDots = ['stopPlaced', 'stopConfirmed', 'stopPreparing', 'stopReady', 'stopOnTheWay', 'stopDelivered'];
        stopDots.forEach(dotId => {
            const newDotClass = doc.querySelector(`#${dotId}`)?.className;
            const currentDot = document.querySelector(`#${dotId}`);
            if (currentDot && newDotClass) {
                currentDot.className = newDotClass;
            }
        });
        
        // Update timeline items
        const newTimeline = doc.querySelector('#timelineItems')?.innerHTML;
        if (newTimeline) {
            const timelineItems = document.querySelector('#timelineItems');
            if (timelineItems) timelineItems.innerHTML = newTimeline;
        }
        
        // Update last updated time
        const now = new Date();
        const timeString = now.toLocaleTimeString();
        const lastUpdated = document.querySelector('#lastUpdatedTime');
        if (lastUpdated) lastUpdated.innerHTML = `<i class="bi bi-clock"></i> Last updated: ${timeString}`;
        
        // If order is delivered, stop auto-refresh
        if (newProgressPercent === 100) {
            stopAutoRefresh();
            document.getElementById('refreshStatus').innerHTML = 'Order delivered! Auto-refresh stopped.';
            document.getElementById('toggleRefreshBtn').disabled = true;
        }
        
        setTimeout(() => {
            document.getElementById('autoRefreshAlert').classList.remove('refreshing');
        }, 500);
    })
    .catch(error => {
        console.error('Error refreshing page:', error);
        document.getElementById('autoRefreshAlert').classList.remove('refreshing');
    });
}

// Countdown timer function
function startCountdown() {
    if (countdownInterval) clearInterval(countdownInterval);
    
    secondsRemaining = 10;
    updateCountdownDisplay();
    
    countdownInterval = setInterval(() => {
        if (!isAutoRefreshEnabled) return;
        
        secondsRemaining--;
        updateCountdownDisplay();
        
        if (secondsRemaining <= 0) {
            refreshPageContent();
            secondsRemaining = 10;
        }
    }, 1000);
}

function updateCountdownDisplay() {
    const timerElement = document.getElementById('countdownTimer');
    if (timerElement && isAutoRefreshEnabled) {
        timerElement.innerHTML = `Next refresh in ${secondsRemaining}s`;
    } else if (timerElement) {
        timerElement.innerHTML = 'Auto-refresh paused';
    }
}

function stopAutoRefresh() {
    isAutoRefreshEnabled = false;
    if (countdownInterval) clearInterval(countdownInterval);
    const timerElement = document.getElementById('countdownTimer');
    if (timerElement) timerElement.innerHTML = 'Auto-refresh paused';
    const toggleBtn = document.getElementById('toggleRefreshBtn');
    if (toggleBtn) {
        toggleBtn.innerHTML = '<i class="bi bi-play-circle"></i> Resume';
        toggleBtn.classList.remove('btn-outline-danger');
        toggleBtn.classList.add('btn-outline-success');
    }
    document.getElementById('refreshStatus').innerHTML = 'Auto-refresh paused';
}

function startAutoRefresh() {
    if (document.querySelector('#progressPercentage')?.innerText === '100') {
        return;
    }
    isAutoRefreshEnabled = true;
    startCountdown();
    const toggleBtn = document.getElementById('toggleRefreshBtn');
    if (toggleBtn) {
        toggleBtn.innerHTML = '<i class="bi bi-pause-circle"></i> Pause';
        toggleBtn.classList.remove('btn-outline-success');
        toggleBtn.classList.add('btn-outline-danger');
    }
    document.getElementById('refreshStatus').innerHTML = 'Auto-refreshing every 10 seconds';
}

// Toggle refresh function
function toggleRefresh() {
    if (isAutoRefreshEnabled) {
        stopAutoRefresh();
    } else {
        startAutoRefresh();
        refreshPageContent(); // Refresh immediately when resuming
    }
}

// Manual refresh
function manualRefresh() {
    refreshPageContent();
    if (isAutoRefreshEnabled) {
        secondsRemaining = 10;
        updateCountdownDisplay();
    }
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
                }
            })
            .catch(error => console.error('Error updating rider location:', error));
    }
    
    // Initialize map when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initMap();
        
        // Update rider location every 5 seconds if order is in transit
        if ('{{ $order->status }}' === 'on_the_way' || '{{ $order->status }}' === 'picked_up' || '{{ $order->status }}' === 'in_transit') {
            setInterval(updateRiderLocation, 5000);
        }
    });
@endif

// Initialize auto-refresh on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check if order is already delivered
    const progressText = document.querySelector('#progressPercentage')?.innerText;
    if (progressText === '100') {
        document.getElementById('refreshStatus').innerHTML = 'Order delivered! Auto-refresh stopped.';
        document.getElementById('toggleRefreshBtn').disabled = true;
    } else {
        startAutoRefresh();
    }
    
    // Set up event listeners
    const toggleBtn = document.getElementById('toggleRefreshBtn');
    if (toggleBtn) toggleBtn.addEventListener('click', toggleRefresh);
    
    const refreshNowBtn = document.getElementById('refreshNowBtn');
    if (refreshNowBtn) refreshNowBtn.addEventListener('click', manualRefresh);
});

// Clean up intervals on page unload
window.addEventListener('beforeunload', function() {
    if (countdownInterval) clearInterval(countdownInterval);
});
</script>
@endsection