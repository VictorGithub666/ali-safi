<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\AdminCommission;
use App\Models\Setting;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        $this->createAdminCommission($order);
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Only create/update commission when order is delivered or cancelled
        if ($order->isDirty('status')) {
            if ($order->status === 'delivered') {
                $this->updateAdminCommission($order);
            } elseif ($order->status === 'cancelled') {
                $this->cancelAdminCommission($order);
            }
        }
    }

    /**
     * Create admin commission record for the order
     */
    protected function createAdminCommission(Order $order): void
    {
        if (AdminCommission::where('order_id', $order->id)->exists()) {
            return;
        }

        $commissionPercentage = Setting::get('platform_fee_percentage', 5);
        
        $platformCommission = $order->subtotal * ($commissionPercentage / 100);
        $riderFee = $order->rider_id ? $order->delivery_fee : 0;
        $adminProfit = $platformCommission + $order->delivery_fee + $riderFee;
        $vendorAmount = $order->subtotal - $platformCommission;

        AdminCommission::create([
            'vendor_id' => $order->vendor_id,
            'order_id' => $order->id,
            'order_subtotal' => $order->subtotal,
            'vendor_amount' => $vendorAmount,
            'platform_commission' => $platformCommission,
            'commission_percentage' => $commissionPercentage,
            'delivery_fee' => $order->delivery_fee,
            'rider_fee' => $riderFee,
            'admin_profit' => $adminProfit,
            'status' => $order->status === 'delivered' ? 'settled' : 'pending',
            'created_at' => $order->created_at, // ADD THIS
            'updated_at' => $order->updated_at, // ADD THIS
        ]);

        \Log::info('AdminCommission created', [
            'order_id' => $order->id,
            'admin_profit' => $adminProfit,
            'created_at' => $order->created_at
        ]);
    }

    /**
     * Update admin commission when order is delivered
     */
    protected function updateAdminCommission(Order $order): void
    {
        $commission = AdminCommission::where('order_id', $order->id)->first();
        
        if ($commission) {
            $commission->update([
                'status' => 'settled',
                'admin_profit' => $commission->platform_commission + $commission->delivery_fee + $commission->rider_fee,
            ]);
        } else {
            $this->createAdminCommission($order);
        }
    }

    /**
     * Cancel admin commission when order is cancelled
     */
    protected function cancelAdminCommission(Order $order): void
    {
        AdminCommission::where('order_id', $order->id)->update(['status' => 'cancelled']);
    }
}