@extends('layouts.admin')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">M-Pesa Notifications</h2>
            <div class="d-flex gap-2">
                <span class="badge bg-success">Today Completed: {{ $todayCompleted }}</span>
                <span class="badge bg-danger">Today Failed: {{ $todayFailed }}</span>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="completed" {{ request('type') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ request('type') === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="cancelled" {{ request('type') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Notifications List -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @forelse($notifications as $notification)
                <div class="list-group-item p-4 border-bottom">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            @if($notification->status === 'completed')
                                <div class="rounded-circle bg-success bg-opacity-10 text-success p-3">
                                    <i class="bi bi-check-circle" style="font-size: 1.5rem;"></i>
                                </div>
                            @elseif($notification->status === 'failed')
                                <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3">
                                    <i class="bi bi-x-circle" style="font-size: 1.5rem;"></i>
                                </div>
                            @else
                                <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary p-3">
                                    <i class="bi bi-dash-circle" style="font-size: 1.5rem;"></i>
                                </div>
                            @endif
                        </div>

                        <div class="col">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1 fw-bold">
                                        @if($notification->status === 'completed')
                                            Payment Successful
                                        @elseif($notification->status === 'failed')
                                            Payment Failed
                                        @else
                                            Payment Cancelled
                                        @endif
                                    </h6>
                                    <p class="text-muted small mb-2">
                                        Order: <strong>{{ $notification->order->order_number }}</strong>
                                        • Customer: <strong>{{ $notification->order->customer->name }}</strong>
                                    </p>
                                    <p class="text-muted small mb-2">
                                        Phone: <code>{{ $notification->phone_number }}</code>
                                        • Amount: <strong>KES {{ number_format($notification->amount, 2) }}</strong>
                                    </p>
                                    @if($notification->mpesa_receipt_number)
                                        <p class="text-muted small mb-0">
                                            Receipt: <code>{{ $notification->mpesa_receipt_number }}</code>
                                        </p>
                                    @endif
                                    @if($notification->result_description)
                                        <p class="text-muted small mb-0">
                                            <em>{{ $notification->result_description }}</em>
                                        </p>
                                    @endif
                                </div>

                                <div class="text-end">
                                    <small class="text-muted d-block">{{ $notification->completed_at->diffForHumans() }}</small>
                                    <small class="text-muted d-block">{{ $notification->completed_at->format('M d, H:i') }}</small>

                                    <div class="mt-3">
                                        <a href="{{ route('admin.mpesa.show', $notification) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i> Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-bell-slash" style="font-size: 2rem; opacity: 0.5;"></i>
                    <p class="mt-3 mb-0">No notifications available</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Pagination -->
@if($notifications->hasPages())
    <nav class="mt-4">
        {{ $notifications->links() }}
    </nav>
@endif

<style>
    .list-group-item {
        transition: background-color 0.3s ease;
    }

    .list-group-item:hover {
        background-color: #f8f9fa;
    }

    .list-group-item:last-child {
        border-bottom: none !important;
    }
</style>
@endsection
