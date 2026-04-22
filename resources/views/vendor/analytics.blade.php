@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-pie-chart"></i> Analytics
            </h2>
            <p class="text-muted mb-0">Detailed insights into your business performance</p>
        </div>
        <a href="{{ route('vendor.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-house"></i> Dashboard
        </a>
    </div>

    <!-- Summary Stats -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #05bb14;">
                        <i class="bi bi-box"></i>
                    </div>
                    <h6 class="text-muted mb-2">Total Orders</h6>
                    <h3 class="fw-bold mb-0">{{ $stats['total_orders'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #237bdd;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h6 class="text-muted mb-2">Completed Orders</h6>
                    <h3 class="fw-bold mb-0">{{ $stats['completed_orders'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #ffc107;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h6 class="text-muted mb-2">Pending Orders</h6>
                    <h3 class="fw-bold mb-0">{{ $stats['pending_orders'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #28a745;">
                        <i class="bi bi-cash"></i>
                    </div>
                    <h6 class="text-muted mb-2">Total Revenue</h6>
                    <h3 class="fw-bold mb-0">KES {{ number_format($stats['total_revenue'] ?? 0, 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" 
                           value="{{ $dateFrom ?? now()->subDays(30)->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" 
                           value="{{ $dateTo ?? now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Apply Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Top Products -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-trophy"></i> Top Products
                    </h6>
                </div>
                <div class="card-body">
                    @if($topProducts && $topProducts->count() > 0)
                        <div class="list-group">
                            @foreach($topProducts as $product)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $product->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $product->orders_count }} orders</small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">
                                        {{ $product->orders_count }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-4 mb-0">No product data available</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Orders by Status -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-diagram-3"></i> Orders by Status
                    </h6>
                </div>
                <div class="card-body">
                    @if($ordersByStatus && $ordersByStatus->count() > 0)
                        <canvas id="ordersByStatusChart" height="200"></canvas>
                    @else
                        <p class="text-muted text-center py-4 mb-0">No order data available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Trend -->
    <div class="card">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <i class="bi bi-graph-up"></i> Revenue Trend
            </h6>
        </div>
        <div class="card-body">
            @if($revenueTrend && $revenueTrend->count() > 0)
                <canvas id="revenueTrendChart" height="100"></canvas>
            @else
                <p class="text-muted text-center py-4 mb-0">No revenue data available</p>
            @endif
        </div>
    </div>
</div>

@push('scripts')
@if(($ordersByStatus && $ordersByStatus->count() > 0) || ($revenueTrend && $revenueTrend->count() > 0))
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Orders by Status Chart
    @if($ordersByStatus && $ordersByStatus->count() > 0)
    const statusCtx = document.getElementById('ordersByStatusChart').getContext('2d');
    const statusData = @json($ordersByStatus);
    
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusData.map(item => item.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())),
            datasets: [{
                data: statusData.map(item => item.count),
                backgroundColor: [
                    '#05bb14', '#237bdd', '#ffc107', '#dc3545', 
                    '#6c757d', '#17a2b8', '#28a745'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });
    @endif

    // Revenue Trend Chart
    @if($revenueTrend && $revenueTrend->count() > 0)
    const revenueCtx = document.getElementById('revenueTrendChart').getContext('2d');
    const revenueData = @json($revenueTrend);
    
    new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: revenueData.map(item => {
                const date = new Date(item.date);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            }),
            datasets: [{
                label: 'Revenue (KES)',
                data: revenueData.map(item => item.revenue),
                backgroundColor: 'rgba(5, 187, 20, 0.7)',
                borderColor: '#05bb14',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'KES ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    @endif
});
</script>
@endif
@endpush

@endsection