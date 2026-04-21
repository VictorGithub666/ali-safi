<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaService
{
    private $consumerKey;
    private $consumerSecret;
    private $businessCode;
    private $shortcode;
    private $passkey;
    private $commandId;
    private $accountReference;
    private $transactionDesc;
    private $apiUrl;
    private $accessToken;

    public function __construct()
    {
        $config = config('services.mpesa');
        
        $this->consumerKey = $config['consumer_key'];
        $this->consumerSecret = $config['consumer_secret'];
        $this->businessCode = $config['business_code'];
        $this->shortcode = $config['shortcode'];
        $this->passkey = $config['passkey'];
        $this->commandId = $config['command_id'];
        $this->accountReference = $config['account_reference'];
        $this->transactionDesc = $config['transaction_desc'];
        $this->apiUrl = $config['api_url'];
    }

    /**
     * Get access token from M-Pesa API
     */
    public function getAccessToken()
    {
        try {
            $url = $this->apiUrl . '/oauth/v1/generate?grant_type=client_credentials';
            
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->get($url);

            if ($response->successful()) {
                $this->accessToken = $response['access_token'];
                return $this->accessToken;
            }

            Log::error('Failed to get M-Pesa access token', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception getting M-Pesa access token', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Initiate STK Push for M-Pesa payment
     */
    public function initiateStkPush($phoneNumber, $amount, $orderId, $callbackUrl = null)
    {
        try {
            // Validate phone number format
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);
            if (!$phoneNumber) {
                return [
                    'success' => false,
                    'message' => 'Invalid phone number format. Must start with 254.',
                ];
            }

            // Get access token
            $token = $this->getAccessToken();
            if (!$token) {
                return [
                    'success' => false,
                    'message' => 'Failed to authenticate with M-Pesa API',
                ];
            }

            // Generate timestamp
            $timestamp = date('YmdHis');

            // Generate password
            $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

            // Set default callback URL
            if (!$callbackUrl) {
                $callbackUrl = route('mpesa.callback');
            }

            // Prepare STK Push request
            $url = $this->apiUrl . '/mpesa/stkpush/v1/processrequest';

            $payload = [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => $this->commandId,
                'Amount' => round($amount),
                'PartyA' => $phoneNumber,
                'PartyB' => $this->businessCode,
                'PhoneNumber' => $phoneNumber,
                'CallBackURL' => $callbackUrl,
                'AccountReference' => $this->accountReference . '-' . $orderId,
                'TransactionDesc' => $this->transactionDesc . ' ' . $orderId,
            ];

            $response = Http::withToken($token)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $payload);

            Log::info('M-Pesa STK Push initiated', [
                'phone' => $phoneNumber,
                'amount' => $amount,
                'order_id' => $orderId,
                'response_status' => $response->status(),
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'M-Pesa prompt sent successfully',
                    'data' => $response->json(),
                ];
            }

            Log::error('M-Pesa STK Push failed', [
                'phone' => $phoneNumber,
                'amount' => $amount,
                'order_id' => $orderId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send M-Pesa prompt',
                'error' => $response->json(),
            ];

        } catch (\Exception $e) {
            Log::error('Exception in STK Push', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error processing M-Pesa request',
            ];
        }
    }

    /**
     * Query STK Push transaction status
     */
    public function queryTransaction($checkoutRequestId)
    {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                return ['success' => false, 'message' => 'Authentication failed'];
            }

            $timestamp = date('YmdHis');
            $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

            $url = $this->apiUrl . '/mpesa/stkpushquery/v1/query';

            $payload = [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'CheckoutRequestID' => $checkoutRequestId,
            ];

            $response = Http::withToken($token)
                ->post($url, $payload);

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];

        } catch (\Exception $e) {
            Log::error('Exception querying transaction', [
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Error querying transaction'];
        }
    }

    /**
     * Format and validate phone number
     * Converts to 254XXXXXXXXX format
     */
    private function formatPhoneNumber($phone)
    {
        // Remove any spaces or special characters
        $phone = preg_replace('/\D/', '', $phone);

        // If starts with 0, replace with 254
        if (strlen($phone) == 10 && substr($phone, 0, 1) == '0') {
            $phone = '254' . substr($phone, 1);
        }

        // Must start with 254 and have 12 digits total
        if (strlen($phone) == 12 && substr($phone, 0, 3) == '254') {
            return $phone;
        }

        // If already in 254format but wrong length, return null
        if (substr($phone, 0, 3) == '254' && strlen($phone) != 12) {
            return null;
        }

        return null;
    }

    /**
     * Handle M-Pesa callback
     */
    public function handleCallback($data)
    {
        try {
            Log::info('M-Pesa callback received', $data);

            $result = $data['Body']['stkCallback']['CallbackMetadata']['Item'] ?? [];
            $metadata = [];

            foreach ($result as $item) {
                $metadata[$item['Name']] = $item['Value'];
            }

            return [
                'success' => true,
                'code' => $data['Body']['stkCallback']['ResultCode'],
                'message' => $data['Body']['stkCallback']['ResultDesc'],
                'amount' => $metadata['Amount'] ?? null,
                'receipt_number' => $metadata['MpesaReceiptNumber'] ?? null,
                'transaction_date' => $metadata['TransactionDate'] ?? null,
                'phone' => $metadata['PhoneNumber'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Error handling M-Pesa callback', [
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Error processing callback'];
        }
    }
}
