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
        Schema::create('technical_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->enum('category', ['hardware', 'software', 'network', 'pos_system', 'website', 'mobile_app', 'training', 'other']);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'pending_info', 'resolved', 'closed', 'cancelled'])->default('open');
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('franchise_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('affected_system')->nullable(); // Which system is affected
            $table->text('steps_to_reproduce')->nullable();
            $table->text('expected_behavior')->nullable();
            $table->text('actual_behavior')->nullable();
            $table->string('browser_version')->nullable();
            $table->string('operating_system')->nullable();
            $table->string('device_type')->nullable();
            $table->json('attachments')->nullable(); // Screenshots, logs, etc.
            $table->text('internal_notes')->nullable(); // For support team only
            $table->text('resolution_notes')->nullable();
            $table->datetime('first_response_at')->nullable();
            $table->datetime('resolved_at')->nullable();
            $table->datetime('closed_at')->nullable();
            $table->integer('response_time_hours')->nullable(); // Time to first response
            $table->integer('resolution_time_hours')->nullable(); // Time to resolution
            $table->integer('satisfaction_rating')->nullable(); // 1-5 rating
            $table->text('satisfaction_feedback')->nullable();
            $table->boolean('is_escalated')->default(false);
            $table->datetime('escalated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technical_requests');
    }
};
