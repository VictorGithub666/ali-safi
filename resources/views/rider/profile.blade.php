@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="fw-bold mb-4">
        <i class="bi bi-person-circle"></i> My Profile
    </h2>

    <div class="row">
        <!-- Personal Info -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-person"></i> Personal Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="row mb-3">
                            <label for="name" class="col-md-3 col-form-label">Full Name</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', Auth::user()->name) }}" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-3 col-form-label">Email Address</label>
                            <div class="col-md-9">
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', Auth::user()->email) }}" required>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="phone" class="col-md-3 col-form-label">Phone Number</label>
                            <div class="col-md-9">
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', Auth::user()->phone) }}" required>
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="address" class="col-md-3 col-form-label">Address</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                       id="address" name="address" value="{{ old('address', Auth::user()->address ?? '') }}">
                                @error('address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-9 offset-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg"></i> Save Changes
                                </button>
                                <a href="{{ route('rider.dashboard') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-lg"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Profile Picture -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-image"></i> Profile Picture</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img src="{{ Auth::user()->picture_url ?? 'https://via.placeholder.com/150' }}" 
                             alt="Profile Picture" class="img-fluid rounded-circle" style="max-width: 150px; height: 150px; object-fit: cover;">
                    </div>
                    <form method="POST" action="{{ route('profile.picture') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <input type="file" class="form-control @error('picture') is-invalid @enderror" 
                                   id="picture" name="picture" accept="image/*" required>
                            @error('picture')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-upload"></i> Upload Picture
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Vehicle Information -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-car-front"></i> Vehicle Information</h6>
                </div>
                <div class="card-body">
                    @php
                        $rider = Auth::user()->rider;
                    @endphp
                    
                    <form method="POST" action="{{ route('rider.profile.update-vehicle') ?? '#' }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <label for="vehicle_type" class="col-md-3 col-form-label">Vehicle Type</label>
                            <div class="col-md-9">
                                <select class="form-select @error('vehicle_type') is-invalid @enderror" 
                                        id="vehicle_type" name="vehicle_type" required>
                                    <option value="">Select vehicle type</option>
                                    <option value="motorcycle" {{ $rider->vehicle_type === 'motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                                    <option value="car" {{ $rider->vehicle_type === 'car' ? 'selected' : '' }}>Car</option>
                                    <option value="van" {{ $rider->vehicle_type === 'van' ? 'selected' : '' }}>Van</option>
                                    <option value="truck" {{ $rider->vehicle_type === 'truck' ? 'selected' : '' }}>Truck</option>
                                </select>
                                @error('vehicle_type')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="vehicle_number" class="col-md-3 col-form-label">Vehicle Number/Plate</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control @error('vehicle_number') is-invalid @enderror" 
                                       id="vehicle_number" name="vehicle_number" value="{{ $rider->vehicle_number }}" required>
                                @error('vehicle_number')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="license_number" class="col-md-3 col-form-label">License Number</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control @error('license_number') is-invalid @enderror" 
                                       id="license_number" name="license_number" value="{{ $rider->license_number }}" required>
                                @error('license_number')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-9 offset-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg"></i> Update Vehicle Info
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Account Stats -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Account Stats</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted small">Total Deliveries</label>
                        <p class="mb-0"><strong style="font-size: 1.3rem;">{{ $rider->total_deliveries ?? 0 }}</strong></p>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted small">Rating</label>
                        <p class="mb-0">
                            <strong style="font-size: 1.3rem;">
                                <i class="bi bi-star-fill" style="color: #ffc107;"></i> 
                                {{ number_format($rider->rating ?? 0, 1) }}/5
                            </strong>
                        </p>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted small">Verification Status</label>
                        <p class="mb-0">
                            @if($rider->is_verified)
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Verified</span>
                            @else
                                <span class="badge bg-warning"><i class="bi bi-hourglass"></i> Pending Verification</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="text-muted small">Availability Status</label>
                        <p class="mb-0">
                            <span class="badge badge-{{ $rider->is_available ? 'success' : 'secondary' }}">
                                {{ $rider->is_available ? 'Online' : 'Offline' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Verification Notice -->
            @if(!$rider->is_verified)
                <div class="alert alert-info">
                    <h6 class="alert-heading">
                        <i class="bi bi-info-circle"></i> Pending Verification
                    </h6>
                    <p class="mb-2">Your account is under verification. Once verified, you'll be able to accept deliveries.</p>
                    <small>This typically takes 24-48 hours.</small>
                </div>
            @endif
        </div>
    </div>

    <!-- Change Password -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-lock"></i> Change Password</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <label for="current_password" class="col-md-3 col-form-label">Current Password</label>
                            <div class="col-md-9">
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                       id="current_password" name="current_password" required>
                                @error('current_password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-3 col-form-label">New Password</label>
                            <div class="col-md-9">
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" required>
                                @error('password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password_confirmation" class="col-md-3 col-form-label">Confirm Password</label>
                            <div class="col-md-9">
                                <input type="password" class="form-control" id="password_confirmation" 
                                       name="password_confirmation" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-9 offset-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg"></i> Update Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
