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
        Schema::create('fullfillment_cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fullfillment_state_id')->constrained('fullfillment_states')->onDelete('cascade');
            $table->string('city_name');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fullfillment_cities');
    }
};
