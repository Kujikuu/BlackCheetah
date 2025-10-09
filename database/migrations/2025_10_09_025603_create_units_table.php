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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('franchise_id')->constrained()->onDelete('cascade');
            $table->foreignId('franchisee_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('unit_name');
            $table->string('unit_code')->unique();
            $table->enum('unit_type', ['store', 'kiosk', 'mobile', 'online', 'warehouse', 'office']);
            $table->string('country');
            $table->string('state_province')->nullable();
            $table->string('city');
            $table->text('address');
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->decimal('size_sqft', 8, 2)->nullable();
            $table->decimal('monthly_rent', 10, 2)->nullable();
            $table->decimal('initial_investment', 12, 2)->nullable();
            $table->date('lease_start_date')->nullable();
            $table->date('lease_end_date')->nullable();
            $table->date('opening_date')->nullable();
            $table->enum('status', ['planning', 'construction', 'training', 'active', 'temporarily_closed', 'permanently_closed'])->default('planning');
            $table->integer('employee_count')->default(0);
            $table->decimal('monthly_revenue', 10, 2)->nullable();
            $table->decimal('monthly_expenses', 10, 2)->nullable();
            $table->json('operating_hours')->nullable();
            $table->json('amenities')->nullable();
            $table->json('equipment')->nullable();
            $table->json('documents')->nullable(); // Store document URLs/paths
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
