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
            {{-- Display Errors at the top --}}
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading">
                        <i class="bi bi-exclamation-triangle"></i> Please fix the following errors:
                    </h5>
                    <ul class="mb-0 mt-2">
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

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Profile Picture Section - COMPLETELY SEPARATE FORM --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-camera"></i> Profile Picture
                    </h5>
                </div>
                <div class="card-body">
                    {{-- IMPORTANT: Separate form with different action --}}
                    <form method="POST" action="{{ route('vendor.profile.picture') }}" enctype="multipart/form-data" id="profilePictureForm">
                        @csrf
                        
                        <div class="d-flex align-items-center gap-4">
                            <div>
                                @if($user->profile_picture)
                                    <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                         alt="{{ $user->name }}" class="rounded-circle" 
                                         style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #05bb14;">
                                @else
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" 
                                         style="width: 100px; height: 100px; border: 3px solid #e9ecef;">
                                        <i class="bi bi-person" style="font-size: 2.5rem; color: #ccc;"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <label class="form-label fw-bold">Upload New Picture</label>
                                <input type="file" class="form-control" 
                                       name="picture" accept="image/jpeg,image/png,image/jpg,image/gif">
                                <small class="form-text text-muted">
                                    <i class="bi bi-info-circle"></i> 
                                    JPEG, PNG, JPG, GIF (Max 2MB)
                                </small>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload"></i> Update Picture
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Profile Information Form --}}
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-person"></i> Profile Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('vendor.profile.update') }}" id="profileInfoForm">
                        @csrf
                        @method('PATCH')

                        {{-- Name --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $user->name) }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $user->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Phone --}}
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $user->phone) }}" 
                                   placeholder="07XXXXXXXX or 254XXXXXXXXX">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        {{-- Business Information --}}
                        <h5 class="mb-3">
                            <i class="bi bi-shop"></i> Business Information
                        </h5>

                        {{-- Business Name --}}
                        <div class="mb-3">
                            <label for="business_name" class="form-label">Business Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('business_name') is-invalid @enderror" 
                                   id="business_name" name="business_name" 
                                   value="{{ old('business_name', $vendor->business_name) }}">
                            @error('business_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Business Phone --}}
                        <div class="mb-3">
                            <label for="business_phone" class="form-label">Business Phone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('business_phone') is-invalid @enderror" 
                                   id="business_phone" name="business_phone" 
                                   value="{{ old('business_phone', $vendor->business_phone) }}" 
                                   placeholder="07XXXXXXXX or 254XXXXXXXXX">
                            <small class="form-text text-muted">
                                <i class="bi bi-whatsapp"></i> WhatsApp notifications will be sent to this number
                            </small>
                            @error('business_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Business Address --}}
                        <div class="mb-3">
                            <label for="business_address" class="form-label">Business Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('business_address') is-invalid @enderror" 
                                      id="business_address" name="business_address" rows="3" 
                                      placeholder="Enter your complete business address">{{ old('business_address', $vendor->business_address) }}</textarea>
                            @error('business_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Location --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="latitude" class="form-label">Latitude</label>
                                <input type="number" step="0.0000000000000001" class="form-control @error('latitude') is-invalid @enderror" 
                                       id="latitude" name="latitude" 
                                       value="{{ old('latitude', $vendor->latitude) }}" placeholder="-1.2921">
                                @error('latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="longitude" class="form-label">Longitude</label>
                                <input type="number" step="0.0000000000000001" class="form-control @error('longitude') is-invalid @enderror" 
                                       id="longitude" name="longitude" 
                                       value="{{ old('longitude', $vendor->longitude) }}" placeholder="36.8219">
                                @error('longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Operating Hours --}}
                        <div class="mb-3">
                            <label for="operating_hours" class="form-label">Operating Hours</label>
                            <textarea class="form-control @error('operating_hours') is-invalid @enderror" 
                                      id="operating_hours" name="operating_hours" rows="4" 
                                      placeholder='{"monday":"9:00-17:00","tuesday":"9:00-17:00","wednesday":"9:00-17:00","thursday":"9:00-17:00","friday":"9:00-17:00","saturday":"10:00-15:00","sunday":"closed"}'>{{ old('operating_hours', is_array($vendor->operating_hours) ? json_encode($vendor->operating_hours) : $vendor->operating_hours) }}</textarea>
                            <small class="form-text text-muted">JSON format. Use "closed" for days off.</small>
                            @error('operating_hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle"></i> Save Profile Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Shop Status --}}
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-shop"></i> Shop Status
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $isOpen = $vendor->is_open ?? false;
                    @endphp
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Current Status:</span>
                        <span class="badge bg-{{ $isOpen ? 'success' : 'danger' }} fs-6">
                            {{ $isOpen ? 'OPEN' : 'CLOSED' }}
                        </span>
                    </div>
                    <form method="POST" action="{{ route('vendor.toggle-status') }}">
                        @csrf
                        <button type="submit" class="btn btn-{{ $isOpen ? 'outline-danger' : 'success' }} w-100">
                            <i class="bi bi-{{ $isOpen ? 'lock' : 'unlock' }}"></i> 
                            {{ $isOpen ? 'Close Shop' : 'Open Shop' }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Account Status --}}
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-shield-check"></i> Account Status
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Email:</span>
                        <span class="badge bg-{{ $user->email_verified_at ? 'success' : 'warning' }}">
                            {{ $user->email_verified_at ? 'Verified' : 'Pending' }}
                        </span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Account:</span>
                        <span class="badge bg-{{ $user->is_verified ? 'success' : 'warning' }}">
                            {{ $user->is_verified ? 'Verified' : 'Pending' }}
                        </span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Business:</span>
                        <span class="badge bg-{{ $vendor->is_verified ? 'success' : 'warning' }}">
                            {{ $vendor->is_verified ? 'Verified' : 'Pending' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Member Since:</span>
                        <strong>{{ $user->created_at->format('M Y') }}</strong>
                    </div>
                </div>
            </div>

            {{-- Business Stats --}}
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-bar-chart"></i> Business Stats
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Products:</span>
                        <strong>{{ $vendor->products()->count() }}</strong>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Total Orders:</span>
                        <strong>{{ $vendor->orders()->count() }}</strong>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Rating:</span>
                        <span class="text-warning">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= floor($vendor->rating ?? 0))
                                    ★
                                @else
                                    ☆
                                @endif
                            @endfor
                            ({{ number_format($vendor->rating ?? 0, 1) }})
                        </span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Wallet Balance:</span>
                        <strong class="text-success">KES {{ number_format($vendor->wallet_balance ?? 0, 2) }}</strong>
                    </div>
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-link"></i> Quick Links
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('vendor.dashboard') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-box"></i> Products
                        </a>
                        <a href="{{ route('vendor.orders.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-list-check"></i> Orders
                        </a>
                        <a href="{{ route('vendor.earnings') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-cash"></i> Earnings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Simple JavaScript to prevent form conflicts --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ensure forms are completely independent
    const pictureForm = document.getElementById('profilePictureForm');
    const infoForm = document.getElementById('profileInfoForm');
    
    if (pictureForm) {
        pictureForm.addEventListener('submit', function(e) {
            // Only validate the picture field in this form
            const fileInput = this.querySelector('input[type="file"]');
            if (!fileInput.value) {
                e.preventDefault();
                alert('Please select a picture to upload.');
            }
        });
    }
});
</script>
@endsection