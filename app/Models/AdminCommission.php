<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminCommission extends Model
{
    protected $table = 'admin_commissions';
    protected $fillable = [
        'vendor_id',
        'order_id',
        'order_subtotal',
        'vendor_amount',
        'platform_commission',
        'commission_percentage',
        'delivery_fee',
        'rider_fee',
        'admin_profit',
        'status'
    ];

    protected $casts = [
        'order_subtotal' => 'decimal:2',
        'vendor_amount' => 'decimal:2',
        'platform_commission' => 'decimal:2',
        'commission_percentage' => 'decimal:4',
        'delivery_fee' => 'decimal:2',
        'rider_fee' => 'decimal:2',
        'admin_profit' => 'decimal:2'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->calculateProfit();
        });
        
        static::updating(function ($model) {
            $model->calculateProfit();
        });
    }

    public function calculateProfit()
    {
        $this->admin_profit = $this->platform_commission + $this->delivery_fee + $this->rider_fee;
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
