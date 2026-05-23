@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-shop"></i> Browse Products
                @if($searchQuery)
                    <span class="text-muted h5" style="font-size: 1rem;">for "{{ $searchQuery }}"</span>
                @endif
                @if($nearbyOnly)
                    <span class="badge bg-success ms-2">Near Me</span>
                @endif
            </h2>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <!-- Search Form -->
            <form action="{{ route('customer.products.index') }}" method="GET" class="flex-grow-1 d-flex gap-2">
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <div class="input-group flex-grow-1">
                    <input type="text" name="search" class="form-control" placeholder="Search products..." value="{{ request('search') }}">
                    <button class="btn btn-secondary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
            
            <!-- Shop Near Me Button -->
            <button class="btn btn-success" id="shopNearMeBtn" title="Find shops in your vicinity">
                <i class="bi bi-geo-alt"></i> Near Me
            </button>
        </div>
    </div>

    <!-- Nearby Shops Banner -->
    @if(isset($nearbyOnly) && $nearbyOnly)
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-geo-alt-fill me-2"></i>
                <strong>Showing shops within 1km of your location!</strong>
                @if(isset($nearbyVendors))
                    <span class="badge bg-light text-dark ms-2">{{ $nearbyVendors->count() }} shops found</span>
                @endif
            </div>
            <div>
                <a href="{{ route('customer.products.index') }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-x-circle"></i> View All Shops
                </a>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    @endif

    <!-- Categories Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <a href="{{ route('customer.products.index') }}" class="btn btn-outline-primary btn-sm {{ !request('category') && !$nearbyOnly ? 'active' : '' }}">
                    All Products
                </a>
                @foreach($categories ?? [] as $category)
                    <a href="{{ route('customer.products.index') }}?category={{ $category->id }}" class="btn btn-outline-primary btn-sm {{ request('category') == $category->id && !$nearbyOnly ? 'active' : '' }}">
                        {{ $category->name }}
                    </a>
                @endforeach
                
                @if($nearbyOnly)
                    <span class="badge bg-success">Showing shops within 1km</span>
                    <a href="{{ route('customer.products.index') }}" class="btn btn-sm btn-outline-secondary">Clear Filter</a>
                @endif
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    @if($products->count())
        <div class="row g-4">
            @foreach($products as $product)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-img-top" style="background-color: #f8f9fa; height: 200px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <div class="text-center">
                                    <i class="bi bi-image" style="font-size: 3rem; color: #ccc;"></i>
                                    <p class="text-muted small mt-2 mb-0">No image</p>
                                </div>
                            @endif
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text text-muted small">{{ Str::limit($product->description, 100) }}</p>
                            
                            <div class="mb-3">
                                <span class="badge bg-success">{{ $product->category->name ?? 'Uncategorized' }}</span>
                                @php
                                    $vendor = $product->vendors()->first();
                                @endphp
                                @if($vendor && $vendor->is_open)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Open
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-lock"></i> Closed
                                    </span>
                                @endif
                            </div>

                            <h6 class="fw-bold mb-3" style="color: var(--primary-green);">
                                KES {{ number_format($product->customer_price ?? $product->final_price, 0) }}
                            </h6>

                            <div class="d-grid gap-2">
                                <a href="{{ route('customer.products.show', $product) }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-5">
            {{ $products->links() }}
        </div>
    @else
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No products available at the moment.
        </div>
    @endif
</div>

<script>
    // Handle Shop Near Me button
    document.getElementById('shopNearMeBtn').addEventListener('click', function() {
        const btn = this;
        const originalText = btn.innerHTML;
        
        // Show loading state
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Locating...';
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    // Build URL with geolocation parameters
                    let url = '{{ route("customer.products.index") }}?nearby=1&lat=' + lat + '&lng=' + lng;
                    
                    // Preserve category filter if set
                    const category = new URLSearchParams(window.location.search).get('category');
                    if (category) {
                        url += '&category=' + category;
                    }
                    
                    // Redirect to filtered results
                    window.location.href = url;
                },
                function(error) {
                    // Handle error
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    
                    let errorMsg = 'Unable to get your location. ';
                    
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMsg += 'Please enable location access in your browser settings.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMsg += 'Your location could not be determined.';
                            break;
                        case error.TIMEOUT:
                            errorMsg += 'Location request timed out.';
                            break;
                        default:
                            errorMsg += 'An error occurred.';
                    }
                    
                    alert(errorMsg);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        } else {
            btn.disabled = false;
            btn.innerHTML = originalText;
            alert('Geolocation is not supported by your browser. Please use a modern browser.');
        }
    });
</script>
@endsection
