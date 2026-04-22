@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h2 class="fw-bold mb-1">
            <i class="bi bi-gear"></i> Vendor Profile & Settings
        </h2>
        <p class="text-muted mb-0">Manage your business information</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading">Please fix the following errors:</h5>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Personal Information -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-person"></i> Personal Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('vendor.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <!-- Profile Picture -->
                        <div class="mb-4">
                            <label class="form-label">Profile Picture</label>
                            <div class="d-flex align-items-end gap-3">
                                <div>
                                    @if($user->profile_picture)
                                        <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                             alt="{{ $user->name }}" class="rounded-circle" 
                                             style="width: 100px; height: 100px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" 
                                             style="width: 100px; height: 100px;">
                                            <i class="bi bi-person" style="font-size: 2rem; color: #ccc;"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" class="form-control @error('picture') is-invalid @enderror" 
                                           name="picture" accept="image/*">
                                    <small class="form-text text-muted">JPEG, PNG, JPG, GIF (Max 2MB)</small>
                                    @error('picture')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Name -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <!-- Business Information -->
                        <h5 class="mb-3">Business Information</h5>

                        <!-- Business Name -->
                        <div class="mb-3">
                            <label for="business_name" class="form-label">Business Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('business_name') is-invalid @enderror" 
                                   id="business_name" name="business_name" 
                                   value="{{ old('business_name', $vendor->business_name) }}" required>
                            @error('business_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Business Phone -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="business_phone" class="form-label">Business Phone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control @error('business_phone') is-invalid @enderror" 
                                       id="business_phone" name="business_phone" 
                                       value="{{ old('business_phone', $vendor->business_phone) }}" required>
                                @error('business_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Business Address -->
                        <div class="mb-3">
                            <label for="business_address" class="form-label">Business Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('business_address') is-invalid @enderror" 
                                      id="business_address" name="business_address" rows="3" required>{{ old('business_address', $vendor->business_address) }}</textarea>
                            @error('business_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Location -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="latitude" class="form-label">Latitude</label>
                                <input type="number" step="0.00000001" class="form-control @error('latitude') is-invalid @enderror" 
                                       id="latitude" name="latitude" 
                                       value="{{ old('latitude', $vendor->latitude) }}" placeholder="-1.2921">
                                @error('latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="longitude" class="form-label">Longitude</label>
                                <input type="number" step="0.00000001" class="form-control @error('longitude') is-invalid @enderror" 
                                       id="longitude" name="longitude" 
                                       value="{{ old('longitude', $vendor->longitude) }}" placeholder="36.8219">
                                @error('longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Operating Hours (JSON format) -->
                        <div class="mb-3">
                            <label for="operating_hours" class="form-label">Operating Hours <span class="text-muted">(JSON format, optional)</span></label>
                            <textarea class="form-control @error('operating_hours') is-invalid @enderror" 
                                      id="operating_hours" name="operating_hours" rows="3" 
                                      placeholder='{"monday":"9:00-17:00","tuesday":"9:00-17:00"...}'>@if($vendor->operating_hours){{ json_encode($vendor->operating_hours) }}@endif</textarea>
                            <small class="form-text text-muted">Specify in JSON format for each day of the week</small>
                            @error('operating_hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <!-- Account Status -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-shield-check"></i> Account Status
                    </h6>
                </div>
                <div class="card-body small">
                    <p class="mb-2">
                        <strong>Verification:</strong><br>
                        @if($user->is_verified)
                            <span class="badge bg-success">Verified</span>
                        @else
                            <span class="badge bg-warning">Pending Verification</span>
                        @endif
                    </p>
                    <p class="mb-2">
                        <strong>Business Verified:</strong><br>
                        @if($vendor->is_verified)
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-warning">Pending</span>
                        @endif
                    </p>
                    <p class="mb-0">
                        <strong>Member Since:</strong><br>
                        {{ $user->created_at->format('M d, Y') }}
                    </p>
                </div>
            </div>

            <!-- Business Stats -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-graph-up"></i> Business Stats
                    </h6>
                </div>
                <div class="card-body small">
                    <p class="mb-2">
                        <strong>Total Products:</strong><br>
                        <span class="badge bg-info">{{ $vendor->products()->count() }}</span>
                    </p>
                    <p class="mb-2">
                        <strong>Total Orders:</strong><br>
                        <span class="badge bg-primary">{{ $vendor->orders()->count() }}</span>
                    </p>
                    <p class="mb-2">
                        <strong>Rating:</strong><br>
                        <span class="badge bg-warning text-dark">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= floor($vendor->rating))
                                    ★
                                @else
                                    ☆
                                @endif
                            @endfor
                            ({{ number_format($vendor->rating, 1) }})
                        </span>
                    </p>
                    <p class="mb-0">
                        <strong>Wallet Balance:</strong><br>
                        <span class="badge bg-success">KES {{ number_format($vendor->wallet_balance, 2) }}</span>
                    </p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning"></i> Quick Links
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('vendor.dashboard') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-house"></i> Dashboard
                        </a>
                        <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-box"></i> Products
                        </a>
                        <a href="{{ route('vendor.orders.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-list"></i> Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
