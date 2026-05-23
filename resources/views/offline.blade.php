@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="mb-4">
                <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                    <i class="bi bi-wifi-off" style="font-size: 3rem; color: #ffc107;"></i>
                </div>
            </div>
            
            <h1 class="fw-bold mb-3">You're Offline</h1>
            <p class="lead text-muted mb-4">
                It looks like you've lost your internet connection. 
                Don't worry, you can still browse some content while offline.
            </p>
            
            <div class="row g-4 mt-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-start">
                            <h5 class="fw-bold mb-3">
                                <i class="bi bi-clock-history me-2" style="color: var(--primary-green);"></i>
                                Previously Viewed Products
                            </h5>
                            <div id="offlineProducts">
                                <p class="text-muted">Loading your recently viewed products...</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-start">
                            <h5 class="fw-bold mb-3">
                                <i class="bi bi-box me-2" style="color: var(--primary-green);"></i>
                                Recent Orders
                            </h5>
                            <div id="offlineOrders">
                                <p class="text-muted">Loading your recent orders...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-5">
                <div class="alert alert-info">
                    <i class="bi bi-arrow-repeat me-2"></i>
                    We'll automatically refresh when you're back online.
                </div>
                
                <button onclick="checkConnectionAndReload()" class="btn btn-primary btn-lg px-5">
                    <i class="bi bi-arrow-repeat me-2"></i>
                    Try Again
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Load cached data from IndexedDB or localStorage
document.addEventListener('DOMContentLoaded', async function() {
    await loadOfflineProducts();
    await loadOfflineOrders();
});

async function loadOfflineProducts() {
    const container = document.getElementById('offlineProducts');
    
    // Try to get products from cache
    try {
        const cache = await caches.open('ali-safi-dynamic-v1');
        const response = await cache.match('/customer/products');
        
        if (response) {
            const text = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            const products = doc.querySelectorAll('.product-card');
            
            if (products.length > 0) {
                let html = '<div class="list-group">';
                Array.from(products).slice(0, 5).forEach(product => {
                    const name = product.querySelector('.product-name')?.textContent || 'Product';
                    const price = product.querySelector('.product-price')?.textContent || 'KES 0';
                    html += `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${name}</strong>
                                    <br>
                                    <small class="text-muted">${price}</small>
                                </div>
                                <span class="badge bg-success">Cached</span>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<p class="text-muted">No cached products available</p>';
            }
        } else {
            container.innerHTML = '<p class="text-muted">No cached products available</p>';
        }
    } catch (error) {
        console.error('Error loading offline products:', error);
        container.innerHTML = '<p class="text-muted">Unable to load cached products</p>';
    }
}

async function loadOfflineOrders() {
    const container = document.getElementById('offlineOrders');
    
    // Try to get orders from localStorage
    const cachedOrders = localStorage.getItem('recentOrders');
    
    if (cachedOrders) {
        try {
            const orders = JSON.parse(cachedOrders);
            if (orders.length > 0) {
                let html = '<div class="list-group">';
                orders.slice(0, 5).forEach(order => {
                    html += `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Order #${order.order_number}</strong>
                                    <br>
                                    <small class="text-muted">Status: ${order.status}</small>
                                </div>
                                <span class="badge bg-secondary">Cached</span>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<p class="text-muted">No cached orders available</p>';
            }
        } catch (e) {
            container.innerHTML = '<p class="text-muted">No cached orders available</p>';
        }
    } else {
        container.innerHTML = '<p class="text-muted">No cached orders available</p>';
    }
}

function checkConnectionAndReload() {
    if (navigator.onLine) {
        window.location.reload();
    } else {
        alert('Still offline. Please check your internet connection.');
    }
}

// Listen for online event
window.addEventListener('online', function() {
    const alert = document.createElement('div');
    alert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
    alert.style.zIndex = '9999';
    alert.style.minWidth = '300px';
    alert.innerHTML = `
        <i class="bi bi-wifi me-2"></i>
        You're back online! Refreshing page...
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alert);
    
    setTimeout(() => {
        window.location.reload();
    }, 2000);
});

// Cache recent orders in localStorage
function cacheRecentOrders(orders) {
    localStorage.setItem('recentOrders', JSON.stringify(orders.slice(0, 10)));
}

// Export for use in other scripts
window.cacheRecentOrders = cacheRecentOrders;
</script>
@endsection