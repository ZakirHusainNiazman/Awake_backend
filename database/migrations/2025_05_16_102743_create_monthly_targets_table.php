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
        Schema::create('monthly_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->onDelete('cascade');
            $table->string('month'); // e.g. '2025-05'
            $table->decimal('revenue_target', 10, 2)->default(0.00);
            $table->unsignedInteger('orders_target')->default(0);
            $table->timestamps();

            $table->unique(['seller_id', 'month']); // One target per seller per month
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_targets');
    }
};
