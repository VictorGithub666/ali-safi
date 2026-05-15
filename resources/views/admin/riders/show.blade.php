@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between mb-4">
        <h1><i class="bi bi-person-video3"></i> {{ $rider->user->name }}</h1>
        <div>
            <a href="{{ route('admin.riders.edit', $rider) }}" class="btn btn-warning"><i class="bi bi-pencil"></i> Edit</a>
            <form method="POST" action="{{ route('admin.riders.destroy', $rider) }}" style="display:inline;" onsubmit="return confirm('Delete?');"><@csrf <@method('DELETE')<button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i></button></form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>{{ $rider->orders_count }}</h5><p class="text-muted">Deliveries</p></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5><i class="bi bi-star-fill" style="color:#ffc107;"></i> {{ $rider->rating ?? '0' }}</h5><p class="text-muted">Rating</p></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>KES 0</h5><p class="text-muted">Wallet</p></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5><span class="badge {{ $rider->is_available ? 'bg-success' : 'bg-danger' }}">{{ $rider->is_available ? 'Available' : 'Busy' }}</span></h5><p class="text-muted">Status</p></div></div></div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Personal Information</h5>
            <dl class="row">
                <dt class="col-sm-3">Name</dt>
                <dd class="col-sm-9">{{ $rider->user->name }}</dd>
                <dt class="col-sm-3">Email</dt>
                <dd class="col-sm-9">{{ $rider->user->email }}</dd>
                <dt class="col-sm-3">Phone</dt>
                <dd class="col-sm-9">{{ $rider->user->phone }}</dd>
                <dt class="col-sm-3">Vehicle Type</dt>
                <dd class="col-sm-9">{{ ucfirst($rider->vehicle_type) }}</dd>
                <dt class="col-sm-3">Vehicle Number</dt>
                <dd class="col-sm-9">{{ $rider->vehicle_number }}</dd>
                <dt class="col-sm-3">License Number</dt>
                <dd class="col-sm-9">{{ $rider->license_number }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection