<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update latitude and longitude columns to accept 16 decimal places
     * Changing from decimal(10,8) and decimal(11,8) to decimal(18,16)
     */
    public function up()
    {
        // Update vendors table
        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('latitude', 18, 16)->nullable()->change();
            $table->decimal('longitude', 18, 16)->nullable()->change();
        });

        // Update riders table
        Schema::table('riders', function (Blueprint $table) {
            $table->decimal('current_latitude', 18, 16)->nullable()->change();
            $table->decimal('current_longitude', 18, 16)->nullable()->change();
        });

        // Update orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('delivery_latitude', 18, 16)->change();
            $table->decimal('delivery_longitude', 18, 16)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Revert vendors table
        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->change();
            $table->decimal('longitude', 11, 8)->nullable()->change();
        });

        // Revert riders table
        Schema::table('riders', function (Blueprint $table) {
            $table->decimal('current_latitude', 10, 8)->nullable()->change();
            $table->decimal('current_longitude', 11, 8)->nullable()->change();
        });

        // Revert orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('delivery_latitude', 10, 8)->change();
            $table->decimal('delivery_longitude', 11, 8)->change();
        });
    }
};
