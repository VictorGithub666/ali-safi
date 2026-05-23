<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $apiUrl;
    protected $apiKey;
    protected $instanceId; // For WhatsApp Business API or services like Ultramsg, WATI, etc.

    public function __construct()
    {
        // Configuration for WhatsApp Business API or third-party service
        $this->apiUrl = env('WHATSAPP_API_URL', 'https://api.whatsapp.com/send');
        $this->apiKey = env('WHATSAPP_API_KEY', '');
        $this->instanceId = env('WHATSAPP_INSTANCE_ID', '');
    }

    /**
     * Send WhatsApp message directly via API (if configured)
     */
    public function sendMessage($phoneNumber, $message)
    {
        $phone = $this->formatPhoneNumber($phoneNumber);
        
        // Example using Ultramsg API (popular WhatsApp gateway)
        if ($this->apiUrl && $this->apiKey && $this->instanceId) {
            return $this->sendViaUltramsg($phone, $message);
        }
        
        // Fallback to generating link
        return $this->generateWhatsAppLink($phone, $message);
    }
    
    /**
     * Send via Ultramsg API
     */
    protected function sendViaUltramsg($phone, $message)
    {
        try {
            $response = Http::post($this->apiUrl, [
                'token' => $this->apiKey,
                'to' => $phone,
                'body' => $message,
                'priority' => 1,
                'referenceId' => ''
            ]);
            
            if ($response->successful() && isset($response['sent'])) {
                Log::info('WhatsApp message sent successfully via Ultramsg', [
                    'phone' => $phone,
                    'response' => $response->json()
                ]);
                return ['success' => true, 'data' => $response->json()];
            }
            
            Log::error('Ultramsg API error', [
                'phone' => $phone,
                'response' => $response->body()
            ]);
            return ['success' => false, 'error' => $response->body()];
            
        } catch (\Exception $e) {
            Log::error('WhatsApp send failed', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send order notification to vendor
     */
    public function sendOrderNotification($order, $vendorPhone)
    {
        $message = $this->formatOrderMessage($order);
        $phone = $this->formatPhoneNumber($vendorPhone);
        
        // Try to send via API first
        $result = $this->sendMessage($phone, $message);
        
        if ($result['success']) {
            return $result;
        }
        
        // Fallback to WhatsApp Web link
        return $this->generateWhatsAppLink($phone, $message);
    }

    /**
     * Format order details into a readable message
     */
    protected function formatOrderMessage($order)
    {
        $items = $order->items->map(function($item) {
            $sizeText = $item->size ? " ({$item->size})" : "";
            return "• {$item->quantity}x {$item->product->name}{$sizeText} - KES " . number_format($item->unit_price * $item->quantity, 2);
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
     * Generate WhatsApp web link (works without API)
     */
    public function generateWhatsAppLink($phone, $message)
    {
        $encodedMessage = urlencode($message);
        return "https://wa.me/{$phone}?text={$encodedMessage}";
    }
}