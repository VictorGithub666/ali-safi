// database/migrations/2024_01_01_000005_create_products_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('image');
            $table->json('gallery')->nullable();
            $table->decimal('base_price', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('final_price', 10, 2);
            $table->string('unit')->default('piece');
            $table->json('sizes')->nullable(); // For gas cylinders: ["6kg", "12kg", "25kg"]
            $table->json('size_prices')->nullable(); // {"6kg": 1200, "12kg": 2300}
            $table->boolean('is_active')->default(true);
            $table->integer('stock_quantity')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};