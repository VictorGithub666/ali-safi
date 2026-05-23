@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="fw-bold mb-4">
        <i class="bi bi-shop"></i> Vendor Dashboard
    </h2>

    @if(session()->has('whatsapp_order_link_' . ($order->id ?? '')))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-whatsapp me-2"></i>
        <strong>New Order Alert!</strong> 
        <a href="{{ session('whatsapp_order_link_' . $order->id) }}" 
        target="_blank" 
        class="alert-link">
            Click here to view order on WhatsApp
        </a>
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
                        <i class="bi bi-wallet"></i>
                    </div>
                    <h6 class="text-muted mb-2">Today's Revenue</h6>
                    <h3 class="fw-bold mb-0">KES {{ number_format($todayRevenue ?? 0, 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Recent Orders</h6>
                        <a href="{{ route('vendor.orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    @if($recentOrders && $recentOrders->count())
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                        @php
                                            $baseTotal = $order->items->sum(function($item) {
                                                return $item->product->base_price * $item->quantity;
                                            });
                                        @endphp
                                        <tr>
                                            <td><strong>#{{ $order->order_number }}</strong></td>
                                            <td>{{ $order->customer->name }}</td>
                                            <td>{{ $order->items_count ?? $order->items->count() }}</td>
                                            <td>
                                                <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td>KES {{ number_format($baseTotal, 0) }}</td>
                                            <td>
                                                <a href="{{ route('vendor.orders.show', $order->id) }}" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4 mb-0">No orders yet</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <!-- Shop Status Card -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Shop Status</h6>
                </div>
                <div class="card-body">
                    @php
                        $vendor = Auth::user()->vendor;
                        $isOpen = $vendor->is_open ?? false;
                    @endphp
                    <p class="mb-3">
                        Status: 
                        <span class="badge bg-{{ $isOpen ? 'success' : 'danger' }}">
                            {{ $isOpen ? 'Open' : 'Closed' }}
                        </span>
                    </p>
                    <form method="POST" action="{{ route('vendor.toggle-status') }}" class="d-inline w-100">
                        @csrf
                        @method('POST')
                        <button type="submit" class="btn btn-{{ $isOpen ? 'outline-danger' : 'success' }} btn-sm w-100">
                            <i class="bi bi-{{ $isOpen ? 'lock' : 'unlock' }}"></i> 
                            {{ $isOpen ? 'Close Shop' : 'Open Shop' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('vendor.orders.index') }}" class="btn btn-primary">
                            <i class="bi bi-box"></i> Manage Orders
                        </a>
                        <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-basket"></i> My Products
                        </a>
                        <a href="{{ route('vendor.earnings') }}" class="btn btn-outline-primary">
                            <i class="bi bi-graph-up"></i> Earnings
                        </a>
                    </div>
                </div>
            </div>

            <!-- Vendor Location Card - EXACT copy of checkout location method -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-geo-alt"></i> Shop Location</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="business_address" class="form-label fw-bold">Business Address</label>
                        <textarea class="form-control" 
                                  id="business_address" 
                                  name="business_address" 
                                  rows="2"
                                  placeholder="Enter your business address">{{ $vendor->business_address ?? '' }}</textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="latitude" class="form-label fw-bold">Latitude</label>
                            <input type="number" 
                                   step="0.000001"
                                   class="form-control" 
                                   id="latitude" 
                                   name="latitude"
                                   placeholder="-1.287389"
                                   value="{{ $vendor->latitude ?? '' }}">
                            <small class="text-muted">Format: -1.287389</small>
                        </div>
                        <div class="col-md-6">
                            <label for="longitude" class="form-label fw-bold">Longitude</label>
                            <input type="number" 
                                   step="0.000001"
                                   class="form-control" 
                                   id="longitude" 
                                   name="longitude"
                                   placeholder="36.789012"
                                   value="{{ $vendor->longitude ?? '' }}">
                            <small class="text-muted">Format: 36.789012</small>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mb-3">
                        <button type="button" id="getLocationBtn" class="btn btn-sm" style="background-color: var(--primary-green); color: white;">
                            <i class="bi bi-geo-alt me-1"></i> Get My Location
                        </button>
                        <button type="button" id="saveLocationBtn" class="btn btn-sm btn-primary">
                            <i class="bi bi-save me-1"></i> Save Location
                        </button>
                        <span id="locationStatus" class="align-self-center small text-muted"></span>
                    </div>

                    <small class="text-muted d-block">
                        <i class="bi bi-info-circle me-1"></i> Click "Get My Location" to auto-fill coordinates from your current position
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get location elements
    const getLocationBtn = document.getElementById('getLocationBtn');
    const saveLocationBtn = document.getElementById('saveLocationBtn');
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const addressInput = document.getElementById('business_address');
    const statusSpan = document.getElementById('locationStatus');

    // Handle Get Location button click (EXACT copy from checkout)
    getLocationBtn.addEventListener('click', function(e) {
        e.preventDefault();
        getLocation();
    });

    // Handle Save Location button click
    saveLocationBtn.addEventListener('click', function(e) {
        e.preventDefault();
        saveLocation();
    });

    // Location function - IDENTICAL to checkout page
    function getLocation() {
        if (!('geolocation' in navigator)) {
            showStatus('Geolocation is not supported by your browser', 'danger');
            return;
        }

        statusSpan.textContent = 'Getting your location...';
        statusSpan.style.color = '#6c757d';
        getLocationBtn.disabled = true;

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);
                
                showStatus('✓ Location updated successfully!', 'success');
                getLocationBtn.disabled = false;
            },
            function(error) {
                getLocationBtn.disabled = false;
                
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        showStatus('❌ Permission denied. Please enable location in your browser settings.', 'danger');
                        break;
                    case error.POSITION_UNAVAILABLE:
                        showStatus('❌ Location information is unavailable.', 'danger');
                        break;
                    case error.TIMEOUT:
                        showStatus('❌ The request timed out. Please try again.', 'danger');
                        break;
                    default:
                        showStatus('❌ An error occurred. Please try again.', 'danger');
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }

    // Save location function
    function saveLocation() {
        const lat = latInput.value;
        const lng = lngInput.value;
        const address = addressInput.value;
        
        if (!lat || !lng) {
            showStatus('❌ Please get your location first or enter coordinates manually', 'danger');
            return;
        }
        
        statusSpan.textContent = 'Saving location...';
        statusSpan.style.color = '#6c757d';
        saveLocationBtn.disabled = true;
        
        fetch('{{ route("vendor.update-location") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                latitude: parseFloat(lat),
                longitude: parseFloat(lng),
                business_address: address
            })
        })
        .then(response => response.json())
        .then(data => {
            saveLocationBtn.disabled = false;
            if (data.success) {
                showStatus('✓ Location saved successfully!', 'success');
                // Update the stored values
                latInput.value = data.data.latitude.toFixed(6);
                lngInput.value = data.data.longitude.toFixed(6);
            } else {
                showStatus('❌ ' + (data.message || 'Failed to save location'), 'danger');
            }
        })
        .catch(error => {
            saveLocationBtn.disabled = false;
            console.error('Error:', error);
            showStatus('❌ An error occurred. Please try again.', 'danger');
        });
    }

    // Show status function - IDENTICAL to checkout page
    function showStatus(message, type) {
        statusSpan.textContent = message;
        if (type === 'success') {
            statusSpan.style.color = 'var(--primary-green)';
            setTimeout(() => {
                statusSpan.textContent = '';
            }, 4000);
        } else if (type === 'danger') {
            statusSpan.style.color = '#dc3545';
            setTimeout(() => {
                statusSpan.textContent = '';
            }, 5000);
        }
    }

    // Auto-get location on page load if fields are empty
    if (!latInput.value && !lngInput.value) {
        getLocation();
    }
});
</script>
@endsection