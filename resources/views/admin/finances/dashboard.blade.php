@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-graph-up"></i> Finance Dashboard</h1>
        <a href="{{ route('admin.finances.sync') }}" class="btn btn-warning" onclick="return confirm('Sync all past orders?')">
            <i class="bi bi-arrow-repeat"></i> Sync Orders
        </a>
    </div>

    <!-- Status Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Settled Transactions</h6>
                    <h3 class="fw-bold text-success">{{ $settledCount ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Pending Transactions</h6>
                    <h3 class="fw-bold text-warning">{{ $pendingCount ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Cancelled Transactions</h6>
                    <h3 class="fw-bold text-danger">{{ $cancelledCount ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="text-muted">Total Orders Value</h5>
                    <h3 style="color:#05bb14;">KES {{ number_format($totalOrders, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="text-muted">Total Profit</h5>
                    <h3 style="color:#05bb14;">KES {{ number_format($totalProfit, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="text-muted">Profit Margin</h5>
                    <h3 style="color:#237bdd;">{{ round($profitMargin, 2) }}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="text-muted">Total Orders</h5>
                    <h3>{{ $orderCount }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Platform Commission</h6>
                    <h4>KES {{ number_format($platformCommission, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Delivery Fees Collected</h6>
                    <h4>KES {{ number_format($deliveryFees, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Rider Fees Paid</h6>
                    <h4>KES {{ number_format($riderFees, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <a href="{{ route('admin.finances.dashboard') }}" class="btn btn-secondary w-100">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
                <button onclick="window.print()" class="btn btn-secondary">
                    <i class="bi bi-printer"></i> Print Report
                </button>


            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Recent Transactions</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Order ID</th>
                        <th>Order #</th>
                        <th>Vendor</th>
                        <th>Order Total</th>
                        <th>Commission</th>
                        <th>Delivery Fee</th>
                        <th>Rider Fee</th>
                        <th>Profit</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trans)
                        <tr>
                            <td>{{ $trans->created_at->setTimezone('Africa/Nairobi')->format('Y-m-d H:i') }}</td>
                            <td>#{{ $trans->order->id ?? 'N/A' }}</td>
                            <td><strong>{{ $trans->order->order_number ?? 'N/A' }}</strong></td>
                            <td>{{ $trans->vendor->business_name ?? 'N/A' }}</td>
                            <td>KES {{ number_format($trans->order_subtotal, 2) }}</td>
                            <td>KES {{ number_format($trans->platform_commission, 2) }}</td>
                            <td>KES {{ number_format($trans->delivery_fee, 2) }}</td>
                            <td>KES {{ number_format($trans->rider_fee, 2) }}</td>
                            <td><strong class="text-success">KES {{ number_format($trans->admin_profit, 2) }}</strong></td>
                            <td>
                                @if($trans->status === 'settled')
                                    <span class="badge bg-success">Settled</span>
                                @elseif($trans->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @else
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                <p class="mt-2 text-muted">No transactions found. Click "Sync Orders" to import past orders.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-center mt-4">{{ $transactions->links() }}</div>
</div>
@endsection