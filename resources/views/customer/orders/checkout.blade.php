@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold mb-4">Checkout</h2>
            
            <!-- Progress Steps -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold" style="width: 40px; height: 40px; background-color: var(--primary-green);">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <p class="small mt-2 fw-bold">Cart Review</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold" style="width: 40px; height: 40px; background-color: var(--primary-green);">
                            2
                        </div>
                        <p class="small mt-2 fw-bold">Delivery Info</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold" style="width: 40px; height: 40px; background-color: var(--primary-green);">
                            3
                        </div>
                        <p class="small mt-2 fw-bold">Payment</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-muted fw-bold" style="width: 40px; height: 40px; background-color: #e9ecef;">
                            4
                        </div>
                        <p class="small mt-2 fw-bold text-muted">Confirmation</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Checkout Form -->
        <div class="col-lg-8">
            <!-- Order Items Review -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Order Review</h5>
                </div>
                <div class="card-body">
                    <!-- Item 1 -->
                    <div class="row g-3 mb-4 pb-3 border-bottom">
                        <div class="col-md-2">
                            <div class="bg-light rounded p-2" style="height: 100px;"></div>
                        </div>
                        <div class="col-md-10">
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <h6 class="fw-bold mb-1">12 Kg Gas Cylinder</h6>
                                    <p class="text-muted small mb-0">Vendor: Ali Safi Gas Supplies</p>
                                </div>
                                <button type="button" class="btn btn-sm btn-link text-danger">Remove</button>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="small text-muted">Size:</span>
                                    <span class="small fw-bold">12 Kg</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="small text-muted">Qty:</span>
                                    <span class="small fw-bold">2</span>
                                </div>
                                <div class="text-end">
                                    <p class="text-muted small mb-0">KES 1,299 × 2</p>
                                    <p class="fw-bold" style="color: var(--primary-green);">KES 2,598</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Item 2 -->
                    <div class="row g-3 pb-3 border-bottom">
                        <div class="col-md-2">
                            <div class="bg-light rounded p-2" style="height: 100px;"></div>
                        </div>
                        <div class="col-md-10">
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <h6 class="fw-bold mb-1">Gas Regulator</h6>
                                    <p class="text-muted small mb-0">Vendor: Ali Safi Gas Supplies</p>
                                </div>
                                <button type="button" class="btn btn-sm btn-link text-danger">Remove</button>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="small text-muted">Qty:</span>
                                    <span class="small fw-bold">1</span>
                                </div>
                                <div class="text-end">
                                    <p class="text-muted small mb-0">KES 450 × 1</p>
                                    <p class="fw-bold" style="color: var(--primary-green);">KES 450</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Cart Link -->
                    <div class="mt-3">
                        <a href="{{ route('customer.cart.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil me-1"></i> Edit Cart
                        </a>
                    </div>
                </div>
            </div>

            <!-- Delivery Address -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Delivery Address</h5>
                </div>
                <div class="card-body">
                    <form>
                        <div class="mb-3">
                            <label for="recipientName" class="form-label">Recipient Name</label>
                            <input type="text" class="form-control" id="recipientName" placeholder="Enter recipient name" value="John Kariuki">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" placeholder="Enter email" value="john@example.com">
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" placeholder="+254 7XX XXX XXX" value="+254712345678">
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="city" class="form-label">City/Town</label>
                                <select class="form-select" id="city">
                                    <option selected>Nairobi</option>
                                    <option>Mombasa</option>
                                    <option>Kisumu</option>
                                    <option>Nakuru</option>
                                    <option>Eldoret</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="area" class="form-label">Area/Neighborhood</label>
                                <input type="text" class="form-control" id="area" placeholder="e.g., Westlands, Karen" value="Westlands">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Detailed Address</label>
                            <textarea class="form-control" id="address" rows="3" placeholder="House number, street name, landmarks">House No. 45, Ring Road, Off Westland Road, Next to ABC Supermarket</textarea>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="saveAddress" checked>
                            <label class="form-check-label" for="saveAddress">
                                Save this address for future orders
                            </label>
                        </div>

                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            <small>We'll use this address to deliver your order. You can change it before confirmation.</small>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delivery Options -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Delivery Options</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="delivery" id="delivery1" checked>
                        <label class="form-check-label" for="delivery1">
                            <div class="fw-bold">Standard Delivery (1-3 business days)</div>
                            <div class="text-muted small">Free for orders above KES 500</div>
                            <div style="color: var(--primary-green);" class="fw-bold small">FREE</div>
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="delivery" id="delivery2">
                        <label class="form-check-label" for="delivery2">
                            <div class="fw-bold">Express Delivery (Same day - 6 PM)</div>
                            <div class="text-muted small">Available for orders before 2 PM</div>
                            <div style="color: var(--primary-green);" class="fw-bold small">KES 350</div>
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="delivery" id="delivery3">
                        <label class="form-check-label" for="delivery3">
                            <div class="fw-bold">Scheduled Delivery</div>
                            <div class="text-muted small">Pick a date and time that suits you</div>
                            <div style="color: var(--primary-green);" class="fw-bold small">KES 200</div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Payment Method</h5>
                </div>
                <div class="card-body">
                    <!-- M-Pesa Payment Option -->
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="radio" name="payment" id="payment1" checked value="mpesa">
                        <label class="form-check-label w-100" for="payment1">
                            <div class="fw-bold d-flex align-items-center gap-2">
                                <i class="bi bi-phone"></i> M-Pesa
                            </div>
                            <div class="text-muted small">Instant payment via M-Pesa STK Push</div>
                        </label>
                    </div>

                    <!-- M-Pesa Phone Number Section -->
                    <div id="mpesaPhoneSection" class="alert alert-info mb-3">
                        <div class="mb-3">
                            <label for="mpesaPhone" class="form-label">M-Pesa Phone Number</label>
                            <input type="tel" class="form-control" id="mpesaPhone" 
                                   placeholder="254712345678 or 0712345678" 
                                   value="{{ auth()->user()->phone ?? '' }}"
                                   pattern="^(254|0)?[7][0-9]{8}$"
                                   required>
                            <small class="form-text text-muted d-block mt-2">
                                <i class="bi bi-info-circle"></i> Enter your Safaricom M-Pesa phone number. You'll receive an STK Push to complete payment.
                            </small>
                        </div>
                    </div>

                    <hr>

                    <!-- Other Payment Options -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="payment" id="payment2" value="bank_transfer">
                        <label class="form-check-label" for="payment2">
                            <div class="fw-bold">Bank Transfer</div>
                            <div class="text-muted small">Direct bank transfer (24-48 hours)</div>
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="payment" id="payment3" value="card">
                        <label class="form-check-label" for="payment3">
                            <div class="fw-bold">Credit/Debit Card</div>
                            <div class="text-muted small">Visa, Mastercard, American Express</div>
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment" id="payment4" value="cod">
                        <label class="form-check-label" for="payment4">
                            <div class="fw-bold">Pay on Delivery (COD)</div>
                            <div class="text-muted small">Cash payment when order arrives</div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <!-- Items -->
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Subtotal (3 items)</span>
                        <span>KES 3,048</span>
                    </div>

                    <!-- Discount Code -->
                    <div class="mb-3">
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" placeholder="Discount code">
                            <button class="btn btn-outline-secondary" type="button">Apply</button>
                        </div>
                    </div>

                    <hr>

                    <!-- Charges Breakdown -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Merchandise Subtotal</span>
                            <span>KES 3,048</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Delivery Fee</span>
                            <span>FREE</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Packaging Fee</span>
                            <span>KES 50</span>
                        </div>
                    </div>

                    <hr>

                    <!-- Total -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0">Total Amount</h6>
                            <h5 class="fw-bold mb-0" style="color: var(--primary-green);">KES 3,098</h5>
                        </div>
                    </div>

                    <!-- Place Order Button -->
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn text-white" style="background-color: var(--primary-green);" id="placeOrderBtn">
                            <i class="bi bi-check-circle me-2"></i> Place Order
                        </button>
                    </div>

                    <!-- Continue Shopping -->
                    <div class="d-grid">
                        <a href="{{ route('customer.products.index') }}" class="btn btn-light border-2" style="border-color: var(--primary-green); color: var(--primary-green);">
                            <i class="bi bi-arrow-left me-2"></i> Continue Shopping
                        </a>
                    </div>

                    <!-- Security & Guarantee -->
                    <div class="mt-4 p-3 rounded" style="background-color: #f8f9fa;">
                        <p class="small fw-bold mb-2">
                            <i class="bi bi-shield-check" style="color: var(--primary-green);"></i> Secure & Protected
                        </p>
                        <p class="small text-muted mb-0">Your payment is secure and your data is encrypted. 30-day money-back guarantee on all orders.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- M-Pesa Payment Modal -->
