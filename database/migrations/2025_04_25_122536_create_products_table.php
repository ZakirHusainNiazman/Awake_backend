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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('sku')->unique()->nullable();
            $table->string('title');
            $table->text('details');
            // current on-hand quantity for no-variant products
            $table->unsignedInteger('stock')->default(0);
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2)->nullable();
            $table->boolean('has_variants')->default(false);
            $table->json('attributes')->nullable(); // store attribute values when no variants
            //fields for discount
            $table->boolean('has_discount')->default(false);
            $table->decimal('discount_amount', 8, 2)->nullable();
            $table->timestamp('discount_start')->nullable();
            $table->timestamp('discount_end')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
