<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_type',
        'vehicle_number',
        'license_number',
        'is_available',
        'current_latitude',
        'current_longitude',
        'rating',
        'total_deliveries',
        'wallet_balance',
        'is_verified',
        'last_location_update',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'is_verified' => 'boolean',
        'last_location_update' => 'datetime',
        'wallet_balance' => 'decimal:2',
        'total_deliveries' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function updateLocation($latitude, $longitude)
    {
        // Ensure we're storing as string to preserve full precision
        $this->update([
            'current_latitude' => (string) $latitude,
            'current_longitude' => (string) $longitude,
            'last_location_update' => now(),
        ]);
    }
}