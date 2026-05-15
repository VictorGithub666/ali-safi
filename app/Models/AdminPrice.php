<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminPrice extends Model
{
    protected $table = "admin_prices";
    protected $fillable = [
        "product_id",
        "vendor_id",
        "customer_visible_price",
        "vendor_price",
        "markup",
        "base_delivery_fee",
        "is_active"
    ];

    protected $casts = [
        "customer_visible_price" => "decimal:2",
        "vendor_price" => "decimal:2",
        "markup" => "decimal:4",
        "base_delivery_fee" => "decimal:2",
        "is_active" => "boolean"
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function getMarkupPercentageAttribute()
    {
        if ($this->vendor_price == 0) {
            return 0;
        }
        return (($this->customer_visible_price - $this->vendor_price) / $this->vendor_price) * 100;
    }
}