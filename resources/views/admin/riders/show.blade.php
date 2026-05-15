@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between mb-4">
        <h1><i class="bi bi-person-video3"></i> {{ $rider->user->name }}</h1>
        <div>
            <a href="{{ route('admin.riders.edit', $rider) }}" class="btn btn-warning"><i class="bi bi-pencil"></i> Edit</a>
            <form method="POST" action="{{ route('admin.riders.destroy', $rider) }}" style="display:inline;" onsubmit="return confirm('Delete this rider?');">
                @csrf 
                @method('DELETE')
                <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Delete</button>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>{{ $rider->orders_count ?? 0 }}</h5>
                    <p class="text-muted">Deliveries</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5><i class="bi bi-star-fill" style="color:#ffc107;"></i> {{ number_format($rider->rating ?? 0, 1) }}</h5>
                    <p class="text-muted">Rating</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="text-success">KES {{ number_format($rider->wallet_balance ?? 0, 2) }}</h5>
                    <p class="text-muted">Wallet Balance</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>
                        <span class="badge {{ $rider->is_available ? 'bg-success' : 'bg-danger' }}" style="font-size: 1rem;">
                            {{ $rider->is_available ? 'Available' : 'Busy' }}
                        </span>
                    </h5>
                    <p class="text-muted">Status</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5>Personal Information</h5>
                    <dl class="row">
                        <dt class="col-sm-3">Name</dt>
                        <dd class="col-sm-9">{{ $rider->user->name }}</dd>
                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9">{{ $rider->user->email }}</dd>
                        <dt class="col-sm-3">Phone</dt>
                        <dd class="col-sm-9">{{ $rider->user->phone }}</dd>
                        <dt class="col-sm-3">Member Since</dt>
                        <dd class="col-sm-9">{{ $rider->created_at->format('M d, Y') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5>Vehicle Information</h5>
                    <dl class="row">
                        <dt class="col-sm-4">Vehicle Type</dt>
                        <dd class="col-sm-8">{{ ucfirst($rider->vehicle_type) }}</dd>
                        <dt class="col-sm-4">Vehicle Number</dt>
                        <dd class="col-sm-8">{{ $rider->vehicle_number }}</dd>
                        <dt class="col-sm-4">License Number</dt>
                        <dd class="col-sm-8">{{ $rider->license_number }}</dd>
                        <dt class="col-sm-4">Verification</dt>
                        <dd class="col-sm-8">
                            @if($rider->is_verified)
                                <span class="badge bg-success">Verified</span>
                            @else
                                <span class="badge bg-warning">Pending Verification</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Deliveries Section --}}
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Deliveries</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Vendor</th>
                        <th>Delivery Fee</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rider->orders()->latest()->take(10)->get() as $order)
                        <tr>
                            <td><strong>#{{ $order->order_number }}</strong></td>
                            <td>{{ $order->customer->name ?? 'N/A' }}</td>
                            <td>{{ $order->vendor->business_name ?? 'N/A' }}</td>
                            <td>KES {{ number_format($order->delivery_fee, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-3">No deliveries yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection