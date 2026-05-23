<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ProductController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Vendor\ProductController as VendorProductController;
use App\Http\Controllers\Rider\DeliveryController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminVendorController;
use App\Http\Controllers\Admin\AdminRiderController;
use App\Http\Controllers\Admin\AdminPriceController;
use App\Http\Controllers\Admin\AdminFinanceController;
use App\Http\Controllers\Admin\AdminMpesaController;

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
        // Inside customer routes group - add this line
        Route::post('/nearby-shops', [ProductController::class, 'getNearbyShops'])->name('products.nearby');
        
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
        
        // M-Pesa Payment Routes
        Route::post('/orders/{order}/mpesa/initiate', [\App\Http\Controllers\PaymentController::class, 'initiateMpesaPayment'])->name('mpesa.initiate');
        Route::get('/orders/{order}/mpesa/status', [\App\Http\Controllers\PaymentController::class, 'getPaymentStatus'])->name('mpesa.status');
        Route::post('/orders/{order}/mpesa/resend', [\App\Http\Controllers\PaymentController::class, 'resendMpesaPrompt'])->name('mpesa.resend');
    });
    
    // Vendor routes
    Route::middleware(['user.type:vendor'])->prefix('vendor')->name('vendor.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('dashboard');
        
        
        Route::post('/toggle-status', [VendorDashboardController::class, 'toggleStatus'])->name('toggle-status');

        // Products CRUD
        Route::get('/products', [VendorProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [VendorProductController::class, 'create'])->name('products.create');
        Route::post('/products', [VendorProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}', [VendorProductController::class, 'show'])->name('products.show');
        Route::get('/products/{product}/edit', [VendorProductController::class, 'edit'])->name('products.edit');
        Route::patch('/products/{product}', [VendorProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [VendorProductController::class, 'destroy'])->name('products.destroy');
        Route::post('/products/bulk-toggle', [VendorProductController::class, 'bulkToggleAvailability'])->name('products.bulk-toggle');
        Route::get('/products/export/csv', [VendorProductController::class, 'export'])->name('products.export');
        Route::post('/products/toggle-availability', [VendorProductController::class, 'toggleAvailability'])->name('products.toggle-availability');
        Route::post('/update-location', [VendorDashboardController::class, 'updateLocation'])->name('update-location');
        
        // Orders
        Route::get('/orders', [VendorOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [VendorOrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [VendorOrderController::class, 'updateStatus'])->name('orders.update-status');
        
        // Earnings & Analytics
        Route::get('/earnings', [VendorDashboardController::class, 'earnings'])->name('earnings');
        Route::get('/analytics', [VendorDashboardController::class, 'analytics'])->name('analytics');
    });
    
    // Rider routes
    Route::middleware(['user.type:rider', 'ensure.rider.profile'])->prefix('rider')->name('rider.')->group(function () {
        Route::get('/dashboard', [DeliveryController::class, 'index'])->name('dashboard');

        Route::get('/dashboard/status', [DeliveryController::class, 'getDashboardStatus'])->name('dashboard.status');
        
        Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries');
        Route::get('/deliveries/{order}', [DeliveryController::class, 'show'])->name('deliveries.show');
        Route::post('/deliveries/{order}/accept', [DeliveryController::class, 'acceptOrder'])->name('deliveries.accept');
        Route::post('/deliveries/{order}/complete', [DeliveryController::class, 'completeDelivery'])->name('deliveries.complete');
        
        Route::post('/location', [DeliveryController::class, 'updateLocation'])->name('location');
        Route::post('/toggle-availability', [DeliveryController::class, 'toggleAvailability'])->name('toggle-availability');
        
        Route::get('/earnings', [DeliveryController::class, 'earnings'])->name('earnings');
        Route::get('/profile', function() { return view('rider.profile'); })->name('profile');
    });
    
    // Admin routes
    Route::middleware(['user.type:admin'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        

        // Orders Management
        Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
       
        
        // Customer Management (full CRUD)
        Route::resource('customers', AdminCustomerController::class);
        Route::post('/customers/{customer}/suspend', [AdminCustomerController::class, 'suspend'])->name('customers.suspend');
        Route::post('/customers/{customer}/activate', [AdminCustomerController::class, 'activate'])->name('customers.activate');
        
        // Vendor Management (full CRUD)
        Route::resource('vendors', AdminVendorController::class);
        Route::post('/vendors/{vendor}/verify', [AdminVendorController::class, 'verify'])->name('vendors.verify');
        Route::post('/vendors/{vendor}/suspend', [AdminVendorController::class, 'suspend'])->name('vendors.suspend');
        Route::post('/vendors/{vendor}/activate', [AdminVendorController::class, 'activate'])->name('vendors.activate');

        
        // Rider Management (full CRUD)

        
        Route::resource('riders', AdminRiderController::class);
        Route::post('/riders/{rider}/verify', [AdminRiderController::class, 'verify'])->name('riders.verify');
        Route::post('/riders/{rider}/suspend', [AdminRiderController::class, 'suspend'])->name('riders.suspend');
        Route::post('/riders/{rider}/activate', [AdminRiderController::class, 'activate'])->name('riders.activate');
        
        // Pricing Management
        Route::get('/prices/get-vendor-price', [AdminPriceController::class, 'getVendorPrice'])->name('prices.get-vendor-price');
        Route::resource('prices', AdminPriceController::class)->except(['show']);
        Route::post('/prices/bulk-update', [AdminPriceController::class, 'bulkUpdate'])->name('prices.bulk-update');
                        
        // Financial Management
        Route::get('/finances/dashboard', [AdminFinanceController::class, 'dashboard'])->name('finances.dashboard');
        Route::get('/finances/margins', [AdminFinanceController::class, 'margins'])->name('finances.margins');
        Route::get('/finances/reports', [AdminFinanceController::class, 'reports'])->name('finances.reports');
        Route::get('/finances/reports/download', [AdminFinanceController::class, 'downloadReport'])->name('finances.download-report');
        Route::get('/finances/vendor-settlement', [AdminFinanceController::class, 'vendorSettlement'])->name('finances.vendor-settlement');
        Route::get('/finances/sync', [AdminFinanceController::class, 'syncOrders'])->name('finances.sync');
        Route::get('/finances/download-simple-report', [AdminFinanceController::class, 'downloadSimpleReport'])->name('finances.download-simple-report');
        
        // M-Pesa Payment Management
        Route::get('/mpesa/dashboard', [AdminMpesaController::class, 'dashboard'])->name('mpesa.dashboard');
        Route::get('/mpesa/transactions', [AdminMpesaController::class, 'index'])->name('mpesa.index');
        Route::get('/mpesa/transactions/{mpesaTransaction}', [AdminMpesaController::class, 'show'])->name('mpesa.show');
        Route::get('/mpesa/notifications', [AdminMpesaController::class, 'notifications'])->name('mpesa.notifications');
        Route::get('/mpesa/export', [AdminMpesaController::class, 'export'])->name('mpesa.export');
        Route::post('/mpesa/transactions/{mpesaTransaction}/confirm', [AdminMpesaController::class, 'confirmPayment'])->name('mpesa.confirm');
        Route::post('/mpesa/transactions/{mpesaTransaction}/resend-callback', [AdminMpesaController::class, 'resendCallback'])->name('mpesa.resend-callback');
        
        // Order Assignment (Rider Assignment)
        Route::get('/orders/assignment', [AdminOrderAssignmentController::class, 'index'])->name('orders.assignment');
        Route::get('/orders/select-rider', [AdminOrderAssignmentController::class, 'getAvailableRiders'])->name('orders.select-rider');
        Route::post('/orders/assign', [AdminOrderAssignmentController::class, 'assign'])->name('orders.assign');
        Route::post('/orders/batch-assign', [AdminOrderAssignmentController::class, 'batchAssign'])->name('orders.batch-assign');
         Route::get('/orders/{order}', [AdminController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/reassign', [AdminOrderAssignmentController::class, 'reassign'])->name('orders.reassign');
        Route::post('/orders/{order}/cancel-assignment', [AdminOrderAssignmentController::class, 'cancelAssignment'])->name('orders.cancel-assignment');
        
        // Settings
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
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

// Offline fallback page (must be last route)
Route::get('/offline', function () {
    return view('offline');
})->name('offline');