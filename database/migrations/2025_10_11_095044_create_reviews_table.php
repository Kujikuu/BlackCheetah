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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->foreignId('franchisee_id')->nullable()->constrained('users')->onDelete('set null'); // Who added this review
            $table->string('customer_name'); // Customer being reviewed
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->integer('rating')->check('rating >= 1 AND rating <= 5');
            $table->text('comment')->nullable();
            $table->enum('sentiment', ['positive', 'neutral', 'negative'])->default('neutral');
            $table->enum('status', ['published', 'draft', 'archived'])->default('published'); // Franchisee controls visibility
            $table->text('internal_notes')->nullable(); // Private notes for franchisee
            $table->enum('review_source', ['in_person', 'phone', 'email', 'social_media', 'other'])->default('in_person');
            $table->boolean('verified_purchase')->default(true); // Franchisee confirms this was real customer
            $table->timestamp('review_date'); // When the actual customer interaction happened
            $table->timestamps();

            $table->index(['unit_id', 'status']);
            $table->index(['rating']);
            $table->index(['review_date']);
            $table->index(['franchisee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
