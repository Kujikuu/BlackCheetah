<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Seed admin dashboard data
        $this->call([
            AdminDashboardSeeder::class,
            FranchisorDashboardSeeder::class,
        ]);

        Notification::factory(5)->create();
    }
}
