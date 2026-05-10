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
                                                <span class="badge badge-info">{{ ucfirst($delivery->status) }}</span>
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

<script>
function acceptOrder(orderId) {
    if ({{ $rider->is_available ? 'true' : 'false' }}) {
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
    } else {
        Swal.fire('Offline', 'You must be online to accept orders. Toggle your availability first.', 'warning');
    }
}

function toggleAvailability() {
    const btn = document.getElementById('availabilityBtn');
    const isAvailable = btn.textContent.includes('Available');
    
    fetch('/rider/toggle-availability', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              Swal.fire({
                  title: 'Updated!',
                  text: data.message,
                  icon: 'success',
                  confirmButtonColor: '#05bb14'
              }).then(() => location.reload());
          }
      });
}

function showDeliveryDetails(orderId) {
    // This will navigate to delivery details page
    window.location.href = `/rider/deliveries/${orderId}`;
}
</script>
@endsection
