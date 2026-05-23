<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mpesa_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('checkout_request_id')->unique();
            $table->string('merchant_request_id')->nullable();
            $table->string('phone_number');
            $table->decimal('amount', 12, 2);
            $table->string('currency')->default('KES');
            $table->string('status')->default('pending'); // pending, completed, failed, cancelled
            $table->string('mpesa_receipt_number')->nullable();
            $table->string('result_code')->nullable();
            $table->string('result_description')->nullable();
            $table->text('callback_response')->nullable();
            $table->timestamp('initiated_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('phone_number');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mpesa_transactions');
    }
};
