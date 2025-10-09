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
        Schema::create('franchises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('franchisor_id')->constrained('users')->onDelete('cascade');
            $table->string('business_name');
            $table->string('brand_name');
            $table->string('industry');
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('business_registration_number')->unique();
            $table->string('tax_id')->nullable();
            $table->enum('business_type', ['corporation', 'llc', 'partnership', 'sole_proprietorship']);
            $table->date('established_date')->nullable();
            $table->string('headquarters_country');
            $table->string('headquarters_city');
            $table->text('headquarters_address');
            $table->string('contact_phone');
            $table->string('contact_email');
            $table->decimal('franchise_fee', 10, 2)->nullable();
            $table->decimal('royalty_percentage', 5, 2)->nullable();
            $table->decimal('marketing_fee_percentage', 5, 2)->nullable();
            $table->integer('total_units')->default(0);
            $table->integer('active_units')->default(0);
            $table->enum('status', ['active', 'inactive', 'pending_approval', 'suspended'])->default('pending_approval');
            $table->json('business_hours')->nullable();
            $table->json('social_media')->nullable();
            $table->json('documents')->nullable(); // Store document URLs/paths
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('franchises');
    }
};
