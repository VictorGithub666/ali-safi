<div class="modal fade" id="nearbyShopsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="nearbyShopsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #05bb14 0%, #0a9a12 100%);">
                <h5 class="modal-title text-white fw-bold" id="nearbyShopsModalLabel">
                    <i class="bi bi-geo-alt-fill me-2"></i>Find Shops Near You
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-4">
                    <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-shop" style="font-size: 2.5rem; color: #05bb14;"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-3">Discover Shops Near You!</h4>
                <p class="text-muted mb-4">
                    Allow location access to find shops within <strong class="text-success">1 kilometer</strong> of your current location.
                    <br>Get faster delivery and support local businesses!
                </p>
                
                <!-- Loading State -->
                <div id="locationLoadingState" style="display: none;">
                    <div class="spinner-border text-success mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted">Getting your location...</p>
                </div>
                
                <!-- Location Status -->
                <div id="locationStatus" class="alert alert-warning small" style="display: none;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span id="locationStatusText"></span>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="button" id="allowLocationBtn" class="btn text-white fw-bold py-2" style="background-color: #05bb14;">
                        <i class="bi bi-geo-alt me-2"></i>Allow Location Access
                    </button>
                    <button type="button" id="dismissModalBtn" class="btn btn-outline-secondary py-2" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Not Now
                    </button>
                </div>
                
                <div class="mt-4 pt-2">
                    <small class="text-muted">
                        <i class="bi bi-shield-check me-1"></i>
                        Your location is only used to find nearby shops and is never shared.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>