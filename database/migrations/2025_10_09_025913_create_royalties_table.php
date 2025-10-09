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
        Schema::create('royalties', function (Blueprint $table) {
            $table->id();
            $table->string('royalty_number')->unique();
            $table->foreignId('franchise_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('franchisee_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['royalty', 'marketing_fee', 'technology_fee', 'other']);
            $table->integer('period_year');
            $table->integer('period_month');
            $table->date('period_start_date');
            $table->date('period_end_date');
            $table->decimal('gross_revenue', 12, 2);
            $table->decimal('royalty_percentage', 5, 2);
            $table->decimal('royalty_amount', 12, 2);
            $table->decimal('marketing_fee_percentage', 5, 2)->nullable();
            $table->decimal('marketing_fee_amount', 12, 2)->nullable();
            $table->decimal('technology_fee_amount', 12, 2)->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('adjustments', 12, 2)->default(0);
            $table->text('adjustment_notes')->nullable();
            $table->enum('status', ['draft', 'pending', 'paid', 'overdue', 'disputed', 'cancelled'])->default('draft');
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->enum('payment_method', ['bank_transfer', 'check', 'credit_card', 'ach', 'wire', 'other'])->nullable();
            $table->string('payment_reference')->nullable();
            $table->decimal('late_fee', 10, 2)->default(0);
            $table->json('revenue_breakdown')->nullable(); // Detailed revenue by category
            $table->json('attachments')->nullable(); // Supporting documents
            $table->text('notes')->nullable();
            $table->boolean('is_auto_generated')->default(true);
            $table->foreignId('generated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('royalties');
    }
};
