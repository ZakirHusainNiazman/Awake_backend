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
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('seller_order_id')
                  ->nullable()
                  ->constrained('seller_orders')
                  ->onDelete('cascade')
                  ->after('order_id'); // Place it after order_id for clarity
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['seller_order_id']);
            $table->dropColumn('seller_order_id');
        });
    }
};
