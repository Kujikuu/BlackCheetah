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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('sales_role', ['senior_sales', 'sales_associate', 'junior_sales'])->nullable()->after('role');
            $table->string('state')->nullable()->after('country');
            $table->foreignId('franchise_id')->nullable()->constrained()->onDelete('cascade')->after('profile_completion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['franchise_id']);
            $table->dropColumn(['sales_role', 'state', 'franchise_id']);
        });
    }
};
