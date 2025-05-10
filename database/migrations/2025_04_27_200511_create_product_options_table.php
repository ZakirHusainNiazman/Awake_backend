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
        Schema::create('product_options', function (Blueprint $table) {
        $table->id();
        $table->foreignUuid('product_id') // <-- Use foreignUuid() instead of uuid()
            ->constrained() // Shortcut for references('id')->on('products')
            ->cascadeOnDelete(); // Ensure UUID is used for `product_id`
        $table->string('name');
        $table->string('type')->default('select');
        $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_options');
    }
};
