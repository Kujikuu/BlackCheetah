<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the type enum to include sales-related task types
        DB::statement("ALTER TABLE tasks MODIFY COLUMN type ENUM('onboarding', 'training', 'compliance', 'maintenance', 'marketing', 'operations', 'finance', 'support', 'other', 'lead_management', 'sales', 'market_research') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE tasks MODIFY COLUMN type ENUM('onboarding', 'training', 'compliance', 'maintenance', 'marketing', 'operations', 'finance', 'support', 'other') NOT NULL");
    }
};
