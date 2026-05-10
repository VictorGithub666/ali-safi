@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="bi bi-truck"></i> Rider Dashboard
        </h2>
        <button type="button" class="btn btn-lg" id="availabilityBtn" 
                onclick="toggleAvailability()" 
                style="background-color: {{ $rider->is_available ? '#28a745' : '#dc3545' }}; color: white;">
            <i class="bi bi-{{ $rider->is_available ? 'toggle-on' : 'toggle-off' }}"></i>
            {{ $rider->is_available ? 'Available' : 'Offline' }}
        </button>
    </div>

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
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="showDeliveryDetails({{ $delivery->id }})">
                                                    <i class="bi bi-map"></i> Details
                                                </button>
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
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>From Vendor</th>
                                        <th>To Location</th>
                                        <th>Fee</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($availableOrders as $order)
                                        <tr>
                                            <td><strong>#{{ $order->order_number }}</strong></td>
                                            <td>{{ $order->vendor->business_name }}</td>
                                            <td>
                                                <small class="text-muted">{{ $order->ward }}, {{ $order->sub_county }}</small>
                                            </td>
                                            <td>
                                                <strong class="text-success">KES {{ number_format($order->delivery_fee, 0) }}</strong>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-success" 
                                                        onclick="acceptOrder({{ $order->id }})">
                                                    <i class="bi bi-check-lg"></i> Accept
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let locationUpdateInterval = null;
let isRiderAvailable = {{ $rider->is_available ? 'true' : 'false' }};

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

// Accept order function
function acceptOrder(orderId) {
    if (!isRiderAvailable) {
        Swal.fire('Offline', 'You must be online to accept orders. Toggle your availability first.', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Accept Delivery?',
        text: 'You are about to accept this delivery order.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#05bb14',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Accept'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/rider/deliveries/${orderId}/accept`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      Swal.fire({
                          title: 'Success!',
                          text: 'Order accepted. Head to the vendor now!',
                          icon: 'success',
                          confirmButtonColor: '#05bb14'
                      }).then(() => location.reload());
                  } else {
                      Swal.fire('Error', data.error, 'error');
                  }
              });
        }
    });
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

function showDeliveryDetails(orderId) {
    window.location.href = `/rider/deliveries/${orderId}`;
}

// Complete delivery function
function completeDelivery(deliveryId) {
    Swal.fire({
        title: 'Complete Delivery?',
        html: `
            <div class="mb-3">
                <label class="form-label">Did customer pay in cash?</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="payment" id="payment-yes" value="1" checked>
                    <label class="btn btn-outline-primary" for="payment-yes">Yes</label>
                    
                    <input type="radio" class="btn-check" name="payment" id="payment-no" value="0">
                    <label class="btn btn-outline-primary" for="payment-no">No</label>
                </div>
            </div>
            <textarea id="notes" class="form-control" placeholder="Add any notes (optional)" rows="3"></textarea>
        `,
        showCancelButton: true,
        confirmButtonColor: '#05bb14',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Mark as Delivered',
        didOpen: () => {
            document.querySelector('input[name="payment"]').checked = true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const paymentReceived = document.querySelector('input[name="payment"]:checked').value === '1';
            const notes = document.getElementById('notes').value;
            
            fetch(`/rider/deliveries/${deliveryId}/complete`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    payment_received: paymentReceived,
                    notes: notes
                })
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      Swal.fire({
                          title: 'Success!',
                          text: 'Delivery marked as completed. Earnings added to your wallet!',
                          icon: 'success',
                          confirmButtonColor: '#05bb14'
                      }).then(() => location.reload());
                  } else {
                      Swal.fire('Error', data.error || 'Failed to complete delivery', 'error');
                  }
              });
        }
    });
}

// Start location tracking on page load if rider is available
if (isRiderAvailable) {
    startLocationTracking();
}

// Clean up interval on page unload
window.addEventListener('beforeunload', function() {
    if (locationUpdateInterval) {
        clearInterval(locationUpdateInterval);
    }
});
</script>
@endsection