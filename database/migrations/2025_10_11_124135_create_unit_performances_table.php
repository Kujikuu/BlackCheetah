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
        Schema::create('unit_performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('franchise_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('period_type', ['daily', 'monthly', 'yearly']);
            $table->date('period_date');
            $table->decimal('revenue', 12, 2)->default(0); // Sales/Revenue
            $table->decimal('expenses', 12, 2)->default(0);
            $table->decimal('royalties', 12, 2)->default(0);
            $table->decimal('profit', 12, 2)->default(0);
            $table->integer('total_transactions')->default(0);
            $table->decimal('average_transaction_value', 8, 2)->default(0);
            $table->integer('customer_reviews_count')->default(0);
            $table->decimal('customer_rating', 3, 2)->default(0); // Average rating like 4.8
            $table->integer('employee_count')->default(0);
            $table->decimal('customer_satisfaction_score', 3, 2)->default(0); // Percentage like 85.5
            $table->decimal('growth_rate', 5, 2)->default(0); // Percentage growth from previous period
            $table->json('additional_metrics')->nullable(); // For future extensibility
            $table->timestamps();

            // Indexes for performance
            $table->index(['franchise_id', 'period_type', 'period_date']);
            $table->index(['unit_id', 'period_type', 'period_date']);
            $table->index('period_date');
            $table->index('period_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_performances');
    }
};
