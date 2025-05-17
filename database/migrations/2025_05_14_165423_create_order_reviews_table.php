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
        Schema::create('order_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('rating')->unsigned(); // 1-5 stars
            $table->text('review');
            $table->boolean('approved')->default(false); // For moderation
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['order_item_id', 'user_id']); // Only 1 review per user per order item
        });
        // Add CHECK constraint separately if not supported in the fluent syntax
        DB::statement('ALTER TABLE order_reviews ADD CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_reviews');
    }
};
