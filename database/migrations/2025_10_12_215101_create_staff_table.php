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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('job_title');
            $table->string('department')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->date('hire_date');
            $table->time('shift_start')->nullable();
            $table->time('shift_end')->nullable();
            $table->enum('status', ['active', 'on_leave', 'terminated', 'inactive'])->default('active');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'temporary'])->default('full_time');
            $table->text('notes')->nullable();
            $table->json('skills')->nullable();
            $table->json('certifications')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
