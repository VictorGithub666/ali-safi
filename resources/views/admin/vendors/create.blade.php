@extends('layouts.app')
@section('content')
<div class="container py-4">
    <h1 class="mb-4"><i class="bi bi-shop"></i> Add Vendor</h1>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.vendors.store') }}">
                        @csrf
                        <h5 class="mb-3">User Information</h5>
                        <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">@error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">@error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                        <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">@error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                        <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control @error('password') is-invalid @enderror">@error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                        <div class="mb-3"><label class="form-label">Confirm Password</label><input type="password" name="password_confirmation" class="form-control"></div>
                        
                        <hr><h5 class="mb-3">Business Information</h5>
                        <div class="mb-3"><label class="form-label">Business Name</label><input type="text" name="business_name" class="form-control @error('business_name') is-invalid @enderror" value="{{ old('business_name') }}">@error('business_name')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                        <div class="mb-3"><label class="form-label">Business Phone</label><input type="text" name="business_phone" class="form-control @error('business_phone') is-invalid @enderror" value="{{ old('business_phone') }}">@error('business_phone')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                        <div class="mb-3"><label class="form-label">Business Address</label><input type="text" name="business_address" class="form-control @error('business_address') is-invalid @enderror" value="{{ old('business_address') }}">@error('business_address')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                        <div class="mb-3"><label class="form-label">City</label><input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city') }}">@error('city')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Create</button>
                            <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection