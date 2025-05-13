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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('seller_id')
                ->nullable() // optional: set to true if existing products don't have sellers yet
                ->constrained()
                ->onDelete('cascade'); // or 'set null' if you prefer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
           // First drop the foreign key constraint
        $table->dropForeign(['seller_id']);

        // Then drop the column
        $table->dropColumn('seller_id');
        });
    }
};
