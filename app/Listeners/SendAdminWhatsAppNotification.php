<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendAdminWhatsAppNotification implements ShouldQueue
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function handle(OrderPlaced $event)
    {
        $order = $event->order;
        
        // Get admin phone numbers from settings or config
        $adminPhones = $this->getAdminPhoneNumbers();
        
        if (empty($adminPhones)) {
            Log::warning('No admin phone numbers configured for WhatsApp notifications');
            return;
        }
        
        // Format message for admin
        $message = $this->formatAdminMessage($order);
        
        // Send to each admin
        $notificationLinks = [];
        foreach ($adminPhones as $adminPhone) {
            if ($adminPhone) {
                $whatsappLink = $this->whatsappService->generateWhatsAppLink($adminPhone, $message);
                $notificationLinks[] = $whatsappLink;
                
                Log::info('Admin WhatsApp notification generated', [
                    'order_id' => $order->id,
                    'admin_phone' => $adminPhone,
                    'whatsapp_link' => $whatsappLink
                ]);
            }
        }
        
        // Store notification links in session
        if (!empty($notificationLinks)) {
            $existingNotifications = session()->get('admin_notifications', []);
            session()->put('admin_notifications', array_merge($existingNotifications, $notificationLinks));
        }
    }
    
    /**
     * Format order details into a readable message for admin
     */
    protected function formatAdminMessage($order)
    {
        // Build items list with proper syntax
        $itemsList = [];
        foreach ($order->items as $item) {
            $sizeText = $item->size ? " ({$item->size})" : "";
            $itemsList[] = "• {$item->quantity}x {$item->product->name}{$sizeText} - KES " . number_format($item->unit_price * $item->quantity, 2);
        }
        $items = implode("\n", $itemsList);
        
        // Get customer phone with fallbacks
        $customerPhone = $order->phone ?? ($order->customer->phone ?? 'N/A');
        
        $message = "*🆕 NEW ORDER #{$order->order_number}*\n\n";
        $message .= "*👤 Customer:* {$order->customer->name}\n";
        $message .= "*📞 Phone:* {$customerPhone}\n";
        $message .= "*🏪 Vendor:* {$order->vendor->business_name}\n";
        $message .= "*📍 Delivery:* {$order->delivery_address}\n";
        
        if ($order->county) {
            $message .= "*🗺️ Location:* {$order->county}, {$order->sub_county}, {$order->ward}\n";
        }
        
        $message .= "\n*📦 Order Items:*\n{$items}\n\n";
        $message .= "*💰 Subtotal:* KES " . number_format($order->subtotal, 2) . "\n";
        $message .= "*🚚 Delivery Fee:* KES " . number_format($order->delivery_fee, 2) . "\n";
        $message .= "*💵 TOTAL:* KES " . number_format($order->total, 2) . "\n";
        $message .= "*💳 Payment:* " . ucfirst($order->payment_method) . "\n";
        
        if ($order->special_instructions) {
            $message .= "\n*📝 Special Instructions:*\n{$order->special_instructions}\n";
        }
        
        $message .= "\n*🔗 Admin Dashboard:* " . route('admin.orders.show', $order);
        
        return $message;
    }
    
    /**
     * Get admin phone numbers from configuration
     */
    protected function getAdminPhoneNumbers()
    {
        // Option 1: Get from .env
        $adminPhonesEnv = env('ADMIN_WHATSAPP_NUMBERS', '');
        if ($adminPhonesEnv) {
            return array_map('trim', explode(',', $adminPhonesEnv));
        }
        
        // Option 2: Get from database settings
        $adminPhonesDb = \App\Models\Setting::get('admin_whatsapp_numbers', '');
        if ($adminPhonesDb) {
            return array_map('trim', explode(',', $adminPhonesDb));
        }
        
        // Option 3: Get from admin users in database
        $adminUsers = \App\Models\User::where('user_type', 'admin')
            ->whereNotNull('phone')
            ->get();
        
        if ($adminUsers->count() > 0) {
            return $adminUsers->pluck('phone')->toArray();
        }
        
        // Default fallback - you should configure this
        return [];
    }
}