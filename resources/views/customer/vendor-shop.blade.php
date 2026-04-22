@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Vendor Header -->
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="bg-light p-5 rounded" style="border-left: 5px solid var(--primary-green);">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="h3 fw-bold mb-2">{{ $vendor->business_name }}</h1>
                        <p class="text-muted mb-3">{{ $vendor->description ?? 'Welcome to our shop!' }}</p>
                        <div class="d-flex gap-4 align-items-center">
                            <div>
                                <span class="badge" style="background-color: var(--primary-green);">
                                    {{ $vendor->is_verified ? 'Verified' : 'Pending Verification' }}
                                </span>
                            </div>
                            <div>
                                <i class="bi bi-star-fill text-warning"></i>
                                <span class="fw-bold">{{ $vendor->rating ?? '4.5' }}</span>
                                <span class="text-muted">({{ $vendor->reviews_count ?? '0' }} ratings)</span>
                            </div>
                            <div>
                                @if($vendor->is_open)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Shop Open
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="bi bi-lock"></i> Shop Closed
                                    </span>
                                @endif
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

    <!-- Products Grid -->
    @if($products->count() > 0)
        <div class="row">
            <div class="col-md-12">
                <h5 class="fw-bold mb-4">Products from {{ $vendor->business_name }}</h5>
            </div>
        </div>
        <div class="row g-4">
            @foreach($products as $product)
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="card border-0 shadow-sm h-100" style="transition: transform 0.2s;">
                        {{-- In the product card loop --}}
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px; overflow: hidden;">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <div class="text-center">
                                    <svg width="60" height="60" fill="#ccc" viewBox="0 0 24 24">
                                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5.04-6.71l-2.75 3.54-1.3-1.54c-.3-.36-.77-.36-1.06 0l-3.63 4.36V7h13v10h-5.26z"/>
                                    </svg>
                                    <p class="text-muted small mt-2 mb-0">No image</p>
                                </div>
                            @endif
                        </div>
                        <div class="card-body">
                            <h6 class="fw-bold mb-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $product->name }}
                            </h6>
                            <p class="text-muted small mb-3">{{ $product->category->name ?? 'Uncategorized' }}</p>
                            
                            <div class="d-flex justify-content-between align-items-baseline mb-3">
                                <div>
                                    <h5 class="fw-bold" style="color: var(--primary-green); margin: 0;">KES {{ number_format($product->final_price, 0) }}</h5>
                                    @if($product->base_price > $product->final_price)
                                        <span class="text-decoration-line-through text-muted small">KES {{ number_format($product->base_price, 0) }}</span>
                                    @endif
                                </div>
                                <span class="badge bg-success">{{ $product->stock_quantity > 0 ? 'In Stock' : 'Out' }}</span>
                            </div>

                            <div class="d-grid gap-2">
                                <a href="{{ route('customer.products.show', $product->id) }}" class="btn btn-sm btn-outline-secondary">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
            <div class="d-flex justify-content-center mt-5">
                {{ $products->links() }}
            </div>
        @endif
    @else
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            This vendor has no products available at the moment.
        </div>
    @endif
</div>
@endsection
