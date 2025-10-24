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
        Schema::create('marketplace_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->enum('inquiry_type', ['franchise', 'property']);
            $table->foreignId('franchise_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('property_id')->nullable()->constrained()->onDelete('set null');
            $table->text('message');
            $table->string('investment_budget')->nullable();
            $table->string('preferred_location')->nullable();
            $table->string('timeline')->nullable();
            $table->enum('status', ['new', 'contacted', 'closed'])->default('new');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_inquiries');
    }
};
