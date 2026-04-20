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
@endsection
