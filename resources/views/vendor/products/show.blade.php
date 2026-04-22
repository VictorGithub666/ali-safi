@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-box-seam"></i> {{ $product->name }}
            </h2>
            <p class="text-muted mb-0">Product Details</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('vendor.products.edit', $product) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Product Image & Basic Info -->
        <div class="col-lg-4 mb-4">
            <div class="card mb-3">
                <div class="card-body p-3">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" 
                             alt="{{ $product->name }}" class="img-fluid rounded mb-3">
                    @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" 
                             style="height: 300px;">
                            <div class="text-center">
                                <i class="bi bi-image" style="font-size: 3rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">No image available</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Info Card -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning"></i> Quick Info
                    </h6>
                </div>
                <div class="card-body small">
                    <div class="mb-3">
                        <p class="text-muted mb-1">Base Price</p>
                        <h5 class="mb-0">KES {{ number_format($product->price, 2) }}</h5>
                    </div>
                    <div class="mb-3">
                        <p class="text-muted mb-1">Current Stock</p>
                        <div>
                            <span class="badge bg-{{ $productData->stock_quantity > 10 ? 'success' : ($productData->stock_quantity > 0 ? 'warning' : 'danger') }} fs-6">
                                {{ $productData->stock_quantity }}
                            </span>
                            <span class="text-muted">units</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <p class="text-muted mb-1">Status</p>
                        <div>
                            @if($product->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Availability</p>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" 
                                   {{ $productData->is_available ? 'checked' : '' }} disabled>
                            <label class="form-check-label">
                                {{ $productData->is_available ? 'Available' : 'Unavailable' }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Details -->
        <div class="col-lg-8">
            <!-- Description -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-file-text"></i> Description
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $product->description }}</p>
                </div>
            </div>

            <!-- Details -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1">Category</p>
                            <p class="mb-0">
                                <span class="badge bg-light text-dark">
                                    {{ $product->category->name ?? 'N/A' }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1">Product ID</p>
                            <p class="mb-0"><code>{{ $product->id }}</code></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1">Created</p>
                            <p class="mb-0">{{ $product->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1">Last Updated</p>
                            <p class="mb-0">{{ $product->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-tag"></i> Pricing
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1">Base Price</p>
                            <h5 class="mb-0">KES {{ number_format($product->price, 2) }}</h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1">Custom Price</p>
                            @if($productData->custom_price)
                                <h5 class="mb-0">KES {{ number_format($productData->custom_price, 2) }}</h5>
                            @else
                                <p class="mb-0 text-muted">Not set (using base price)</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-boxes"></i> Inventory
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1">Stock Quantity</p>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="mb-0">{{ $productData->stock_quantity }}</h5>
                                <span class="badge bg-{{ $productData->stock_quantity > 10 ? 'success' : ($productData->stock_quantity > 0 ? 'warning' : 'danger') }}">
                                    {{ $productData->stock_quantity > 10 ? 'Well Stocked' : ($productData->stock_quantity > 0 ? 'Low Stock' : 'Out of Stock') }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1">Availability</p>
                            <p class="mb-0">
                                @if($productData->is_available)
                                    <span class="badge bg-success">Available for Purchase</span>
                                @else
                                    <span class="badge bg-danger">Not Available</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-sliders"></i> Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('vendor.products.edit', $product) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Edit Product
                        </a>
                        <form action="{{ route('vendor.products.destroy', $product) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this product?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-trash"></i> Delete Product
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
