@extends('layouts.app')
@section('content')
<div class="container py-4">
    <h1 class="mb-4"><i class="bi bi-pencil-square"></i> Edit Price</h1>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.prices.update', $price) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <input type="text" class="form-control" value="{{ $price->product->name }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Vendor</label>
                            <input type="text" class="form-control" value="{{ $price->vendor->business_name }}" disabled>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Vendor Price (Cost)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">KES</span>
                                        <input type="number" name="vendor_price" id="vendor_price" class="form-control" step="0.01" value="{{ old('vendor_price', $price->vendor_price) }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Customer Visible Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">KES</span>
                                        <input type="number" name="customer_visible_price" id="customer_visible_price" class="form-control" step="0.01" value="{{ old('customer_visible_price', $price->customer_visible_price) }}" required>
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
                                        <input type="number" name="markup" id="markup" class="form-control" step="0.01" value="{{ old('markup', $price->markup) }}">
                                    </div>
                                    <small class="text-muted" id="markupPercentageDisplay"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Base Delivery Fee</label>
                                    <div class="input-group">
                                        <span class="input-group-text">KES</span>
                                        <input type="number" name="base_delivery_fee" class="form-control" step="0.01" value="{{ old('base_delivery_fee', $price->base_delivery_fee) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $price->is_active ? 'checked' : '' }}>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-check-circle"></i> Update
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
                        <strong>Markup Calculation:</strong>
                        <ul class="mt-2 mb-0">
                            <li>Markup Amount = Customer Price - Vendor Price</li>
                            <li>Markup Percentage = (Markup / Vendor Price) × 100%</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const vendorPriceInput = document.getElementById('vendor_price');
    const customerPriceInput = document.getElementById('customer_visible_price');
    const markupInput = document.getElementById('markup');
    const markupPercentageDisplay = document.getElementById('markupPercentageDisplay');
    
    function calculateMarkup() {
        const vendorPrice = parseFloat(vendorPriceInput.value) || 0;
        const customerPrice = parseFloat(customerPriceInput.value) || 0;
        
        if (vendorPrice > 0) {
            const markupAmount = customerPrice - vendorPrice;
            markupInput.value = markupAmount.toFixed(2);
            
            const markupPercentage = (markupAmount / vendorPrice) * 100;
            markupPercentageDisplay.innerHTML = `<strong>Markup Percentage:</strong> ${markupPercentage.toFixed(2)}%`;
            
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
    
    customerPriceInput.addEventListener('input', calculateMarkup);
    customerPriceInput.addEventListener('change', calculateMarkup);
    
    calculateMarkup();
});
</script>
@endsection