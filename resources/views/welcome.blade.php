@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #05bb14 0%, #237bdd 100%); color: white; padding: 5rem 0;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 fw-bold mb-4">Fast & Reliable Delivery</h1>
                <p class="lead mb-4">Get fresh gas and water delivered to your doorstep within minutes. Download the Ali-Safi app today!</p>
                <div class="d-flex gap-3">
                    @auth
                        @if(Auth::user()->user_type === 'customer')
                            <a href="{{ route('customer.products.index') }}" class="btn btn-light btn-lg">
                                <i class="bi bi-shop"></i> Start Shopping
                            </a>
                        @else
                            <a href="{{ route('home') }}" class="btn btn-light btn-lg">
                                <i class="bi bi-speedometer2"></i> Go to Dashboard
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-light btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                        <a href="{{ route('google.login') }}" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-google"></i> Google Login
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-person-plus"></i> Sign Up
                        </a>
                    @endauth
                </div>
            </div>
            <div class="col-md-6 text-center">
                <img style="display: inline; height:300px;" src="/storage/logo-1000.png" alt="Ali-Safi Logo">
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5 fw-bold">Why Choose Ali-Safi?</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-lightning-charge-fill" style="font-size: 2rem; color: #05bb14;"></i>
                    </div>
                    <h5 class="card-title">Fast Delivery</h5>
                    <p class="card-text text-muted">Quick and reliable delivery service with real-time tracking</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-shield-check" style="font-size: 2rem; color: #237bdd;"></i>
                    </div>
                    <h5 class="card-title">Verified Vendors</h5>
                    <p class="card-text text-muted">All our vendors are verified for quality and reliability</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-wallet2" style="font-size: 2rem; color: #05bb14;"></i>
                    </div>
                    <h5 class="card-title">Best Prices</h5>
                    <p class="card-text text-muted">Competitive pricing with special discounts for regular customers</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-5" style="background-color: #f8f9fa;">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 mb-4 mb-md-0">
                <h3 class="fw-bold" style="color: #05bb14;">10K+</h3>
                <p class="text-muted">Active Users</p>
            </div>
            <div class="col-md-3 mb-4 mb-md-0">
                <h3 class="fw-bold" style="color: #237bdd;">500+</h3>
                <p class="text-muted">Vendors</p>
            </div>
            <div class="col-md-3 mb-4 mb-md-0">
                <h3 class="fw-bold" style="color: #05bb14;">50K+</h3>
                <p class="text-muted">Orders Delivered</p>
            </div>
            <div class="col-md-3">
                <h3 class="fw-bold" style="color: #237bdd;">4.8/5</h3>
                <p class="text-muted">Average Rating</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto text-center">
                <h3 class="fw-bold mb-3">Ready to Get Started?</h3>
                <p class="text-muted mb-4">Join thousands of satisfied customers and vendors on Ali-Safi</p>
                @auth
                    <a href="{{ route('customer.products.index') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-arrow-right"></i> Continue
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-person-plus"></i> Create Account
                    </a>
                @endauth
            </div>
        </div>
    </div>
</section>

<!-- Nearby Shops Modal -->
@include('customer.partials.nearby-modal')

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if we should show the modal
    @auth
        @if(Auth::user()->user_type === 'customer')
            // Check if user has already used nearby feature
            const hasUsedNearby = localStorage.getItem('nearbyUsed');
            const lastDismissed = localStorage.getItem('nearbyModalDismissed');
            const currentTime = Date.now();
            
            // Show if never used nearby, never dismissed, or dismissed more than 7 days ago
            if (!hasUsedNearby && (!lastDismissed || (currentTime - parseInt(lastDismissed)) > 7 * 24 * 60 * 60 * 1000)) {
                setTimeout(function() {
                    const modal = new bootstrap.Modal(document.getElementById('nearbyShopsModal'));
                    modal.show();
                }, 1500);
            }
        @endif
    @endauth
    
    // Handle Allow Location button
    const allowBtn = document.getElementById('allowLocationBtn');
    const loadingState = document.getElementById('locationLoadingState');
    const statusDiv = document.getElementById('locationStatus');
    const statusText = document.getElementById('locationStatusText');
    
    if (allowBtn) {
        allowBtn.addEventListener('click', function() {
            getLocation();
        });
    }
    
    // Store dismissal time when modal is closed
    const modalElement = document.getElementById('nearbyShopsModal');
    if (modalElement) {
        modalElement.addEventListener('hidden.bs.modal', function() {
            localStorage.setItem('nearbyModalDismissed', Date.now().toString());
        });
    }
    
    // Handle dismiss button
    const dismissBtn = document.getElementById('dismissModalBtn');
    if (dismissBtn) {
        dismissBtn.addEventListener('click', function() {
            localStorage.setItem('nearbyModalDismissed', Date.now().toString());
        });
    }
    
    function getLocation() {
        if (!navigator.geolocation) {
            showStatus('Geolocation is not supported by your browser', 'danger');
            return;
        }
        
        // Show loading state
        allowBtn.style.display = 'none';
        if (dismissBtn) dismissBtn.style.display = 'none';
        loadingState.style.display = 'block';
        statusDiv.style.display = 'none';
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                // Send location to server to find nearby shops
                findNearbyShops(lat, lng);
            },
            function(error) {
                loadingState.style.display = 'none';
                allowBtn.style.display = 'block';
                if (dismissBtn) dismissBtn.style.display = 'block';
                
                let errorMsg = '';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMsg = 'Location permission denied. Please enable location access in your browser settings to find nearby shops.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMsg = 'Location information is unavailable. Please check your GPS.';
                        break;
                    case error.TIMEOUT:
                        errorMsg = 'Location request timed out. Please try again.';
                        break;
                    default:
                        errorMsg = 'An error occurred while getting your location.';
                }
                
                showStatus(errorMsg, 'danger');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }
    
    function findNearbyShops(lat, lng) {
        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                         document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        console.log('Sending request to find nearby shops...');
        console.log('Latitude:', lat, 'Longitude:', lng);
        
        fetch('{{ route("customer.products.nearby") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                latitude: lat,
                longitude: lng
            })
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Error response:', text);
                    throw new Error(`HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                // Mark that user has used nearby feature
                localStorage.setItem('nearbyUsed', 'true');
                
                // Close the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('nearbyShopsModal'));
                if (modal) {
                    modal.hide();
                }
                
                // Show success message
                showStatus(data.message || 'Found nearby shops! Redirecting...', 'success');
                
                // Redirect after a short delay
                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 1000);
            } else {
                showStatus(data.message || 'Failed to find nearby shops', 'danger');
                loadingState.style.display = 'none';
                allowBtn.style.display = 'block';
                if (dismissBtn) dismissBtn.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showStatus('An error occurred. Please try again.', 'danger');
            loadingState.style.display = 'none';
            allowBtn.style.display = 'block';
            if (dismissBtn) dismissBtn.style.display = 'block';
        });
    }
    
    function showStatus(message, type) {
        statusText.textContent = message;
        statusDiv.className = 'alert alert-' + type + ' small';
        statusDiv.style.display = 'block';
        
        // Auto-hide after 5 seconds for success messages
        if (type === 'success') {
            setTimeout(() => {
                statusDiv.style.display = 'none';
            }, 5000);
        }
    }
});
</script>
@endsection