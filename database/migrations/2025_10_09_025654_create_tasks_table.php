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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['onboarding', 'training', 'compliance', 'maintenance', 'marketing', 'operations', 'finance', 'support', 'other', 'lead_management', 'sales', 'market_research']);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled', 'on_hold'])->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('franchise_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('lead_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('due_date')->nullable();
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->integer('estimated_hours')->nullable();
            $table->integer('actual_hours')->nullable();
            $table->json('checklist')->nullable(); // Array of checklist items
            $table->json('attachments')->nullable(); // Array of file URLs/paths
            $table->text('notes')->nullable();
            $table->text('completion_notes')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurrence_type', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->nullable();
            $table->integer('recurrence_interval')->nullable(); // Every X days/weeks/months
            $table->date('recurrence_end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
