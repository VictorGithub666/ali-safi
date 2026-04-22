@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h2 class="fw-bold mb-1">
            <i class="bi bi-pencil-square"></i> Edit Product
        </h2>
        <p class="text-muted mb-0">Update product details</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('vendor.products.update', $product) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <!-- Product Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" placeholder="Enter product name" 
                                   value="{{ old('name', $product->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" 
                                      placeholder="Describe your product..." required>{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror" 
                                    id="category_id" name="category_id" required>
                                <option value="">Select a category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Price -->
                        <div class="mb-3">
                            <label for="price" class="form-label">Price (KES) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" 
                                   id="price" name="price" placeholder="0.00" 
                                   value="{{ old('price', $product->price) }}" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Stock Quantity -->
                        <div class="mb-3">
                            <label for="stock_quantity" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" 
                                   id="stock_quantity" name="stock_quantity" placeholder="0" 
                                   value="{{ old('stock_quantity', $productData->stock_quantity ?? 0) }}" required>
                            @error('stock_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Custom Price (Optional) -->
                        <div class="mb-3">
                            <label for="custom_price" class="form-label">Custom Price (KES) <span class="text-muted">(Optional)</span></label>
                            <input type="number" step="0.01" class="form-control @error('custom_price') is-invalid @enderror" 
                                   id="custom_price" name="custom_price" placeholder="Leave empty to use default price" 
                                   value="{{ old('custom_price', $productData->custom_price) }}">
                            <small class="form-text text-muted">Use this to override the base product price</small>
                            @error('custom_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Product Image -->
                        <div class="mb-3">
                            <label for="image" class="form-label">Product Image <span class="text-muted">(Optional)</span></label>
                            
                            @if($product->image)
                                <div class="mb-2">
                                    <p class="text-muted mb-2">Current Image:</p>
                                    <img src="{{ asset('storage/' . $product->image) }}" 
                                         alt="{{ $product->name }}" class="rounded" style="max-width: 200px; max-height: 200px;">
                                </div>
                            @endif

                            <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                   id="image" name="image" accept="image/*">
                            <small class="form-text text-muted">Accepted formats: JPEG, PNG, JPG, GIF (Max 2MB)</small>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="imagePreview" class="mt-2"></div>
                        </div>

                        <!-- Availability -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_available" 
                                       name="is_available" value="1" {{ old('is_available', $productData->is_available ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_available">
                                    Product is available for purchase
                                </label>
                            </div>
                        </div>

                        <!-- Active Status -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" 
                                       name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Product is active (visible in store)
                                </label>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Product
                            </button>
                            <a href="{{ route('vendor.products.show', $product) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Product Info
                    </h6>
                </div>
                <div class="card-body small">
                    <p class="mb-2">
                        <strong>Product ID:</strong><br>
                        {{ $product->id }}
                    </p>
                    <p class="mb-2">
                        <strong>Created:</strong><br>
                        {{ $product->created_at->format('M d, Y') }}
                    </p>
                    <p class="mb-0">
                        <strong>Last Updated:</strong><br>
                        {{ $product->updated_at->format('M d, Y H:i') }}
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-graph-up"></i> Stats
                    </h6>
                </div>
                <div class="card-body small">
                    <p class="mb-2">
                        <strong>Current Stock:</strong><br>
                        <span class="badge bg-{{ $productData->stock_quantity > 10 ? 'success' : ($productData->stock_quantity > 0 ? 'warning' : 'danger') }}">
                            {{ $productData->stock_quantity }} units
                        </span>
                    </p>
                    <p class="mb-0">
                        <strong>Status:</strong><br>
                        @if($product->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('image').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.maxWidth = '200px';
            img.style.maxHeight = '200px';
            img.className = 'rounded';
            preview.appendChild(img);
        };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>
@endpush

@endsection
