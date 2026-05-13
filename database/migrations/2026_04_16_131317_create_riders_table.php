// database/migrations/2024_01_01_000003_create_riders_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('riders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('vehicle_type')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('license_number')->nullable();
            $table->boolean('is_available')->default(false);
            $table->decimal('current_latitude', 18, 16)->nullable();
            $table->decimal('current_longitude', 18, 16)->nullable();
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_deliveries')->default(0);
            $table->decimal('wallet_balance', 10, 2)->default(0);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('last_location_update')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('riders');
    }
};