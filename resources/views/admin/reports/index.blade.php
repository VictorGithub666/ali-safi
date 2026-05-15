@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h1 class="mb-4"><i class="bi bi-file-text"></i> Reports</h1>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Report Type</label>
                    <select name="type" class="form-select">
                        <option value="orders" {{ request('type') == 'orders' ? 'selected' : '' }}>Orders Report</option>
                        <option value="vendors" {{ request('type') == 'vendors' ? 'selected' : '' }}>Vendors Report</option>
                        <option value="riders" {{ request('type') == 'riders' ? 'selected' : '' }}>Riders Report</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from', $dateFrom) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to', $dateTo) }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ ucfirst($reportType) }} Report</h5>
            <span class="text-muted small">{{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    @if($reportType == 'orders')
                    <tr>
                        <th>Date</th>
                        <th>Total Orders</th>
                        <th>Completed</th>
                        <th>Cancelled</th>
                        <th>Revenue (KES)</th>
                        <th>Platform Revenue (KES)</th>
                    </tr>
                    @elseif($reportType == 'vendors')
                    <tr>
                        <th>Vendor</th>
                        <th>Email</th>
                        <th>Orders</th>
                        <th>Total Revenue (KES)</th>
                    </tr>
                    @elseif($reportType == 'riders')
                    <tr>
                        <th>Rider</th>
                        <th>Email</th>
                        <th>Deliveries</th>
                        <th>Total Earnings (KES)</th>
                    </tr>
                    @endif
                </thead>
                <tbody>
                    @forelse($data as $item)
                    <tr>
                        @if($reportType == 'orders')
                            <td>{{ \Carbon\Carbon::parse($item->date)->format('M d, Y') }}</td>
                            <td>{{ $item->total_orders }}</td>
                            <td>{{ $item->completed_orders }}</td>
                            <td>{{ $item->cancelled_orders }}</td>
                            <td>KES {{ number_format($item->total_revenue, 2) }}</td>
                            <td>KES {{ number_format($item->platform_revenue, 2) }}</td>
                        @elseif($reportType == 'vendors')
                            <td><strong>{{ $item->business_name }}</strong></td>
                            <td>{{ $item->user->email }}</td>
                            <td>{{ $item->orders_count }}</td>
                            <td>KES {{ number_format($item->total_revenue, 2) }}</td>
                        @elseif($reportType == 'riders')
                            <td><strong>{{ $item->user->name }}</strong></td>
                            <td>{{ $item->user->email }}</td>
                            <td>{{ $item->orders_count }}</td>
                            <td>KES {{ number_format($item->total_earnings, 2) }}</td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">No data found for the selected period</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($reportType == 'orders' && $data->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <td><strong>Total</strong></td>
                        <td><strong>{{ $data->sum('total_orders') }}</strong></td>
                        <td><strong>{{ $data->sum('completed_orders') }}</strong></td>
                        <td><strong>{{ $data->sum('cancelled_orders') }}</strong></td>
                        <td><strong>KES {{ number_format($data->sum('total_revenue'), 2) }}</strong></td>
                        <td><strong>KES {{ number_format($data->sum('platform_revenue'), 2) }}</strong></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection