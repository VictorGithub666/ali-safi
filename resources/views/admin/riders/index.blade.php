@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between mb-4">
        <h1><i class="bi bi-person-video3"></i> Riders</h1>
        <a href="{{ route('admin.riders.create') }}" class="btn btn-success"><i class="bi bi-plus-circle"></i> Add</a>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Name</th><th>Vehicle</th><th>License</th><th>Deliveries</th><th>Rating</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($riders as $rider)
                        <tr>
                            <td>{{ $rider->user->name }}</td>
                            <td>{{ $rider->vehicle_type }} - {{ $rider->vehicle_number }}</td>
                            <td>{{ $rider->license_number }}</td>
                            <td><span class="badge bg-info">{{ $rider->orders_count }}</span></td>
                            <td><i class="bi bi-star-fill" style="color:#ffc107;"></i> {{ $rider->rating ?? '0' }}</td>
                            <td><span class="badge {{ $rider->is_verified ? 'bg-success' : 'bg-warning' }}">{{ $rider->is_verified ? 'Verified' : 'Pending' }}</span></td>
                            <td>
                                <a href="{{ route('admin.riders.show', $rider) }}" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                                @if(!$rider->is_verified)<form method="POST" action="{{ route('admin.riders.verify', $rider) }}" style="display:inline;"><@csrf<button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-circle"></i></button></form>@endif
                                <a href="{{ route('admin.riders.edit', $rider) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4">No riders</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-center mt-4">{{ $riders->links() }}</div>
</div>
@endsection