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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('product_id') // <-- Use foreignUuid() instead of uuid()
            ->constrained() // Shortcut for references('id')->on('products')
            ->cascadeOnDelete();

            $table->foreignUuid('product_variant_id')
                ->nullable()
                ->constrained('product_variants')
                ->cascadeOnDelete();

            $table->string('title'); // snapshot of product title
            $table->string('sku');   // snapshot SKU
            $table->integer('quantity');
            $table->decimal('price', 10, 2); // price per unit at the time of order
            $table->decimal('total_price', 10, 2); // price × quantity
            $table->json('attributes')->nullable(); // {"Color": "Red", "Size": "M"}
            $table->string('image')->nullable(); // snapshot image URL
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
