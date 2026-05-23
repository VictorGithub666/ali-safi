<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

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
        
        if (!$vendorPhone) {
            Log::warning('No phone number found for vendor', [
                'order_id' => $order->id,
                'vendor_id' => $vendor->id
            ]);
            return;
        }
        
        // Format the message
        $message = $this->formatVendorMessage($order);
        
        // Generate WhatsApp link
        $whatsappLink = $this->whatsappService->generateWhatsAppLink($vendorPhone, $message);
        
        // Store the link in session for vendor to see
        session()->flash('whatsapp_order_link_' . $order->id, $whatsappLink);
        
        Log::info('WhatsApp order notification generated for vendor', [
            'order_id' => $order->id,
            'vendor_id' => $vendor->id,
            'vendor_phone' => $vendorPhone,
            'whatsapp_link' => $whatsappLink
        ]);
    }
    
    /**
     * Format order details into a readable message for vendor
     */
    protected function formatVendorMessage($order)
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
        
        $message .= "\n*🔗 Confirm Order:* " . route('vendor.orders.show', $order);
        $message .= "\n*✅ Accept:* Reply with 'CONFIRM {$order->order_number}'";
        $message .= "\n*❌ Reject:* Reply with 'REJECT {$order->order_number}'";
        
        return $message;
    }
}