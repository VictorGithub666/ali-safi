@extends('layouts.app')

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('customer.products.index') }}" class="text-decoration-none">Products</a></li>
            <li class="breadcrumb-item active" aria-current="page">Product Details</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Product Image -->
        <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 400px; overflow: hidden;">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <div class="text-center">
                            <svg class="mb-3" width="80" height="80" fill="#ccc" viewBox="0 0 24 24">
                                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5.04-6.71l-2.75 3.54-1.3-1.54c-.3-.36-.77-.36-1.06 0l-3.63 4.36V7h13v10h-5.26z"/>
                            </svg>
                            <p class="text-muted mb-0">No Product Image</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

            
        </div>

        <!-- Product Details -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <!-- Product Header -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h1 class="h3 fw-bold mb-2">{{ $product->name }}</h1>
                                <span class="badge" style="background-color: var(--primary-green);">{{ $product->stock_quantity > 0 ? 'In Stock' : 'Out of Stock' }}</span>
                            </div>
                            <button class="btn btn-link text-danger" type="button" data-bs-toggle="tooltip" title="Add to Wishlist">
                                <i class="bi bi-heart"></i>
                            </button>
                        </div>
                        <p class="text-muted mb-0">Category: <strong>{{ $product->category->name ?? 'N/A' }}</strong></p>
                    </div>

                    <!-- Rating -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex gap-1">
                                <i class="bi bi-star-fill" style="color: #ffc107;"></i>
                                <i class="bi bi-star-fill" style="color: #ffc107;"></i>
                                <i class="bi bi-star-fill" style="color: #ffc107;"></i>
                                <i class="bi bi-star-fill" style="color: #ffc107;"></i>
                                <i class="bi bi-star-half" style="color: #ffc107;"></i>
                            </div>
                            <span class="text-muted">(245 reviews)</span>
                        </div>
                        <p class="small text-muted mt-2 mb-0">4.5 out of 5 stars | 89% would recommend</p>
                    </div>

                    <!-- Pricing -->
                    <div class="mb-4 pb-3 border-bottom">
                        <div class="d-flex align-items-baseline gap-3">
                            <h2 class="h4 fw-bold" style="color: var(--primary-green);">
                                KES {{ number_format($customerPrice ?? $product->final_price, 0) }}
                            </h2>
                            @if(($customerPrice ?? $product->final_price) < $product->base_price)
                                <span class="text-decoration-line-through text-muted">
                                    KES {{ number_format($product->base_price, 0) }}
                                </span>
                            @endif
                        </div>
                        <p class="small text-muted mb-0">{{ $product->description }}</p>
                    </div>

                    <!-- Options -->
                    <form id="addToCartForm" method="POST" action="{{ route('customer.cart.add') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        @php
                            $vendor = $product->vendors()->first();
                        @endphp
                        <input type="hidden" name="vendor_id" value="{{ $vendor->id ?? 1 }}">
                        
                        <div class="mb-4">
                            <!-- Size Selection -->
                                @php
                                    $sizes = json_decode($product->sizes, true) ?? [];
                                    $sizePrices = json_decode($product->size_prices, true) ?? [];
                                @endphp

                                @if(!empty($sizes))
                                    <div class="mb-4">
                                        <label class="form-label fw-bold mb-2">Size</label>
                                        <div class="d-flex gap-2 flex-wrap">
                                            @foreach($sizes as $index => $size)
                                                <input type="radio" class="btn-check" name="size" id="size_{{ $index }}" 
                                                    value="{{ $size }}" {{ $index === 0 ? 'checked' : '' }}>
                                                <label class="btn btn-outline-secondary" for="size_{{ $index }}">
                                                    {{ $size }}
                                                    @php
                                                        // Use admin price if available, otherwise use original size price
                                                        $sizePrice = isset($sizePricesWithMarkup[$size]) 
                                                            ? $sizePricesWithMarkup[$size] 
                                                            : ($sizePrices[$size] ?? $customerPrice ?? $product->final_price);
                                                    @endphp
                                                    <br><small class="text-success">KES {{ number_format($sizePrice, 0) }}</small>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                            <!-- Quantity -->
                            <div class="mb-4">
                                <label class="form-label fw-bold mb-2">Quantity</label>
                                <div class="input-group" style="width: 150px;">
                                    <button class="btn btn-outline-secondary" type="button" id="decreaseQty">-</button>
                                    <input type="number" class="form-control text-center" id="quantity" name="quantity" value="1" min="1" max="{{ $product->stock_quantity }}">
                                    <button class="btn btn-outline-secondary" type="button" id="increaseQty">+</button>
                                </div>
                                @if($product->stock_quantity <= 0)
                                    <div class="alert alert-danger mt-2 py-2">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        <strong>Out of Stock!</strong> This product is currently unavailable.
                                    </div>
                                    <button class="btn btn-secondary w-100" disabled style="background-color: #6c757d;">
                                        <i class="bi bi-bag-plus me-2"></i> Out of Stock
                                    </button>
                                @else
                                    <p class="small text-muted mt-2 mb-0">{{ $product->stock_quantity }} units available</p>
                                    @if($product->stock_quantity < 10)
                                        <p class="small text-warning mt-1 mb-0">
                                            <i class="bi bi-exclamation-triangle"></i> Only {{ $product->stock_quantity }} left! Order soon.
                                        </p>
                                    @endif
                                @endif
                            </div>

                            <!-- Total Price -->
                            <div class="mb-4 p-3 rounded" style="background-color: #f8f9fa;">
                                <p class="text-muted mb-2">Total Price:</p>
                                <h5 class="fw-bold" style="color: var(--primary-green);">KES <span id="totalPrice">{{ number_format($customerPrice ?? $product->final_price, 0) }}</span></h5>
                            </div>
                        </div>

                        <!-- Buy Now Flag -->
                        <input type="hidden" id="buyNowFlag" name="buy_now" value="0">

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <button class="btn btn-light border-2" style="border-color: var(--primary-green); color: var(--primary-green);" type="submit">
                                <i class="bi bi-bag-plus me-2"></i> Add to Cart
                            </button>
                            <button class="btn text-white" style="background-color: var(--primary-green);" type="button" id="buyNowBtn">
                                <i class="bi bi-lightning-charge me-2"></i> Buy Now
                            </button>
                        </div>
                    </form>

                    <!-- Vendor Info -->
                    <div class="mt-4 p-3 rounded" style="background-color: #f8f9fa;">
                        <h6 class="fw-bold mb-3">Sold by</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="fw-bold mb-1">{{ $vendor->business_name ?? 'Vendor' }}</p>
                                <p class="small text-muted mb-0">
                                    <i class="bi bi-star-fill text-warning"></i> {{ $vendor->rating ?? '4.5' }} ({{ $vendor->reviews_count ?? '0' }} ratings)
                                </p>
                            </div>
                            <a href="{{ route('shop.vendor', $vendor->id) }}" class="btn btn-sm btn-outline-secondary">View Shop</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Description -->
    <div class="row mt-5">
        <div class="col-md-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" href="#description" data-bs-toggle="tab" role="tab">Description</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#specifications" data-bs-toggle="tab" role="tab">Specifications</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#reviews" data-bs-toggle="tab" role="tab">Reviews</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Description Tab -->
                        <div class="tab-pane fade show active" id="description">
                            <h5 class="fw-bold mb-3">Product Description</h5>
                            <p>Our premium LPG gas cylinders are designed for safe, efficient home and commercial use. Each cylinder undergoes rigorous quality testing to ensure durability and safety.</p>
                            <h6 class="fw-bold mt-4 mb-2">Key Features:</h6>
                            <ul>
                                <li>High-quality steel construction for durability</li>
                                <li>Safety valve with pressure relief mechanism</li>
                                <li>Easy to handle and transport</li>
                                <li>Certified and tested for safety standards</li>
                                <li>Long-lasting performance</li>
                                <li>Available in 6kg, 12kg, and 13kg sizes</li>
                            </ul>
                            <h6 class="fw-bold mt-4 mb-2">Usage Instructions:</h6>
                            <ol>
                                <li>Ensure proper ventilation before using the cylinder</li>
                                <li>Check for any leaks using soapy water</li>
                                <li>Store in a cool, dry place away from direct sunlight</li>
                                <li>Use only approved regulators and burners</li>
                                <li>Never tamper with the safety valve</li>
                            </ol>
                        </div>

                        <!-- Specifications Tab -->
                        <div class="tab-pane fade" id="specifications">
                            <h5 class="fw-bold mb-3">Specifications</h5>
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td class="fw-bold text-muted">Material</td>
                                        <td>High-Grade Steel</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Available Sizes</td>
                                        <td>6kg, 12kg, 13kg</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Pressure Rating</td>
                                        <td>17.5 bar</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Valve Type</td>
                                        <td>Safety Valve with Pressure Relief</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Weight (Empty)</td>
                                        <td>3.5 kg</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Warranty</td>
                                        <td>1 Year</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Certification</td>
                                        <td>ISO 4705 Certified</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Reviews Tab -->
                        <div class="tab-pane fade" id="reviews">
                            <h5 class="fw-bold mb-4">Customer Reviews (245)</h5>
                            
                            {{-- Review Summary --}}
                            <div class="mb-4 p-3 rounded" style="background-color: #f8f9fa;">
                                <div class="row text-center">
                                    <div class="col">
                                        <h3 class="fw-bold">4.5</h3>
                                        <div class="text-warning mb-2">
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-half"></i>
                                        </div>
                                        <p class="text-muted small mb-0">Based on 245 reviews</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Individual Reviews --}}
                            <div class="mb-4 pb-3 border-bottom">
                                <div class="d-flex justify-content-between mb-2">
                                    <h6 class="fw-bold mb-0">John Kariuki</h6>
                                    <small class="text-muted">2 days ago</small>
                                </div>
                                <div class="text-warning mb-2">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                </div>
                                <p class="mb-0">Excellent quality cylinder! Delivered on time and works perfectly. Highly recommend!</p>
                            </div>

                            <div class="mb-4 pb-3 border-bottom">
                                <div class="d-flex justify-content-between mb-2">
                                    <h6 class="fw-bold mb-0">Mary Ochieng</h6>
                                    <small class="text-muted">1 week ago</small>
                                </div>
                                <div class="text-warning mb-2">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star"></i>
                                </div>
                                <p class="mb-0">Good product, but delivery took longer than expected. Otherwise satisfied with the purchase.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
