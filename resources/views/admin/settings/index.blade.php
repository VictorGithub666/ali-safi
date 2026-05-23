@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h1 class="mb-4"><i class="bi bi-gear"></i> Platform Settings</h1>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">General Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="platform_fee_percentage" class="form-label">Platform Fee Percentage (%)</label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control @error('platform_fee_percentage') is-invalid @enderror" 
                                       id="platform_fee_percentage" 
                                       name="platform_fee_percentage" 
                                       value="{{ old('platform_fee_percentage', \App\Models\Setting::get('platform_fee_percentage', 5)) }}" 
                                       step="0.1" 
                                       min="0" 
                                       max="100" 
                                       required>
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Percentage charged on each order as platform fee</small>
                            @error('platform_fee_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="base_delivery_fee" class="form-label">Base Delivery Fee (KES)</label>
                            <div class="input-group">
                                <span class="input-group-text">KES</span>
                                <input type="number" 
                                       class="form-control @error('base_delivery_fee') is-invalid @enderror" 
                                       id="base_delivery_fee" 
                                       name="base_delivery_fee" 
                                       value="{{ old('base_delivery_fee', \App\Models\Setting::get('base_delivery_fee', 50)) }}" 
                                       step="1" 
                                       min="0" 
                                       required>
                            </div>
                            <small class="text-muted">Base delivery fee for all orders</small>
                            @error('base_delivery_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="per_km_delivery_fee" class="form-label">Per Kilometer Delivery Fee (KES/km)</label>
                            <div class="input-group">
                                <span class="input-group-text">KES</span>
                                <input type="number" 
                                       class="form-control @error('per_km_delivery_fee') is-invalid @enderror" 
                                       id="per_km_delivery_fee" 
                                       name="per_km_delivery_fee" 
                                       value="{{ old('per_km_delivery_fee', \App\Models\Setting::get('per_km_delivery_fee', 10)) }}" 
                                       step="1" 
                                       min="0" 
                                       required>
                            </div>
                            <small class="text-muted">Additional fee per kilometer for delivery</small>
                            @error('per_km_delivery_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="vendor_commission_percentage" class="form-label">Vendor Commission Percentage (%)</label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control @error('vendor_commission_percentage') is-invalid @enderror" 
                                       id="vendor_commission_percentage" 
                                       name="vendor_commission_percentage" 
                                       value="{{ old('vendor_commission_percentage', \App\Models\Setting::get('vendor_commission_percentage', 10)) }}" 
                                       step="0.1" 
                                       min="0" 
                                       max="100" 
                                       required>
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Commission charged to vendors on each sale</small>
                            @error('vendor_commission_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label for="min_delivery_distance" class="form-label">Minimum Delivery Distance (km)</label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control" 
                                       id="min_delivery_distance" 
                                       name="min_delivery_distance" 
                                       value="{{ old('min_delivery_distance', \App\Models\Setting::get('min_delivery_distance', 0)) }}" 
                                       step="0.1" 
                                       min="0">
                                <span class="input-group-text">km</span>
                            </div>
                            <small class="text-muted">Minimum distance for delivery fee calculation</small>
                        </div>

                        <div class="mb-3">
                            <label for="max_delivery_distance" class="form-label">Maximum Delivery Distance (km)</label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control" 
                                       id="max_delivery_distance" 
                                       name="max_delivery_distance" 
                                       value="{{ old('max_delivery_distance', \App\Models\Setting::get('max_delivery_distance', 15)) }}" 
                                       step="1" 
                                       min="0">
                                <span class="input-group-text">km</span>
                            </div>
                            <small class="text-muted">Maximum distance for delivery availability</small>
                        </div>

                        <!-- Add this after the existing settings -->
                        <div class="mb-3">
                            <label for="admin_whatsapp_numbers" class="form-label">Admin WhatsApp Numbers</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                                <input type="text" 
                                    class="form-control @error('admin_whatsapp_numbers') is-invalid @enderror" 
                                    id="admin_whatsapp_numbers" 
                                    name="admin_whatsapp_numbers" 
                                    value="{{ old('admin_whatsapp_numbers', \App\Models\Setting::get('admin_whatsapp_numbers', '')) }}" 
                                    placeholder="254712345678,254798765432">
                            </div>
                            <small class="text-muted">Comma-separated list of admin WhatsApp numbers (include country code 254)</small>
                            @error('admin_whatsapp_numbers')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Settings
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="bi bi-arrow-repeat"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Information</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>About Platform Settings</strong>
                        <hr>
                        <p class="small mb-0">These settings affect the entire platform including order calculations, fees, and commissions. Changes will apply to new orders only.</p>
                    </div>
                    
                    <div class="mt-3">
                        <h6>Current Values:</h6>
                        <ul class="list-unstyled small">
                            <li><strong>Platform Fee:</strong> {{ \App\Models\Setting::get('platform_fee_percentage', 5) }}%</li>
                            <li><strong>Base Delivery Fee:</strong> KES {{ number_format(\App\Models\Setting::get('base_delivery_fee', 50), 2) }}</li>
                            <li><strong>Per KM Fee:</strong> KES {{ number_format(\App\Models\Setting::get('per_km_delivery_fee', 10), 2) }}/km</li>
                            <li><strong>Vendor Commission:</strong> {{ \App\Models\Setting::get('vendor_commission_percentage', 10) }}%</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection