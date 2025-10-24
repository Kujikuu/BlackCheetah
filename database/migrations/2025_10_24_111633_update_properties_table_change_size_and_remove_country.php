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
        Schema::table('properties', function (Blueprint $table) {
            // Rename size_sqft to size_sqm
            $table->renameColumn('size_sqft', 'size_sqm');
            
            // Drop country column
            $table->dropColumn('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Rename size_sqm back to size_sqft
            $table->renameColumn('size_sqm', 'size_sqft');
            
            // Add country column back
            $table->string('country')->after('size_sqft');
        });
    }
};
