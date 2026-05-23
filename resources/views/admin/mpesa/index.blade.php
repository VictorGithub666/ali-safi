@extends('layouts.admin')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">M-Pesa Transactions</h2>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="bi bi-download me-2"></i> Export
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
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
                            <p class="stat-label">Completed</p>
                            <p class="stat-value text-success">{{ $totalCompleted }}</p>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="stat-label">Pending</p>
                            <p class="stat-value text-warning">{{ $totalPending }}</p>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-clock"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="stat-label">Failed</p>
                            <p class="stat-value text-danger">{{ $totalFailed }}</p>
                        </div>
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-x-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control" placeholder="254..." value="{{ request('phone') }}">
            </div>

            <div class="col-md-2">
                <label class="form-label">From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>

            <div class="col-md-2">
                <label class="form-label">To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>

            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i> Search
                </button>
                <a href="{{ route('admin.mpesa.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Transactions Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h5 class="card-title fw-bold mb-0">Transactions</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Transaction ID</th>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Receipt</th>
                        <th>Initiated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>
                                <code class="text-muted small">{{ $transaction->id }}</code>
                            </td>
                            <td>
                                <a href="{{ route('admin.orders.show', $transaction->order_id) }}" class="text-decoration-none fw-bold">
                                    {{ $transaction->order->order_number ?? 'N/A' }}
                                </a>
                            </td>
                            <td>
                                <div class="text-truncate">
                                    {{ $transaction->order->customer->name ?? 'N/A' }}
                                </div>
                                <small class="text-muted">{{ $transaction->order->customer->email ?? '' }}</small>
                            </td>
                            <td>
                                <code>{{ $transaction->phone_number }}</code>
                            </td>
                            <td>
                                <strong>KES {{ number_format($transaction->amount, 2) }}</strong>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'completed' => 'success',
                                        'failed' => 'danger',
                                        'cancelled' => 'secondary',
                                    ];
                                @endphp
                                <span class="badge badge-{{ $statusColors[$transaction->status] ?? 'secondary' }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                            <td>
                                @if($transaction->mpesa_receipt_number)
                                    <code class="small">{{ $transaction->mpesa_receipt_number }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $transaction->initiated_at->format('M d, H:i') }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.mpesa.show', $transaction) }}" class="btn btn-outline-primary" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($transaction->status !== 'completed')
                                        <form method="POST" action="{{ route('admin.mpesa.confirm', $transaction) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Confirm Payment" onclick="return confirm('Confirm this payment?')">
                                                <i class="bi bi-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.5;"></i>
                                <p class="mt-3 mb-0">No M-Pesa transactions found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
@if($transactions->hasPages())
    <nav class="mt-4">
        {{ $transactions->links() }}
    </nav>
@endif

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export M-Pesa Transactions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="{{ route('admin.mpesa.export') }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">From Date</label>
                            <input type="date" name="date_from" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">To Date</label>
                            <input type="date" name="date_to" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-download me-1"></i> Export CSV
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .badge-success {
        background-color: #d4edda;
        color: #155724;
    }
    .badge-warning {
        background-color: #fff3cd;
        color: #856404;
    }
    .badge-danger {
        background-color: #f8d7da;
        color: #721c24;
    }
    .badge-secondary {
        background-color: #e2e3e5;
        color: #383d41;
    }
</style>
@endsection
