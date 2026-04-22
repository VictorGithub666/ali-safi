@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Vendor Header -->
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="bg-light p-5 rounded" style="border-left: 5px solid #dc3545;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="h3 fw-bold mb-2">{{ $vendor->business_name }}</h1>
                        <p class="text-muted mb-3">{{ $vendor->description ?? 'Welcome to our shop!' }}</p>
                        <div class="d-flex gap-4 align-items-center">
                            <div>
                                <span class="badge bg-danger">
                                    <i class="bi bi-lock"></i> Shop Closed
                                </span>
                            </div>
                            <div>
                                <i class="bi bi-star-fill text-warning"></i>
                                <span class="fw-bold">{{ $vendor->rating ?? '0.0' }}</span>
                                <span class="text-muted">({{ $vendor->reviews_count ?? '0' }} ratings)</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="{{ route('customer.products.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i> Back to All Products
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Closed Message -->
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="bi bi-shop" style="font-size: 3rem; color: #dc3545;"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-3">Shop is Currently Closed</h3>
                    <p class="text-muted mb-4">
                        {{ $vendor->business_name }} is currently closed and not accepting orders at this time.
                    </p>
                    
                    @if($vendor->operating_hours)
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Operating Hours</h6>
                            @php
                                $hours = is_string($vendor->operating_hours) 
                                    ? json_decode($vendor->operating_hours, true) 
                                    : $vendor->operating_hours;
                            @endphp
                            @if($hours)
                                <div class="row justify-content-center">
                                    <div class="col-md-8">
                                        <table class="table table-sm">
                                            <tbody>
                                                @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                                    <tr>
                                                        <td class="text-end fw-bold">{{ ucfirst($day) }}</td>
                                                        <td class="text-start">{{ $hours[$day] ?? 'Closed' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                    
                    <p class="text-muted mb-4">
                        Please check back during operating hours to place an order.
                    </p>
                    
                    <div class="d-grid gap-2 d-md-block">
                        <a href="{{ route('customer.products.index') }}" class="btn btn-primary">
                            <i class="bi bi-shop me-2"></i> Browse Other Shops
                        </a>
                        <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-house me-2"></i> Go to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection