@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between mb-4">
        <h1><i class="bi bi-shop"></i> {{ $vendor->business_name }}</h1>
        <div>
            <a href="{{ route('admin.vendors.edit', $vendor) }}" class="btn btn-warning"><i class="bi bi-pencil"></i> Edit</a>
            <form method="POST" action="{{ route('admin.vendors.destroy', $vendor) }}" style="display:inline;" onsubmit="return confirm('Delete?');">
                @csrf 
                @method('DELETE')
                <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i></button>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>{{ $vendor->orders()->count() }}</h5>
                    <p class="text-muted">Total Orders</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5><i class="bi bi-star-fill" style="color:#ffc107;"></i> {{ number_format($vendor->rating ?? 0, 1) }}</h5>
                    <p class="text-muted">Rating</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="text-success">KES {{ number_format($vendor->wallet_balance ?? 0, 2) }}</h5>
                    <p class="text-muted">Wallet Balance</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>
                        <span class="badge {{ $vendor->is_verified ? 'bg-success' : 'bg-warning' }}" style="font-size: 1rem;">
                            {{ $vendor->is_verified ? 'Verified' : 'Pending' }}
                        </span>
                    </h5>
                    <p class="text-muted">Status</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5>Business Information</h5>
                    <dl class="row">
                        <dt class="col-sm-4">Business Name</dt>
                        <dd class="col-sm-8">{{ $vendor->business_name }}</dd>
                        <dt class="col-sm-4">Owner</dt>
                        <dd class="col-sm-8">{{ $vendor->user->name }}</dd>
                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">{{ $vendor->user->email }}</dd>
                        <dt class="col-sm-4">Phone</dt>
                        <dd class="col-sm-8">{{ $vendor->user->phone }}</dd>
                        <dt class="col-sm-4">Business Phone</dt>
                        <dd class="col-sm-8">{{ $vendor->business_phone }}</dd>
                        <dt class="col-sm-4">Address</dt>
                        <dd class="col-sm-8">{{ $vendor->business_address }}</dd>
                        <dt class="col-sm-4">City</dt>
                        <dd class="col-sm-8">{{ $vendor->city ?? 'N/A' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5>Financial Summary</h5>
                    <dl class="row">
                        <dt class="col-sm-6">Total Revenue (Delivered Orders)</dt>
                        <dd class="col-sm-6"><strong class="text-success">KES {{ number_format($vendor->orders()->where('status', 'delivered')->sum('subtotal'), 2) }}</strong></dd> 
                        
                        <dt class="col-sm-6">Wallet Balance</dt>
                        <dd class="col-sm-6"><strong class="text-primary">KES {{ number_format($vendor->wallet_balance ?? 0, 2) }}</strong></dd>
                        
                        <dt class="col-sm-6">Total Orders</dt>
                        <dd class="col-sm-6"><strong>{{ $vendor->orders_count ?? 0 }}</strong></dd>
                        
                        <dt class="col-sm-6">Completed Orders</dt>
                        <dd class="col-sm-6"><strong>{{ $vendor->orders()->where('status', 'delivered')->count() }}</strong></dd>
                        
                        <dt class="col-sm-6">Pending Orders</dt>
                        <dd class="col-sm-6"><strong>{{ $vendor->orders()->whereIn('status', ['pending', 'confirmed', 'preparing'])->count() }}</strong></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection