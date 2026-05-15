@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between mb-4">
        <h1><i class="bi bi-person-video3"></i> Riders</h1>
        <a href="{{ route('admin.riders.create') }}" class="btn btn-success"><i class="bi bi-plus-circle"></i> Add</a>
    </div>

    {{-- Add Filter Form --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email, phone, or vehicle..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="unverified" {{ request('status') === 'unverified' ? 'selected' : '' }}>Unverified</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filter</button>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admin.riders.index') }}" class="btn btn-secondary w-100"><i class="bi bi-x-circle"></i> Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Vehicle</th>
                        <th>License</th>
                        <th>Deliveries</th>
                        <th>Wallet Balance</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riders as $rider)
                        <tr>
                            <td>
                                <strong>{{ $rider->user->name }}</strong><br>
                                <small class="text-muted">{{ $rider->user->email }}</small>
                            </td>
                            <td>
                                {{ ucfirst($rider->vehicle_type) }}<br>
                                <small class="text-muted">{{ $rider->vehicle_number }}</small>
                            </td>
                            <td>{{ $rider->license_number }}</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $rider->orders_count ?? 0 }}
                                </span>
                            </td>
                            <td>
                                <strong class="text-success">
                                    KES {{ number_format($rider->wallet_balance ?? 0, 2) }}
                                </strong>
                            </td>
                            <td>
                                <i class="bi bi-star-fill" style="color:#ffc107;"></i> 
                                {{ number_format($rider->rating ?? 0, 1) }}
                            </td>
                            <td>
                                <span class="badge {{ $rider->is_verified ? 'bg-success' : 'bg-warning' }}">
                                    {{ $rider->is_verified ? 'Verified' : 'Pending' }}
                                </span>
                                <span class="badge {{ $rider->is_available ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $rider->is_available ? 'Available' : 'Offline' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.riders.show', $rider) }}" class="btn btn-sm btn-info" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(!$rider->is_verified)
                                        <form method="POST" action="{{ route('admin.riders.verify', $rider) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Verify Rider">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.riders.edit', $rider) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.riders.destroy', $rider) }}" style="display:inline;" onsubmit="return confirm('Delete this rider? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                <p class="mt-2 text-muted">No riders found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-center mt-4">{{ $riders->links() }}</div>
</div>

@push('scripts')
<script>
    // Add tooltips for action buttons
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
@endsection