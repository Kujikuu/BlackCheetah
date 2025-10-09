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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('franchise_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('company_name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('country');
            $table->string('city');
            $table->text('address')->nullable();
            $table->enum('lead_source', ['website', 'referral', 'social_media', 'advertisement', 'cold_call', 'event', 'other']);
            $table->enum('status', ['new', 'contacted', 'qualified', 'proposal_sent', 'negotiating', 'closed_won', 'closed_lost']);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->decimal('estimated_investment', 12, 2)->nullable();
            $table->decimal('franchise_fee_quoted', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->date('expected_decision_date')->nullable();
            $table->date('last_contact_date')->nullable();
            $table->date('next_follow_up_date')->nullable();
            $table->integer('contact_attempts')->default(0);
            $table->json('interests')->nullable(); // Store areas of interest
            $table->json('documents')->nullable(); // Store document URLs/paths
            $table->json('communication_log')->nullable(); // Store communication history
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
