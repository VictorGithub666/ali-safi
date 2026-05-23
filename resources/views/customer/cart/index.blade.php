@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold mb-1">Shopping Cart</h2>
            <p class="text-muted">You have {{ count($cartItems) }} item{{ count($cartItems) !== 1 ? 's' : '' }} in your cart</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('customer.products.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Continue Shopping
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Cart Items -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Items in Your Cart ({{ count($cartItems) }})</h5>
                </div>
                <div class="card-body">
                    @if($cartItems->count())
                        @foreach($cartItems as $item)
                            @php
                                // Check if product is still in stock
                                $vendorProduct = $item->vendor->products()
                                    ->where('product_id', $item->product_id)
                                    ->first();
                                $isInStock = $vendorProduct && $vendorProduct->pivot->stock_quantity >= $item->quantity;
                                $currentStock = $vendorProduct ? $vendorProduct->pivot->stock_quantity : 0;
                            @endphp
                            
                            @if(!$isInStock)
                                {{-- Show out of stock warning --}}
                                @include('customer.cart.partials.out-of-stock-item', ['item' => $item])
                            @else
                                {{-- Normal in-stock item display --}}
                                <div class="row g-3 mb-4 pb-3 border-bottom">
                                    <!-- ... existing item display code ... -->
                                    <div class="col-md-2">
                                        <!-- ... existing image code ... -->
                                    </div>
                                    <div class="col-md-5">
                                        <h6 class="fw-bold mb-2">{{ $item->product->name }}</h6>
                                        <p class="text-muted small mb-2">{{ $item->vendor->business_name ?? 'Vendor' }}</p>
                                        <div class="d-flex gap-3 mb-3">
                                            @if($item->size)
                                                <span class="small"><strong>Size:</strong> {{ $item->size }}</span>
                                            @endif
                                            <span class="small"><strong>Category:</strong> {{ $item->product->category->name ?? 'N/A' }}</span>
                                        </div>
                                        <p class="small">
                                            <span class="badge bg-info">Free Delivery</span>
                                            @if($currentStock < 10)
                                                <span class="badge bg-warning ms-2">Only {{ $currentStock }} left!</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-5">
                                        <!-- ... rest of existing item display ... -->
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <p class="text-center text-muted py-5">Your cart is empty. <a href="{{ route('customer.products.index') }}">Continue shopping</a></p>
                    @endif
                </div>
            </div>

            <!-- Promo Code -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Apply Discount Code</h5>
                </div>
                <div class="card-body">
                    <form class="d-flex gap-2">
                        <input type="text" class="form-control" placeholder="Enter promo code" value="">
                        <button class="btn btn-outline-secondary" type="button">Apply</button>
                    </form>
                    <p class="small text-muted mt-2 mb-0 ">
                        <i class="bi bi-lightbulb me-1"></i> Don't have a code? Subscribe to get 10% off!
                    </p>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <!-- Price Breakdown -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal ({{ count($cartItems) }} item{{ count($cartItems) !== 1 ? 's' : '' }})</span>
                            <span>KES {{ number_format($total, 0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Delivery Fee</span>
                            <span style="color: var(--primary-green);" class="fw-bold">FREE</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Discount</span>
                            <span>-KES 0</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Tax</span>
                            <span>KES 0</span>
                        </div>
                    </div>

                    <hr>

                    <!-- Total -->
                    <div class="d-flex justify-content-between mb-4">
                        <h6 class="fw-bold mb-0">Total Amount</h6>
                        <h5 class="fw-bold mb-0" style="color: var(--primary-green);">KES {{ number_format($total, 0) }}</h5>
                    </div>

                    <!-- Savings Badge -->
                    <div class="alert alert-success mb-4" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>You save KES 500!</strong> Free delivery on this order
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 mb-3">
                        <a href="{{ route('customer.checkout') }}" class="btn text-white" style="background-color: var(--primary-green);">
                            <i class="bi bi-bag-check me-2"></i> Proceed to Checkout
                        </a>
                        <a href="{{ route('customer.products.index') }}" class="btn btn-light border-2" style="border-color: var(--primary-green); color: var(--primary-green);">
                            <i class="bi bi-bag-plus me-2"></i> Continue Shopping
                        </a>
                    </div>

                    <!-- Info Box -->
                    <div class="p-3 rounded mb-3" style="background-color: #f8f9fa;">
                        <p class="small fw-bold mb-2">
                            <i class="bi bi-truck" style="color: var(--primary-green);"></i> Delivery Benefits
                        </p>
                        <ul class="list-unstyled small text-muted mb-0">
                            <li class="mb-1">✓ FREE delivery (order above KES 500)</li>
                            <li class="mb-1">✓ Delivery in 1-3 business days</li>
                            <li>✓ Same-day delivery available</li>
                        </ul>
                    </div>

                    <!-- Secure Checkout -->
                    <div class="p-3 rounded" style="background-color: #f8f9fa; border: 1px solid #dee2e6;">
                        <p class="small fw-bold mb-2">
                            <i class="bi bi-shield-check" style="color: var(--primary-green);"></i> Secure Checkout
                        </p>
                        <p class="small text-muted mb-0">Your payment information is safe and encrypted. We use industry-standard security protocols.</p>
                    </div>
                </div>
            </div>

            <!-- Seller Info -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Sold & Shipped By</h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold mb-2">Ali Safi Gas Supplies</h6>
                    <div class="d-flex gap-1 mb-3">
                        <i class="bi bi-star-fill text-warning" style="font-size: 14px;"></i>
                        <i class="bi bi-star-fill text-warning" style="font-size: 14px;"></i>
                        <i class="bi bi-star-fill text-warning" style="font-size: 14px;"></i>
                        <i class="bi bi-star-fill text-warning" style="font-size: 14px;"></i>
                        <i class="bi bi-star-half text-warning" style="font-size: 14px;"></i>
                    </div>
                    <p class="small text-muted mb-2">4.8 rating (1,203 reviews)</p>
                    <button class="btn btn-sm btn-outline-secondary w-100 mb-2" type="button">
                        <i class="bi bi-shop me-1"></i> Visit Store
                    </button>
                    <button class="btn btn-sm btn-outline-secondary w-100" type="button">
                        <i class="bi bi-chat-dots me-1"></i> Contact Seller
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty Cart Message (Hidden by default) -->
    <div class="text-center py-5" style="display: none;">
        <svg width="100" height="100" fill="#ccc" viewBox="0 0 24 24" class="mb-3">
            <path d="M7 4V2h10v2h5v2h-1l-1.1 11.3c-.1 1.1-1 1.9-2.1 1.9H6.1c-1.1 0-2-0.8-2.1-1.9L3 6H2V4h5zm2 16c0 1.1-0.9 2-2 2s-2-0.9-2-2 0.9-2 2-2 2 0.9 2 2zm10 0c0 1.1-0.9 2-2 2s-2-0.9-2-2 0.9-2 2-2 2 0.9 2 2z"/>
        </svg>
        <h5 class="fw-bold mt-3">Your Cart is Empty</h5>
        <p class="text-muted">Add some products to get started!</p>
        <a href="{{ route('customer.products.index') }}" class="btn" style="background-color: var(--primary-green); color: white;">
            <i class="bi bi-shop me-2"></i> Start Shopping
        </a>
    </div>
</div>
@endsection