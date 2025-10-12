<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staff = [
            \App\Models\Staff::factory()->manager()->create([
                'name' => 'Alice Johnson',
                'email' => 'alice.johnson@blackcheetah.com',
                'job_title' => 'Store Manager',
                'department' => 'Operations',
            ]),
            \App\Models\Staff::factory()->create([
                'name' => 'Bob Wilson',
                'email' => 'bob.wilson@blackcheetah.com',
                'job_title' => 'Assistant Manager',
                'department' => 'Operations',
            ]),
            \App\Models\Staff::factory()->create([
                'name' => 'Carol Davis',
                'email' => 'carol.davis@blackcheetah.com',
                'job_title' => 'Sales Associate',
                'department' => 'Sales',
            ]),
            \App\Models\Staff::factory()->onLeave()->create([
                'name' => 'David Smith',
                'email' => 'david.smith@blackcheetah.com',
                'job_title' => 'Barista',
                'department' => 'Food & Beverage',
            ]),
            \App\Models\Staff::factory()->partTime()->create([
                'name' => 'Emma Wilson',
                'email' => 'emma.wilson@blackcheetah.com',
                'job_title' => 'Cashier',
                'department' => 'Sales',
            ]),
        ];

        // Create additional random staff
        $additionalStaff = \App\Models\Staff::factory(15)->create();
        $allStaff = collect($staff)->merge($additionalStaff);

        // Get all units and assign staff to them
        $units = \App\Models\Unit::all();

        foreach ($units as $unit) {
            // Assign 5-8 random staff members to each unit
            $staffToAssign = $allStaff->random(rand(5, 8));

            foreach ($staffToAssign as $index => $staffMember) {
                $roles = ['manager', 'assistant_manager', 'sales_associate', 'cashier', 'barista'];
                $role = $index === 0 ? 'manager' : $roles[array_rand($roles)];

                // Assign staff to unit with role
                $staffMember->assignToUnit($unit->id, $role, $index === 0);
            }
        }
    }
}
