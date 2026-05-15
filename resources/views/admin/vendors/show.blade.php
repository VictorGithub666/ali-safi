@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between mb-4">
        <h1><i class="bi bi-shop"></i> {{ $vendor->business_name }}</h1>
        <div>
            <a href="{{ route('admin.vendors.edit', $vendor) }}" class="btn btn-warning"><i class="bi bi-pencil"></i> Edit</a>
            <form method="POST" action="{{ route('admin.vendors.destroy', $vendor) }}" style="display:inline;" onsubmit="return confirm('Delete?');"><@csrf <@method('DELETE')<button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i></button></form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>{{ $vendor->orders_count }}</h5><p class="text-muted">Total Orders</p></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5><i class="bi bi-star-fill" style="color:#ffc107;"></i> {{ $vendor->rating ?? '0' }}</h5><p class="text-muted">Rating</p></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5>KES 0</h5><p class="text-muted">Wallet</p></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h5><span class="badge {{ $vendor->is_verified ? 'bg-success' : 'bg-warning' }}">{{ $vendor->is_verified ? 'Verified' : 'Pending' }}</span></h5><p class="text-muted">Status</p></div></div></div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Business Information</h5>
            <dl class="row">
                <dt class="col-sm-3">Business Name</dt>
                <dd class="col-sm-9">{{ $vendor->business_name }}</dd>
                <dt class="col-sm-3">Owner</dt>
                <dd class="col-sm-9">{{ $vendor->user->name }}</dd>
                <dt class="col-sm-3">Email</dt>
                <dd class="col-sm-9">{{ $vendor->user->email }}</dd>
                <dt class="col-sm-3">Phone</dt>
                <dd class="col-sm-9">{{ $vendor->user->phone }}</dd>
                <dt class="col-sm-3">Address</dt>
                <dd class="col-sm-9">{{ $vendor->business_address }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection