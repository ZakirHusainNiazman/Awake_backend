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
        Schema::create('product_stats', function (Blueprint $table) {
            $table->id();
             $table->foreignUuid('product_id')
            ->constrained() // Shortcut for references('id')->on('products')
            ->cascadeOnDelete();
            $table->enum('event', ['view','wishlist', 'cart', 'purchase']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stats');
    }
};
