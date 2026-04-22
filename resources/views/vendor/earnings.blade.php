@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-graph-up"></i> Earnings
            </h2>
            <p class="text-muted mb-0">Track your revenue and sales performance</p>
        </div>
        <a href="{{ route('vendor.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-house"></i> Dashboard
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #05bb14;">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <h6 class="text-muted mb-2">Total Earnings</h6>
                    <h3 class="fw-bold mb-0">KES {{ number_format($totalEarnings ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #237bdd;">
                        <i class="bi bi-box"></i>
                    </div>
                    <h6 class="text-muted mb-2">Total Orders</h6>
                    <h3 class="fw-bold mb-0">{{ $totalOrders ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #28a745;">
                        <i class="bi bi-calculator"></i>
                    </div>
                    <h6 class="text-muted mb-2">Average Order Value</h6>
                    <h3 class="fw-bold mb-0">
                        KES {{ $totalOrders > 0 ? number_format($totalEarnings / $totalOrders, 2) : '0.00' }}
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="{{ $dateFrom ?? now()->subDays(30)->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
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

    <!-- Earnings Chart -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <i class="bi bi-bar-chart"></i> Earnings Trend
            </h6>
        </div>
        <div class="card-body">
            @if($earnings && $earnings->count() > 0)
                <canvas id="earningsChart" height="100"></canvas>
            @else
                <p class="text-muted text-center py-5 mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    No earnings data available for the selected period.
                </p>
            @endif
        </div>
    </div>

    <!-- Earnings Table -->
    <div class="card">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <i class="bi bi-table"></i> Daily Earnings Breakdown
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Earnings</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($earnings ?? [] as $earning)
                        <tr>
                            <td>
                                <strong>{{ \Carbon\Carbon::parse($earning->date)->format('M d, Y') }}</strong>
                                <br>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($earning->date)->format('l') }}</small>
                            </td>
                            <td>
                                <strong class="text-success">KES {{ number_format($earning->total, 2) }}</strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center py-4 text-muted">
                                <i class="bi bi-info-circle me-2"></i>
                                No earnings data available for the selected period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($earnings && $earnings->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th>Total</th>
                            <th class="text-success">KES {{ number_format($totalEarnings, 2) }}</th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

@push('scripts')
@if($earnings && $earnings->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('earningsChart').getContext('2d');
    
    const earningsData = @json($earnings);
    
    const labels = earningsData.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
    
    const data = earningsData.map(item => parseFloat(item.total));
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Daily Earnings (KES)',
                data: data,
                borderColor: '#05bb14',
                backgroundColor: 'rgba(5, 187, 20, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#05bb14',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'KES ' + context.parsed.y.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                }
            },
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
});
</script>
@endif
@endpush

@endsection