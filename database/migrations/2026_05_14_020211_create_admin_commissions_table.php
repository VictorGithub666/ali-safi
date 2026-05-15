<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('order_id');
            $table->decimal('order_subtotal', 10, 2);
            $table->decimal('vendor_amount', 10, 2)->comment('Amount vendor receives');
            $table->decimal('platform_commission', 10, 2)->comment('Admin platform fee');
            $table->decimal('commission_percentage', 5, 2)->comment('Commission % at time of order');
            $table->decimal('delivery_fee', 10, 2);
            $table->decimal('rider_fee', 10, 2);
            $table->decimal('admin_profit', 10, 2)->comment('Total admin profit from order');
            $table->enum('status', ['pending', 'settled', 'cancelled'])->default('pending');
            $table->timestamps();
            
            $table->foreign('vendor_id')->references('id')->on('vendors')->cascadeOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->unique('order_id');
            $table->index(['vendor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_commissions');
    }
};
