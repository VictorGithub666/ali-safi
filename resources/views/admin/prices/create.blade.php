@extends('layouts.app')
@section('content')
<div class="container py-4">
    <h1 class="mb-4"><i class="bi bi-tag-fill"></i> Set Product Price</h1>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.prices.store') }}">
                        @csrf
                        <div class="mb-3"><label class="form-label">Product</label><select name="product_id" class="form-select @error('product_id') is-invalid @enderror"><option value="">Select Product</option>@foreach($products as $id => $name)<option value="{{ $id }}" {{ old('product_id') == $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select>@error('product_id')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                        <div class="mb-3"><label class="form-label">Vendor</label><select name="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror"><option value="">Select Vendor</option>@foreach($vendors as $id => $name)<option value="{{ $id }}" {{ old('vendor_id') == $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select>@error('vendor_id')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                        
                        <div class="row">
                            <div class="col-md-6"><div class="mb-3"><label class="form-label">Vendor Price (Cost)</label><input type="number" name="vendor_price" class="form-control" step="0.01" value="{{ old('vendor_price') }}"></div></div>
                            <div class="col-md-6"><div class="mb-3"><label class="form-label">Customer Visible Price</label><input type="number" name="customer_visible_price" class="form-control" step="0.01" value="{{ old('customer_visible_price') }}"></div></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6"><div class="mb-3"><label class="form-label">Markup Amount</label><input type="number" name="markup" class="form-control" step="0.01" value="{{ old('markup') }}" readonly></div></div>
                            <div class="col-md-6"><div class="mb-3"><label class="form-label">Base Delivery Fee</label><input type="number" name="base_delivery_fee" class="form-control" step="0.01" value="{{ old('base_delivery_fee') }}"></div></div>
                        </div>

                        <div class="mb-3"><div class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" checked><label class="form-check-label">Active</label></div></div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Create</button>
                            <a href="{{ route('admin.prices.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection