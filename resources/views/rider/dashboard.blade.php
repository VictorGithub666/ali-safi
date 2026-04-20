@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="fw-bold mb-4">
        <i class="bi bi-truck"></i> Rider Dashboard
    </h2>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #05bb14;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h6 class="text-muted mb-2">Completed Today</h6>
                    <h3 class="fw-bold mb-0">{{ $completedToday ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #237bdd;">
                        <i class="bi bi-box"></i>
                    </div>
                    <h6 class="text-muted mb-2">Available Jobs</h6>
                    <h3 class="fw-bold mb-0">{{ $availableJobs ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #28a745;">
                        <i class="bi bi-wallet"></i>
                    </div>
                    <h6 class="text-muted mb-2">Today's Earnings</h6>
                    <h3 class="fw-bold mb-0">KES {{ number_format($todayEarnings ?? 0, 0) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #ffc107;">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <h6 class="text-muted mb-2">Rating</h6>
                    <h3 class="fw-bold mb-0">{{ number_format($rating ?? 0, 1) }}/5</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Active Deliveries</h6>
                        <a href="{{ route('rider.deliveries') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    @if($activeDeliveries && $activeDeliveries->count())
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Pickup Location</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeDeliveries as $delivery)
                                        <tr>
                                            <td><strong>#{{ $delivery->order_number }}</strong></td>
                                            <td>{{ $delivery->customer->name }}</td>
                                            <td>{{ $delivery->vendor->business_name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge badge-info">{{ ucfirst($delivery->status) }}</span>
                                            </td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-map"></i> View Map
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4 mb-0">No active deliveries. <a href="{{ route('rider.deliveries') }}">Check available jobs</a></p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('rider.deliveries') }}" class="btn btn-primary">
                            <i class="bi bi-truck"></i> View Deliveries
                        </a>
                        <a href="{{ route('rider.earnings') }}" class="btn btn-outline-primary">
                            <i class="bi bi-graph-up"></i> My Earnings
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Availability</h6>
                </div>
                <div class="card-body">
                    @php
                        $rider = Auth::user()->rider;
                        $isAvailable = $rider->is_available ?? false;
                    @endphp
                    <p class="mb-3">
                        Status: 
                        <span class="badge badge-{{ $isAvailable ? 'success' : 'secondary' }}">
                            {{ $isAvailable ? 'Available' : 'Offline' }}
                        </span>
                    </p>
                    <form method="POST" action="{{ route('rider.toggle-availability') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-{{ $isAvailable ? 'outline-danger' : 'success' }} btn-sm w-100">
                            <i class="bi bi-{{ $isAvailable ? 'pause' : 'play' }}"></i> 
                            {{ $isAvailable ? 'Go Offline' : 'Go Online' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
