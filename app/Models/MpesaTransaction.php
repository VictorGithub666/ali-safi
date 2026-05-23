<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MpesaTransaction extends Model
{
    use HasFactory;

    protected $table = 'mpesa_transactions';

    protected $fillable = [
        'order_id',
        'checkout_request_id',
        'merchant_request_id',
        'phone_number',
        'amount',
        'currency',
        'status',
        'mpesa_receipt_number',
        'result_code',
        'result_description',
        'callback_response',
        'initiated_at',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the order associated with this transaction
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope to get completed transactions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get failed transactions
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Check if transaction is successful
     */
    public function isSuccessful()
    {
        return $this->status === 'completed' && $this->result_code === '0';
    }

    /**
     * Mark transaction as completed
     */
    public function markAsCompleted($mpesaReceiptNumber = null, $resultCode = '0', $resultDescription = 'Success')
    {
        $this->update([
            'status' => 'completed',
            'mpesa_receipt_number' => $mpesaReceiptNumber,
            'result_code' => $resultCode,
            'result_description' => $resultDescription,
            'completed_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark transaction as failed
     */
    public function markAsFailed($resultCode = '1', $resultDescription = 'Transaction failed')
    {
        $this->update([
            'status' => 'failed',
            'result_code' => $resultCode,
            'result_description' => $resultDescription,
        ]);

        return $this;
    }

    /**
     * Mark transaction as cancelled
     */
    public function markAsCancelled()
    {
        $this->update([
            'status' => 'cancelled',
            'result_description' => 'User cancelled the transaction',
        ]);

        return $this;
    }
}
