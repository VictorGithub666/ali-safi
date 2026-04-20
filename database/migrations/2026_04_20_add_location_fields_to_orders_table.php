<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('county')->nullable()->after('delivery_address');
            $table->string('sub_county')->nullable()->after('county');
            $table->string('ward')->nullable()->after('sub_county');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['county', 'sub_county', 'ward']);
        });
    }
};
