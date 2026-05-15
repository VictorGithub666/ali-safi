<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (!Schema::hasColumn('vendors', 'wallet_balance')) {
                $table->decimal('wallet_balance', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('vendors', 'total_orders')) {
                $table->integer('total_orders')->default(0);
            }
        });
    }

    public function down()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['wallet_balance', 'total_orders']);
        });
    }
};