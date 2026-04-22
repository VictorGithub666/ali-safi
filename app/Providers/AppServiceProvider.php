<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        
        OrderPlaced::class => [
            SendVendorWhatsAppNotification::class,
        ],
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

     /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }

     
}
