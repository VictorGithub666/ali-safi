<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        // You can use services like Twilio, WhatsApp Business API, or UltraMsg
        // For this example, we'll use a generic API structure
        $this->apiUrl = env('WHATSAPP_API_URL', 'https://api.whatsapp.com/send');
        $this->apiKey = env('WHATSAPP_API_KEY', '');
    }

    /**
     * Send order notification to vendor
     */
    public function sendOrderNotification($order, $vendorPhone)
    {
        $message = $this->formatOrderMessage($order);
        
        // Format phone number (remove any non-numeric characters and add country code if needed)
        $phone = $this->formatPhoneNumber($vendorPhone);
        
        // Method 1: Using WhatsApp Web API (requires business account)
        // return $this->sendViaAPI($phone, $message);
        
        // Method 2: Using WhatsApp Web link (opens in browser - simpler)
        return $this->generateWhatsAppLink($phone, $message);
    }

    /**
     * Format order details into a readable message
     */
    protected function formatOrderMessage($order)
    {
        $items = $order->items->map(function($item) {
            return "• {$item->quantity}x {$item->product->name} - KES " . number_format($item->unit_price * $item->quantity, 2);
        })->implode("\n");

        $message = "*NEW ORDER #{$order->order_number}*\n\n";
        $message .= "*Customer:* {$order->customer->name}\n";
        $message .= "*Phone:* {$order->customer->phone}\n";
        $message .= "*Delivery Address:* {$order->delivery_address}\n";
        if ($order->county) {
            $message .= "*Location:* {$order->county}, {$order->sub_county}, {$order->ward}\n";
        }
        $message .= "\n*Order Items:*\n{$items}\n\n";
        $message .= "*Subtotal:* KES " . number_format($order->subtotal, 2) . "\n";
        $message .= "*Delivery Fee:* KES " . number_format($order->delivery_fee, 2) . "\n";
        $message .= "*TOTAL:* KES " . number_format($order->total, 2) . "\n\n";
        
        if ($order->special_instructions) {
            $message .= "*Special Instructions:* {$order->special_instructions}\n\n";
        }
        
        $message .= "Please confirm this order as soon as possible.\n";
        $message .= "Login to your vendor dashboard: " . route('vendor.dashboard');
        
        return $message;
    }

    /**
     * Format phone number for WhatsApp
     */
    protected function formatPhoneNumber($phone)
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add country code if not present (Kenya = 254)
        if (strlen($phone) === 10 && substr($phone, 0, 2) === '07') {
            $phone = '254' . substr($phone, 1);
        } elseif (strlen($phone) === 9 && substr($phone, 0, 1) === '7') {
            $phone = '254' . $phone;
        }
        
        return $phone;
    }

    /**
     * Generate WhatsApp web link
     */
    public function generateWhatsAppLink($phone, $message)
    {
        $encodedMessage = urlencode($message);
        return "https://wa.me/{$phone}?text={$encodedMessage}";
    }

    /**
     * Send via WhatsApp Business API (requires business account)
     */
    protected function sendViaAPI($phone, $message)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'to' => $phone,
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp notification sent successfully', [
                    'phone' => $phone,
                    'response' => $response->json()
                ]);
                return true;
            }

            Log::error('WhatsApp API error', [
                'phone' => $phone,
                'response' => $response->body()
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('WhatsApp notification failed', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}