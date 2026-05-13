// database/migrations/2024_01_01_000007_create_orders_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->foreignId('rider_id')->nullable()->constrained('riders');
            
            // Delivery addresses
            $table->text('delivery_address');
            $table->decimal('delivery_latitude', 18, 16);
            $table->decimal('delivery_longitude', 18, 16);
            
            // Order details
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2);
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            
            // Status tracking
            $table->enum('status', [
                'pending', 'confirmed', 'preparing', 'ready_for_pickup',
                'picked_up', 'in_transit', 'delivered', 'cancelled'
            ])->default('pending');
            
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->enum('payment_method', ['cash', 'mpesa', 'card'])->default('cash');
            $table->string('payment_reference')->nullable();
            
            // Timestamps for tracking
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->text('cancellation_reason')->nullable();
            $table->text('special_instructions')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};