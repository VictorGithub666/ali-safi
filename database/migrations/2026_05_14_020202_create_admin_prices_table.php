<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('vendor_id');
            $table->decimal('customer_visible_price', 10, 2)->comment('Price shown to customers');
            $table->decimal('vendor_price', 10, 2)->comment('What vendor set as their price');
            $table->decimal('markup', 10, 2)->default(0)->comment('Admin markup on vendor price');
            $table->decimal('base_delivery_fee', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('vendor_id')->references('id')->on('vendors')->cascadeOnDelete();
            $table->unique(['product_id', 'vendor_id']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_prices');
    }
};