<div class="modal fade" id="mpesaPaymentModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-phone me-2" style="color: var(--primary-green);"></i>M-Pesa Payment
                </h5>
            </div>
            <div class="modal-body">
                <div id="mpesaStatus" style="display: none;">
                    <div class="text-center py-4">
                        <div class="spinner-border text-success mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h6 class="fw-bold">Processing Payment</h6>
                        <p class="text-muted">Check your phone for the M-Pesa prompt...</p>
                        <p class="small text-muted">
                            <strong id="mpesaPhoneDisplay"></strong><br>
                            Amount: <strong id="mpesaAmountDisplay"></strong>
                        </p>
                    </div>
                </div>
                <div id="mpesaForm">
                    <div class="mb-3">
                        <label class="form-label">M-Pesa Phone Number</label>
                        <input type="tel" class="form-control form-control-lg" id="finalMpesaPhone"
                               placeholder="254712345678" required>
                        <small class="form-text text-muted">Enter the phone number that has M-Pesa registered</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelPaymentBtn">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmMpesaBtn" onclick="initiateMpesa()">
                    <i class="bi bi-phone me-1"></i> Send M-Pesa Prompt
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle M-Pesa phone section based on payment method
    document.querySelectorAll('input[name="payment"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const mpesaSection = document.getElementById('mpesaPhoneSection');
            if (this.value === 'mpesa') {
                mpesaSection.style.display = 'block';
            } else {
                mpesaSection.style.display = 'none';
            }
        });
    });

    // Initialize on page load
    const paymentMethods = document.querySelectorAll('input[name="payment"]');
    if (paymentMethods[0].checked) {
        document.getElementById('mpesaPhoneSection').style.display = 'block';
    }

    // Handle Place Order button
    document.getElementById('placeOrderBtn').addEventListener('click', function(e) {
        e.preventDefault();
        
        const selectedPayment = document.querySelector('input[name="payment"]:checked');
        
        if (selectedPayment.value === 'mpesa') {
            const mpesaPhone = document.getElementById('mpesaPhone').value.trim();
            
            if (!mpesaPhone) {
                alert('Please enter your M-Pesa phone number');
                return;
            }
            
            // Show M-Pesa modal
            document.getElementById('mpesaPhoneDisplay').textContent = mpesaPhone;
            document.getElementById('mpesaAmountDisplay').textContent = 'KES 3,098';
            document.getElementById('finalMpesaPhone').value = mpesaPhone;
            
            const mpesaModal = new bootstrap.Modal(document.getElementById('mpesaPaymentModal'));
            mpesaModal.show();
        } else {
            // Handle other payment methods
            document.querySelector('form').submit();
        }
    });

    // Initiate M-Pesa payment
    async function initiateMpesa() {
        const phone = document.getElementById('finalMpesaPhone').value;
        const confirmBtn = document.getElementById('confirmMpesaBtn');
        
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';
        
        try {
            // Submit the order form first to create the order
            const formData = new FormData(document.querySelector('form'));
            
            // The form submission will be handled after showing M-Pesa status
            document.getElementById('mpesaForm').style.display = 'none';
            document.getElementById('mpesaStatus').style.display = 'block';
            
            // You can add AJAX request here to initiate M-Pesa payment
            // const response = await fetch('/customer/orders/{{ $order->id }}/mpesa/initiate', {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json',
            //         'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
            //     },
            //     body: JSON.stringify({ phone_number: phone })
            // });
            
        } catch (error) {
            console.error('Error:', error);
            alert('Error processing payment. Please try again.');
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="bi bi-phone me-1"></i> Send M-Pesa Prompt';
        }
    }

    // Format phone number input
    document.getElementById('mpesaPhone').addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        
        // Add 254 prefix if starts with 0 or 7
        if (value.length > 0) {
            if (value.startsWith('0')) {
                value = '254' + value.substring(1);
            } else if (value.startsWith('7')) {
                value = '254' + value;
            }
        }
        
        this.value = value;
    });

    document.getElementById('finalMpesaPhone').addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        
        if (value.length > 0) {
            if (value.startsWith('0')) {
                value = '254' + value.substring(1);
            } else if (value.startsWith('7')) {
                value = '254' + value;
            }
        }
        
        this.value = value;
    });
</script>

<style>
    #mpesaPhoneSection {
        background-color: #f0f9ff;
        border: 1px solid #d4e3f7;
        border-radius: 10px;
        padding: 15px;
    }
</style>
@endsection
