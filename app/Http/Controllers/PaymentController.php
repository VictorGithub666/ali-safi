<?php

namespace App\Http\Controllers;

use App\Models\Order;
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

            // Extract metadata
            $metadata = [];
            if (isset($callbackData['CallbackMetadata']['Item'])) {
                foreach ($callbackData['CallbackMetadata']['Item'] as $item) {
                    $metadata[$item['Name']] = $item['Value'];
                }
            }

            // Get order by checkout request ID or account reference
            $accountRef = $callbackData['CallbackMetadata']['Item'][3]['Value'] ?? null;
            
            // Extract order number from account reference (format: ALISAFI-ORD-XXXXX)
            $orderNumber = null;
            if ($accountRef && strpos($accountRef, 'ORD-') !== false) {
                $parts = explode('-', $accountRef);
                $orderNumber = end($parts);
            }

            $order = $orderNumber ? Order::where('order_number', $orderNumber)->first() : null;

            if (!$order) {
                Log::warning('Order not found for callback', [
                    'checkout_request_id' => $checkoutRequestId,
                    'account_reference' => $accountRef,
                ]);
                
                return response()->json([
                    'ResultCode' => 1,
                    'ResultDesc' => 'Order not found'
                ]);
            }

            // Handle successful payment (ResultCode 0 = Success)
            if ($resultCode == 0) {
                $order->update([
                    'payment_status' => 'paid',
                    'payment_reference' => $metadata['MpesaReceiptNumber'] ?? $checkoutRequestId,
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);

                Log::info('M-Pesa payment confirmed', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'receipt' => $metadata['MpesaReceiptNumber'] ?? null,
                    'amount' => $metadata['Amount'] ?? null,
                ]);

                // TODO: Send confirmation SMS to customer
                // TODO: Notify vendor about paid order
                // TODO: Trigger order processing automation

            } else {
                // Payment failed or was cancelled
                $order->update([
                    'payment_status' => 'failed',
                    'payment_reference' => $checkoutRequestId,
                ]);

                Log::warning('M-Pesa payment failed', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
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
    public function resendMpesaPrompt(Order $order, MpesaService $mpesaService)
    {
        // Authorize that the customer can only resend for their own orders
        if ($order->customer_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized action');
        }

        // Check if payment is still pending
        if ($order->payment_status !== 'pending' || !$order->mpesa_number) {
            return redirect()->back()->with('error', 'This order is not pending M-Pesa payment');
        }

        // Initiate new STK Push
        $result = $mpesaService->initiateStkPush(
            $order->mpesa_number,
            $order->total,
            $order->order_number
        );

        if ($result['success']) {
            return redirect()->back()->with('success', 'M-Pesa prompt resent successfully');
        } else {
            return redirect()->back()->with('error', 'Failed to resend M-Pesa prompt: ' . $result['message']);
        }
    }
}
