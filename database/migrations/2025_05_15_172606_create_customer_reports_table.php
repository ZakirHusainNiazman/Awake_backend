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
        Schema::create('customer_reports', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('user_id'); // References customers table
            $table->unsignedBigInteger('order_item_id');    // References orders table
            $table->enum('issue_type', ['not_received', 'partially_received'])->default('not_received');
            $table->text('description')->nullable();   // Optional detailed description
            $table->enum('status', ['pending', 'in_progress', 'resolved'])->default('pending');
            $table->timestamp('resolved_at')->nullable(); // Timestamp when resolved
            $table->timestamps(); // created_at and updated_at

            // Foreign key constraints (optional, uncomment if needed)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_reports');
    }
};
