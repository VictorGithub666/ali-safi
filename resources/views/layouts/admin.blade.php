<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Panel - {{ config('app.name', 'Ali-Safi') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fb;
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 280px;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .sidebar.collapsed {
            width: 80px;
        }
        
        .sidebar.collapsed .sidebar-text,
        .sidebar.collapsed .nav-label {
            display: none;
        }
        
        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 12px;
        }
        
        .sidebar.collapsed .nav-link i {
            margin-right: 0;
            font-size: 1.25rem;
        }
        
        .sidebar.collapsed .logo-text {
            display: none;
        }
        
        .sidebar.collapsed .logo-icon {
            display: block !important;
        }
        
        .sidebar .logo {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar .logo h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .sidebar .logo .logo-icon {
            display: none;
            font-size: 1.8rem;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 10px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, #05bb14, #0a9a12);
            color: white;
        }
        
        .sidebar .nav-link i {
            font-size: 1.2rem;
            width: 24px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            transition: all 0.3s ease;
            min-height: 100vh;
        }
        
        .main-content.expanded {
            margin-left: 80px;
        }
        
        /* Top Navbar */
        .top-navbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .toggle-sidebar {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #333;
        }
        
        /* Cards */
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-card .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 10px 0 5px;
        }
        
        .stat-card .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Tables */
        .data-table {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, #05bb14, #0a9a12);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0a9a12, #058010);
        }
        
        /* Badges */
        .badge-success {
            background: #d4edda;
            color: #155724;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
        }
        
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
        }
        
        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .main-content.expanded {
                margin-left: 0;
            }
        }
        
        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease;
        }
        
        /* Modal */
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        /* Form Controls */
        .form-control, .form-select {
            border-radius: 10px;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #05bb14;
            box-shadow: 0 0 0 0.2rem rgba(5, 187, 20, 0.25);
        }
        
        /* Chart Containers */
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
    </style>
    
    @stack('styles')
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="logo">
        <h3 class="logo-text">
            <i class="bi bi-shop"></i> Ali-Safi
        </h3>
        <div class="logo-icon">
            <i class="bi bi-shop"></i>
        </div>
    </div>
    
    <nav class="nav flex-column mt-3">
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span class="sidebar-text">Dashboard</span>
        </a>
        
        <div class="nav-label px-3 mt-3 mb-2">
            <small class="text-white-50 sidebar-text">MANAGEMENT</small>
        </div>
        
        <a href="{{ route('admin.customers.index') }}" class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i>
            <span class="sidebar-text">Customers</span>
        </a>
        
        <a href="{{ route('admin.vendors.index') }}" class="nav-link {{ request()->routeIs('admin.vendors.*') ? 'active' : '' }}">
            <i class="bi bi-shop"></i>
            <span class="sidebar-text">Vendors</span>
        </a>
        
        <a href="{{ route('admin.riders.index') }}" class="nav-link {{ request()->routeIs('admin.riders.*') ? 'active' : '' }}">
            <i class="bi bi-truck"></i>
            <span class="sidebar-text">Riders</span>
        </a>
        
        <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders.*') && !request()->routeIs('admin.orders.assignment') ? 'active' : '' }}">
            <i class="bi bi-cart-check"></i>
            <span class="sidebar-text">Orders</span>
        </a>
        
        <div class="nav-label px-3 mt-3 mb-2">
            <small class="text-white-50 sidebar-text">PRICING</small>
        </div>
        
        <a href="{{ route('admin.prices.index') }}" class="nav-link {{ request()->routeIs('admin.prices.*') ? 'active' : '' }}">
            <i class="bi bi-currency-dollar"></i>
            <span class="sidebar-text">Product Pricing</span>
        </a>
        
        <div class="nav-label px-3 mt-3 mb-2">
            <small class="text-white-50 sidebar-text">DELIVERY</small>
        </div>
        
        <a href="{{ route('admin.orders.assignment') }}" class="nav-link {{ request()->routeIs('admin.orders.assignment') ? 'active' : '' }}">
            <i class="bi bi-person-check"></i>
            <span class="sidebar-text">Assign Riders</span>
        </a>
        
        <div class="nav-label px-3 mt-3 mb-2">
            <small class="text-white-50 sidebar-text">FINANCE</small>
        </div>
        
        <a href="{{ route('admin.finances.dashboard') }}" class="nav-link {{ request()->routeIs('admin.finances.dashboard') ? 'active' : '' }}">
            <i class="bi bi-graph-up"></i>
            <span class="sidebar-text">Overview</span>
        </a>
        
        <a href="{{ route('admin.finances.margins') }}" class="nav-link {{ request()->routeIs('admin.finances.margins') ? 'active' : '' }}">
            <i class="bi bi-pie-chart"></i>
            <span class="sidebar-text">Profit Margins</span>
        </a>
        
        <a href="{{ route('admin.finances.reports') }}" class="nav-link {{ request()->routeIs('admin.finances.reports') ? 'active' : '' }}">
            <i class="bi bi-file-text"></i>
            <span class="sidebar-text">Reports</span>
        </a>
        
        <a href="{{ route('admin.finances.vendor-settlement') }}" class="nav-link {{ request()->routeIs('admin.finances.vendor-settlement') ? 'active' : '' }}">
            <i class="bi bi-calculator"></i>
            <span class="sidebar-text">Vendor Settlement</span>
        </a>
        
        <div class="nav-label px-3 mt-3 mb-2">
            <small class="text-white-50 sidebar-text">PAYMENTS</small>
        </div>
        
        <a href="{{ route('admin.mpesa.dashboard') }}" class="nav-link {{ request()->routeIs('admin.mpesa.dashboard') ? 'active' : '' }}">
            <i class="bi bi-cash-coin"></i>
            <span class="sidebar-text">M-Pesa Dashboard</span>
        </a>
        
        <a href="{{ route('admin.mpesa.index') }}" class="nav-link {{ request()->routeIs('admin.mpesa.index') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i>
            <span class="sidebar-text">Transactions</span>
        </a>
        
        <a href="{{ route('admin.mpesa.notifications') }}" class="nav-link {{ request()->routeIs('admin.mpesa.notifications') ? 'active' : '' }}">
            <i class="bi bi-bell"></i>
            <span class="sidebar-text">Notifications</span>
        </a>
        
        <div class="nav-label px-3 mt-3 mb-2">
            <small class="text-white-50 sidebar-text">SYSTEM</small>
        </div>
        
        <a href="{{ route('admin.settings') }}" class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
            <i class="bi bi-gear"></i>
            <span class="sidebar-text">Settings</span>
        </a>
        
        <hr class="my-3 mx-3 bg-white-50">
        
        <a href="{{ route('home') }}" class="nav-link">
            <i class="bi bi-box-arrow-left"></i>
            <span class="sidebar-text">Back to Site</span>
        </a>
        
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="nav-link w-100 text-start" style="background: none; border: none;">
                <i class="bi bi-power"></i>
                <span class="sidebar-text">Logout</span>
            </button>
        </form>
    </nav>
</div>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Top Navbar -->
    <div class="top-navbar">
        <button class="toggle-sidebar" id="toggleSidebar">
            <i class="bi bi-list"></i>
        </button>
        
        <div class="d-flex align-items-center gap-3">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px;">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <span class="ms-2 d-none d-md-block">{{ Auth::user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i> Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Page Content -->
    <div class="container-fluid p-4 fade-in">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @yield('content')
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Sidebar Toggle
    document.getElementById('toggleSidebar').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('collapsed');
        document.getElementById('mainContent').classList.toggle('expanded');
    });
    
    // Initialize Select2
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
        
        // DataTables
        $('.datatable').DataTable({
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search..."
            }
        });
    });
    
    // SweetAlert for delete confirmations
    function confirmDelete(url, name) {
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete ${name}. This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }
</script>

@stack('scripts')
</body>
</html>