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
        Schema::create('staff_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->date('assigned_date')->default(now());
            $table->date('end_date')->nullable();
            $table->enum('role', ['manager', 'assistant_manager', 'supervisor', 'sales_associate', 'cashier', 'barista', 'cook', 'cleaner', 'security', 'maintenance'])->default('sales_associate');
            $table->boolean('is_primary')->default(false); // Primary assignment
            $table->timestamps();

            $table->unique(['staff_id', 'unit_id', 'role'], 'staff_unit_role_unique');
            $table->index(['unit_id', 'role']);
            $table->index(['staff_id', 'assigned_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_unit');
    }
};
