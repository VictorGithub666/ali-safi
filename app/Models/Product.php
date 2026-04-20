<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'image',
        'gallery',
        'base_price',
        'delivery_fee',
        'final_price',
        'unit',
        'sizes',
        'size_prices',
        'is_active',
        'stock_quantity',
    ];

    protected $casts = [
        'gallery' => 'array',
        'sizes' => 'array',
        'size_prices' => 'array',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'vendor_products')
                    ->withPivot('stock_quantity', 'is_available', 'custom_price')
                    ->withTimestamps();
    }

    public function getPriceForSize($size)
    {
        if ($this->size_prices && isset($this->size_prices[$size])) {
            return $this->size_prices[$size];
        }
        return $this->final_price;
    }
}