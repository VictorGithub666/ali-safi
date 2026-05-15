@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-person"></i> {{ $customer->name }}</h1>
        <div>
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-warning"><i class="bi bi-pencil"></i> Edit</a>
            <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" style="display:inline;" onsubmit="return confirm('Delete this customer?');"><@csrf <@method('DELETE')<button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Delete</button></form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Customer Information</h5>
                    <dl class="row">
                        <dt class="col-sm-3">Name</dt>
                        <dd class="col-sm-9">{{ $customer->name }}</dd>
                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9">{{ $customer->email }}</dd>
                        <dt class="col-sm-3">Phone</dt>
                        <dd class="col-sm-9">{{ $customer->phone }}</dd>
                        <dt class="col-sm-3">City</dt>
                        <dd class="col-sm-9">{{ $customer->city ?? '-' }}</dd>
                        <dt class="col-sm-3">Address</dt>
                        <dd class="col-sm-9">{{ $customer->address ?? '-' }}</dd>
                        <dt class="col-sm-3">Member Since</dt>
                        <dd class="col-sm-9">{{ $customer->created_at->format('M d, Y') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <h5 class="mb-3">Recent Orders</h5>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Order ID</th><th>Amount</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                    @forelse($customer->orders as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>KES {{ number_format($order->total_price, 2) }}</td>
                            <td><span class="badge bg-info">{{ ucfirst($order->status) }}</span></td>
                            <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-3">No orders yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection