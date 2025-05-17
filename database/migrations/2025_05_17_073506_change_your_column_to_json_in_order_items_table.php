<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Run this BEFORE your migration that changes the column type:
        DB::table('order_items')->whereNull('attributes')->update(['attributes' => '{}']);

        Schema::table('order_items', function (Blueprint $table) {
            $table->json('attributes')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
             $table->text('attributes')->nullable()->change();
        });
    }
};
