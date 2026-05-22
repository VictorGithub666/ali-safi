@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="fw-bold mb-3">Checkout</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('customer.cart') }}">Cart</a></li>
                    <li class="breadcrumb-item active">Checkout</li>
                </ol>
            </nav>
        </div>
    </div>

    <form action="{{ route('customer.orders.store') }}" method="POST">
        @csrf
        
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Oops! Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            <!-- Checkout Form -->
            <div class="col-lg-8">
                <!-- Delivery Address Section -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title fw-bold mb-0">Delivery Address</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                        <label for="county" class="form-label fw-bold">County <span class="text-danger">*</span></label>
                        <select class="form-control @error('county') is-invalid @enderror" 
                                id="county" 
                                name="county"
                                required>
                            <option value="">-- Select County --</option>
                        </select>
                        @error('county')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="sub_county" class="form-label fw-bold">Sub-County <span class="text-danger">*</span></label>
                        <select class="form-control @error('sub_county') is-invalid @enderror" 
                                id="sub_county" 
                                name="sub_county"
                                required
                                disabled>
                            <option value="">-- Select Sub-County --</option>
                        </select>
                        @error('sub_county')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="ward" class="form-label fw-bold">Ward <span class="text-danger">*</span></label>
                        <select class="form-control @error('ward') is-invalid @enderror" 
                                id="ward" 
                                name="ward"
                                required
                                disabled>
                            <option value="">-- Select Ward --</option>
                        </select>
                        @error('ward')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="delivery_address" class="form-label fw-bold">Delivery Address <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('delivery_address') is-invalid @enderror" 
                                  id="delivery_address" 
                                  name="delivery_address" 
                                  rows="3"
                                  placeholder="Enter your apartment number, house number, building name, or any specific location details"
                                  required>{{ old('delivery_address') }}</textarea>
                        @error('delivery_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">e.g., Apt 101, Block A, Nyali Towers</small>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" 
                               class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" 
                               name="phone"
                               placeholder="e.g., 254712345678 or 0712345678"
                               value="{{ old('phone', Auth::user()->phone ?? '') }}"
                               required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Include country code (254) or local format (07xx)</small>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="delivery_latitude" class="form-label fw-bold">Latitude</label>
                            <input type="number" 
                                   step="0.0000000000000001"
                                   class="form-control @error('delivery_latitude') is-invalid @enderror" 
                                   id="delivery_latitude" 
                                   name="delivery_latitude"
                                   placeholder="-1.287389"
                                   value="{{ old('delivery_latitude') }}"
                                   required>
                            @error('delivery_latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: -1.287389</small>
                        </div>
                        <div class="col-md-6">
                            <label for="delivery_longitude" class="form-label fw-bold">Longitude</label>
                            <input type="number" 
                                   step="0.0000000000000001"
                                   class="form-control @error('delivery_longitude') is-invalid @enderror" 
                                   id="delivery_longitude" 
                                   name="delivery_longitude"
                                   placeholder="36.789012"
                                   value="{{ old('delivery_longitude') }}"
                                   required>
                            @error('delivery_longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: 36.789012</small>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3 mb-3">
                        <button type="button" id="getLocationBtn" class="btn btn-sm" style="background-color: var(--primary-green); color: white;">
                            <i class="bi bi-geo-alt me-1"></i> Get My Location
                        </button>
                        <span id="locationStatus" class="align-self-center small text-muted"></span>
                    </div>

                    <small class="text-muted d-block mb-3">
                        <i class="bi bi-info-circle me-1"></i> Click "Get My Location" to auto-fill coordinates or find them using Google Maps
                    </small>
                </div>

                <!-- Payment Method Section -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title fw-bold mb-0">Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input payment-method-radio" 
                                       type="radio" 
                                       id="payment_cash" 
                                       name="payment_method" 
                                       value="cash"
                                       {{ old('payment_method') === 'cash' ? 'checked' : '' }}>
                                <label class="form-check-label" for="payment_cash">
                                    <strong>Pay on Delivery (COD)</strong>
                                    <div class="text-muted small">Pay when your order arrives</div>
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input payment-method-radio" 
                                       type="radio" 
                                       id="payment_mpesa" 
                                       name="payment_method" 
                                       value="mpesa"
                                       {{ old('payment_method') === 'mpesa' ? 'checked' : '' }}>
                                <label class="form-check-label" for="payment_mpesa">
                                    <strong>M-Pesa</strong>
                                    <div class="text-muted small">Fast and secure mobile payment</div>
                                </label>
                            </div>
                        </div>

                        <!-- M-Pesa Number Input (Hidden by default) -->
                        <div id="mpesa_section" class="border-top pt-3" style="display: {{ old('payment_method') === 'mpesa' ? 'block' : 'none' }};">
                            <div class="alert alert-info" role="alert">
                                <i class="bi bi-info-circle me-2"></i>
                                <small>Enter your M-Pesa number to complete the payment process. An M-Pesa prompt will be sent to this number for you to confirm the payment.</small>
                            </div>
                            <div class="mb-3">
                                <label for="mpesa_number" class="form-label fw-bold">M-Pesa Number <span class="text-danger">*</span></label>
                                <input type="tel" 
                                       class="form-control @error('mpesa_number') is-invalid @enderror" 
                                       id="mpesa_number" 
                                       name="mpesa_number"
                                       placeholder="e.g., 254712345678"
                                       value="{{ old('mpesa_number') }}"
                                       {{ old('payment_method') === 'mpesa' ? 'required' : '' }}>
                                @error('mpesa_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Must start with 254 (Kenya country code)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Special Instructions Section -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title fw-bold mb-0">Special Instructions (Optional)</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" 
                                  id="special_instructions" 
                                  name="special_instructions" 
                                  rows="3"
                                  placeholder="e.g., Ring the bell, Leave at door, Call on arrival..."
                                  maxlength="500">{{ old('special_instructions') }}</textarea>
                        <small class="text-muted">Max 500 characters</small>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title fw-bold mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <!-- Items List -->
                        <div class="mb-4" style="max-height: 300px; overflow-y: auto;">
                            @foreach($cartItems as $item)
                                <div class="d-flex justify-content-between mb-3 pb-3 border-bottom small">
                                    {{-- In the checkout order items loop --}}
                                        <div class="col-md-2">
                                            <div class="bg-light rounded p-2" style="height: 100px; overflow: hidden;">
                                                @if($item->product->image)
                                                    <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                @else
                                                    <div class="text-center">
                                                        <i class="bi bi-image" style="font-size: 1.5rem; color: #ccc;"></i>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    <div>
                                        <p class="mb-1"><strong>{{ $item->product->name }}</strong></p>
                                        <p class="text-muted mb-0">
                                            Qty: {{ $item->quantity }}
                                            @if($item->size)
                                                | Size: {{ $item->size }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <p class="mb-0 fw-bold">KES {{ number_format($item->price * $item->quantity, 0) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <hr>

                        <!-- Price Breakdown -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <span>KES {{ number_format($total, 0) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Delivery Fee</span>
                                <span style="color: var(--primary-green);" class="fw-bold">FREE</span>
                            </div>
                        </div>

                        <hr>

                        <!-- Total -->
                        <div class="d-flex justify-content-between mb-4">
                            <h6 class="fw-bold mb-0">Total Amount</h6>
                            <h5 class="fw-bold mb-0" style="color: var(--primary-green);">KES {{ number_format($total, 0) }}</h5>
                        </div>

                        <!-- Info Alert -->
                        <div class="alert alert-info mb-4" role="alert">
                            <i class="bi bi-info-circle me-2"></i>
                            <small>You will receive an SMS with your order confirmation and rider details.</small>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn text-white fw-bold" style="background-color: var(--primary-green); padding: 12px;">
                                <i class="bi bi-check-circle me-2"></i> Place Order
                            </button>
                            <a href="{{ route('customer.cart') }}" class="btn btn-light border-2" style="border-color: var(--primary-green); color: var(--primary-green);">
                                <i class="bi bi-arrow-left me-2"></i> Back to Cart
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get location elements
    const getLocationBtn = document.getElementById('getLocationBtn');
    const latInput = document.getElementById('delivery_latitude');
    const lngInput = document.getElementById('delivery_longitude');
    const statusSpan = document.getElementById('locationStatus');

    // Get location select elements
    const countySelect = document.getElementById('county');
    const subCountySelect = document.getElementById('sub_county');
    const wardSelect = document.getElementById('ward');

    // Load counties on page load
    loadCounties();

    // Set up event listeners
    countySelect.addEventListener('change', function() {
        if (this.value) {
            loadSubCounties(this.value);
            subCountySelect.disabled = false;
            wardSelect.disabled = true;
            wardSelect.value = '';
        } else {
            subCountySelect.disabled = true;
            wardSelect.disabled = true;
            subCountySelect.value = '';
            wardSelect.value = '';
        }
    });

    subCountySelect.addEventListener('change', function() {
        if (this.value && countySelect.value) {
            loadWards(countySelect.value, this.value);
            wardSelect.disabled = false;
        } else {
            wardSelect.disabled = true;
            wardSelect.value = '';
        }
    });

    // Auto-get location on page load if fields are empty
    if (!latInput.value && !lngInput.value) {
        getLocationAutomatically();
    }

    // Handle manual button click
    getLocationBtn.addEventListener('click', function(e) {
        e.preventDefault();
        getLocation();
    });

    // Location functions
    function getLocationAutomatically() {
        statusSpan.textContent = 'Getting location...';
        statusSpan.style.color = '#6c757d';
        
        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    latInput.value = lat.toFixed(6);
                    lngInput.value = lng.toFixed(6);
                    
                    statusSpan.textContent = '✓ Location found!';
                    statusSpan.style.color = 'var(--primary-green)';
                    setTimeout(() => {
                        statusSpan.textContent = '';
                    }, 3000);
                },
                function(error) {
                    statusSpan.textContent = '';
                }
            );
        }
    }

    function getLocation() {
        if (!('geolocation' in navigator)) {
            showStatus('Geolocation is not supported by your browser', 'danger');
            return;
        }

        statusSpan.textContent = 'Getting your location...';
        statusSpan.style.color = '#6c757d';
        getLocationBtn.disabled = true;

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);
                
                showStatus('✓ Location updated successfully!', 'success');
                getLocationBtn.disabled = false;
            },
            function(error) {
                getLocationBtn.disabled = false;
                
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        showStatus('❌ Permission denied. Please enable location in your browser settings.', 'danger');
                        break;
                    case error.POSITION_UNAVAILABLE:
                        showStatus('❌ Location information is unavailable.', 'danger');
                        break;
                    case error.TIMEOUT:
                        showStatus('❌ The request timed out. Please try again.', 'danger');
                        break;
                    default:
                        showStatus('❌ An error occurred. Please try again.', 'danger');
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }

    function showStatus(message, type) {
        statusSpan.textContent = message;
        if (type === 'success') {
            statusSpan.style.color = 'var(--primary-green)';
            setTimeout(() => {
                statusSpan.textContent = '';
            }, 4000);
        } else if (type === 'danger') {
            statusSpan.style.color = '#dc3545';
            setTimeout(() => {
                statusSpan.textContent = '';
            }, 5000);
        }
    }

    // Location cascading functions
    function loadCounties() {
        fetch('/api/locations/counties')
            .then(response => response.json())
            .then(data => {
                const oldValue = '{{ old('county') }}';
                data.forEach(county => {
                    const option = document.createElement('option');
                    option.value = county;
                    option.textContent = county;
                    if (oldValue && oldValue === county) {
                        option.selected = true;
                    }
                    countySelect.appendChild(option);
                });
                // If there was a previously selected county, load sub-counties
                if (oldValue) {
                    countySelect.dispatchEvent(new Event('change'));
                }
            })
            .catch(error => console.error('Error loading counties:', error));
    }

    function loadSubCounties(county) {
        // Clear existing options
        subCountySelect.innerHTML = '<option value="">-- Select Sub-County --</option>';
        
        fetch(`/api/locations/${encodeURIComponent(county)}/sub-counties`)
            .then(response => response.json())
            .then(data => {
                const oldValue = '{{ old('sub_county') }}';
                data.forEach(subCounty => {
                    const option = document.createElement('option');
                    option.value = subCounty;
                    option.textContent = subCounty;
                    if (oldValue && oldValue === subCounty) {
                        option.selected = true;
                    }
                    subCountySelect.appendChild(option);
                });
                // If there was a previously selected sub-county, load wards
                if (oldValue) {
                    subCountySelect.dispatchEvent(new Event('change'));
                }
            })
            .catch(error => console.error('Error loading sub-counties:', error));
    }

    function loadWards(county, subCounty) {
        // Clear existing options
        wardSelect.innerHTML = '<option value="">-- Select Ward --</option>';
        
        fetch(`/api/locations/${encodeURIComponent(county)}/${encodeURIComponent(subCounty)}/wards`)
            .then(response => response.json())
            .then(data => {
                const oldValue = '{{ old('ward') }}';
                data.forEach(ward => {
                    const option = document.createElement('option');
                    option.value = ward;
                    option.textContent = ward;
                    if (oldValue && oldValue === ward) {
                        option.selected = true;
                    }
                    wardSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error loading wards:', error));
    }

    // Handle M-Pesa payment method visibility and validation
    const paymentMethodRadios = document.querySelectorAll('.payment-method-radio');
    const mpesaSection = document.getElementById('mpesa_section');
    const mpesaInput = document.getElementById('mpesa_number');

    paymentMethodRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'mpesa') {
                mpesaSection.style.display = 'block';
                mpesaInput.required = true;
            } else {
                mpesaSection.style.display = 'none';
                mpesaInput.required = false;
                mpesaInput.value = '';
            }
        });
    });

    // Handle M-Pesa input validation (must start with 254)
    if (mpesaInput) {
        mpesaInput.addEventListener('blur', function() {
            if (this.value && !this.value.startsWith('254')) {
                this.classList.add('is-invalid');
                let feedback = this.nextElementSibling || document.createElement('div');
                if (!feedback.classList.contains('invalid-feedback')) {
                    feedback.className = 'invalid-feedback';
                    feedback.textContent = 'M-Pesa number must start with 254';
                    this.parentElement.appendChild(feedback);
                }
            } else {
                this.classList.remove('is-invalid');
            }
        });

        // Also validate on input for real-time feedback
        mpesaInput.addEventListener('input', function() {
            if (this.value && !this.value.startsWith('254')) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }
});
</script>
@endsection
