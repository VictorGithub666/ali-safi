// database/migrations/2024_01_01_000002_create_vendors_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('business_name');
            $table->string('business_phone');
            $table->text('business_address');
            $table->decimal('latitude', 18, 16);
            $table->decimal('longitude', 18, 16);
            $table->json('operating_hours')->nullable();
            $table->boolean('is_open')->default(true);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_orders')->default(0);
            $table->decimal('wallet_balance', 10, 2)->default(0);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendors');
    }
};