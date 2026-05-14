@extends('layouts.app')

@use('App\Services\DistanceService')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">
                <i class="bi bi-truck"></i> Rider Dashboard
            </h2>
            <p class="text-muted small mt-1" id="lastRefreshTime">
                <i class="bi bi-clock-history"></i> Auto-refreshing every <span id="countdown">10</span> seconds
                <span class="badge bg-info ms-2" id="autoRefreshBadge">Auto-refresh: ON</span>
            </p>
        </div>
        <button type="button" class="btn btn-lg" id="availabilityBtn" 
                onclick="toggleAvailability()" 
                style="background-color: {{ $rider->is_available ? '#28a745' : '#dc3545' }}; color: white;">
            <i class="bi bi-{{ $rider->is_available ? 'toggle-on' : 'toggle-off' }}"></i>
            {{ $rider->is_available ? 'Available' : 'Offline' }}
        </button>
    </div>

    <!-- Active Delivery Warning (if has active delivery) -->
    @if(isset($hasActiveDelivery) && $hasActiveDelivery)
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Active Delivery In Progress!</strong> You cannot accept new orders until you complete your current delivery.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Stats Section -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #05bb14;">
                        <i class="bi bi-box"></i>
                    </div>
                    <h6 class="text-muted mb-2">Available Orders</h6>
                    <h3 class="fw-bold mb-0">{{ $availableOrders->count() }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #237bdd;">
                        <i class="bi bi-hourglass"></i>
                    </div>
                    <h6 class="text-muted mb-2">Active Deliveries</h6>
                    <h3 class="fw-bold mb-0">{{ $myDeliveries->count() }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #28a745;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h6 class="text-muted mb-2">Completed Today</h6>
                    <h3 class="fw-bold mb-0">{{ $completedToday }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #dc3545;">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <h6 class="text-muted mb-2">Rating</h6>
                    <h3 class="fw-bold mb-0">{{ number_format($rider->rating ?? 0, 1) }}/5</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Location Status Indicator -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-geo-alt-fill" style="color: var(--primary-green);"></i>
                            <span id="locationStatusText" class="small ms-2">
                                @if($rider->is_available)
                                    <span class="text-success">📍 Location tracking active - Updating every 10 seconds</span>
                                @else
                                    <span class="text-muted">📍 Location tracking paused (Go online to start tracking)</span>
                                @endif
                            </span>
                        </div>
                        <div>
                            <span id="lastUpdateTime" class="small text-muted"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Active Deliveries -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-hourglass"></i> Active Deliveries</h6>
                        @if($myDeliveries->count() === 0)
                            <span class="badge bg-secondary">None</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($myDeliveries->count())
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Customer</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($myDeliveries as $delivery)
                                        <tr>
                                            <td><strong>#{{ $delivery->order_number }}</strong></td>
                                            <td>{{ $delivery->customer->name }}</td>
                                            <td>
                                                <small class="text-muted">{{ $delivery->ward }}, {{ $delivery->sub_county }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ ucfirst($delivery->status) }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('rider.deliveries.show', $delivery->id) }}" 
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> Details
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> No active deliveries. Accept available orders to start earning!
                        </div>
                    @endif
                </div>
            </div>

            <!-- Available Orders -->
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-lightning-fill"></i> Available Orders</h6>
                        <span class="badge bg-success">{{ $availableOrders->count() }} new</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($availableOrders->count())
                        @foreach($availableOrders as $order)
                            <div class="card mb-3 border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title mb-0">Order #{{ $order->order_number }}</h5>
                                        <span class="badge bg-warning text-dark">Ready for Pickup</span>
                                    </div>
                                    
                                    <!-- Vendor Information & Location -->
                                    <div class="mb-3 p-2 bg-light rounded">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-shop fs-5 text-primary me-2"></i>
                                            <strong>{{ $order->vendor->business_name }}</strong>
                                        </div>
                                        <div class="ps-3">
                                            <small class="text-muted d-block mb-2">
                                                <i class="bi bi-geo-alt"></i> {{ $order->vendor->business_address ?? 'Address not provided' }}
                                            </small>
                                            
                                            <!-- Vendor Location Button -->
                                            @if(isset($order->vendor_directions_url) && $order->vendor_directions_url)
                                                <a href="{{ $order->vendor_directions_url }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary w-100 mb-2">
                                                    <i class="bi bi-geo-alt-fill me-1"></i> 
                                                    🏪 Navigate to Shop (Get Directions)
                                                </a>
                                            @elseif(isset($order->vendor_maps_url) && $order->vendor_maps_url)
                                                <a href="{{ $order->vendor_maps_url }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary w-100 mb-2">
                                                    <i class="bi bi-geo-alt-fill me-1"></i> 
                                                    🏪 View Shop Location
                                                </a>
                                            @else
                                                <div class="alert alert-warning py-1 mb-2">
                                                    <small><i class="bi bi-exclamation-triangle"></i> Shop location not available</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Customer Information & Location -->
                                    <div class="mb-3 p-2 bg-light rounded">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-person fs-5 text-success me-2"></i>
                                            <strong>{{ $order->customer->name }}</strong>
                                        </div>
                                        <div class="ps-3">
                                            <small class="text-muted d-block mb-2">
                                                <i class="bi bi-geo-alt"></i> {{ $order->delivery_address }}
                                            </small>
                                            
                                            <!-- Customer Location Button -->
                                            @if(isset($order->customer_directions_url) && $order->customer_directions_url)
                                                <a href="{{ $order->customer_directions_url }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-success w-100 mb-2">
                                                    <i class="bi bi-house-door-fill me-1"></i> 
                                                    🏠 Navigate to Customer (Get Directions)
                                                </a>
                                            @elseif(isset($order->customer_maps_url) && $order->customer_maps_url)
                                                <a href="{{ $order->customer_maps_url }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-success w-100 mb-2">
                                                    <i class="bi bi-house-door-fill me-1"></i> 
                                                    🏠 View Customer Location
                                                </a>
                                            @else
                                                <div class="alert alert-warning py-1 mb-2">
                                                    <small><i class="bi bi-exclamation-triangle"></i> Customer location not available</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Order Summary -->
                                    <div class="bg-white p-2 rounded mb-3 border">
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <small class="text-muted">Items</small>
                                                <div class="fw-bold">{{ $order->items->count() }}</div>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted">Delivery Fee</small>
                                                <div class="fw-bold text-success">KES {{ number_format($order->delivery_fee, 2) }}</div>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted">Total</small>
                                                <div class="fw-bold">KES {{ number_format($order->total, 2) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Accept Order Button -->
                                    @if(!(isset($hasActiveDelivery) && $hasActiveDelivery))
                                        <form action="{{ route('rider.deliveries.accept', $order) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="bi bi-check-circle me-1"></i> Accept & Pick Up Order
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-secondary w-100" disabled>
                                            <i class="bi bi-ban me-1"></i> Complete Active Delivery First
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle"></i> No available orders at the moment. Check back soon!
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions & Stats -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-lightning-charge"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('rider.earnings') }}" class="btn btn-outline-primary">
                            <i class="bi bi-graph-up"></i> View Earnings
                        </a>
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">
                            <i class="bi bi-person"></i> My Profile
                        </a>
                        <button class="btn btn-outline-primary" onclick="updateLocationManually()">
                            <i class="bi bi-geo-alt"></i> Update Location Now
                        </button>
                        <a href="#" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#supportModal">
                            <i class="bi bi-question-circle"></i> Get Help
                        </a>
                    </div>
                </div>
            </div>

            <!-- Vehicle Info -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-car-front"></i> Vehicle Info</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Vehicle Type</label>
                        <p class="mb-0"><strong>{{ ucfirst($rider->vehicle_type) }}</strong></p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Vehicle Number</label>
                        <p class="mb-0"><strong>{{ $rider->vehicle_number }}</strong></p>
                    </div>
                    <div>
                        <label class="text-muted small">License Number</label>
                        <p class="mb-0"><strong>{{ $rider->license_number }}</strong></p>
                    </div>
                </div>
            </div>

            <!-- Performance Stats -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-award"></i> Performance</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Total Deliveries</label>
                        <p class="mb-0"><strong>{{ $rider->total_deliveries ?? 0 }}</strong></p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Verification Status</label>
                        <p class="mb-0">
                            @if($rider->is_verified)
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Verified</span>
                            @else
                                <span class="badge bg-warning"><i class="bi bi-hourglass"></i> Pending</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="text-muted small">Account Balance</label>
                        <p class="mb-0"><strong class="text-success">KES {{ number_format($rider->wallet_balance ?? 0, 0) }}</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Support Modal -->
<div class="modal fade" id="supportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Need Help?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Here are some helpful resources:</p>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="text-decoration-none"><i class="bi bi-question-circle"></i> How to Accept Orders</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none"><i class="bi bi-map"></i> Navigation Guide</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none"><i class="bi bi-wallet2"></i> Earnings FAQ</a></li>
                    <li><a href="#" class="text-decoration-none"><i class="bi bi-telephone"></i> Contact Support</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-effect {
        cursor: pointer;
    }
    
    .hover-effect:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12) !important;
        transform: translateY(-2px);
    }
    
    .order-card-header {
        border-bottom: 1px solid #e9ecef;
    }
    
    #autoRefreshBadge {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
    
    @media (max-width: 768px) {
        .col-md-4 {
            margin-top: 1rem;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let locationUpdateInterval = null;
let refreshTimer = null;
let countdown = 10;
let isRiderAvailable = {{ $rider->is_available ? 'true' : 'false' }};

// Function to reload the page
function reloadPage() {
    window.location.reload();
}

// Countdown timer for next refresh
function startCountdown() {
    countdown = 10;
    const countdownElement = document.getElementById('countdown');
    
    if (refreshTimer) {
        clearInterval(refreshTimer);
    }
    
    refreshTimer = setInterval(() => {
        countdown--;
        if (countdownElement) {
            countdownElement.textContent = countdown;
        }
        
        if (countdown <= 0) {
            clearInterval(refreshTimer);
            reloadPage();
        }
    }, 1000);
}

// Function to update rider location
function updateRiderLocation() {
    if (!isRiderAvailable) {
        return;
    }
    
    if (!('geolocation' in navigator)) {
        console.log('Geolocation not supported');
        return;
    }
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            // Send location to server
            fetch('/rider/location', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    latitude: lat,
                    longitude: lng
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update last update time display
                    const now = new Date();
                    const timeString = now.toLocaleTimeString();
                    document.getElementById('lastUpdateTime').innerHTML = `<i class="bi bi-clock"></i> Last update: ${timeString}`;
                }
            })
            .catch(error => {
                console.error('Location update error:', error);
            });
        },
        function(error) {
            console.error('Geolocation error:', error);
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}

// Start/Stop location tracking based on availability
function startLocationTracking() {
    if (locationUpdateInterval) {
        clearInterval(locationUpdateInterval);
        locationUpdateInterval = null;
    }
    
    if (isRiderAvailable) {
        // Update immediately when becoming available
        updateRiderLocation();
        // Then update every 10 seconds
        locationUpdateInterval = setInterval(updateRiderLocation, 10000);
        document.getElementById('locationStatusText').innerHTML = '<span class="text-success">📍 Location tracking active - Updating every 10 seconds</span>';
    } else {
        document.getElementById('locationStatusText').innerHTML = '<span class="text-muted">📍 Location tracking paused (Go online to start tracking)</span>';
        document.getElementById('lastUpdateTime').innerHTML = '';
    }
}

// Manual location update
function updateLocationManually() {
    if (!isRiderAvailable) {
        Swal.fire('Offline', 'You must be online to update location', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Updating Location...',
        text: 'Getting your current position',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    if (!('geolocation' in navigator)) {
        Swal.fire('Error', 'Geolocation is not supported by your browser', 'error');
        return;
    }
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            fetch('/rider/location', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    latitude: lat,
                    longitude: lng
                })
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    Swal.fire('Success!', 'Your location has been updated', 'success');
                    const now = new Date();
                    document.getElementById('lastUpdateTime').innerHTML = `<i class="bi bi-clock"></i> Last update: ${now.toLocaleTimeString()}`;
                } else {
                    Swal.fire('Error', 'Failed to update location', 'error');
                }
            })
            .catch(error => {
                Swal.close();
                Swal.fire('Error', 'An error occurred', 'error');
            });
        },
        function(error) {
            Swal.close();
            Swal.fire('Error', 'Unable to get your location. Please check your GPS settings.', 'error');
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}

