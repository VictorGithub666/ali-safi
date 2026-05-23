@extends('layouts.admin')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('admin.mpesa.index') }}" class="btn btn-outline-secondary mb-3">
            <i class="bi bi-arrow-left me-2"></i> Back to M-Pesa Transactions
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column - Main Details -->
    <div class="col-lg-8">
        <!-- Transaction Summary -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0">Transaction Details</h5>
                <span class="badge badge-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">
                    {{ ucfirst($transaction->status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Transaction ID</label>
                            <div class="fw-bold">{{ $transaction->id }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Checkout Request ID</label>
                            <code class="d-block text-break">{{ $transaction->checkout_request_id }}</code>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Merchant Request ID</label>
                            <code class="d-block text-break">{{ $transaction->merchant_request_id ?? 'N/A' }}</code>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Amount</label>
                            <div class="fw-bold h5">KES {{ number_format($transaction->amount, 2) }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Phone Number</label>
                            <code>{{ $transaction->phone_number }}</code>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Currency</label>
                            <div>{{ $transaction->currency }}</div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">M-Pesa Receipt Number</label>
                            <div class="fw-bold">{{ $transaction->mpesa_receipt_number ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Result Code</label>
                            <code>{{ $transaction->result_code ?? '—' }}</code>
                        </div>
                    </div>
                </div>

                @if($transaction->result_description)
                    <div class="alert alert-info mb-0 mt-3">
                        <strong>Result Description:</strong> {{ $transaction->result_description }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Timestamps -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title fw-bold mb-0">Timeline</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <strong>Transaction Initiated</strong>
                            <p class="text-muted mb-0">{{ $transaction->initiated_at->format('M d, Y - H:i:s') }}</p>
                        </div>
                    </div>

                    @if($transaction->completed_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-{{ $transaction->status === 'completed' ? 'success' : 'danger' }}"></div>
                            <div class="timeline-content">
                                <strong>{{ ucfirst($transaction->status) }}</strong>
                                <p class="text-muted mb-0">{{ $transaction->completed_at->format('M d, Y - H:i:s') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($transaction->callback_response)
            <!-- Callback Response -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title fw-bold mb-0">Callback Response</h5>
                </div>
                <div class="card-body">
                    <pre class="mb-0"><code>{{ json_encode(json_decode($transaction->callback_response), JSON_PRETTY_PRINT) }}</code></pre>
                </div>
            </div>
        @endif
    </div>

    <!-- Right Column - Related Information -->
    <div class="col-lg-4">
        <!-- Order Information -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title fw-bold mb-0">Related Order</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted small">Order Number</label>
                    <a href="{{ route('admin.orders.show', $transaction->order_id) }}" class="fw-bold text-decoration-none">
                        {{ $transaction->order->order_number }}
                    </a>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Status</label>
                    <div>
                        @php
                            $statusColors = [
                                'pending' => 'warning',
                                'confirmed' => 'info',
                                'prepared' => 'info',
                                'picked_up' => 'info',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                            ];
                        @endphp
                        <span class="badge badge-{{ $statusColors[$transaction->order->status] ?? 'secondary' }}">
                            {{ ucfirst(str_replace('_', ' ', $transaction->order->status)) }}
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Order Total</label>
                    <div class="fw-bold">KES {{ number_format($transaction->order->total, 2) }}</div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Payment Status</label>
                    <div>
                        <span class="badge badge-{{ $transaction->order->payment_status === 'paid' ? 'success' : 'warning' }}">
                            {{ ucfirst($transaction->order->payment_status) }}
                        </span>
                    </div>
                </div>

                <a href="{{ route('admin.orders.show', $transaction->order_id) }}" class="btn btn-sm btn-outline-primary w-100 mt-3">
                    <i class="bi bi-eye me-1"></i> View Full Order
                </a>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title fw-bold mb-0">Customer</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted small">Name</label>
                    <div class="fw-bold">{{ $transaction->order->customer->name }}</div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Email</label>
                    <div>
                        <a href="mailto:{{ $transaction->order->customer->email }}" class="text-decoration-none">
                            {{ $transaction->order->customer->email }}
                        </a>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Phone</label>
                    <div>
                        <a href="tel:{{ $transaction->order->customer->phone }}" class="text-decoration-none">
                            {{ $transaction->order->customer->phone }}
                        </a>
                    </div>
                </div>

                <a href="{{ route('admin.customers.show', $transaction->order->customer->id) }}" class="btn btn-sm btn-outline-primary w-100 mt-3">
                    <i class="bi bi-person me-1"></i> View Customer
                </a>
            </div>
        </div>

        <!-- Actions -->
        @if($transaction->status !== 'completed')
            <div class="card border-0 shadow-sm mb-4 border-warning">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.mpesa.confirm', $transaction) }}">
                        @csrf
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Confirm this payment?')">
                            <i class="bi bi-check-circle me-1"></i> Confirm Payment
                        </button>
                    </form>
                    <small class="text-muted mt-2 d-block text-center">Admin override for manual confirmation</small>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding: 20px 0;
    }

    .timeline-item {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
        position: relative;
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }

    .timeline-marker {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 4px;
    }

    .timeline-content strong {
        display: block;
        margin-bottom: 5px;
    }

    .badge-success {
        background-color: #d4edda;
        color: #155724;
    }
    .badge-warning {
        background-color: #fff3cd;
        color: #856404;
    }
    .badge-danger {
        background-color: #f8d7da;
        color: #721c24;
    }
    .badge-info {
        background-color: #d1ecf1;
        color: #0c5460;
    }
    .badge-secondary {
        background-color: #e2e3e5;
        color: #383d41;
    }
</style>
@endsection
