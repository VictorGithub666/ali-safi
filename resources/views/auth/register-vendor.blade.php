@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register as Vendor') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register.vendor') }}">
                        @csrf

                        <!-- Personal Information -->
                        <h5 class="mb-3">Personal Information</h5>

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Full Name') }} <span class="text-danger">*</span></label>
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Business Information -->
                        <h5 class="mb-3 mt-4">Business Information</h5>

                        <div class="row mb-3">
                            <label for="business_name" class="col-md-4 col-form-label text-md-end">{{ __('Business Name') }} <span class="text-danger">*</span></label>
                            <div class="col-md-6">
                                <input id="business_name" type="text" class="form-control @error('business_name') is-invalid @enderror" name="business_name" value="{{ old('business_name') }}" required>
                                @error('business_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }} <span class="text-danger">*</span></label>
                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="phone" class="col-md-4 col-form-label text-md-end">{{ __('Phone Number') }} <span class="text-danger">*</span></label>
                            <div class="col-md-6">
                                <input id="phone" type="tel" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" required placeholder="254712345678">
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">Include country code (254) or local format (07xx)</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="business_phone" class="col-md-4 col-form-label text-md-end">{{ __('Business Phone') }}</label>
                            <div class="col-md-6">
                                <input id="business_phone" type="tel" class="form-control @error('business_phone') is-invalid @enderror" name="business_phone" value="{{ old('business_phone') }}" placeholder="254712345678">
                                @error('business_phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">Optional - Different from personal phone</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="business_address" class="col-md-4 col-form-label text-md-end">{{ __('Business Address') }}</label>
                            <div class="col-md-6">
                                <textarea id="business_address" class="form-control @error('business_address') is-invalid @enderror" name="business_address" rows="2" placeholder="Stima Plaza, 3rd Floor, Room 12">{{ old('business_address') }}</textarea>
                                @error('business_address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Location Section - Same as checkout -->
                        <h5 class="mb-3 mt-4">Shop Location (Optional but recommended)</h5>

                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-end">{{ __('Get Location') }}</label>
                            <div class="col-md-6">
                                <button type="button" id="getLocationBtn" class="btn btn-sm" style="background-color: var(--primary-green); color: white; width: 100%;">
                                    <i class="bi bi-geo-alt me-1"></i> Get My Shop Location
                                </button>
                                <span id="locationStatus" class="small text-muted d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="row">
                                    <div class="col-6">
                                        <label for="latitude" class="form-label small">Latitude</label>
                                        <input type="number" step="0.0000000000000001" class="form-control form-control-sm @error('latitude') is-invalid @enderror" 
                                               id="latitude" name="latitude" placeholder="-1.287389" value="{{ old('latitude') }}">
                                    </div>
                                    <div class="col-6">
                                        <label for="longitude" class="form-label small">Longitude</label>
                                        <input type="number" step="0.0000000000000001" class="form-control form-control-sm @error('longitude') is-invalid @enderror" 
                                               id="longitude" name="longitude" placeholder="36.789012" value="{{ old('longitude') }}">
                                    </div>
                                </div>
                                @error('latitude')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                                @error('longitude')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">Click "Get My Shop Location" to auto-fill coordinates</small>
                            </div>
                        </div>

                        <!-- Password Section -->
                        <h5 class="mb-3 mt-4">Account Security</h5>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }} <span class="text-danger">*</span></label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Minimum 8 characters</small>
                                @error('password')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }} <span class="text-danger">*</span></label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password-confirm">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Info Alert -->
                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <small>Your account will be reviewed by admin before you can start selling. Once verified, you'll receive an email notification.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-shop"></i> {{ __('Register as Vendor') }}
                                </button>
                                <a href="{{ route('login') }}" class="btn btn-link">
                                    {{ __('Already have an account? Login') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });

    // Location picker - Same working code from checkout
    const getLocationBtn = document.getElementById('getLocationBtn');
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const statusSpan = document.getElementById('locationStatus');
    const addressInput = document.getElementById('business_address');

    if (getLocationBtn) {
        getLocationBtn.addEventListener('click', function(e) {
            e.preventDefault();
            getLocation();
        });
    }

    function getLocation() {
        if (!('geolocation' in navigator)) {
            showStatus('Geolocation is not supported by your browser', 'danger');
            return;
        }

        statusSpan.textContent = 'Getting your location...';
        statusSpan.style.color = '#6c757d';
        getLocationBtn.disabled = true;

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);
                
                showStatus('✓ Location updated successfully!', 'success');
                getLocationBtn.disabled = false;
            },
            function(error) {
                getLocationBtn.disabled = false;
                
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        showStatus('❌ Permission denied. Please enable location in your browser settings.', 'danger');
                        break;
                    case error.POSITION_UNAVAILABLE:
                        showStatus('❌ Location information is unavailable.', 'danger');
                        break;
                    case error.TIMEOUT:
                        showStatus('❌ The request timed out. Please try again.', 'danger');
                        break;
                    default:
                        showStatus('❌ An error occurred. Please try again.', 'danger');
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }

    function showStatus(message, type) {
        statusSpan.textContent = message;
        if (type === 'success') {
            statusSpan.style.color = 'var(--primary-green)';
            setTimeout(() => {
                statusSpan.textContent = '';
            }, 4000);
        } else if (type === 'danger') {
            statusSpan.style.color = '#dc3545';
            setTimeout(() => {
                statusSpan.textContent = '';
            }, 5000);
        }
    }
});
</script>
@endsection