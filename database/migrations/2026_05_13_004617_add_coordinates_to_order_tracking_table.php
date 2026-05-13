<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            if (!Schema::hasColumn('order_tracking', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('status');
            }
            if (!Schema::hasColumn('order_tracking', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
        });
    }

    public function down()
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};