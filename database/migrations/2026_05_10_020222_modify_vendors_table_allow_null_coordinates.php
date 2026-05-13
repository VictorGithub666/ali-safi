<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('latitude', 18, 16)->nullable()->change();
            $table->decimal('longitude', 18, 16)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('latitude', 18, 16)->nullable(false)->change();
            $table->decimal('longitude', 18, 16)->nullable(false)->change();
        });
    }
};