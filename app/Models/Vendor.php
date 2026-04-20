<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'business_phone',
        'business_address',
        'latitude',
        'longitude',
        'operating_hours',
        'is_open',
        'rating',
        'total_orders',
        'wallet_balance',
        'is_verified',
    ];

    protected $casts = [
        'operating_hours' => 'array',
        'is_open' => 'boolean',
        'is_verified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'vendor_products')
                    ->withPivot('stock_quantity', 'is_available', 'custom_price')
                    ->withTimestamps();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getDistanceAttribute()
    {
        // Calculate distance from customer location
        return 0; // Implement distance calculation
    }
}