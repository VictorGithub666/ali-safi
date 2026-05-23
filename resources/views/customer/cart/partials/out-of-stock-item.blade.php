<div class="row g-3 mb-4 pb-3 border-bottom">
    <div class="col-md-2">
        <div class="bg-light rounded p-2" style="height: 100px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
            @if($item->product->image)
                <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.5;">
            @else
                <div class="text-center">
                    <i class="bi bi-image" style="font-size: 2rem; color: #ccc;"></i>
                    <p class="text-muted small mb-0">{{ $item->product->name }}</p>
                </div>
            @endif
        </div>
    </div>
    <div class="col-md-5">
        <h6 class="fw-bold mb-2 text-muted">{{ $item->product->name }}</h6>
        <p class="text-muted small mb-2">{{ $item->vendor->business_name ?? 'Vendor' }}</p>
        <div class="d-flex gap-3 mb-3">
            @if($item->size)
                <span class="small"><strong>Size:</strong> {{ $item->size }}</span>
            @endif
        </div>
    </div>
    <div class="col-md-5">
        <div class="alert alert-danger mb-3 py-2">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Out of Stock!</strong> This product is no longer available.
            <form action="{{ route('customer.cart.remove') }}" method="POST" class="d-inline-block ms-2">
                @csrf
                <input type="hidden" name="cart_id" value="{{ $item->id }}">
                <button type="submit" class="btn btn-link btn-sm text-danger p-0" onclick="return confirm('Remove this item?')">
                    Remove from cart
                </button>
            </form>
        </div>
    </div>
</div>