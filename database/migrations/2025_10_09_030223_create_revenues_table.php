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
        Schema::create('revenues', function (Blueprint $table) {
            $table->id();
            $table->string('revenue_number')->unique();
            $table->foreignId('franchise_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Who recorded this revenue
            
            // Revenue details
            $table->enum('type', ['sales', 'franchise_fee', 'royalty', 'marketing_fee', 'other']);
            $table->enum('category', ['product_sales', 'service_sales', 'initial_fee', 'ongoing_fee', 'commission', 'other']);
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('SAR');
            $table->text('description')->nullable();
            
            // Date and period information
            $table->date('revenue_date');
            $table->integer('period_year');
            $table->integer('period_month');
            $table->date('period_start_date')->nullable();
            $table->date('period_end_date')->nullable();
            
            // Source and customer information
            $table->string('source')->nullable(); // online, in-store, phone, etc.
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('receipt_number')->nullable();
            
            // Payment information
            $table->enum('payment_method', ['cash', 'credit_card', 'debit_card', 'bank_transfer', 'check', 'other'])->nullable();
            $table->string('payment_reference')->nullable();
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('completed');
            
            // Tax and fees
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 15, 2); // Amount after tax and discounts
            
            // Product/service breakdown
            $table->json('line_items')->nullable(); // Detailed breakdown of products/services
            $table->json('metadata')->nullable(); // Additional data
            
            // Verification and approval
            $table->enum('status', ['draft', 'pending', 'verified', 'disputed'])->default('verified');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            
            // Recurring revenue tracking
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurrence_type', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->nullable();
            $table->integer('recurrence_interval')->nullable();
            $table->date('recurrence_end_date')->nullable();
            $table->foreignId('parent_revenue_id')->nullable()->constrained('revenues')->onDelete('cascade');
            
            // Additional information
            $table->json('attachments')->nullable(); // File paths for receipts, invoices, etc.
            $table->text('notes')->nullable();
            $table->boolean('is_auto_generated')->default(false);
            $table->timestamp('recorded_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['franchise_id', 'revenue_date']);
            $table->index(['unit_id', 'revenue_date']);
            $table->index(['period_year', 'period_month']);
            $table->index(['type', 'category']);
            $table->index(['status', 'payment_status']);
            $table->index('revenue_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenues');
    }
};
