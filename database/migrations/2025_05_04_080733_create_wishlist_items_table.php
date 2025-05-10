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
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wishlist_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->uuid('variant_id')->nullable();
            $table->foreign('variant_id')->references('id')->on('product_variants')->cascadeOnDelete();
            $table->timestamps();

            // Enforce uniqueness of wishlist, product, and variant combination
            $table->unique(['wishlist_id', 'product_id', 'variant_id'], 'wishlist_unique_product_variant');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlist_items');
    }
};
