<?php
namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use App\Events\OrderPlaced;
use App\Events\OrderStatusUpdated;
use App\Listeners\SendVendorWhatsAppNotification;
use App\Listeners\SendAdminWhatsAppNotification;
use App\Listeners\DeductProductStockOnDelivery;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        OrderStatusUpdated::class => [
            DeductProductStockOnDelivery::class,
        ],
        OrderPlaced::class => [
            SendVendorWhatsAppNotification::class,
            SendAdminWhatsAppNotification::class,  // Add this line
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Order::observe(OrderObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}