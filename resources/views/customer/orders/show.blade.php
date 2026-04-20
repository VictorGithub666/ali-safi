@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('customer.orders.index') }}" class="text-decoration-none">My Orders</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Order #ORD-2026-004582</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-1">Order #ORD-2026-004582</h2>
            <p class="text-muted">Placed on April 15, 2026 at 2:30 PM</p>
        </div>
        <div class="col-md-4 text-md-end">
            <span class="badge bg-warning text-dark fs-6">
                <i class="bi bi-truck"></i> In Transit
            </span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Order Timeline/Status -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Order Status</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Step 1: Order Confirmed -->
                        <div class="col-md-3 text-center">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold" style="width: 50px; height: 50px; background-color: var(--primary-green);">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <h6 class="mt-2 fw-bold">Order Confirmed</h6>
                            <p class="text-muted small">April 15, 2:30 PM</p>
                        </div>
                        <!-- Step 2: Processing -->
                        <div class="col-md-3 text-center">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold" style="width: 50px; height: 50px; background-color: var(--primary-green);">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <h6 class="mt-2 fw-bold">Processing</h6>
                            <p class="text-muted small">April 15, 3:15 PM</p>
                        </div>
                        <!-- Step 3: Shipped -->
                        <div class="col-md-3 text-center">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold" style="width: 50px; height: 50px; background-color: var(--primary-green);">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <h6 class="mt-2 fw-bold">Shipped</h6>
                            <p class="text-muted small">April 15, 4:45 PM</p>
                        </div>
                        <!-- Step 4: In Transit -->
                        <div class="col-md-3 text-center">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold" style="width: 50px; height: 50px; background-color: var(--primary-blue);">
                                <i class="bi bi-truck"></i>
                            </div>
                            <h6 class="mt-2 fw-bold">In Transit</h6>
                            <p class="text-muted small">Expected Apr 16</p>
                        </div>
                    </div>
                    <div class="mt-4 p-3 rounded" style="background-color: #f8f9fa;">
                        <p class="text-muted small mb-1">
                            <i class="bi bi-info-circle me-2"></i> Current Status
                        </p>
                        <p class="fw-bold mb-0">Your order is on the way! Your package will arrive by April 16, 2026. You'll receive a notification when it's out for delivery.</p>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Order Items (2)</h5>
                </div>
                <div class="card-body">
                    <!-- Item 1 -->
                    <div class="row g-3 mb-4 pb-3 border-bottom">
                        <div class="col-md-2">
                            <div class="bg-light rounded p-2" style="height: 100px;"></div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-2">12 Kg Gas Cylinder</h6>
                            <p class="text-muted small mb-2">Product ID: PROD-001</p>
                            <p class="small mb-2">
                                <span class="badge bg-light text-dark">Size: 12 Kg</span>
                                <span class="badge bg-light text-dark">Color: Standard</span>
                            </p>
                            <p class="text-muted small mb-0">Sold by: <strong>Ali Safi Gas Supplies</strong></p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <p class="text-muted small mb-1">Price: KES 1,299</p>
                            <p class="text-muted small mb-1">Quantity: 2</p>
                            <p class="fw-bold" style="color: var(--primary-green);">KES 2,598</p>
                            <button class="btn btn-sm btn-link text-decoration-none mt-2">Write a Review</button>
                        </div>
                    </div>

                    <!-- Item 2 -->
                    <div class="row g-3 pb-0">
                        <div class="col-md-2">
                            <div class="bg-light rounded p-2" style="height: 100px;"></div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-2">Gas Regulator</h6>
                            <p class="text-muted small mb-2">Product ID: PROD-010</p>
                            <p class="small mb-2">
                                <span class="badge bg-light text-dark">Standard Quality</span>
                            </p>
                            <p class="text-muted small mb-0">Sold by: <strong>Ali Safi Gas Supplies</strong></p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <p class="text-muted small mb-1">Price: KES 450</p>
                            <p class="text-muted small mb-1">Quantity: 1</p>
                            <p class="fw-bold" style="color: var(--primary-green);">KES 450</p>
                            <button class="btn btn-sm btn-link text-decoration-none mt-2">Write a Review</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery Information -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Delivery Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Delivered To</h6>
                            <p class="fw-bold mb-1">John Kariuki</p>
                            <p class="text-muted small mb-3">
                                House No. 45, Ring Road, Off Westland Road, Next to ABC Supermarket<br>
                                Westlands, Nairobi
                            </p>
                            <p class="small mb-1">
                                <i class="bi bi-envelope"></i> john@example.com
                            </p>
                            <p class="small mb-0">
                                <i class="bi bi-phone"></i> +254712345678
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Delivery Details</h6>
                            <p class="small mb-2">
                                <strong>Service:</strong> Standard Delivery
                            </p>
                            <p class="small mb-2">
                                <strong>Expected Delivery:</strong> April 16, 2026
                            </p>
                            <p class="small mb-2">
                                <strong>Delivery Type:</strong> Leave with recipient
                            </p>
                            <p class="small mb-2">
                                <strong>Rider:</strong> James Kipchoge
                            </p>
                            <p class="small mb-0">
                                <i class="bi bi-phone"></i>
                                <a href="tel:+254712345678" class="text-decoration-none">+254712345678</a>
                            </p>
                        </div>
                    </div>

                    <!-- Map Placeholder -->
                    <div class="mt-4" style="background-color: #e9ecef; border-radius: 8px; height: 250px; display: flex; align-items: center; justify-content: center;">
                        <div class="text-center">
                            <i class="bi bi-map" style="font-size: 48px; color: #999;"></i>
                            <p class="text-muted mt-2 mb-0">Live tracking map would appear here</p>
                        </div>
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('customer.orders.track', 1) }}" class="btn btn-sm" style="background-color: var(--primary-green); color: white;">
                            <i class="bi bi-compass"></i> View Real-time Tracking
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Order Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal (2 items)</span>
                        <span>KES 3,048</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Delivery Fee</span>
                        <span>FREE</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Packaging Fee</span>
                        <span>KES 50</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <h6 class="fw-bold mb-0">Total Amount</h6>
                        <h5 class="fw-bold mb-0" style="color: var(--primary-green);">KES 3,098</h5>
                    </div>

                    <hr>

                    <!-- Payment Info -->
                    <h6 class="fw-bold mb-3">Payment Details</h6>
                    <p class="small mb-1">
                        <strong>Method:</strong> M-Pesa on Delivery
                    </p>
                    <p class="small mb-1">
                        <strong>Status:</strong>
                        <span class="badge bg-warning text-dark">Pending</span>
                    </p>
                    <p class="small mb-0">
                        <strong>Reference:</strong> ORD-2026-004582
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Actions</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('customer.orders.track', 1) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-compass me-2"></i> Track Delivery
                    </a>
                    <button class="btn btn-outline-secondary btn-sm" type="button">
                        <i class="bi bi-download me-2"></i> Download Invoice
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" type="button">
                        <i class="bi bi-printer me-2"></i> Print Order
                    </button>
                </div>
            </div>

            <!-- Support -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Need Help?</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">Contact our support team if you have any questions about this order.</p>
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-secondary btn-sm" type="button">
                            <i class="bi bi-chat-dots me-2"></i> Contact Support
                        </button>
                        <button class="btn btn-outline-danger btn-sm" type="button">
                            <i class="bi bi-x-circle me-2"></i> Cancel Order
                        </button>
                    </div>

                    <hr>

                    <h6 class="fw-bold small mb-2">Return Policy</h6>
                    <p class="small text-muted mb-0">You can return or exchange items within 30 days of delivery. Read our <a href="#" class="text-decoration-none">return policy</a> for more information.</p>
                </div>
            </div>

            <!-- Related Orders -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">More from this Vendor</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">Explore more products from Ali Safi Gas Supplies</p>
                    <a href="#" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-shop me-1"></i> Visit Shop
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
