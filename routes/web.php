<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ProductController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Rider\DeliveryController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\SocialiteController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Public vendor shop route
Route::get('/shop/{vendor}', [ProductController::class, 'vendorShop'])->name('shop.vendor');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('google.login');
    Route::get('auth/google/callback', [SocialiteController::class, 'handleGoogleCallback']);
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::post('/profile/picture', [ProfileController::class, 'updatePicture'])->name('profile.picture');
    
    // Customer routes
    Route::middleware(['user.type:customer'])->prefix('customer')->name('customer.')->group(function () {
        Route::get('/dashboard', function () {
            return view('customer.dashboard');
        })->name('dashboard');
        
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
        
        Route::get('/cart', [CartController::class, 'index'])->name('cart');
        Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
        Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
        Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
        Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
        
        Route::get('/checkout', [CustomerOrderController::class, 'checkout'])->name('checkout');
        Route::post('/orders', [CustomerOrderController::class, 'store'])->name('orders.store');
        Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders');
        Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/track', [CustomerOrderController::class, 'track'])->name('orders.track');
        Route::get('/orders/{order}/rider-location', [CustomerOrderController::class, 'getRiderLocation'])->name('orders.rider-location');
        Route::get('/orders/{order}/invoice', [CustomerOrderController::class, 'downloadInvoice'])->name('orders.invoice');
    });
    
    // Vendor routes
    Route::middleware(['user.type:vendor'])->prefix('vendor')->name('vendor.')->group(function () {
        Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('dashboard');
        
        Route::get('/orders', [VendorOrderController::class, 'index'])->name('orders');
        Route::post('/orders/{order}/status', [VendorOrderController::class, 'updateStatus'])->name('orders.status');
        
        Route::get('/products', [VendorDashboardController::class, 'products'])->name('products');
        Route::post('/products/stock', [VendorDashboardController::class, 'updateStock'])->name('products.stock');
        
        Route::post('/toggle-status', [VendorDashboardController::class, 'toggleStatus'])->name('toggle-status');
        
        Route::get('/earnings', [VendorDashboardController::class, 'earnings'])->name('earnings');
    });
    
    // Rider routes
    Route::middleware(['user.type:rider'])->prefix('rider')->name('rider.')->group(function () {
        Route::get('/dashboard', [DeliveryController::class, 'index'])->name('dashboard');
        
        Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries');
        Route::post('/deliveries/{order}/accept', [DeliveryController::class, 'acceptOrder'])->name('deliveries.accept');
        Route::post('/deliveries/{order}/complete', [DeliveryController::class, 'completeDelivery'])->name('deliveries.complete');
        
        Route::post('/location', [DeliveryController::class, 'updateLocation'])->name('location');
        Route::post('/toggle-availability', [DeliveryController::class, 'toggleAvailability'])->name('toggle-availability');
        
        Route::get('/earnings', [DeliveryController::class, 'earnings'])->name('earnings');
    });
    
    // Admin routes
    Route::middleware(['user.type:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // Order management
        Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
        Route::post('/orders/{order}/assign-rider', [AdminController::class, 'assignRider'])->name('orders.assign-rider');
        Route::post('/orders/{order}/cancel', [AdminController::class, 'cancelOrder'])->name('orders.cancel');
        
        // Vendor management
        Route::get('/vendors', [AdminController::class, 'vendors'])->name('vendors');
        Route::post('/vendors/{vendor}/verify', [AdminController::class, 'verifyVendor'])->name('vendors.verify');
        Route::post('/vendors/{vendor}/suspend', [AdminController::class, 'suspendVendor'])->name('vendors.suspend');
        
        // Rider management
        Route::get('/riders', [AdminController::class, 'riders'])->name('riders');
        Route::post('/riders/{rider}/verify', [AdminController::class, 'verifyRider'])->name('riders.verify');
        Route::post('/riders/{rider}/suspend', [AdminController::class, 'suspendRider'])->name('riders.suspend');
        
        // Settings
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
        
        // Reports
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    });
});

// Location API routes (for cascading selects in checkout)
Route::prefix('api/locations')->group(function () {
    Route::get('/counties', [LocationController::class, 'getCounties']);
    Route::get('/{county}/sub-counties', [LocationController::class, 'getSubCounties']);
    Route::get('/{county}/{subCounty}/wards', [LocationController::class, 'getWards']);
});

require __DIR__.'/auth.php';

// M-Pesa Payment Routes (Webhook - No Auth Required)
Route::post('/mpesa/callback', [\App\Http\Controllers\PaymentController::class, 'mpesaCallback'])->name('mpesa.callback');

// API routes for mobile apps
Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    // API endpoints for mobile apps
});