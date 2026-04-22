<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendVendorWhatsAppNotification implements ShouldQueue
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function handle(OrderPlaced $event)
    {
        $order = $event->order;
        $vendor = $order->vendor;
        
        // Get vendor's business phone
        $vendorPhone = $vendor->business_phone ?? $vendor->user->phone;
        
        if ($vendorPhone) {
            $whatsappLink = $this->whatsappService->sendOrderNotification($order, $vendorPhone);
            
            // Store the WhatsApp link in session or database for the vendor to access
            session()->flash('whatsapp_order_link', $whatsappLink);
            
            // Log the notification
            \Log::info('WhatsApp order notification generated', [
                'order_id' => $order->id,
                'vendor_id' => $vendor->id,
                'whatsapp_link' => $whatsappLink
            ]);
        }
    }
}