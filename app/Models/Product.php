<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            // Set final_price same as base_price if not set
            if (empty($product->final_price) && !empty($product->base_price)) {
                $product->final_price = $product->base_price;
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            // Update final_price when base_price changes
            if ($product->isDirty('base_price')) {
                $product->final_price = $product->base_price;
            }
        });
    }

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

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')
                    ->withPivot('quantity', 'unit_price', 'size', 'total')
                    ->withTimestamps();
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Accessor for price
    public function getPriceAttribute()
    {
        return $this->final_price ?? $this->base_price;
    }

    public function getPriceForSize($size)
    {
        if ($this->size_prices && isset($this->size_prices[$size])) {
            return $this->size_prices[$size];
        }
        return $this->final_price;
    }

    /**
     * Get the customer-visible price for a specific vendor
     */
    public function getCustomerPriceForVendor($vendorId)
    {
        // Check if there's an admin price record for this product and vendor
        $adminPrice = \App\Models\AdminPrice::where('product_id', $this->id)
            ->where('vendor_id', $vendorId)
            ->where('is_active', true)
            ->first();
        
        if ($adminPrice && $adminPrice->customer_visible_price) {
            return $adminPrice->customer_visible_price;
        }
        
        // Fallback to product's final price
        return $this->final_price;
    }

    /**
     * Get the customer-visible price with vendor context for size-based pricing
     */
    public function getCustomerPriceForSizeAndVendor($size, $vendorId)
    {
        // First check if there's an admin price override
        $adminPrice = \App\Models\AdminPrice::where('product_id', $this->id)
            ->where('vendor_id', $vendorId)
            ->where('is_active', true)
            ->first();
        
        if ($adminPrice && $adminPrice->customer_visible_price) {
            return $adminPrice->customer_visible_price;
        }
        
        // Fallback to size-based pricing
        if ($this->size_prices && isset($this->size_prices[$size])) {
            return $this->size_prices[$size];
        }
        
        return $this->final_price;
    }

    /**
     * Get sizes as array
     */
    public function getSizesArrayAttribute()
    {
        return json_decode($this->sizes, true) ?? [];
    }

    /**
     * Get size prices as array
     */
    public function getSizePricesArrayAttribute()
    {
        return json_decode($this->size_prices, true) ?? [];
    }
}