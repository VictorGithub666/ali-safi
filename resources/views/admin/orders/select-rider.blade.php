@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    <h1 class="mb-4"><i class="bi bi-person-check"></i> Select Rider</h1>

    <form id="assignForm" action="{{ route('admin.orders.assign') }}" method="POST">
        @csrf
        <input type="hidden" name="order_id" id="order_id" value="{{ $order->id ?? '' }}">
        <input type="hidden" name="rider_id" id="rider_id" value="">
        <input type="hidden" name="distance_km" id="distance_km_hidden" value="">
        <input type="hidden" name="base_fee" id="base_fee_hidden" value="">
        <input type="hidden" name="per_km_fee" id="per_km_fee_hidden" value="">
        <input type="hidden" name="bonus" id="bonus_hidden" value="">
        
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header"><h5>Order Details</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">Order #</small>
                            <p><strong>{{ $order->order_number ?? 'N/A' }}</strong></p>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Customer</small>
                            <p><strong>{{ $order->customer->name ?? 'Customer Name' }}</strong></p>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Customer Phone</small>
                            <p><strong>{{ $order->customer->phone ?? 'N/A' }}</strong></p>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Delivery Address</small>
                            <p>{{ $order->delivery_address ?? 'Address not provided' }}</p>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Vendor</small>
                            <p><strong>{{ $order->vendor->business_name ?? 'N/A' }}</strong></p>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Order Total</small>
                            <p><strong style="color:#05bb14;">KES {{ number_format($order->total ?? 0, 2) }}</strong></p>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Delivery Fee to Customer</small>
                            <p><strong>KES {{ number_format($order->delivery_fee ?? 0, 2) }}</strong></p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5>Fee Calculation</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Distance (km)</label>
                            <input type="number" id="distance_km" class="form-control" step="0.1" value="1" placeholder="Distance in km">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Base Fee (KES)</label>
                            <input type="number" id="base_fee" class="form-control" step="1" value="50" placeholder="Base fee">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Per KM Fee (KES/km)</label>
                            <input type="number" id="per_km_fee" class="form-control" step="1" value="50" placeholder="Per KM fee">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bonus (optional)</label>
                            <input type="number" id="bonus" class="form-control" step="0.01" value="0" placeholder="Bonus amount">
                        </div>
                        <div class="alert alert-info">
                            <strong>Total Rider Fee:</strong> <span id="totalFee">KES 100.00</span>
                        </div>
                        <div class="alert alert-warning mt-3">
                            <small>
                                <i class="bi bi-info-circle"></i>
                                <strong>Note:</strong> This fee will be paid to the rider from the platform's commission.
                                The customer has already paid KES {{ number_format($order->delivery_fee ?? 0, 2) }} for delivery.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Available Riders</h5>
                        <small class="text-muted">Select a rider to assign to this order</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Rider</th>
                                    <th>Vehicle</th>
                                    <th>Deliveries</th>
                                    <th>Rating</th>
                                    <th>Location</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($riders as $rider)
                                    <tr>
                                        <td>
                                            <strong>{{ $rider->user->name }}</strong><br>
                                            <small class="text-muted">{{ $rider->user->phone }}</small>
                                        </td>
                                        <td>{{ ucfirst($rider->vehicle_type) }}<br><small>{{ $rider->vehicle_number }}</small></td>
                                        <td><span class="badge bg-info">{{ $rider->orders_count }}</span></td>
                                        <td>
                                            <i class="bi bi-star-fill" style="color: #ffc107;"></i> 
                                            {{ number_format($rider->rating ?? 0, 1) }}
                                        </td>
                                        <td>
                                            @if($rider->current_latitude && $rider->current_longitude && $order->vendor->latitude && $order->vendor->longitude)
                                                @php
                                                    $distanceToVendor = \App\Services\DistanceService::calculateDistance(
                                                        $rider->current_latitude,
                                                        $rider->current_longitude,
                                                        $order->vendor->latitude,
                                                        $order->vendor->longitude
                                                    );
                                                @endphp
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-geo-alt"></i> {{ $distanceToVendor }} km away
                                                </span>
                                            @else
                                                <span class="text-muted small">Location unknown</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" 
                                                    class="btn btn-sm btn-success assign-btn" 
                                                    data-rider-id="{{ $rider->id }}"
                                                    data-rider-name="{{ $rider->user->name }}">
                                                <i class="bi bi-check-circle"></i> Assign
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-4">
                                        <i class="bi bi-exclamation-triangle"></i> No available riders found.
                                        <br><small>Please ensure riders are verified and marked as available.</small>
                                    </td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // DOM Elements
    const distanceInput = document.getElementById('distance_km');
    const baseFeeInput = document.getElementById('base_fee');
    const perKmFeeInput = document.getElementById('per_km_fee');
    const bonusInput = document.getElementById('bonus');
    const totalFeeSpan = document.getElementById('totalFee');
    
    // Hidden form inputs
    const distanceHidden = document.getElementById('distance_km_hidden');
    const baseFeeHidden = document.getElementById('base_fee_hidden');
    const perKmFeeHidden = document.getElementById('per_km_fee_hidden');
    const bonusHidden = document.getElementById('bonus_hidden');
    const riderIdHidden = document.getElementById('rider_id');
    const assignForm = document.getElementById('assignForm');

    // Calculate total fee function
    function calculateFee() {
        const base = parseFloat(baseFeeInput.value) || 0;
        const perKm = parseFloat(perKmFeeInput.value) || 0;
        const distance = parseFloat(distanceInput.value) || 0;
        const bonus = parseFloat(bonusInput.value) || 0;
        const total = base + (distance * perKm) + bonus;
        totalFeeSpan.textContent = 'KES ' + total.toFixed(2);
        
        // Update hidden inputs
        distanceHidden.value = distance;
        baseFeeHidden.value = base;
        perKmFeeHidden.value = perKm;
        bonusHidden.value = bonus;
    }

    // Add event listeners
    distanceInput.addEventListener('input', calculateFee);
    baseFeeInput.addEventListener('input', calculateFee);
    perKmFeeInput.addEventListener('input', calculateFee);
    bonusInput.addEventListener('input', calculateFee);
    
    // Initialize calculation
    calculateFee();

    // Assign rider function with SweetAlert confirmation
    function assignRider(riderId, riderName) {
        // Get distance for display (can be 0)
        const distance = parseFloat(distanceInput.value) || 0;
        
        // Confirm assignment
        Swal.fire({
            title: 'Assign Rider?',
            html: `
                <div class="text-start">
                    <p>You are about to assign <strong>${riderName}</strong> to this order.</p>
                    <hr>
                    <p><strong>Rider Fee Breakdown:</strong></p>
                    <ul class="list-unstyled">
                        <li>Base Fee: KES ${parseFloat(baseFeeInput.value).toFixed(2)}</li>
                        <li>Distance: ${distance} km × KES ${parseFloat(perKmFeeInput.value).toFixed(2)} = KES ${(distance * parseFloat(perKmFeeInput.value)).toFixed(2)}</li>
                        <li>Bonus: KES ${parseFloat(bonusInput.value).toFixed(2)}</li>
                        <li><strong>Total Rider Fee: ${totalFeeSpan.textContent}</strong></li>
                    </ul>
                    <hr>
                    <p class="text-muted small">The customer has already paid KES {{ number_format($order->delivery_fee ?? 0, 2) }} for delivery.</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#05bb14',
            cancelButtonColor: '#dc3545',
            confirmButtonText: '<i class="bi bi-check-circle"></i> Yes, Assign Rider',
            cancelButtonText: '<i class="bi bi-x-circle"></i> Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Set rider ID and submit form
                riderIdHidden.value = riderId;
                
                // Show loading state
                Swal.fire({
                    title: 'Assigning Rider...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit the form
                assignForm.submit();
            }
        });
    }

    // Attach click handlers to all assign buttons
    document.querySelectorAll('.assign-btn').forEach(button => {
        button.addEventListener('click', function() {
            const riderId = this.getAttribute('data-rider-id');
            const riderName = this.getAttribute('data-rider-name');
            assignRider(riderId, riderName);
        });
    });
    
    // Optional: Auto-calculate distance from rider location (if geolocation API is available)
    function suggestDistanceFromRider(riderLat, riderLng, vendorLat, vendorLng) {
        if (riderLat && riderLng && vendorLat && vendorLng) {
            const distance = calculateHaversineDistance(riderLat, riderLng, vendorLat, vendorLng);
            distanceInput.value = distance.toFixed(1);
            calculateFee();
        }
    }
    
    // Haversine formula for distance calculation
    function calculateHaversineDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Earth's radius in km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }
    
    // Store rider coordinates from server-side (if available)
    @php
        $ridersWithCoords = [];
        foreach($riders ?? [] as $r) {
            if ($r->current_latitude && $r->current_longitude && isset($order->vendor)) {
                $ridersWithCoords[] = [
                    'id' => $r->id,
                    'lat' => (float) $r->current_latitude,
                    'lng' => (float) $r->current_longitude,
                    'name' => $r->user->name
                ];
            }
        }
    @endphp
    
    const ridersCoordinates = @json($ridersWithCoords);
    const vendorLat = {{ $order->vendor->latitude ?? 0 }};
    const vendorLng = {{ $order->vendor->longitude ?? 0 }};
    
    // Add quick distance suggestion buttons next to riders (if coordinates available)
    if (ridersCoordinates.length > 0 && vendorLat && vendorLng) {
        ridersCoordinates.forEach(rider => {
            const distance = calculateHaversineDistance(rider.lat, rider.lng, vendorLat, vendorLng);
            // You can add a button to suggest this distance
            console.log(`Rider ${rider.name} is ${distance.toFixed(1)} km from vendor`);
        });
    }
</script>

<style>
    .assign-btn {
        transition: all 0.2s ease;
    }
    .assign-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .table td {
        vertical-align: middle;
    }
</style>
@endsection