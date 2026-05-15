@extends('layouts.app')
@section('content')
<div class="container py-4">
    <h1 class="mb-4"><i class="bi bi-pencil-square"></i> Edit Rider</h1>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.riders.update', $rider) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{ old('name', $rider->user->name) }}"></div>
                        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $rider->user->email) }}"></div>
                        <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone', $rider->user->phone) }}"></div>
                        
                        <hr><h5 class="mb-3">Vehicle Information</h5>
                        <div class="mb-3"><label class="form-label">Vehicle Type</label><select name="vehicle_type" class="form-select"><option value="motorcycle" {{ $rider->vehicle_type === 'motorcycle' ? 'selected' : '' }}>Motorcycle</option><option value="bike" {{ $rider->vehicle_type === 'bike' ? 'selected' : '' }}>Bike</option><option value="van" {{ $rider->vehicle_type === 'van' ? 'selected' : '' }}>Van</option></select></div>
                        <div class="mb-3"><label class="form-label">Vehicle Number</label><input type="text" name="vehicle_number" class="form-control" value="{{ old('vehicle_number', $rider->vehicle_number) }}"></div>
                        <div class="mb-3"><label class="form-label">License Number</label><input type="text" name="license_number" class="form-control" value="{{ old('license_number', $rider->license_number) }}"></div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Update</button>
                            <a href="{{ route('admin.riders.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection