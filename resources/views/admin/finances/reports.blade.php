@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between mb-4">
        <h1><i class="bi bi-file-earmark-pdf"></i> Finance Reports</h1>
        <a href="{{ route('admin.finances.download-report', request()->query()) }}" class="btn btn-success"><i class="bi bi-download"></i> Download CSV</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <select name="vendor_id" class="form-select">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $id => $name)<option value="{{ $id }}" {{ request('vendor_id') == $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="settled" {{ request('status') === 'settled' ? 'selected' : '' }}>Settled</option>
                    </select>
                </div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Date</th><th>Order ID</th><th>Vendor</th><th>Order Total</th><th>Commission</th><th>Delivery</th><th>Rider</th><th>Profit</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trans)
                        <tr>
                            <td>{{ $trans->created_at->format('M d, Y') }}</td>
                            <td>#{{ $trans->order->id ?? 'N/A' }}</td>
                            <td>{{ $trans->vendor->business_name ?? 'N/A' }}</td>
                            <td>KES {{ number_format($trans->order_subtotal, 2) }}</td>
                            <td>KES {{ number_format($trans->platform_commission, 2) }}</td>
                            <td>KES {{ number_format($trans->delivery_fee, 2) }}</td>
                            <td>KES {{ number_format($trans->rider_fee, 2) }}</td>
                            <td><strong>KES {{ number_format($trans->admin_profit, 2) }}</strong></td>
                            <td><span class="badge bg-info">{{ ucfirst($trans->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-4">No records</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-center mt-4">{{ $transactions->links() }}</div>
</div>
@endsection