<?php

namespace App\Models;


use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture',
        'phone',
        'user_type',
        'is_active',
        'is_verified',
        'verification_token',
        'google_id',
        'latitude',
        'longitude',
        'address',
        'city',
        'postal_code',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
    ];

    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }

    public function rider()
    {
        return $this->hasOne(Rider::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function cart()
    {
        return $this->hasMany(Cart::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function isAdmin()
    {
        return $this->user_type === 'admin';
    }

    public function isVendor()
    {
        return $this->user_type === 'vendor';
    }

    public function isRider()
    {
        return $this->user_type === 'rider';
    }

    public function isCustomer()
    {
        return $this->user_type === 'customer';
    }
}