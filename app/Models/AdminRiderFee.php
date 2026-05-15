<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminRiderFee extends Model
{
    protected $table = 'admin_rider_fees';
    protected $fillable = [
        'rider_id',
        'order_id',
        'base_fee',
        'per_km_fee',
        'distance_km',
        'calculated_fee',
        'bonus',
        'status'
    ];

    protected $casts = [
        'base_fee' => 'decimal:2',
        'per_km_fee' => 'decimal:2',
        'distance_km' => 'decimal:4',
        'calculated_fee' => 'decimal:2',
        'bonus' => 'decimal:2'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->calculateTotalFee();
        });
        
        static::updating(function ($model) {
            $model->calculateTotalFee();
        });
    }

    public function calculateTotalFee()
    {
        $this->calculated_fee = $this->base_fee + ($this->distance_km * $this->per_km_fee) + $this->bonus;
    }

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
