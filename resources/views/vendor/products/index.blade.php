@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-box-seam"></i> Products
            </h2>
            <p class="text-muted mb-0">Manage your products and inventory</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('vendor.products.export') }}" class="btn btn-outline-secondary">
                <i class="bi bi-download"></i> Export CSV
            </a>
            <a href="{{ route('vendor.products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Product
            </a>
        </div>
    </div>

    @if($products->count() > 0)
        <!-- Filter & Search -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Search products..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Products Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Available</th>
                            <th style="width: 150px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input product-checkbox" 
                                           value="{{ $product->id }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" 
                                                 alt="{{ $product->name }}" 
                                                 class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="rounded me-2 bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $product->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ Str::limit($product->description, 40) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $product->category->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <strong>KES {{ number_format($product->price, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge {{ $product->pivot->stock_quantity > 10 ? 'bg-success' : ($product->pivot->stock_quantity > 0 ? 'bg-warning' : 'bg-danger') }}">
                                        {{ $product->pivot->stock_quantity }}
                                    </span>
                                </td>
                                <td>
                                    @if($product->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input availability-toggle" type="checkbox" 
                                               data-product-id="{{ $product->id }}"
                                               {{ $product->pivot->is_available ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('vendor.products.show', $product) }}" 
                                       class="btn btn-sm btn-outline-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('vendor.products.edit', $product) }}" 
                                       class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('vendor.products.destroy', $product) }}" 
                                          method="POST" class="d-inline" 
                                          onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $products->links() }}
        </div>
    @else
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            <strong>No products yet!</strong> Start by adding your first product.
            <a href="{{ route('vendor.products.create') }}" class="alert-link">Create Product</a>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.product-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Availability toggle with AJAX - using direct URL path
    const csrfToken = "{{ csrf_token() }}";
    
    document.querySelectorAll('.availability-toggle').forEach(toggle => {
        toggle.addEventListener('change', async function() {
            const productId = this.dataset.productId;
            const isAvailable = this.checked;
            const originalState = this.checked;
            
            try {
                // Use direct URL path instead of route helper
                const response = await fetch('/vendor/products/toggle-availability', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        is_available: isAvailable
                    })
                });
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                const data = await response.json();
                
                if (data.success) {
                    console.log('Availability updated for product:', productId);
                } else {
                    throw new Error(data.message || 'Update failed');
                }
            } catch (error) {
                console.error('Error:', error);
                // Revert the toggle if there's an error
                this.checked = !originalState;
                alert('Failed to update availability. Please try again.');
            }
        });
    });
});
</script>
@endpush

@endsection
