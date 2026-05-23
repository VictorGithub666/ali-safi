<?php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class DeductProductStockOnDelivery
{
    public function handle(OrderStatusUpdated $event): void
    {
        $order = $event->order;
        
        // Only deduct stock when order status changes to 'delivered'
        if ($order->status === 'delivered') {
            $vendor = $order->vendor;
            
            foreach ($order->items as $item) {
                $vendorProduct = $vendor->products()
                    ->where('product_id', $item->product_id)
                    ->first();
                
                if ($vendorProduct) {
                    $currentStock = $vendorProduct->pivot->stock_quantity;
                    $newStock = max(0, $currentStock - $item->quantity);
                    
                    $vendor->products()->updateExistingPivot($item->product_id, [
                        'stock_quantity' => $newStock,
                        'is_available' => $newStock > 0 ? $vendorProduct->pivot->is_available : false,
                    ]);
                    
                    Log::info('Stock deducted via event listener', [
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'old_stock' => $currentStock,
                        'quantity_sold' => $item->quantity,
                        'new_stock' => $newStock,
                    ]);
                }
            }
        }
    }
}