</div>

<script>
    // Get price data from product
    @php
        $sizePricesForJs = !empty($sizePricesWithMarkup) ? $sizePricesWithMarkup : (json_decode($product->size_prices, true) ?? []);
    @endphp
    const sizePrices = {!! json_encode($sizePricesForJs) !!};
    const basePrice = {{ $customerPrice ?? $product->final_price }};

    function updateTotalPrice() {
        const quantity = parseInt(document.getElementById('quantity').value) || 1;
        const size = document.querySelector('input[name="size"]:checked')?.value;
        let price = basePrice;
        
        if (size && sizePrices[size]) {
            price = sizePrices[size];
        }
        
        const total = price * quantity;
        document.getElementById('totalPrice').textContent = Math.round(total).toLocaleString();
    }

    document.getElementById('increaseQty').addEventListener('click', function() {
        let qty = parseInt(document.getElementById('quantity').value) || 1;
        let max = parseInt(document.getElementById('quantity').getAttribute('max')) || 10;
        if (qty < max) {
            document.getElementById('quantity').value = qty + 1;
            updateTotalPrice();
        }
    });

    document.getElementById('decreaseQty').addEventListener('click', function() {
        let qty = parseInt(document.getElementById('quantity').value) || 1;
        if (qty > 1) {
            document.getElementById('quantity').value = qty - 1;
            updateTotalPrice();
        }
    });

    // Update total when quantity input changes
    document.getElementById('quantity').addEventListener('change', updateTotalPrice);

    // Update total when size changes
    document.querySelectorAll('input[name="size"]').forEach(radio => {
        radio.addEventListener('change', updateTotalPrice);
    });

    // Handle Buy Now button
    document.getElementById('buyNowBtn').addEventListener('click', function(e) {
        e.preventDefault();
        
        // Set the buy_now flag
        document.getElementById('buyNowFlag').value = '1';
        
        // Submit the form
        const form = document.getElementById('addToCartForm');
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData
        })
        .then(response => {
            if (response.ok) {
                // Redirect to checkout
                window.location.href = '{{ route("customer.checkout") }}';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to add product to cart');
        });
    });
</script>
@endsection
