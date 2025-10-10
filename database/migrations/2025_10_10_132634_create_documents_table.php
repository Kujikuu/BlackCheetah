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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('franchise_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type'); // e.g., 'contract', 'manual', 'certificate', etc.
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_extension');
            $table->bigInteger('file_size'); // in bytes
            $table->string('mime_type');
            $table->enum('status', ['active', 'archived', 'deleted'])->default('active');
            $table->date('expiry_date')->nullable();
            $table->boolean('is_confidential')->default(false);
            $table->json('metadata')->nullable(); // For additional document metadata
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
