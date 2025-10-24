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
        Schema::table('franchises', function (Blueprint $table) {
            $table->foreignId('broker_id')->nullable()->after('franchisor_id')
                ->constrained('users')->onDelete('set null');
            $table->boolean('is_marketplace_listed')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('franchises', function (Blueprint $table) {
            $table->dropForeign(['broker_id']);
            $table->dropColumn(['broker_id', 'is_marketplace_listed']);
        });
    }
};
