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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Reference existing fulfillment tables
            $table->foreignId('fullfillment_country_id')
                  ->constrained('fullfillment_countries')
                  ->onDelete('restrict');
            $table->foreignId('fullfillment_state_id')
                  ->constrained('fullfillment_states')
                  ->onDelete('restrict');
            $table->foreignId('fullfillment_city_id')
                  ->constrained('fullfillment_cities')
                  ->onDelete('restrict');

            // Address lines and extras
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone')->nullable();

            // Mark one address as default per user
            // Make nullable so multiple non-default (NULL) allowed
            $table->boolean('is_default')
                  ->nullable()
                  ->default(null);

            $table->timestamps();

            // Ensure only one default (is_default = 1) per user
            $table->unique(['user_id', 'is_default'], 'user_default_address_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
