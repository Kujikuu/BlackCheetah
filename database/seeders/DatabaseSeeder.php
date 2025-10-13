<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the minimal data seeder
        $this->call([
            MinimalDataSeeder::class,
        ]);
    }
}
