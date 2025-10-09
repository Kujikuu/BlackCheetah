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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->enum('type', ['revenue', 'expense', 'royalty', 'marketing_fee', 'franchise_fee', 'refund', 'adjustment']);
            $table->enum('category', ['sales', 'cost_of_goods', 'labor', 'rent', 'utilities', 'marketing', 'equipment', 'supplies', 'insurance', 'taxes', 'other']);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('SAR');
            $table->text('description');
            $table->foreignId('franchise_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Who recorded the transaction
            $table->date('transaction_date');
            $table->enum('status', ['pending', 'completed', 'cancelled', 'refunded'])->default('completed');
            $table->enum('payment_method', ['cash', 'credit_card', 'debit_card', 'bank_transfer', 'check', 'digital_wallet', 'other'])->nullable();
            $table->string('reference_number')->nullable(); // Invoice number, receipt number, etc.
            $table->string('vendor_customer')->nullable(); // Vendor for expenses, customer for revenue
            $table->json('metadata')->nullable(); // Additional transaction details
            $table->json('attachments')->nullable(); // Receipts, invoices, etc.
            $table->text('notes')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurrence_type', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->nullable();
            $table->integer('recurrence_interval')->nullable();
            $table->date('recurrence_end_date')->nullable();
            $table->foreignId('parent_transaction_id')->nullable()->constrained('transactions')->onDelete('set null'); // For recurring transactions
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
