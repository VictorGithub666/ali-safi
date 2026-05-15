@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    <h1 class="mb-4"><i class="bi bi-graph-up"></i> Finance Dashboard</h1>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card"><div class="card-body text-center"><h5 class="text-muted">Total Orders Value</h5><h3 style="color:#05bb14;">KES {{ number_format($totalOrders, 2) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body text-center"><h5 class="text-muted">Total Profit</h5><h3 style="color:#05bb14;">KES {{ number_format($totalProfit, 2) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body text-center"><h5 class="text-muted">Profit Margin</h5><h3 style="color:#237bdd;">{{ round($profitMargin, 2) }}%</h3></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body text-center"><h5 class="text-muted">Total Orders</h5><h3>{{ $orderCount }}</h3></div></div></div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4"><div class="card"><div class="card-body"><h6 class="text-muted">Platform Commission</h6><h4>KES {{ number_format($platformCommission, 2) }}</h4></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><h6 class="text-muted">Delivery Fees</h6><h4>KES {{ number_format($deliveryFees, 2) }}</h4></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><h6 class="text-muted">Rider Fees Paid</h6><h4>KES {{ number_format($riderFees, 2) }}</h4></div></div></div>
    </div>

    <div class="card">
        <div class="card-header"><h5>Recent Transactions</h5></div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Order ID</th><th>Vendor</th><th>Order Total</th><th>Commission</th><th>Delivery Fee</th><th>Rider Fee</th><th>Profit</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trans)
                        <tr>
                            <td>#{{ $trans->order->id ?? 'N/A' }}</td>
                            <td>{{ $trans->vendor->business_name ?? 'N/A' }}</td>
                            <td>KES {{ number_format($trans->order_subtotal, 2) }}</td>
                            <td>KES {{ number_format($trans->platform_commission, 2) }}</td>
                            <td>KES {{ number_format($trans->delivery_fee, 2) }}</td>
                            <td>KES {{ number_format($trans->rider_fee, 2) }}</td>
                            <td><strong style="color:#05bb14;">KES {{ number_format($trans->admin_profit, 2) }}</strong></td>
                            <td><span class="badge bg-info">{{ ucfirst($trans->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4">No transactions</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-center mt-4">{{ $transactions->links() }}</div>
</div>
@endsection