@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="fw-bold mb-4">
                <i class="bi bi-person-circle"></i> Edit Profile
            </h2>

            <div class="row">
                <div class="col-md-4 text-center mb-4 mb-md-0">
                    <div class="card">
                        <div class="card-body">
                            <div style="width: 150px; height: 150px; margin: 0 auto 1rem; border-radius: 50%; overflow: hidden; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                @if(Auth::user()->profile_picture)
                                    <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <i class="bi bi-person-fill" style="font-size: 5rem; color: #ccc;"></i>
                                @endif
                            </div>
                            <h5 class="fw-bold">{{ Auth::user()->name }}</h5>
                            <p class="text-muted small mb-3">{{ Auth::user()->email }}</p>
                            <span class="badge badge-success">{{ ucfirst(Auth::user()->user_type) }}</span>

                            <form method="POST" action="{{ route('profile.picture') }}" enctype="multipart/form-data" class="mt-4">
                                @csrf
                                <div class="mb-3">
                                    <label for="picture" class="form-label">Upload New Picture</label>
                                    <input type="file" class="form-control @error('picture') is-invalid @enderror" id="picture" name="picture" accept="image/*" required>
                                    @error('picture')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-upload"></i> Update Picture
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Personal Information</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('profile.update') }}">
                                @csrf
                                @method('PATCH')

                                <!-- Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', Auth::user()->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', Auth::user()->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Phone -->
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', Auth::user()->phone) }}" placeholder="+254712345678">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Address -->
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2" placeholder="Your home address">{{ old('address', Auth::user()->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- City -->
                                <div class="mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', Auth::user()->city) }}" placeholder="Nairobi">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Save Button -->
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Password Change -->
                    <div class="card mt-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Change Password</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('password.update') }}">
                                @csrf
                                @method('PUT')

                                <!-- Current Password -->
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- New Password -->
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Update Button -->
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-secondary">
                                        <i class="bi bi-key"></i> Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
