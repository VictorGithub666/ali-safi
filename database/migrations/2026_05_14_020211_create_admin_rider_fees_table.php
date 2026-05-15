<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_rider_fees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rider_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->decimal('base_fee', 10, 2)->comment('Admin-set base delivery fee');
            $table->decimal('per_km_fee', 10, 2)->comment('Admin-set per km fee');
            $table->decimal('distance_km', 10, 2)->nullable();
            $table->decimal('calculated_fee', 10, 2)->nullable()->comment('Total calculated fee');
            $table->decimal('bonus', 10, 2)->default(0)->comment('Admin bonus for special assignments');
            $table->enum('status', ['pending', 'accepted', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
            
            $table->foreign('rider_id')->references('id')->on('riders')->cascadeOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_rider_fees');
    }
};
