@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between mb-4">
        <h1><i class="bi bi-tag"></i> Pricing Management</h1>
        <a href="{{ route('admin.prices.create') }}" class="btn btn-success"><i class="bi bi-plus-circle"></i> Add Price</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4"><input type="text" name="search" class="form-control" placeholder="Search product or vendor..." value="{{ request('search') }}"></div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Product</th><th>Vendor</th><th>Vendor Price</th><th>Customer Price</th><th>Markup %</th><th>Delivery Fee</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($prices as $price)
                        <tr>
                            <td>{{ $price->product->name }}</td>
                            <td>{{ $price->vendor->business_name }}</td>
                            <td>KES {{ number_format($price->vendor_price, 2) }}</td>
                            <td><strong style="color:#05bb14;">KES {{ number_format($price->customer_visible_price, 2) }}</strong></td>
                            <td><span class="badge bg-info">{{ round($price->markup_percentage, 2) }}%</span></td>
                            <td>KES {{ number_format($price->base_delivery_fee, 2) }}</td>
                            <td><span class="badge {{ $price->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $price->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <a href="{{ route('admin.prices.edit', $price) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.prices.destroy', $price) }}" style="display:inline;" onsubmit="return confirm('Delete?');"><@csrf <@method('DELETE')<button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4">No prices</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-center mt-4">{{ $prices->links() }}</div>
</div>
@endsection