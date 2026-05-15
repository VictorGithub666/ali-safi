@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4"><i class="bi bi-pencil-square"></i> Edit Customer</h1>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $customer->name) }}">
                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $customer->email) }}">
                            @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $customer->phone) }}">
                            @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $customer->address) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $customer->city) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Postal Code</label>
                            <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code', $customer->postal_code) }}">
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $customer->is_active ? 'checked' : '' }}>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Update</button>
                            <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection