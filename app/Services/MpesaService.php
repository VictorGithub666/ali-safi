<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaService
{
    private $consumerKey;
    private $consumerSecret;
    private $shortcode;
    private $passkey;
    private $commandId;
    private $accountReference;
    private $transactionDesc;
    private $apiUrl;
    private $accessToken;

    public function __construct()
    {
        $this->consumerKey = config('services.mpesa.consumer_key');
        $this->consumerSecret = config('services.mpesa.consumer_secret');
        $this->shortcode = config('services.mpesa.shortcode');
        $this->passkey = config('services.mpesa.passkey');
        $this->commandId = config('services.mpesa.command_id', 'CustomerPayBillOnline');
        $this->accountReference = config('services.mpesa.account_reference', 'ALISAFI');
        $this->transactionDesc = config('services.mpesa.transaction_desc', 'AliSafi Order');
        $this->apiUrl = config('services.mpesa.api_url', 'https://sandbox.safaricom.co.ke');
    }

    /**
     * Get access token from M-Pesa API
     */
    public function getAccessToken()
    {
        try {
            $url = $this->apiUrl . '/oauth/v1/generate?grant_type=client_credentials';
            
            Log::info('Requesting M-Pesa access token', [
                'url' => $url,
                'consumer_key' => substr($this->consumerKey, 0, 10) . '...'
            ]);
            
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->get($url);

            Log::info('M-Pesa token response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $this->accessToken = $response['access_token'];
                Log::info('M-Pesa access token obtained successfully');
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
    public function initiateStkPush($phoneNumber, $amount, $orderNumber, $callbackUrl = null)
    {
        try {
            // Format and validate phone number
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);
            if (!$phoneNumber) {
                return [
                    'success' => false,
                    'message' => 'Invalid phone number format. Must be a valid Safaricom number starting with 254.',
                ];
            }

            // Get access token
            $token = $this->getAccessToken();
            if (!$token) {
                return [
                    'success' => false,
                    'message' => 'Failed to authenticate with M-Pesa API. Check your credentials.',
                ];
            }

            // Generate timestamp in format YYYYMMDDHHmmss
            $timestamp = date('YmdHis');
            
            // Generate password - CRITICAL: Must be Base64(Shortcode + Passkey + Timestamp)
            $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
            
            // Format amount to whole number (M-Pesa doesn't accept decimals in sandbox)
            $formattedAmount = (string) round($amount);
            
            // Set callback URL
            if (!$callbackUrl) {
                $callbackUrl = route('mpesa.callback');
            }
            
            // Ensure callback URL is HTTPS for production
            if (app()->environment('production') && !str_starts_with($callbackUrl, 'https://')) {
                $callbackUrl = str_replace('http://', 'https://', $callbackUrl);
            }
            
            // Truncate AccountReference to max 12 characters
            $accountReference = substr($this->accountReference . '-' . $orderNumber, 0, 12);
            
            // Truncate TransactionDesc to max 13 characters
            $transactionDesc = substr($this->transactionDesc . ' ' . $orderNumber, 0, 13);

            // Prepare STK Push request according to official documentation
            $url = $this->apiUrl . '/mpesa/stkpush/v1/processrequest';
            
            $payload = [
                'BusinessShortCode' => (int) $this->shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => $this->commandId,
                'Amount' => $formattedAmount,
                'PartyA' => $phoneNumber,
                'PartyB' => (int) $this->shortcode,
                'PhoneNumber' => $phoneNumber,
                'CallBackURL' => $callbackUrl,
                'AccountReference' => $accountReference,
                'TransactionDesc' => $transactionDesc,
            ];

            Log::info('M-Pesa STK Push Request', [
                'url' => $url,
                'payload' => array_merge($payload, ['Password' => substr($password, 0, 20) . '...']),
                'token' => substr($token, 0, 20) . '...'
            ]);

            $response = Http::withToken($token)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $payload);

            $responseData = $response->json();
            
            Log::info('M-Pesa STK Push Response', [
                'status' => $response->status(),
                'body' => $responseData
            ]);

            if ($response->successful() && isset($responseData['ResponseCode']) && $responseData['ResponseCode'] == '0') {
                return [
                    'success' => true,
                    'message' => 'M-Pesa prompt sent successfully. Please check your phone and enter your PIN.',
                    'data' => $responseData,
                ];
            }

            // Handle specific error cases
            $errorMessage = $responseData['errorMessage'] ?? $responseData['ResponseDescription'] ?? 'Failed to send M-Pesa prompt';
            
            Log::error('M-Pesa STK Push failed', [
                'phone' => $phoneNumber,
                'amount' => $formattedAmount,
                'order_number' => $orderNumber,
                'status' => $response->status(),
                'error' => $errorMessage,
                'full_response' => $responseData
            ]);

            return [
                'success' => false,
                'message' => $errorMessage,
                'error' => $responseData,
            ];

        } catch (\Exception $e) {
            Log::error('Exception in STK Push', [
                'order_number' => $orderNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error processing M-Pesa request: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Format and validate phone number
     * Converts to 254XXXXXXXXX format as required by Safaricom
     */
    private function formatPhoneNumber($phone)
    {
        // Remove any spaces or special characters
        $phone = preg_replace('/\D/', '', $phone);

        // Handle different formats
        if (strlen($phone) == 9 && substr($phone, 0, 1) == '7') {
            // Format: 7XXXXXXXX
            $phone = '254' . $phone;
        } elseif (strlen($phone) == 10 && substr($phone, 0, 1) == '0') {
            // Format: 07XXXXXXXX
            $phone = '254' . substr($phone, 1);
        } elseif (strlen($phone) == 10 && substr($phone, 0, 1) == '7') {
            // Format: 7XXXXXXXXX
            $phone = '254' . $phone;
        }

        // Validate final format
        if (strlen($phone) == 12 && substr($phone, 0, 3) == '254' && substr($phone, 3, 1) == '7') {
            return $phone;
        }

        return null;
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
                'BusinessShortCode' => (int) $this->shortcode,
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
     * Handle M-Pesa callback
     */
    public function handleCallback($data)
    {
        try {
            Log::info('M-Pesa callback received', $data);

            $stkCallback = $data['Body']['stkCallback'] ?? [];
            $resultCode = $stkCallback['ResultCode'] ?? null;
            $resultDesc = $stkCallback['ResultDesc'] ?? null;
            $merchantRequestId = $stkCallback['MerchantRequestID'] ?? null;
            $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? null;
            
            $metadata = [];
            if (isset($stkCallback['CallbackMetadata']['Item'])) {
                foreach ($stkCallback['CallbackMetadata']['Item'] as $item) {
                    $metadata[$item['Name']] = $item['Value'] ?? null;
                }
            }

            return [
                'success' => true,
                'result_code' => $resultCode,
                'result_desc' => $resultDesc,
                'merchant_request_id' => $merchantRequestId,
                'checkout_request_id' => $checkoutRequestId,
                'amount' => $metadata['Amount'] ?? null,
                'receipt_number' => $metadata['MpesaReceiptNumber'] ?? null,
                'transaction_date' => $metadata['TransactionDate'] ?? null,
                'phone' => $metadata['PhoneNumber'] ?? null,
                'metadata' => $metadata
            ];

        } catch (\Exception $e) {
            Log::error('Error handling M-Pesa callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ['success' => false, 'message' => 'Error processing callback'];
        }
    }
}