@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between mb-4">
        <h1><i class="bi bi-shop"></i> Vendors</h1>
        <a href="{{ route('admin.vendors.create') }}" class="btn btn-success"><i class="bi bi-plus-circle"></i> Add</a>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Business Name</th><th>Owner</th><th>Email</th><th>Orders</th><th>Rating</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($vendors as $vendor)
                        <tr>
                            <td><strong>{{ $vendor->business_name }}</strong></td>
                            <td>{{ $vendor->user->name }}</td>
                            <td>{{ $vendor->user->email }}</td>
                            <td><span class="badge bg-info">{{ $vendor->orders_count }}</span></td>
                            <td><i class="bi bi-star-fill" style="color:#ffc107;"></i> {{ $vendor->rating ?? '0' }}</td>
                            <td>
                                <span class="badge {{ $vendor->is_verified ? 'bg-success' : 'bg-warning' }}">{{ $vendor->is_verified ? 'Verified' : 'Pending' }}</span>
                                <span class="badge {{ $vendor->user->is_active ? 'bg-success' : 'bg-danger' }}">{{ $vendor->user->is_active ? 'Active' : 'Suspended' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.vendors.show', $vendor) }}" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                                @if(!$vendor->is_verified)<form method="POST" action="{{ route('admin.vendors.verify', $vendor) }}" style="display:inline;"><@csrf<button type="submit" class="btn btn-sm btn-success" title="Verify"><i class="bi bi-check-circle"></i></button></form>@endif
                                <a href="{{ route('admin.vendors.edit', $vendor) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                            </td>
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