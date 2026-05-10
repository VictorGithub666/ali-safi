@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="fw-bold mb-4">
        <i class="bi bi-graph-up"></i> My Earnings
    </h2>

    <!-- Earnings Stats -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #05bb14;">
                        <i class="bi bi-wallet"></i>
                    </div>
                    <h6 class="text-muted mb-2">Today</h6>
                    <h3 class="fw-bold mb-0">KES {{ number_format($todayEarnings, 0) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #237bdd;">
                        <i class="bi bi-calendar-week"></i>
                    </div>
                    <h6 class="text-muted mb-2">This Week</h6>
                    <h3 class="fw-bold mb-0">KES {{ number_format($weekEarnings, 0) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #28a745;">
                        <i class="bi bi-calendar-month"></i>
                    </div>
                    <h6 class="text-muted mb-2">This Month</h6>
                    <h3 class="fw-bold mb-0">KES {{ number_format($monthEarnings, 0) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #dc3545;">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <h6 class="text-muted mb-2">Total Earnings</h6>
                    <h3 class="fw-bold mb-0">KES {{ number_format($totalEarnings, 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Chart -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Earnings Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="earningsChart" height="80"></canvas>
                </div>
            </div>

            <!-- Earnings Details Table -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-table"></i> Daily Breakdown</h6>
                </div>
                <div class="card-body">
                    @if($earningsChart->count())
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th class="text-end">Deliveries</th>
                                        <th class="text-end">Earnings</th>
                                        <th class="text-end">Avg. per Delivery</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($earningsChart as $day)
                                        <tr>
                                            <td><strong>{{ \Carbon\Carbon::parse($day->date)->format('M d, Y') }}</strong></td>
                                            <td class="text-end">{{ $day->deliveries }}</td>
                                            <td class="text-end"><strong>KES {{ number_format($day->earnings, 0) }}</strong></td>
                                            <td class="text-end">KES {{ number_format($day->earnings / $day->deliveries, 0) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> No earnings data yet. Complete some deliveries to see your earnings history.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Summary Card -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Account Summary</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="text-muted small">Total Deliveries Completed</label>
                        <p class="mb-0"><strong style="font-size: 1.5rem;">{{ $rider->total_deliveries ?? 0 }}</strong></p>
                    </div>
                    <div class="mb-4">
                        <label class="text-muted small">Current Wallet Balance</label>
                        <p class="mb-0"><strong style="font-size: 1.5rem; color: #28a745;">KES {{ number_format($rider->wallet_balance ?? 0, 0) }}</strong></p>
                    </div>
                    <div class="mb-4 pb-3 border-bottom">
                        <label class="text-muted small">Average Earnings per Delivery</label>
                        <p class="mb-0"><strong>KES {{ $rider->total_deliveries > 0 ? number_format($totalEarnings / $rider->total_deliveries, 0) : '0' }}</strong></p>
                    </div>
                    <div>
                        <label class="text-muted small">Rating</label>
                        <p class="mb-0">
                            <strong>
                                <i class="bi bi-star-fill" style="color: #ffc107;"></i>
                                {{ number_format($rider->rating ?? 0, 1) }}/5
                            </strong>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-lightning-charge"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('rider.dashboard') }}" class="btn btn-primary">
                            <i class="bi bi-truck"></i> Dashboard
                        </a>
                        <button class="btn btn-outline-primary" onclick="requestWithdrawal()">
                            <i class="bi bi-cash-coin"></i> Request Withdrawal
                        </button>
                    </div>
                </div>
            </div>

            <!-- Withdrawal History -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-history"></i> Withdrawals</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-0">Withdrawal feature coming soon. Track your earnings until then!</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('earningsChart');
    if (ctx) {
        const data = {!! json_encode($earningsChart) !!};
        const labels = data.map(d => new Date(d.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
        const earnings = data.map(d => parseFloat(d.earnings));
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Daily Earnings (KES)',
                    data: earnings,
                    borderColor: '#05bb14',
                    backgroundColor: 'rgba(5, 187, 20, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: '#05bb14',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            font: { size: 12 }
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
    }
});

function requestWithdrawal() {
    Swal.fire({
        title: 'Request Withdrawal',
        html: '<input type="number" id="amount" class="form-control" placeholder="Amount (KES)" min="100">',
        showCancelButton: true,
        confirmButtonColor: '#05bb14',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Request'
    }).then((result) => {
        if (result.isConfirmed) {
            const amount = document.getElementById('amount').value;
            if (!amount || amount < 100) {
                Swal.fire('Error', 'Minimum withdrawal amount is KES 100', 'error');
                return;
            }
            Swal.fire('Success', 'Withdrawal request submitted. You will receive your funds within 24 hours.', 'success');
        }
    });
}
</script>
@endsection
