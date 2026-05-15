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
                        
                        <div class="mb-3"><label class="form-label">Product</label><input type="text" class="form-control" value="{{ $price->product->name }}" disabled></div>
                        <div class="mb-3"><label class="form-label">Vendor</label><input type="text" class="form-control" value="{{ $price->vendor->business_name }}" disabled></div>
                        
                        <div class="row">
                            <div class="col-md-6"><div class="mb-3"><label class="form-label">Vendor Price</label><input type="number" name="vendor_price" class="form-control" step="0.01" value="{{ old('vendor_price', $price->vendor_price) }}"></div></div>
                            <div class="col-md-6"><div class="mb-3"><label class="form-label">Customer Visible Price</label><input type="number" name="customer_visible_price" class="form-control" step="0.01" value="{{ old('customer_visible_price', $price->customer_visible_price) }}"></div></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6"><div class="mb-3"><label class="form-label">Markup Amount</label><input type="number" name="markup" class="form-control" step="0.01" value="{{ old('markup', $price->markup) }}"></div></div>
                            <div class="col-md-6"><div class="mb-3"><label class="form-label">Base Delivery Fee</label><input type="number" name="base_delivery_fee" class="form-control" step="0.01" value="{{ old('base_delivery_fee', $price->base_delivery_fee) }}"></div></div>
                        </div>

                        <div class="mb-3"><div class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $price->is_active ? 'checked' : '' }}><label class="form-check-label">Active</label></div></div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Update</button>
                            <a href="{{ route('admin.prices.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection