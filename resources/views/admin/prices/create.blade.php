@extends('layouts.app')
@section('content')
<div class="container py-4">
    <h1 class="mb-4"><i class="bi bi-tag-fill"></i> Set Product Price</h1>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.prices.store') }}" id="priceForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Product <span class="text-danger">*</span></label>
                            <select name="product_id" id="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                                <option value="">-- Select Product --</option>
                                @foreach($products as $id => $name)
                                    <option value="{{ $id }}" {{ old('product_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Vendor <span class="text-danger">*</span></label>
                            <select name="vendor_id" id="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror" required>
                                <option value="">-- Select Vendor --</option>
                                @foreach($vendors as $id => $name)
                                    <option value="{{ $id }}" {{ old('vendor_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('vendor_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Loading Indicator -->
                        <div id="priceLoading" class="alert alert-info mb-3" style="display: none;">
                            <i class="bi bi-hourglass-split me-2"></i> Loading vendor price...
                        </div>
                        
                        <!-- Existing Price Alert -->
                        <div id="existingPriceAlert" class="alert alert-warning mb-3" style="display: none;">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Price already exists!</strong> Loading existing data. You can update it below.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Vendor Price (Cost) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">KES</span>
                                        <input type="number" name="vendor_price" id="vendor_price" class="form-control" step="0.01" value="{{ old('vendor_price') }}" readonly style="background-color: #f8f9fa;">
                                    </div>
                                    <small class="text-muted">Auto-filled from vendor's product price</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Customer Visible Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">KES</span>
                                        <input type="number" name="customer_visible_price" id="customer_visible_price" class="form-control" step="0.01" value="{{ old('customer_visible_price') }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Markup Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">KES</span>
                                        <input type="number" name="markup" id="markup" class="form-control" step="0.01" value="{{ old('markup') }}" readonly style="background-color: #f8f9fa;">
                                    </div>
                                    <small class="text-muted" id="markupPercentageDisplay"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Base Delivery Fee</label>
                                    <div class="input-group">
                                        <span class="input-group-text">KES</span>
                                        <input type="number" name="base_delivery_fee" id="base_delivery_fee" class="form-control" step="0.01" value="{{ old('base_delivery_fee', 0) }}">
                                    </div>
                                    <small class="text-muted">Optional - Specific delivery fee for this product-vendor combination</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input" checked>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="bi bi-check-circle"></i> Create
                            </button>
                            <a href="{{ route('admin.prices.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Information</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>How it works:</strong>
                        <ul class="mt-2 mb-0">
                            <li>Select a product and vendor</li>
                            <li>Vendor price will auto-fill automatically</li>
                            <li>Enter the customer visible price</li>
                            <li>Markup will calculate automatically</li>
                        </ul>
                    </div>
                    <div class="mt-3">
                        <h6>Formula:</h6>
                        <p class="mb-1 small">
                            <strong>Markup Amount</strong> = Customer Price - Vendor Price
                        </p>
                        <p class="mb-0 small">
                            <strong>Markup Percentage</strong> = (Markup Amount / Vendor Price) × 100%
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const getVendorPriceUrl = "{{ route('admin.prices.get-vendor-price') }}";
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const productSelect = document.getElementById('product_id');
    const vendorSelect = document.getElementById('vendor_id');
    const vendorPriceInput = document.getElementById('vendor_price');
    const customerPriceInput = document.getElementById('customer_visible_price');
    const markupInput = document.getElementById('markup');
    const markupPercentageDisplay = document.getElementById('markupPercentageDisplay');
    const baseDeliveryFeeInput = document.getElementById('base_delivery_fee');
    const isActiveCheckbox = document.getElementById('is_active');
    const loadingDiv = document.getElementById('priceLoading');
    const existingAlert = document.getElementById('existingPriceAlert');
    const submitBtn = document.getElementById('submitBtn');
    const priceForm = document.getElementById('priceForm');
    
    let isExistingPrice = false;
    let existingPriceId = null;

    // Calculate markup when customer price changes
    function calculateMarkup() {
        const vendorPrice = parseFloat(vendorPriceInput.value) || 0;
        const customerPrice = parseFloat(customerPriceInput.value) || 0;
        
        if (vendorPrice > 0) {
            const markupAmount = customerPrice - vendorPrice;
            markupInput.value = markupAmount.toFixed(2);
            
            const markupPercentage = (markupAmount / vendorPrice) * 100;
            markupPercentageDisplay.innerHTML = `<strong>Markup Percentage:</strong> ${markupPercentage.toFixed(2)}%`;
            
            // Color code the markup
            if (markupPercentage < 0) {
                markupPercentageDisplay.style.color = '#dc3545';
                markupPercentageDisplay.innerHTML += ' <span class="badge bg-danger">Loss</span>';
            } else if (markupPercentage < 10) {
                markupPercentageDisplay.style.color = '#ffc107';
                markupPercentageDisplay.innerHTML += ' <span class="badge bg-warning text-dark">Low Margin</span>';
            } else if (markupPercentage < 30) {
                markupPercentageDisplay.style.color = '#17a2b8';
                markupPercentageDisplay.innerHTML += ' <span class="badge bg-info">Good Margin</span>';
            } else {
                markupPercentageDisplay.style.color = '#28a745';
                markupPercentageDisplay.innerHTML += ' <span class="badge bg-success">High Margin</span>';
            }
        } else {
            markupInput.value = '0.00';
            markupPercentageDisplay.innerHTML = '';
        }
    }

    // Replace the loadVendorPrice function in create.blade.php with this:

    async function loadVendorPrice() 
    {
        const productId = productSelect.value;
        const vendorId = vendorSelect.value;
        
        if (!productId || !vendorId) {
            vendorPriceInput.value = '';
            vendorPriceInput.placeholder = 'Select product and vendor first';
            vendorPriceInput.readOnly = false;
            submitBtn.disabled = true;
            return;
        }
        
        // Show loading indicator
        loadingDiv.style.display = 'block';
        existingAlert.style.display = 'none';
        vendorPriceInput.readOnly = true;
        vendorPriceInput.value = '';
        vendorPriceInput.placeholder = 'Loading...';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';
        
        try {
            // Build URL with proper encoding
            const url = `${getVendorPriceUrl}?product_id=${encodeURIComponent(productId)}&vendor_id=${encodeURIComponent(vendorId)}`;
            console.log('Fetching URL:', url);
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Error response:', errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Response data:', data);
            
            if (data.success) {
                vendorPriceInput.value = parseFloat(data.vendor_price).toFixed(2);
                vendorPriceInput.placeholder = '';
                
                if (data.exists) {
                    isExistingPrice = true;
                    existingPriceId = data.id;
                    existingAlert.style.display = 'block';
                    
                    if (data.customer_visible_price) {
                        customerPriceInput.value = parseFloat(data.customer_visible_price).toFixed(2);
                    }
                    if (data.markup) {
                        markupInput.value = parseFloat(data.markup).toFixed(2);
                    }
                    if (data.base_delivery_fee) {
                        baseDeliveryFeeInput.value = parseFloat(data.base_delivery_fee).toFixed(2);
                    }
                    isActiveCheckbox.checked = data.is_active !== false;
                    
                    submitBtn.innerHTML = '<i class="bi bi-pencil-square"></i> Update Existing Price';
                    submitBtn.classList.remove('btn-success');
                    submitBtn.classList.add('btn-warning');
                } else {
                    isExistingPrice = false;
                    existingAlert.style.display = 'none';
                    
                    if (!customerPriceInput.value) {
                        customerPriceInput.value = '';
                    }
                    calculateMarkup();
                    
                    submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Create';
                    submitBtn.classList.remove('btn-warning');
                    submitBtn.classList.add('btn-success');
                }
                
                submitBtn.disabled = false;
            } else {
                throw new Error(data.message || 'Failed to load vendor price');
            }
        } catch (error) {
            console.error('Error loading vendor price:', error);
            vendorPriceInput.value = '';
            vendorPriceInput.placeholder = 'Error loading price';
            vendorPriceInput.readOnly = false;
            submitBtn.disabled = true;
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load vendor price: ' + error.message,
                confirmButtonColor: '#dc3545'
            });
        } finally {
            loadingDiv.style.display = 'none';
        }
    }

    // Show error message
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonColor: '#dc3545'
        });
    }
    
    // Show warning about existing price
    function showExistingPriceWarning() {
        Swal.fire({
            icon: 'warning',
            title: 'Price Already Exists',
            html: `
                <p>A price record already exists for this product and vendor.</p>
                <p>The existing data has been loaded. You can:</p>
                <ul class="text-start">
                    <li><strong>Update</strong> - Click the "Update Existing Price" button to save changes</li>
                    <li><strong>Cancel</strong> - Go back to the price list</li>
                </ul>
            `,
            confirmButtonText: 'I Understand',
            confirmButtonColor: '#ffc107'
        });
    }
    
    // Handle form submission
    priceForm.addEventListener('submit', async function(e) {
        if (isExistingPrice) {
            e.preventDefault();
            
            // Confirm update
            const result = await Swal.fire({
                title: 'Update Existing Price?',
                html: `
                    <p>A price record already exists for this product and vendor.</p>
                    <p>Do you want to update the existing record?</p>
                    <hr>
                    <div class="text-start">
                        <p><strong>Product:</strong> ${productSelect.options[productSelect.selectedIndex]?.text}</p>
                        <p><strong>Vendor:</strong> ${vendorSelect.options[vendorSelect.selectedIndex]?.text}</p>
                        <p><strong>Vendor Price:</strong> KES ${vendorPriceInput.value}</p>
                        <p><strong>Customer Price:</strong> KES ${customerPriceInput.value}</p>
                        <p><strong>Markup:</strong> KES ${markupInput.value}</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-pencil-square"></i> Yes, Update',
                cancelButtonText: '<i class="bi bi-x-circle"></i> Cancel'
            });
            
            if (result.isConfirmed) {
                // Change form action to update route
                this.action = `/admin/prices/${existingPriceId}`;
                
                // Add method spoofing for PUT
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                this.appendChild(methodInput);
                
                // Submit the form
                this.submit();
            }
        }
        // If not existing, let the normal form submission happen
    });
    
    // Event listeners
    productSelect.addEventListener('change', loadVendorPrice);
    vendorSelect.addEventListener('change', loadVendorPrice);
    customerPriceInput.addEventListener('input', calculateMarkup);
    customerPriceInput.addEventListener('change', calculateMarkup);
    
    // Initial calculation if values exist
    calculateMarkup();
    
    // If both fields have values on page load (e.g., after validation error)
    if (productSelect.value && vendorSelect.value) {
        loadVendorPrice();
    }
});
</script>

<style>
    #vendor_price:read-only {
        background-color: #e9ecef;
        cursor: not-allowed;
    }
    
    #markup:read-only {
        background-color: #e9ecef;
    }
    
    #markupPercentageDisplay {
        font-size: 0.85rem;
        transition: all 0.3s ease;
    }
    
    .alert-warning {
        border-left: 4px solid #ffc107;
    }
    
    #priceLoading {
        border-left: 4px solid #0d6efd;
    }
</style>
@endsection