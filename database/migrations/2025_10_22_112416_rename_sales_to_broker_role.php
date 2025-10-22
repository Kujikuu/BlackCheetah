<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, modify the ENUM to include 'broker'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'franchisor', 'franchisee', 'sales', 'broker') DEFAULT 'franchisee'");
        
        // Update all users with 'sales' role to 'broker' role
        DB::statement("UPDATE users SET role = 'broker' WHERE role = 'sales'");
        
        // Finally, remove 'sales' from the ENUM
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'franchisor', 'franchisee', 'broker') DEFAULT 'franchisee'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, add 'sales' back to the ENUM
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'franchisor', 'franchisee', 'sales', 'broker') DEFAULT 'franchisee'");
        
        // Revert broker role back to sales
        DB::statement("UPDATE users SET role = 'sales' WHERE role = 'broker'");
        
        // Finally, remove 'broker' from the ENUM
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'franchisor', 'franchisee', 'sales') DEFAULT 'franchisee'");
    }
};
