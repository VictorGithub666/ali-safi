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
        $this->update([
            'current_latitude' => $latitude,
            'current_longitude' => $longitude,
            'last_location_update' => now(),
        ]);
    }
}