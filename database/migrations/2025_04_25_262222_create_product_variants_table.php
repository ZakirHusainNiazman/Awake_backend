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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('sku')->unique()->nullable();
            $table->decimal('price', 10, 2);// override product price
            $table->unsignedInteger('stock')->default(0);
            $table->string('image');
            $table->json('attributes')->nullable(); // {"size":"M","color":"Red","image":"url_or_path.jpg"}
            //dicount fields
             $table->boolean('has_discount')->default(false);
            $table->enum('condiation', ['new', 'used', 'refurbished'])->default('new');
            $table->foreignUuid('product_id') // <-- Use foreignUuid() instead of uuid()
            ->constrained() // Shortcut for references('id')->on('products')
            ->cascadeOnDelete();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
