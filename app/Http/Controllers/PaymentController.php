<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\MpesaTransaction;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $mpesaService;

    public function __construct(MpesaService $mpesaService)
    {
        $this->mpesaService = $mpesaService;
    }

    /**
     * Initiate M-Pesa payment for an order
     */
    public function initiateMpesaPayment(Order $order, Request $request)
    {
        // Authorize that the customer can only pay for their own orders
        if ($order->customer_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Validate phone number
        $request->validate([
            'phone_number' => 'required|string|regex:/^(254|0)?(7\d{8})$/'
        ]);

        $phoneNumber = $request->phone_number;

        try {
            // Create M-Pesa transaction record
            $transaction = MpesaTransaction::create([
                'order_id' => $order->id,
                'checkout_request_id' => uniqid('CHK_'),
                'phone_number' => $phoneNumber,
                'amount' => $order->total,
                'currency' => 'KES',
                'status' => 'pending',
                'initiated_at' => now(),
            ]);

            // Initiate STK Push
            $result = $this->mpesaService->initiateStkPush(
                $phoneNumber,
                $order->total,
                $order->order_number,
                route('mpesa.callback')
            );

            if ($result['success']) {
                // Update transaction with merchant request ID
                if (isset($result['data']['MerchantRequestID'])) {
                    $transaction->update([
                        'merchant_request_id' => $result['data']['MerchantRequestID'],
                    ]);
                }

                // Update order with M-Pesa number
                $order->update([
                    'mpesa_number' => $phoneNumber,
                    'payment_method' => 'mpesa',
                ]);

                Log::info('M-Pesa STK Push initiated successfully', [
                    'order_id' => $order->id,
                    'transaction_id' => $transaction->id,
                    'phone' => $phoneNumber,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'M-Pesa prompt sent successfully. Please enter your PIN on your phone.',
                    'transaction_id' => $transaction->id,
                ]);
            } else {
                // Mark transaction as failed
                $transaction->markAsFailed('1', $result['message']);

                Log::error('Failed to initiate M-Pesa STK Push', [
                    'order_id' => $order->id,
                    'transaction_id' => $transaction->id,
                    'error' => $result['message'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Exception initiating M-Pesa payment', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error initiating payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle M-Pesa STK Push callback
     */
    public function mpesaCallback(Request $request)
    {
        try {
            $data = $request->all();
            
            Log::info('M-Pesa callback received', $data);

            // Parse the callback data
            $callbackData = $data['Body']['stkCallback'] ?? [];
            $resultCode = $callbackData['ResultCode'] ?? null;
            $resultDesc = $callbackData['ResultDesc'] ?? null;
            $checkoutRequestId = $callbackData['CheckoutRequestID'] ?? null;
            $merchantRequestId = $callbackData['MerchantRequestID'] ?? null;

            // Extract metadata
            $metadata = [];
            if (isset($callbackData['CallbackMetadata']['Item'])) {
                foreach ($callbackData['CallbackMetadata']['Item'] as $item) {
                    $metadata[$item['Name']] = $item['Value'] ?? null;
                }
            }

            // Find the M-Pesa transaction
            $transaction = MpesaTransaction::where('checkout_request_id', $checkoutRequestId)->first();
            
            if (!$transaction) {
                Log::warning('M-Pesa transaction not found for callback', [
                    'checkout_request_id' => $checkoutRequestId,
                    'merchant_request_id' => $merchantRequestId,
                ]);
                
                return response()->json([
                    'ResultCode' => 1,
                    'ResultDesc' => 'Transaction not found'
                ]);
            }

            $order = $transaction->order;

            if (!$order) {
                Log::warning('Order not found for M-Pesa transaction', [
                    'transaction_id' => $transaction->id,
                    'checkout_request_id' => $checkoutRequestId,
                ]);
                
                return response()->json([
                    'ResultCode' => 1,
                    'ResultDesc' => 'Order not found'
                ]);
            }

            // Store callback response
            $transaction->update([
                'callback_response' => json_encode($callbackData),
                'result_code' => $resultCode,
                'result_description' => $resultDesc,
            ]);

            // Handle successful payment (ResultCode 0 = Success)
            if ($resultCode == 0) {
                $mpesaReceiptNumber = $metadata['MpesaReceiptNumber'] ?? null;
                $transactionAmount = $metadata['Amount'] ?? null;
                $transactionDate = $metadata['TransactionDate'] ?? null;

                // Mark transaction as completed
                $transaction->markAsCompleted(
                    $mpesaReceiptNumber,
                    '0',
                    'Payment successful'
                );

                // Update order payment status
                $order->update([
                    'payment_status' => 'paid',
                    'payment_reference' => $mpesaReceiptNumber,
                    'payment_method' => 'mpesa',
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);

                Log::info('M-Pesa payment confirmed', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'transaction_id' => $transaction->id,
                    'receipt' => $mpesaReceiptNumber,
                    'amount' => $transactionAmount,
                ]);

                // TODO: Send confirmation SMS/WhatsApp to customer
                // TODO: Notify vendor about confirmed payment
                // TODO: Trigger order processing automation

            } else {
                // Payment failed or was cancelled
                if ($resultCode == 1032) {
                    // User cancelled
                    $transaction->markAsCancelled();
                } else {
                    // Payment failed
                    $transaction->markAsFailed($resultCode, $resultDesc);
                }

                $order->update([
                    'payment_status' => 'failed',
                    'payment_reference' => $checkoutRequestId,
                ]);

                Log::warning('M-Pesa payment failed', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'transaction_id' => $transaction->id,
                    'result_code' => $resultCode,
                    'result_desc' => $resultDesc,
                ]);

                // TODO: Send failure notification to customer
            }

            // Return success response to M-Pesa
            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'The service request has been received successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing M-Pesa callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Error processing callback'
            ], 500);
        }
    }

    /**
     * Resend M-Pesa prompt for a pending order
     */
    public function resendMpesaPrompt(Order $order)
    {
        // Authorize that the customer can only resend for their own orders
        if ($order->customer_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized action');
        }

        // Check if payment is still pending
        if ($order->payment_status !== 'pending' || !$order->mpesa_number) {
            return redirect()->back()->with('error', 'This order is not pending M-Pesa payment');
        }

        try {
            // Initiate new STK Push
            $result = $this->mpesaService->initiateStkPush(
                $order->mpesa_number,
                $order->total,
                $order->order_number,
                route('mpesa.callback')
            );

            if ($result['success']) {
                // Create new transaction record
                MpesaTransaction::create([
                    'order_id' => $order->id,
                    'checkout_request_id' => $result['data']['CheckoutRequestID'] ?? uniqid('CHK_'),
                    'merchant_request_id' => $result['data']['MerchantRequestID'] ?? null,
                    'phone_number' => $order->mpesa_number,
                    'amount' => $order->total,
                    'currency' => 'KES',
                    'status' => 'pending',
                    'initiated_at' => now(),
                ]);

                return redirect()->back()->with('success', 'M-Pesa prompt resent successfully');
            } else {
                return redirect()->back()->with('error', 'Failed to resend M-Pesa prompt: ' . $result['message']);
            }
        } catch (\Exception $e) {
            Log::error('Error resending M-Pesa prompt', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Error resending M-Pesa prompt');
        }
    }

    /**
     * Get payment status for an order
     */
    public function getPaymentStatus(Order $order)
    {
        // Authorize
        if ($order->customer_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $transaction = $order->mpesaTransactions()->latest()->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'No payment found',
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => $transaction->status,
            'payment_status' => $order->payment_status,
            'receipt' => $transaction->mpesa_receipt_number,
            'message' => $transaction->result_description,
        ]);
    }
}
}
