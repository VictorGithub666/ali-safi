<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'vendor_id',
        'rider_id',
        'county',
        'sub_county',
        'ward',
        'delivery_address',
        'delivery_latitude',
        'delivery_longitude',
        'subtotal',
        'delivery_fee',
        'platform_fee',
        'total',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'phone',
        'mpesa_number',
        'confirmed_at',
        'prepared_at',
        'picked_up_at',
        'delivered_at',
        'cancelled_at',
        'cancellation_reason',
        'special_instructions',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'prepared_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($order) {
            $order->order_number = 'ORD-' . strtoupper(uniqid());
        });
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function tracking()
    {
        return $this->hasMany(OrderTracking::class);
    }

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    public function updateStatus($status, $notes = null)
    {
        $this->update(['status' => $status]);
        
        $this->tracking()->create([
            'status' => $status,
            'notes' => $notes,
        ]);

        // Update timestamp based on status
        $timestampField = $status . '_at';
        if (in_array($timestampField, ['confirmed_at', 'prepared_at', 'picked_up_at', 'delivered_at', 'cancelled_at'])) {
            $this->update([$timestampField => now()]);
        }

        // Send notification
        event(new OrderStatusUpdated($this));
    }
}