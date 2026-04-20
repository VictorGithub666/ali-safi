<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Ali-Safi') }} - {{ $pageTitle ?? 'Marketplace' }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

        <!-- Custom CSS -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --primary-green: #05bb14;
                --primary-blue: #237bdd;
                --light-gray: #f8f9fa;
            }

            body {
                font-family: 'Poppins', sans-serif;
                background-color: #fafafa;
            }

            .navbar {
                background-color: #fff;
                border-bottom: 1px solid #e9ecef;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }

            .navbar-brand {
                font-weight: 700;
                color: var(--primary-green) !important;
                font-size: 1.5rem;
            }

            .nav-link {
                color: #495057 !important;
                transition: all 0.3s ease;
            }

            .nav-link:hover {
                color: var(--primary-green) !important;
            }

            .nav-link.active {
                color: var(--primary-green) !important;
                font-weight: 600;
            }

            .btn-primary {
                background-color: var(--primary-green);
                border-color: var(--primary-green);
            }

            .btn-primary:hover {
                background-color: #048a0f;
                border-color: #048a0f;
            }

            .btn-secondary {
                background-color: var(--primary-blue);
                border-color: var(--primary-blue);
            }

            .btn-secondary:hover {
                background-color: #1a59a8;
                border-color: #1a59a8;
            }

            .footer {
                background-color: #2c3e50;
                color: #ecf0f1;
                padding: 2rem 0;
                margin-top: 3rem;
            }

            .footer a {
                color: #ecf0f1;
                text-decoration: none;
            }

            .footer a:hover {
                color: var(--primary-green);
            }

            .card {
                border: none;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                transition: transform 0.3s, box-shadow 0.3s;
            }

            .card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            }

            .alert {
                border: none;
            }

            .alert-success {
                background-color: rgba(5, 187, 20, 0.1);
                color: #048a0f;
            }

            .alert-danger {
                background-color: rgba(220, 53, 69, 0.1);
                color: #c82333;
            }

            .form-control:focus {
                border-color: var(--primary-green);
                box-shadow: 0 0 0 0.2rem rgba(5, 187, 20, 0.25);
            }

            .badge-success {
                background-color: var(--primary-green);
            }

            .badge-info {
                background-color: var(--primary-blue);
            }
        </style>
    </head>
    <body>
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light sticky-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ route('home') }}">
                    <img style="display: inline; height:75px;" src="/storage/logo-100.png" alt=""> Ali-Safi
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        @auth
                            @if(Auth::user()->user_type === 'customer')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('customer.products.index') }}"><i class="bi bi-shop"></i> Products</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link position-relative" href="{{ route('customer.cart') }}">
                                        <i class="bi bi-cart"></i> Cart
                                        @php
                                            $cartCount = auth()->user()->cart()->count();
                                        @endphp
                                        @if($cartCount > 0)
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" style="background-color: var(--primary-green); font-size: 0.7rem;">
                                                {{ $cartCount }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('customer.orders') }}"><i class="bi bi-box"></i> Orders</a>
                                </li>
                            @elseif(Auth::user()->user_type === 'vendor')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('vendor.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('vendor.orders') }}"><i class="bi bi-box"></i> Orders</a>
                                </li>
                            @elseif(Auth::user()->user_type === 'rider')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('rider.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('rider.deliveries') }}"><i class="bi bi-truck"></i> Deliveries</a>
                                </li>
                            @elseif(Auth::user()->user_type === 'admin')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.orders') }}"><i class="bi bi-box"></i> Orders</a>
                                </li>
                            @endif
                            
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> Profile</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Logout</button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">Register</a>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Alerts -->
        <div class="container-fluid" style="margin-top: 1rem;">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="bi bi-exclamation-circle"></i> Errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>

        <!-- Main Content -->
        <main class="min-vh-100">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <h6><i class="bi bi-droplet-fill"></i> Ali-Safi</h6>
                        <p class="small mb-0">Your trusted gas and water delivery platform</p>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <h6>Quick Links</h6>
                        <ul class="list-unstyled small">
                            <li><a href="{{ route('home') }}">Home</a></li>
                            <li><a href="#">About</a></li>
                            <li><a href="#">Contact</a></li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6>Support</h6>
                        <p class="small mb-0">
                            📧 support@ali-safi.com<br>
                            📱 +254 700 000 000
                        </p>
                    </div>
                </div>
                <hr style="border-color: rgba(255,255,255,0.1); margin: 1rem 0;">
                <div class="text-center small">
                    <p class="mb-0">&copy; 2026 Ali-Safi. All rights reserved.</p>
                </div>
            </div>
        </footer>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Password Toggle Script -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Handle password visibility toggle
                const toggleButtons = document.querySelectorAll('.toggle-password');
                
                toggleButtons.forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
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
            });
        </script>
    </body>
</html>
