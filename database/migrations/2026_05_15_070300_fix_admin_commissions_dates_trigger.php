<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Fix existing records
        DB::statement("
            UPDATE admin_commissions ac
            JOIN orders o ON o.id = ac.order_id
            SET 
                ac.created_at = o.created_at,
                ac.updated_at = o.updated_at
            WHERE ac.created_at != o.created_at
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
