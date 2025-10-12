<?php

namespace Database\Seeders;

use App\Models\Franchise;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
                'name' => 'Ahmed Al-Mudeer',
                'email' => 'admin@blackcheetah.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'phone' => '+966501234567',
                'country' => 'Saudi Arabia',
                'city' => 'Riyadh',
                'last_login_at' => now(),
                'email_verified_at' => now(),
            ]
        );

        // Create franchisors
        $franchisors = [
            [
                'name' => 'Mohammed bin Abdullah Al-Ahmed',
                'email' => 'mohammed.ahmed@example.com',
                'phone' => '+966501234568',
                'city' => 'Jeddah',
                'franchise_name' => 'Al-Ahmed Trading Establishment',
                'plan' => 'Pro',
            ],
            [
                'name' => 'Fatima bint Saad Al-Otaibi',
                'email' => 'fatima.otaibi@example.com',
                'phone' => '+966501234569',
                'city' => 'Dammam',
                'franchise_name' => 'Al-Otaibi Holding Company',
                'plan' => 'Enterprise',
            ],
            [
                'name' => 'Khalid bin Mohammed Al-Qahtani',
                'email' => 'khalid.qahtani@example.com',
                'phone' => '+966501234570',
                'city' => 'Makkah',
                'franchise_name' => 'Al-Qahtani Group',
                'plan' => 'Basic',
            ],
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
                    'country' => 'Saudi Arabia',
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
                    'description' => 'A successful franchise business in the Kingdom of Saudi Arabia',
                    'business_registration_number' => '700'.str_pad($franchisor->id, 7, '0', STR_PAD_LEFT),
                    'business_type' => 'llc',
                    'headquarters_country' => 'Saudi Arabia',
                    'headquarters_city' => $franchiseData['city'],
                    'headquarters_address' => '123 Business Street, '.$franchiseData['city'].', Kingdom of Saudi Arabia',
                    'contact_phone' => $franchiseData['phone'],
                    'contact_email' => $franchiseData['email'],
                    'franchise_fee' => 187500.00,
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
                'name' => 'Aisha bint Ahmed Al-Zahrani',
                'email' => 'aisha.zahrani@example.com',
                'phone' => '+966501234571',
                'city' => 'Taif',
            ],
            [
                'name' => 'Abdulrahman bin Salem Al-Ghamdi',
                'email' => 'abdulrahman.ghamdi@example.com',
                'phone' => '+966501234572',
                'city' => 'Abha',
            ],
            [
                'name' => 'Nora bint Abdulaziz Al-Shamri',
                'email' => 'nora.shamri@example.com',
                'phone' => '+966501234573',
                'city' => 'Hail',
            ],
            [
                'name' => 'Saad bin Mohammed Al-Dosari',
                'email' => 'saad.dosari@example.com',
                'phone' => '+966501234574',
                'city' => 'Khobar',
            ],
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
                    'country' => 'Saudi Arabia',
                    'city' => $franchiseeData['city'],
                    'last_login_at' => Carbon::now()->subDays(rand(1, 15)),
                    'email_verified_at' => now(),
                ]
            );
        }

        // Create sales users
        $salesUsers = [
            [
                'name' => 'Hind bint Abdullah Al-Mutairi',
                'email' => 'hind.mutairi@example.com',
                'phone' => '+966501234575',
                'city' => 'Madinah',
            ],
            [
                'name' => 'Youssef bin Ibrahim Al-Anzi',
                'email' => 'youssef.anzi@example.com',
                'phone' => '+966501234576',
                'city' => 'Tabuk',
            ],
            [
                'name' => 'Reem bint Fahd Al-Harbi',
                'email' => 'reem.harbi@example.com',
                'phone' => '+966501234577',
                'city' => 'Buraydah',
            ],
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
                    'country' => 'Saudi Arabia',
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