// Toggle availability function
function toggleAvailability() {
    const btn = document.getElementById('availabilityBtn');
    btn.disabled = true;
    btn.style.opacity = '0.6';
    
    Swal.fire({
        title: 'Updating Status...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch('/rider/toggle-availability', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              isRiderAvailable = data.is_available;
              
              // Start or stop location tracking based on new availability
              startLocationTracking();
              
              Swal.close();
              Swal.fire({
                  title: data.message,
                  text: 'Page will refresh now...',
                  icon: 'success',
                  confirmButtonColor: '#05bb14',
                  timer: 1500,
                  showConfirmButton: false
              }).then(() => {
                  location.reload();
              });
          } else {
              btn.disabled = false;
              btn.style.opacity = '1';
              Swal.fire('Error', data.message || 'Failed to update status', 'error');
          }
      })
      .catch(error => {
          btn.disabled = false;
          btn.style.opacity = '1';
          console.error('Error:', error);
          Swal.fire('Error', 'An error occurred. Please try again.', 'error');
      });
}

// Start countdown timer for page refresh
startCountdown();

// Start location tracking on page load if rider is available
if (isRiderAvailable) {
    startLocationTracking();
}

// Clean up intervals on page unload
window.addEventListener('beforeunload', function() {
    if (locationUpdateInterval) {
        clearInterval(locationUpdateInterval);
    }
    if (refreshTimer) {
        clearInterval(refreshTimer);
    }
});
</script>
@endsection