@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    <h1 class="mb-4"><i class="bi bi-percent"></i> Profit Margins Analysis</h1>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5 class="text-muted">Total Orders Value</h5><h3>KES {{ number_format($totalOrders, 2) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5 class="text-muted">Total Profit</h5><h3 style="color:#05bb14;">KES {{ number_format($totalProfit, 2) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5 class="text-muted">Avg Margin</h5><h3>{{ round($avgMargin, 2) }}%</h3></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5 class="text-muted">Transactions</h5><h3>{{ $transactionCount }}</h3></div></div></div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Vendor</th><th>Total Orders Value</th><th>Platform Commission</th><th>Margin %</th><th>Transactions</th></tr>
                </thead>
                <tbody>
                    @forelse($vendors as $vendor)
                        <tr>
                            <td><strong>{{ $vendor->business_name }}</strong></td>
                            <td>KES {{ number_format($marginData[$vendor->id]['orders_value'], 2) }}</td>
                            <td>KES {{ number_format($marginData[$vendor->id]['profit'], 2) }}</td>
                            <td><span class="badge bg-info">{{ round($marginData[$vendor->id]['margin'], 2) }}%</span></td>
                            <td>{{ $marginData[$vendor->id]['count'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4">No data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection