@extends('layouts.app')

@use('App\Services\DistanceService')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('rider.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Delivery Details</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-1">Delivery Details</h2>
                    <p class="text-muted">Order #{{ $order->order_number }}</p>
                </div>
                <span class="badge bg-info fs-6">
                    <i class="bi bi-truck"></i> {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Order Details Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title fw-bold mb-0">Order Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Pickup Information -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3"><i class="bi bi-shop"></i> Pickup Location (Vendor)</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                <p class="small text-muted mb-1">Business Name</p>
                                <p class="fw-bold mb-3">{{ $order->vendor->business_name }}</p>
                                
                                <p class="small text-muted mb-1">Address</p>
                                <p class="mb-3">
                                    <strong>{{ $order->vendor->business_address }}</strong>
                                </p>
                                
                                <p class="small text-muted mb-1">Coordinates</p>
                                <p class="small mb-2">
                                    <strong>Latitude:</strong> {{ $order->vendor->latitude }}<br>
                                    <strong>Longitude:</strong> {{ $order->vendor->longitude }}
                                </p>
                                
                                @if(isset($order->distance_to_vendor_formatted))
                                    <div class="border-top pt-3">
                                        <p class="small text-muted mb-1">Distance from your location</p>
                                        <p class="fw-bold text-success">{{ $order->distance_to_vendor_formatted }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Delivery Information -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3"><i class="bi bi-pin-map"></i> Delivery Location (Customer)</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                <p class="small text-muted mb-1">Customer Name</p>
                                <p class="fw-bold mb-3">{{ $order->customer->name }}</p>
                                
                                <p class="small text-muted mb-1">Delivery Address</p>
                                <p class="mb-3">
                                    <strong>{{ $order->delivery_address }}</strong><br>
                                    <small class="text-muted">{{ $order->ward }}, {{ $order->sub_county }}, {{ $order->county }}</small>
                                </p>
                                
                                <p class="small text-muted mb-1">Coordinates</p>
                                <p class="small mb-2">
                                    <strong>Latitude:</strong> {{ $order->delivery_latitude }}<br>
                                    <strong>Longitude:</strong> {{ $order->delivery_longitude }}
                                </p>
                                
                                @if(isset($order->delivery_distance_formatted))
                                    <div class="border-top pt-3">
                                        <p class="small text-muted mb-1">Distance from vendor</p>
                                        <p class="fw-bold text-success">{{ $order->delivery_distance_formatted }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title fw-bold mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->product->name }}</strong>
                                        </td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>KES {{ number_format($item->price, 0) }}</td>
                                        <td><strong>KES {{ number_format($item->total, 0) }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Special Instructions Card -->
            @if($order->special_instructions)
                <div class="card border-0 shadow-sm border-warning">
                    <div class="card-header bg-warning bg-opacity-10">
                        <h5 class="card-title fw-bold mb-0"><i class="bi bi-info-circle"></i> Special Instructions</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $order->special_instructions }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Contact Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title fw-bold mb-0">Contact Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Customer Phone</label>
                        <p class="mb-0">
                            <strong>{{ $order->phone }}</strong><br>
                            <a href="tel:{{ $order->phone }}" class="btn btn-sm btn-outline-success mt-2">
                                <i class="bi bi-telephone"></i> Call Customer
                            </a>
                        </p>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small">Vendor Phone</label>
                        <p class="mb-0">
                            <strong>{{ $order->vendor->business_phone }}</strong><br>
                            <a href="tel:{{ $order->vendor->business_phone }}" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="bi bi-telephone"></i> Call Vendor
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title fw-bold mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <strong>KES {{ number_format($order->subtotal, 0) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Platform Fee</span>
                            <strong>KES {{ number_format($order->platform_fee, 0) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Delivery Fee</span>
                            <strong class="text-success">KES {{ number_format($order->delivery_fee, 0) }}</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-0">
                            <span class="fw-bold">Total Amount</span>
                            <strong class="fw-bold" style="color: var(--primary-green); font-size: 1.2rem;">KES {{ number_format($order->total, 0) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            @if($order->status === 'picked_up' || $order->status === 'on_the_way')
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title fw-bold mb-0">Actions</h5>
                    </div>
                    <div class="card-body d-grid gap-2">
                        <button type="button" class="btn btn-success btn-lg" onclick="completeDelivery({{ $order->id }})">
                            <i class="bi bi-check-lg"></i> Mark as Delivered
                        </button>
                        <a href="{{ route('rider.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-grid gap-2">
                        <a href="{{ route('rider.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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
                      }).then(() => {
                          window.location.href = '{{ route("rider.dashboard") }}';
                      });
                  } else {
                      Swal.fire('Error', data.error || 'Failed to complete delivery', 'error');
                  }
              });
        }
    });
}
</script>
@endsection
