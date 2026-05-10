@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="bi bi-list-task"></i> My Deliveries
        </h2>
        <a href="{{ route('rider.dashboard') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                <i class="bi bi-list"></i> All Deliveries
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                <i class="bi bi-hourglass"></i> In Progress
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                <i class="bi bi-check-circle"></i> Completed
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- All Deliveries -->
        <div class="tab-pane fade show active" id="all" role="tabpanel">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">All Deliveries</h6>
                </div>
                <div class="card-body">
                    @if($myDeliveries->count())
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>From Vendor</th>
                                        <th>Customer Location</th>
                                        <th>Status</th>
                                        <th>Delivery Fee</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($myDeliveries as $delivery)
                                        <tr>
                                            <td><strong>#{{ $delivery->order_number }}</strong></td>
                                            <td>{{ $delivery->vendor->business_name }}</td>
                                            <td>
                                                <small class="text-muted">{{ $delivery->ward }}<br>{{ $delivery->sub_county }}</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $delivery->status === 'delivered' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($delivery->status) }}
                                                </span>
                                            </td>
                                            <td><strong class="text-success">KES {{ number_format($delivery->delivery_fee, 0) }}</strong></td>
                                            <td><small>{{ $delivery->created_at->format('M d, H:i') }}</small></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewDetails({{ $delivery->id }})">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> No deliveries yet. Check the dashboard for available orders!
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- In Progress Deliveries -->
        <div class="tab-pane fade" id="pending" role="tabpanel">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Active/In Progress Deliveries</h6>
                </div>
                <div class="card-body">
                    @php
                        $inProgressDeliveries = $myDeliveries->whereIn('status', ['picked_up', 'in_transit']);
                    @endphp
                    @if($inProgressDeliveries->count())
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>From Vendor</th>
                                        <th>To Location</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inProgressDeliveries as $delivery)
                                        <tr>
                                            <td><strong>#{{ $delivery->order_number }}</strong></td>
                                            <td>{{ $delivery->vendor->business_name }}</td>
                                            <td>
                                                <small class="text-muted">{{ $delivery->customer->name }}<br>{{ $delivery->delivery_address }}</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-warning">{{ ucfirst($delivery->status) }}</span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-success" onclick="completeDelivery({{ $delivery->id }})">
                                                    <i class="bi bi-check-lg"></i> Complete
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> No active deliveries. Accept orders from the dashboard to get started!
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Completed Deliveries -->
        <div class="tab-pane fade" id="completed" role="tabpanel">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Completed Deliveries</h6>
                </div>
                <div class="card-body">
                    @php
                        $completedDeliveries = $myDeliveries->where('status', 'delivered');
                    @endphp
                    @if($completedDeliveries->count())
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Location</th>
                                        <th>Delivery Fee</th>
                                        <th>Completed</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($completedDeliveries as $delivery)
                                        <tr>
                                            <td><strong>#{{ $delivery->order_number }}</strong></td>
                                            <td>{{ $delivery->customer->name }}</td>
                                            <td>
                                                <small class="text-muted">{{ $delivery->ward }}<br>{{ $delivery->sub_county }}</small>
                                            </td>
                                            <td><strong class="text-success">KES {{ number_format($delivery->delivery_fee, 0) }}</strong></td>
                                            <td><small>{{ $delivery->delivered_at ? $delivery->delivered_at->format('M d, H:i') : 'N/A' }}</small></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-secondary" onclick="viewDetails({{ $delivery->id }})">
                                                    <i class="bi bi-receipt"></i> Receipt
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> No completed deliveries yet. Start accepting orders to build your history!
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewDetails(deliveryId) {
    // Navigate to delivery details page
    window.location.href = `/rider/deliveries/${deliveryId}`;
}

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
</script>
@endsection
