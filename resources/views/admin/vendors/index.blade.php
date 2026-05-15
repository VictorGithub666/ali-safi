@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between mb-4">
        <h1><i class="bi bi-shop"></i> Vendors</h1>
        <a href="{{ route('admin.vendors.create') }}" class="btn btn-success"><i class="bi bi-plus-circle"></i> Add</a>
    </div>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search Business Name..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="unverified" {{ request('status') === 'unverified' ? 'selected' : '' }}>Unverified</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filter</button>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary w-100"><i class="bi bi-x-circle"></i> Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Business Name</th>
                        <th>Owner</th>
                        <th>Email</th>
                        <th>Orders</th>
                        <th>Total Revenue</th>
                        <th>Wallet Balance</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendors as $vendor)
                        <tr>
                            <td><strong>{{ $vendor->business_name }}</strong></td>
                            <td>{{ $vendor->user->name }}</td>
                            <td>{{ $vendor->user->email }}</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $vendor->orders_count ?? 0 }}
                                </span>
                            </td>
                            <td>
                                <strong class="text-success">
                                    KES {{ number_format($vendor->total_revenue ?? 0, 2) }}
                                </strong>
                            </td>
                            <td>
                                <strong class="text-primary">
                                    KES {{ number_format($vendor->wallet_balance ?? 0, 2) }}
                                </strong>
                            </td>
                            <td>
                                <i class="bi bi-star-fill" style="color:#ffc107;"></i> 
                                {{ number_format($vendor->rating ?? 0, 1) }}
                            </td>
                            <td>
                                <span class="badge {{ $vendor->is_verified ? 'bg-success' : 'bg-warning' }}">
                                    {{ $vendor->is_verified ? 'Verified' : 'Pending' }}
                                </span>
                                <span class="badge {{ $vendor->user->is_active ? 'bg-success' : 'bg-danger' }}">
                                    {{ $vendor->user->is_active ? 'Active' : 'Suspended' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.vendors.show', $vendor) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(!$vendor->is_verified)
                                    <form method="POST" action="{{ route('admin.vendors.verify', $vendor) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Verify">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.vendors.edit', $vendor) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if(!$vendor->user->is_active)
                                    <form method="POST" action="{{ route('admin.vendors.activate', $vendor) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Activate">
                                            <i class="bi bi-play-circle"></i>
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.vendors.suspend', $vendor) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Suspend" onclick="return confirm('Suspend this vendor?')">
                                            <i class="bi bi-pause-circle"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-inbox"></i> No vendors found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-center mt-4">{{ $vendors->links() }}</div>
</div>
@endsection