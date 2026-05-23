@extends('layouts.admin')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold mb-4">M-Pesa Dashboard</h2>

        <!-- Date Range Filter -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter me-1"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Key Statistics -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-label">Total Revenue</p>
                    <p class="stat-value text-success">KES {{ number_format($totalRevenue, 2) }}</p>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-cash-flow"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-label">Total Transactions</p>
                    <p class="stat-value">{{ $totalTransactions }}</p>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-cash-coin"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-label">Completion Rate</p>
                    <p class="stat-value text-info">{{ number_format($completionRate, 1) }}%</p>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-percent"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-label">Completed</p>
                    <p class="stat-value text-success">{{ $completedTransactions }}</p>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Breakdown Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Pending</p>
                        <h4 class="mb-0 text-warning">{{ $pendingTransactions }}</h4>
                    </div>
                    <i class="bi bi-clock text-warning" style="font-size: 1.5rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Failed</p>
                        <h4 class="mb-0 text-danger">{{ $failedTransactions }}</h4>
                    </div>
                    <i class="bi bi-x-circle text-danger" style="font-size: 1.5rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Cancelled</p>
                        <h4 class="mb-0 text-secondary">{{ $cancellledTransactions }}</h4>
                    </div>
                    <i class="bi bi-dash-circle text-secondary" style="font-size: 1.5rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <a href="{{ route('admin.mpesa.index') }}" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-eye me-1"></i> View All Transactions
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Daily Revenue Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title fw-bold mb-0">Daily Revenue</h5>
            </div>
            <div class="card-body">
                <canvas id="dailyRevenueChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Status Distribution -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title fw-bold mb-0">Status Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top Phones Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h5 class="card-title fw-bold mb-0">Top Performing Phone Numbers</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Phone Number</th>
                        <th>Transaction Count</th>
                        <th>Total Amount</th>
                        <th>Average per Transaction</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topPhones as $phone)
                        <tr>
                            <td><code>{{ $phone->phone_number }}</code></td>
                            <td><strong>{{ $phone->count }}</strong></td>
                            <td>KES {{ number_format($phone->total, 2) }}</td>
                            <td>KES {{ number_format($phone->total / $phone->count, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.5;"></i>
                                <p class="mt-3 mb-0">No data available</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Daily Revenue Chart
    const dailyCtx = document.getElementById('dailyRevenueChart').getContext('2d');
    const dailyData = {
        labels: {!! json_encode($dailyData->pluck('date')->map(fn($date) => date('M d', strtotime($date)))) !!},
        datasets: [{
            label: 'Revenue (KES)',
            data: {!! json_encode($dailyData->pluck('revenue')) !!},
            borderColor: '#05bb14',
            backgroundColor: 'rgba(5, 187, 20, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    };

    new Chart(dailyCtx, {
        type: 'line',
        data: dailyData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusData = {
        labels: {!! json_encode($statusBreakdown->pluck('status')->map('ucfirst')) !!},
        datasets: [{
            data: {!! json_encode($statusBreakdown->pluck('count')) !!},
            backgroundColor: [
                'rgba(5, 187, 20, 0.8)',  // completed
                'rgba(255, 193, 7, 0.8)', // pending
                'rgba(244, 67, 54, 0.8)', // failed
                'rgba(158, 158, 158, 0.8)' // cancelled
            ],
            borderColor: [
                '#05bb14',
                '#ffc107',
                '#f44336',
                '#9e9e9e'
            ],
            borderWidth: 2
        }]
    };

    new Chart(statusCtx, {
        type: 'doughnut',
        data: statusData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>

<style>
    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .stat-value {
        font-size: 1.8rem;
        font-weight: 700;
        margin: 10px 0 5px;
    }

    .stat-label {
        color: #666;
        font-size: 0.9rem;
    }
</style>
@endsection
