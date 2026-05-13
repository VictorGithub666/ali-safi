<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Fix vendors table
        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('latitude', 18, 16)->nullable()->change();
            $table->decimal('longitude', 18, 16)->nullable()->change();
        });

        // Fix riders table
        Schema::table('riders', function (Blueprint $table) {
            $table->decimal('current_latitude', 18, 16)->nullable()->change();
            $table->decimal('current_longitude', 18, 16)->nullable()->change();
        });

        // Fix orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('delivery_latitude', 18, 16)->change();
            $table->decimal('delivery_longitude', 18, 16)->change();
        });

        // Fix order_tracking table
        Schema::table('order_tracking', function (Blueprint $table) {
            $table->decimal('latitude', 18, 16)->nullable()->change();
            $table->decimal('longitude', 18, 16)->nullable()->change();
        });
    }

    public function down()
    {
        // Revert to previous precision if needed
        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->change();
            $table->decimal('longitude', 11, 8)->nullable()->change();
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->decimal('current_latitude', 10, 8)->nullable()->change();
            $table->decimal('current_longitude', 11, 8)->nullable()->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('delivery_latitude', 10, 8)->change();
            $table->decimal('delivery_longitude', 11, 8)->change();
        });

        Schema::table('order_tracking', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->change();
            $table->decimal('longitude', 11, 8)->nullable()->change();
        });
    }
};