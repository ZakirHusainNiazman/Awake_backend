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
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->enum('account_status', ['pending', 'approved', 'block'])->default('pending');
            $table->date('dob');
            $table->string('whatsapp_no');
            $table->string('business_description')->nullable();
            $table->enum('identity_type', ['passport', 'driving_license', 'national_id_card']);
            $table->string('proof_of_identity');
            $table->string("brand_name");
            $table->string("brand_logo");
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
