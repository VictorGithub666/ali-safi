@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between mb-4">
        <h1><i class="bi bi-file-earmark-text"></i> Finance Reports</h1>
        <div>
            {{-- Download buttons with current filters --}}
            <a href="{{ route('admin.finances.download-report', array_merge(request()->query(), ['format' => 'csv'])) }}" class="btn btn-success me-2">
                <i class="bi bi-download"></i> Download CSV
            </a>
            <a href="{{ route('admin.finances.download-report', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="btn btn-danger">
                <i class="bi bi-file-pdf"></i> Download PDF
            </a>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Order # or Vendor..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vendor</label>
                    <select name="vendor_id" class="form-select">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $id => $name)
                            <option value="{{ $id }}" {{ request('vendor_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="settled" {{ request('status') === 'settled' ? 'selected' : '' }}>Settled</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('admin.finances.reports') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Rest of your view remains the same -->
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Total Orders Value</h6>
                    <h4>KES {{ number_format($transactions->sum('order_subtotal'), 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Total Commission</h6>
                    <h4>KES {{ number_format($transactions->sum('platform_commission'), 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Total Profit</h6>
                    <h4 class="text-success">KES {{ number_format($transactions->sum('admin_profit'), 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Transactions</h6>
                    <h4>{{ $transactions->total() }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card">
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
                        <th>Delivery</th>
                        <th>Rider Fee</th>
                        <th>Profit</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trans)
                        <tr>
                            <td>{{ $trans->created_at->format('M d, Y H:i') }}</td>
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
                                <p class="mt-2 text-muted">No records found</p>
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