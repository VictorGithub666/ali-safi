@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    <h1 class="mb-4"><i class="bi bi-credit-card"></i> Vendor Settlement</h1>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Vendor</th><th>Total Orders</th><th>Orders Value</th><th>Commission</th><th>Payout Due</th><th>Pending</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($vendors as $vendor)
                        <tr>
                            <td><strong>{{ $vendor->business_name }}</strong><br><small class="text-muted">{{ $vendor->user->email }}</small></td>
                            <td>{{ $vendor->orders_count }}</td>
                            <td>KES {{ number_format($settlementData[$vendor->id]['total_orders'], 2) }}</td>
                            <td>KES {{ number_format($settlementData[$vendor->id]['commission'], 2) }}</td>
                            <td><strong>KES {{ number_format($settlementData[$vendor->id]['payout'], 2) }}</strong></td>
                            <td><span class="badge bg-warning">{{ $settlementData[$vendor->id]['pending_orders'] }}</span></td>
                            <td>@if($settlementData[$vendor->id]['pending_orders'] > 0)<span class="badge bg-info">Pending</span>@else<span class="badge bg-success">Ready</span>@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4">No vendors</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-center mt-4">{{ $vendors->links() }}</div>
</div>
@endsection