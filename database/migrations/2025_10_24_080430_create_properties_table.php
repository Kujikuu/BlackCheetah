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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broker_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('property_type'); // retail, office, kiosk, food_court, standalone
            $table->decimal('size_sqft', 10, 2);
            $table->string('country');
            $table->string('state_province');
            $table->string('city');
            $table->string('address');
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('monthly_rent', 12, 2);
            $table->decimal('deposit_amount', 12, 2)->nullable();
            $table->integer('lease_term_months')->nullable();
            $table->date('available_from')->nullable();
            $table->json('amenities')->nullable();
            $table->json('images')->nullable();
            $table->enum('status', ['available', 'under_negotiation', 'leased', 'unavailable'])->default('available');
            $table->text('contact_info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
