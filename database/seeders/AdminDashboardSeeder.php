<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Franchise;
use App\Models\Unit;
use App\Models\TechnicalRequest;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminDashboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@blackcheetah.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@blackcheetah.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'phone' => '+1234567890',
                'country' => 'USA',
                'city' => 'New York',
                'last_login_at' => now(),
                'email_verified_at' => now(),
            ]
        );

        // Create franchisors
        $franchisors = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@example.com',
                'phone' => '+1234567891',
                'city' => 'Los Angeles',
                'franchise_name' => 'Smith Enterprises',
                'plan' => 'Pro'
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@example.com',
                'phone' => '+1234567892',
                'city' => 'Chicago',
                'franchise_name' => 'Johnson Holdings',
                'plan' => 'Enterprise'
            ],
            [
                'name' => 'Mike Davis',
                'email' => 'mike.davis@example.com',
                'phone' => '+1234567893',
                'city' => 'Houston',
                'franchise_name' => 'Davis Corp',
                'plan' => 'Basic'
            ]
        ];

        foreach ($franchisors as $franchiseData) {
            $franchisor = User::firstOrCreate(
                ['email' => $franchiseData['email']],
                [
                    'name' => $franchiseData['name'],
                    'email' => $franchiseData['email'],
                    'password' => Hash::make('password'),
                    'role' => 'franchisor',
                    'status' => 'active',
                    'phone' => $franchiseData['phone'],
                    'country' => 'USA',
                    'city' => $franchiseData['city'],
                    'last_login_at' => Carbon::now()->subDays(rand(1, 30)),
                    'email_verified_at' => now(),
                ]
            );

            // Create franchise for franchisor
            Franchise::firstOrCreate(
                ['franchisor_id' => $franchisor->id],
                [
                    'franchisor_id' => $franchisor->id,
                    'business_name' => $franchiseData['franchise_name'],
                    'brand_name' => $franchiseData['franchise_name'],
                    'industry' => 'Food & Beverage',
                    'description' => 'A successful franchise business',
                    'business_registration_number' => 'REG' . str_pad($franchisor->id, 6, '0', STR_PAD_LEFT),
                    'business_type' => 'corporation',
                    'headquarters_country' => 'USA',
                    'headquarters_city' => $franchiseData['city'],
                    'headquarters_address' => '123 Business St, ' . $franchiseData['city'],
                    'contact_phone' => $franchiseData['phone'],
                    'contact_email' => $franchiseData['email'],
                    'franchise_fee' => 50000.00,
                    'royalty_percentage' => 5.00,
                    'marketing_fee_percentage' => 2.00,
                    'total_units' => rand(5, 20),
                    'active_units' => rand(3, 15),
                    'status' => 'active',
                    'plan' => $franchiseData['plan'],
                ]
            );
        }

        // Create franchisees
        $franchisees = [
            [
                'name' => 'Alice Brown',
                'email' => 'alice.brown@example.com',
                'phone' => '+1234567894',
                'city' => 'Miami'
            ],
            [
                'name' => 'Bob Wilson',
                'email' => 'bob.wilson@example.com',
                'phone' => '+1234567895',
                'city' => 'Seattle'
            ],
            [
                'name' => 'Carol Martinez',
                'email' => 'carol.martinez@example.com',
                'phone' => '+1234567896',
                'city' => 'Denver'
            ],
            [
                'name' => 'David Lee',
                'email' => 'david.lee@example.com',
                'phone' => '+1234567897',
                'city' => 'Phoenix'
            ]
        ];

        foreach ($franchisees as $franchiseeData) {
            User::firstOrCreate(
                ['email' => $franchiseeData['email']],
                [
                    'name' => $franchiseeData['name'],
                    'email' => $franchiseeData['email'],
                    'password' => Hash::make('password'),
                    'role' => 'franchisee',
                    'status' => 'active',
                    'phone' => $franchiseeData['phone'],
                    'country' => 'USA',
                    'city' => $franchiseeData['city'],
                    'last_login_at' => Carbon::now()->subDays(rand(1, 15)),
                    'email_verified_at' => now(),
                ]
            );
        }

        // Create sales users
        $salesUsers = [
            [
                'name' => 'Emma Thompson',
                'email' => 'emma.thompson@example.com',
                'phone' => '+1234567898',
                'city' => 'Boston'
            ],
            [
                'name' => 'James Rodriguez',
                'email' => 'james.rodriguez@example.com',
                'phone' => '+1234567899',
                'city' => 'Atlanta'
            ],
            [
                'name' => 'Lisa Chen',
                'email' => 'lisa.chen@example.com',
                'phone' => '+1234567800',
                'city' => 'San Francisco'
            ]
        ];

        foreach ($salesUsers as $salesData) {
            User::firstOrCreate(
                ['email' => $salesData['email']],
                [
                    'name' => $salesData['name'],
                    'email' => $salesData['email'],
                    'password' => Hash::make('password'),
                    'role' => 'sales',
                    'status' => 'active',
                    'phone' => $salesData['phone'],
                    'country' => 'USA',
                    'city' => $salesData['city'],
                    'last_login_at' => Carbon::now()->subDays(rand(1, 7)),
                    'email_verified_at' => now(),
                ]
            );
        }

        // Note: Technical requests creation is skipped for now due to ticket number conflicts
        // This can be added later once the ticket number generation is fixed

        $this->command->info('Admin dashboard test data created successfully!');
    }
}
