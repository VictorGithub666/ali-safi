@extends('layouts.app')
@section('content')
<div class="container py-4">
    <h1 class="mb-4"><i class="bi bi-pencil-square"></i> Edit Vendor</h1>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.vendors.update', $vendor) }}">
                        @csrf
                        @method('PUT')
                        <h5 class="mb-3">User Information</h5>
                        <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{ old('name', $vendor->user->name) }}"></div>
                        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $vendor->user->email) }}"></div>
                        <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone', $vendor->user->phone) }}"></div>
                        
                        <hr><h5 class="mb-3">Business Information</h5>
                        <div class="mb-3"><label class="form-label">Business Name</label><input type="text" name="business_name" class="form-control" value="{{ old('business_name', $vendor->business_name) }}"></div>
                        <div class="mb-3"><label class="form-label">Business Phone</label><input type="text" name="business_phone" class="form-control" value="{{ old('business_phone', $vendor->business_phone) }}"></div>
                        <div class="mb-3"><label class="form-label">Business Address</label><input type="text" name="business_address" class="form-control" value="{{ old('business_address', $vendor->business_address) }}"></div>
                        <div class="mb-3"><label class="form-label">City</label><input type="text" name="city" class="form-control" value="{{ old('city', $vendor->city) }}"></div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Update</button>
                            <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection