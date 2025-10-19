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
            $table->enum('role', ['admin', 'franchisor', 'franchisee', 'sales'])->default('franchisee')->after('email');
            $table->enum('status', ['active', 'inactive', 'pending', 'suspended'])->default('pending')->after('role');
            $table->string('phone')->nullable()->after('status');
            $table->string('avatar')->nullable()->after('phone');
            $table->date('date_of_birth')->nullable()->after('avatar');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            $table->string('country')->nullable()->after('gender');
            $table->string('city')->nullable()->after('country');
            $table->string('state')->nullable()->after('city');
            $table->text('address')->nullable()->after('city');
            $table->timestamp('last_login_at')->nullable()->after('address');
            $table->json('preferences')->nullable()->after('last_login_at');
            $table->json('profile_completion')->nullable()->after('preferences');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'status',
                'phone',
                'avatar',
                'date_of_birth',
                'gender',
                'country',
                'city',
                'state',
                'address',
                'last_login_at',
                'preferences',
                'profile_completion'
            ]);
        });
    }
};
