@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Success Animation -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <div class="mb-4">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white" style="width: 120px; height: 120px; background-color: var(--primary-green);">
                    <i class="bi bi-check-lg" style="font-size: 64px;"></i>
                </div>
            </div>
            <h1 class="fw-bold mb-2">Order Confirmed!</h1>
            <p class="lead text-muted mb-4">Thank you for your order. We've received it and are preparing it for delivery.</p>
            <p class="h5 fw-bold" style="color: var(--primary-green);">Order #ORD-2026-004582</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- What's Next -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">What's Next?</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 text-center">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold" style="width: 50px; height: 50px; background-color: var(--primary-green);" >
                                1
                            </div>
                            <h6 class="fw-bold mt-2 small">Processing</h6>
                            <p class="text-muted small">Within 1 hour</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold" style="width: 50px; height: 50px; background-color: var(--primary-green);">
                                2
                            </div>
                            <h6 class="fw-bold mt-2 small">Picking</h6>
                            <p class="text-muted small">Within 2 hours</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold" style="width: 50px; height: 50px; background-color: var(--primary-green);">
                                3
                            </div>
                            <h6 class="fw-bold mt-2 small">Delivery</h6>
                            <p class="text-muted small">Within 24-48 hrs</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold" style="width: 50px; height: 50px; background-color: var(--primary-green);">
                                4
                            </div>
                            <h6 class="fw-bold mt-2 small">Delivered</h6>
                            <p class="text-muted small">Confirmed</p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="fw-bold mb-3">Key Information:</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex gap-2 mb-3">
                                <i class="bi bi-envelope" style="color: var(--primary-green); font-size: 20px;"></i>
                                <div>
                                    <p class="small text-muted mb-1">Confirmation Email</p>
                                    <p class="small fw-bold mb-0">Sent to john@example.com</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2 mb-3">
                                <i class="bi bi-telephone" style="color: var(--primary-green); font-size: 20px;"></i>
                                <div>
                                    <p class="small text-muted mb-1">SMS Notification</p>
                                    <p class="small fw-bold mb-0">Sent to +254712345678</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2 mb-3">
                                <i class="bi bi-truck" style="color: var(--primary-green); font-size: 20px;"></i>
                                <div>
                                    <p class="small text-muted mb-1">Expected Delivery</p>
                                    <p class="small fw-bold mb-0">April 16, 2026</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2 mb-3">
                                <i class="bi bi-map-pin" style="color: var(--primary-green); font-size: 20px;"></i>
                                <div>
                                    <p class="small text-muted mb-1">Delivery Location</p>
                                    <p class="small fw-bold mb-0">Westlands, Nairobi</p>
                                </div>
                            </div>
                        </div>
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
                        <div class="col-md-10">
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <h6 class="fw-bold mb-1">12 Kg Gas Cylinder</h6>
                                    <p class="text-muted small mb-0">Ali Safi Gas Supplies</p>
                                </div>
                                <div class="fw-bold" style="color: var(--primary-green);">KES 2,598</div>
                            </div>
                            <div class="d-flex gap-3 mt-3">
                                <span class="small"><strong>Size:</strong> 12 Kg</span>
                                <span class="small"><strong>Quantity:</strong> 2</span>
                            </div>
                        </div>
                    </div>

                    <!-- Item 2 -->
                    <div class="row g-3">
                        <div class="col-md-2">
                            <div class="bg-light rounded p-2" style="height: 100px;"></div>
                        </div>
                        <div class="col-md-10">
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <h6 class="fw-bold mb-1">Gas Regulator</h6>
                                    <p class="text-muted small mb-0">Ali Safi Gas Supplies</p>
                                </div>
                                <div class="fw-bold" style="color: var(--primary-green);">KES 450</div>
                            </div>
                            <div class="mt-3">
                                <span class="small"><strong>Quantity:</strong> 1</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery Address -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Delivery Address</h5>
                </div>
                <div class="card-body">
                    <p class="fw-bold mb-1">John Kariuki</p>
                    <p class="text-muted small mb-3">
                        House No. 45, Ring Road, Off Westland Road, Next to ABC Supermarket<br>
                        Westlands, Nairobi<br>
                        +254712345678
                    </p>
                    <button class="btn btn-sm btn-outline-secondary" type="button">
                        <i class="bi bi-pencil me-1"></i> Modify Address
                    </button>
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
                        <span class="text-muted">Subtotal</span>
                        <span>KES 3,048</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Delivery</span>
                        <span style="color: var(--primary-green);">FREE</span>
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

                    <div>
                        <p class="small fw-bold mb-1">Payment Method</p>
                        <p class="small text-muted mb-1">M-Pesa on Delivery</p>
                        <span class="badge bg-warning text-dark">Pending Payment</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Quick Actions</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('customer.orders.track', 1) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-compass me-1"></i> Track Order
                    </a>
                    <a href="{{ route('customer.orders.show', 1) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-receipt me-1"></i> View Details
                    </a>
                    <button class="btn btn-outline-secondary btn-sm" type="button">
                        <i class="bi bi-download me-1"></i> Download Invoice
                    </button>
                </div>
            </div>

            <!-- Continue Shopping -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center py-4">
                    <h6 class="fw-bold mb-3">Happy with your order?</h6>
                    <a href="{{ route('customer.products.index') }}" class="btn" style="background-color: var(--primary-green); color: white; width: 100%;">
                        <i class="bi bi-shop me-1"></i> Continue Shopping
                    </a>
                </div>
            </div>

            <!-- Help Section -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Need Help?</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">Have questions about your order? Visit our help center or contact support.</p>
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-question-circle me-1"></i> Help Center
                        </a>
                        <button class="btn btn-sm btn-outline-secondary" type="button">
                            <i class="bi bi-chat-dots me-1"></i> Contact Support
                        </button>
                    </div>

                    <hr>

                    <h6 class="fw-bold small mb-2">Important Info</h6>
                    <ul class="list-unstyled small text-muted">
                        <li class="mb-1">✓ Free shipping on all orders</li>
                        <li class="mb-1">✓ 30-day return policy</li>
                        <li class="mb-1">✓ Secure payment guaranteed</li>
                        <li>✓ Track your order anytime</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Similar Products Section -->
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="fw-bold mb-4">You Might Also Like</h3>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="bg-light p-3" style="height: 180px;"></div>
                        <div class="card-body">
                            <h6 class="fw-bold mb-2">6 Kg Gas Cylinder</h6>
                            <p class="text-success fw-bold mb-3">KES 799</p>
                            <button class="btn btn-sm btn-outline-secondary w-100" type="button">View Product</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="bg-light p-3" style="height: 180px;"></div>
                        <div class="card-body">
                            <h6 class="fw-bold mb-2">Gas Hose Pipe</h6>
                            <p class="text-success fw-bold mb-3">KES 350</p>
                            <button class="btn btn-sm btn-outline-secondary w-100" type="button">View Product</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="bg-light p-3" style="height: 180px;"></div>
                        <div class="card-body">
                            <h6 class="fw-bold mb-2">Cooker Stand</h6>
                            <p class="text-success fw-bold mb-3">KES 1,499</p>
                            <button class="btn btn-sm btn-outline-secondary w-100" type="button">View Product</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="bg-light p-3" style="height: 180px;"></div>
                        <div class="card-body">
                            <h6 class="fw-bold mb-2">Fire Extinguisher</h6>
                            <p class="text-success fw-bold mb-3">KES 2,999</p>
                            <button class="btn btn-sm btn-outline-secondary w-100" type="button">View Product</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
