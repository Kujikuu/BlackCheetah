<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Franchise;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Revenue;
use App\Models\Review;
use App\Models\Royalty;
use App\Models\Task;
use App\Models\TechnicalRequest;
use App\Models\Transaction;
use App\Models\Unit;
use App\Models\UnitPerformance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FranchisorDashboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first franchisor for testing
        $franchisor = User::where('role', 'franchisor')->first();

        if (! $franchisor) {
            $this->command->error('No franchisor found. Please run AdminDashboardSeeder first.');

            return;
        }

        $franchise = Franchise::where('franchisor_id', $franchisor->id)->first();

        // Create franchise if it doesn't exist
        if (! $franchise) {
            $franchise = Franchise::create([
                'franchisor_id' => $franchisor->id,
                'business_name' => 'Black Cheetah Saudi Arabia LLC',
                'brand_name' => 'Black Cheetah',
                'description' => 'Premium coffee and food franchise operating across Saudi Arabia',
                'industry' => 'Food & Beverage',
                'business_type' => 'llc',
                'headquarters_country' => 'Saudi Arabia',
                'headquarters_city' => 'Riyadh',
                'headquarters_address' => 'King Fahd District, Riyadh',
                'contact_phone' => '+966112345678',
                'contact_email' => 'info@blackcheetah.sa',
                'website' => 'https://blackcheetah.sa',
                'logo' => null,
                'status' => 'active',
                'plan' => 'Enterprise',
                'franchise_fee' => 150000, // 40k USD equivalent in SAR
                'royalty_percentage' => 6.5,
                'marketing_fee_percentage' => 2.0,
                'total_units' => 0,
                'active_units' => 0,
                'established_date' => now()->subYears(5),
                'business_registration_number' => 'CR-'.rand(1000000000, 9999999999),
                'tax_id' => 'VAT-'.rand(100000000, 999999999),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create Leads
        $this->createLeads($franchisor->id);

        // Create Tasks
        $this->createTasks($franchisor->id);

        // Create Units
        $this->createUnits($franchise->id);

        // Create Unit Performance
        $this->createUnitPerformance($franchise->id);

        // Create Technical Requests
        $this->createTechnicalRequests($franchise->id);

        // Create Reviews
        $this->createReviews($franchise->id);

        // Create Documents
        $this->createDocuments($franchise->id);

        // Create Products
        $this->createProducts($franchise->id);

        // Create Timeline Data
        $this->createTimelineData($franchise->id);

        // Create Revenues
        $this->createRevenues($franchise->id);

        // Create Royalties
        $this->createRoyalties($franchise->id);

        // Create Transactions
        $this->createTransactions($franchise->id);

        $this->command->info('Franchisor dashboard test data created successfully!');
    }

    private function createLeads($franchisorId)
    {
        $franchise = Franchise::where('franchisor_id', $franchisorId)->first();
        if (! $franchise) {
            return;
        }

        // Check if leads already exist for this franchise
        if (Lead::where('franchise_id', $franchise->id)->exists()) {
            $this->command->info('Leads already exist for franchise ID: '.$franchise->id.'. Skipping lead creation.');

            return;
        }

        // Get actual user IDs to assign leads to
        $users = User::pluck('id')->toArray();
        if (empty($users)) {
            $users = [$franchisorId];
        }

        $leadSources = ['website', 'referral', 'social_media', 'advertisement', 'cold_call', 'event', 'other'];
        $leadStatuses = ['new', 'contacted', 'qualified', 'proposal_sent', 'negotiating', 'closed_won', 'closed_lost'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $countries = ['Saudi Arabia'];
        $cities = ['Riyadh', 'Jeddah', 'Dammam', 'Tabuk', 'Hail', 'Makkah', 'Madinah'];

        $arabicFirstNames = ['Ahmed', 'Mohammed', 'Fatima', 'Aisha', 'Omar', 'Khalid', 'Nora', 'Sara', 'Abdullah', 'Youssef', 'Maryam', 'Hassan', 'Layla', 'Ali', 'Zainab'];
        $arabicLastNames = ['Al-Ahmed', 'Al-Otaibi', 'Al-Qahtani', 'Al-Zahrani', 'Al-Ghamdi', 'Al-Shamri', 'Al-Dosari', 'Al-Mutairi', 'Al-Anzi', 'Al-Harbi', 'Al-Rashid', 'Al-Saud', 'Al-Faisal'];

        $jobTitles = ['CEO', 'General Manager', 'Business Owner', 'Investment Manager', 'Operations Director', 'Franchise Developer', 'Business Development Manager'];
        $companyTypes = ['Trading Company', 'Investment Group', 'Business Ventures', 'Holdings', 'Enterprises', 'Commercial Group', 'Development Company'];
        $interests = [
            ['food_service', 'quick_service'],
            ['restaurant_management', 'franchise_operations'],
            ['multi_unit_development', 'territory_rights'],
            ['investment_opportunity', 'roi_analysis'],
            ['brand_recognition', 'marketing_support'],
        ];

        $leads = [];
        for ($i = 1; $i <= 50; $i++) {
            $createdAt = Carbon::now()->subDays(rand(1, 90));
            $firstName = $arabicFirstNames[array_rand($arabicFirstNames)];
            $lastName = $arabicLastNames[array_rand($arabicLastNames)];
            $status = $leadStatuses[array_rand($leadStatuses)];
            $contactAttempts = rand(0, 8);

            // Generate realistic communication log
            $communicationLog = [];
            for ($j = 0; $j < $contactAttempts; $j++) {
                $communicationLog[] = [
                    'date' => $createdAt->copy()->addDays($j * 3)->format('Y-m-d'),
                    'type' => ['phone', 'email', 'meeting', 'whatsapp'][array_rand(['phone', 'email', 'meeting', 'whatsapp'])],
                    'notes' => 'Follow-up contact attempt '.($j + 1),
                    'outcome' => ['connected', 'voicemail', 'no_answer', 'email_sent'][array_rand(['connected', 'voicemail', 'no_answer', 'email_sent'])],
                ];
            }

            $leads[] = [
                'franchise_id' => $franchise->id,
                'assigned_to' => $users[array_rand($users)],
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => strtolower($firstName.'.'.str_replace('Al-', '', $lastName)).$i.'@'.['gmail.com', 'hotmail.com', 'outlook.com', 'yahoo.com'][array_rand(['gmail.com', 'hotmail.com', 'outlook.com', 'yahoo.com'])],
                'phone' => '+9665'.rand(0, 9).str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'company_name' => $lastName.' '.$companyTypes[array_rand($companyTypes)],
                'job_title' => $jobTitles[array_rand($jobTitles)],
                'country' => $countries[array_rand($countries)],
                'city' => $cities[array_rand($cities)],
                'address' => 'Building '.rand(1, 999).', '.['King Fahd Road', 'Prince Mohammed Road', 'Olaya Street', 'Tahlia Street', 'King Abdul Aziz Road'][array_rand(['King Fahd Road', 'Prince Mohammed Road', 'Olaya Street', 'Tahlia Street', 'King Abdul Aziz Road'])],
                'lead_source' => $leadSources[array_rand($leadSources)],
                'status' => $status,
                'priority' => $priorities[array_rand($priorities)],
                'estimated_investment' => rand(187500, 1125000), // 50k-300k USD converted to SAR
                'franchise_fee_quoted' => rand(93750, 375000), // 25k-100k USD converted to SAR
                'notes' => 'Interested in '.$interests[array_rand($interests)][0].'. Previous experience in '.['retail', 'hospitality', 'food service', 'business management'][array_rand(['retail', 'hospitality', 'food service', 'business management'])],
                'expected_decision_date' => $status === 'negotiating' ? $createdAt->copy()->addDays(rand(7, 30))->format('Y-m-d') : null,
                'last_contact_date' => $contactAttempts > 0 ? $createdAt->copy()->addDays(($contactAttempts - 1) * 3)->format('Y-m-d') : null,
                'next_follow_up_date' => in_array($status, ['new', 'contacted', 'qualified', 'proposal_sent', 'negotiating']) ? $createdAt->copy()->addDays(rand(1, 14))->format('Y-m-d') : null,
                'contact_attempts' => $contactAttempts,
                'interests' => json_encode($interests[array_rand($interests)]),
                'communication_log' => json_encode($communicationLog),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        Lead::insert($leads);
    }

    private function createTasks($franchisorId)
    {
        $franchise = Franchise::where('franchisor_id', $franchisorId)->first();
        if (! $franchise) {
            return;
        }

        $taskTypes = ['onboarding', 'training', 'compliance', 'maintenance', 'marketing', 'operations', 'finance', 'support'];
        $taskStatuses = ['pending', 'in_progress', 'completed', 'cancelled', 'on_hold'];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        // Get some users to assign tasks to
        $users = User::whereIn('role', ['franchisor', 'franchisee', 'sales'])->pluck('id')->toArray();
        if (empty($users)) {
            $users = [$franchisorId];
        }

        $tasks = [];
        for ($i = 1; $i <= 30; $i++) {
            $createdAt = Carbon::now()->subDays(rand(1, 60));
            $dueDate = Carbon::now()->addDays(rand(-10, 30)); // Some overdue, some future
            $status = $taskStatuses[array_rand($taskStatuses)];

            $tasks[] = [
                'title' => 'Task '.$i.' - '.ucfirst($taskTypes[array_rand($taskTypes)]).' Related',
                'description' => 'Description for task '.$i.'. This is a sample task for testing purposes.',
                'type' => $taskTypes[array_rand($taskTypes)],
                'status' => $status,
                'priority' => $priorities[array_rand($priorities)],
                'assigned_to' => $users[array_rand($users)],
                'created_by' => $franchisorId,
                'franchise_id' => $franchise->id,
                'due_date' => $dueDate,
                'estimated_hours' => rand(1, 8),
                'actual_hours' => $status === 'completed' ? rand(1, 10) : null,
                'started_at' => in_array($status, ['in_progress', 'completed']) ? $createdAt->addHours(rand(1, 24)) : null,
                'completed_at' => $status === 'completed' ? $createdAt->addDays(rand(1, 5)) : null,
                'notes' => 'Sample notes for task '.$i,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        Task::insert($tasks);
    }

    private function createUnits($franchiseId)
    {
        // Check if units already exist for this franchise
        $existingUnitsCount = Unit::where('franchise_id', $franchiseId)->count();
        if ($existingUnitsCount > 0) {
            return; // Skip unit creation if units already exist
        }

        $saudiCities = [
            'Riyadh', 'Jeddah', 'Makkah', 'Madinah', 'Dammam', 'Khobar', 'Taif',
            'Buraidah', 'Tabuk', 'Khamis Mushait', 'Hail', 'Jubail', 'Kharj', 'Al-Ahsa', 'Najran',
            'Yanbu', 'Arar', 'Sakaka', 'Jazan', 'Qatif', 'Al-Baha', 'Rafha', 'Wadi Al-Dawasir',
        ];

        $unitTypes = ['store', 'kiosk', 'mobile', 'online', 'warehouse', 'office'];
        $statuses = ['planning', 'construction', 'training', 'active', 'temporarily_closed', 'permanently_closed'];

        // Create franchisees (unit owners) - check if they already exist
        $franchisees = [];
        for ($i = 1; $i <= 15; $i++) {
            $email = 'franchisee'.$i.'@blackcheetah.sa';
            $franchisee = User::where('email', $email)->first();

            if (! $franchisee) {
                $franchisee = User::create([
                    'name' => $this->getRandomSaudiName(),
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'role' => 'franchisee',
                    'phone' => '+966'.rand(500000000, 599999999),
                    'city' => $saudiCities[array_rand($saudiCities)],
                    'profile_completed' => false,
                    'created_at' => now()->subDays(rand(30, 365)),
                ]);
            }

            $franchisees[] = $franchisee;
        }

        // Create units for each franchisee
        foreach ($franchisees as $franchisee) {
            $numUnits = rand(1, 3); // Each franchisee can have 1-3 units

            for ($j = 0; $j < $numUnits; $j++) {
                $openingDate = now()->subDays(rand(30, 730));
                $status = $statuses[array_rand($statuses)];

                Unit::create([
                    'franchise_id' => $franchiseId,
                    'franchisee_id' => $franchisee->id,
                    'unit_code' => 'BCU-'.str_pad(($franchisee->id * 10) + $j, 4, '0', STR_PAD_LEFT),
                    'unit_name' => 'Black Cheetah '.$saudiCities[array_rand($saudiCities)],
                    'unit_type' => $unitTypes[array_rand($unitTypes)],
                    'status' => $status,
                    'address' => $this->generateSaudiAddress($saudiCities[array_rand($saudiCities)]),
                    'city' => $saudiCities[array_rand($saudiCities)],
                    'country' => 'Saudi Arabia',
                    'state_province' => 'Saudi Arabia',
                    'phone' => '+966'.rand(100000000, 199999999),
                    'email' => 'unit'.$franchisee->id.$j.'@blackcheetah.sa',
                    'opening_date' => $status === 'active' ? $openingDate : null,
                    'size_sqft' => rand(500, 3000), // Convert from sqm to sqft (approx 10x)
                    'monthly_rent' => rand(7500, 37500), // 2k-10k USD equivalent in SAR
                    'employee_count' => $status === 'active' ? rand(3, 15) : 0,
                    'created_at' => $openingDate,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function generateSaudiAddress(string $city): string
    {
        $districts = [
            'King Fahd District', 'Al-Olaya District', 'Al-Nakheel District', 'Al-Rawdah District', 'Al-Salamah District',
            'Al-Faisaliyah District', 'Al-Murooj District', 'Al-Narjis District', 'Al-Yasmin District', 'Al-Wurud District',
        ];

        $streets = [
            'King Abdulaziz Street', 'Prince Mohammed bin Abdulaziz Street', 'Al-Urubah Street',
            'King Fahd Street', 'Tahlia Street', 'Prince Sultan Street',
        ];

        return $districts[array_rand($districts)].', '.$streets[array_rand($streets)].', '.$city;
    }

    private function getRandomSaudiName(): string
    {
        $firstNames = [
            'Mohammed', 'Ahmed', 'Abdullah', 'Abdulrahman', 'Ali', 'Khalid', 'Saad', 'Fahd', 'Abdulaziz',
            'Sultan', 'Faisal', 'Omar', 'Youssef', 'Ibrahim', 'Hassan', 'Mishaal', 'Nawaf', 'Bandar',
            'Fatima', 'Aisha', 'Khadijah', 'Zainab', 'Maryam', 'Sarah', 'Nora', 'Hind', 'Reem', 'Lujain',
        ];

        $lastNames = [
            'Al-Otaibi', 'Al-Mutairi', 'Al-Dosari', 'Al-Qahtani', 'Al-Ghamdi', 'Al-Zahrani', 'Al-Shehri',
            'Al-Anzi', 'Al-Harbi', 'Al-Shamri', 'Al-Saud', 'Al-Faisal', 'Al-Rashid', 'Al-Abdullah',
        ];

        return $firstNames[array_rand($firstNames)].' '.$lastNames[array_rand($lastNames)];
    }

    private function createUnitPerformance($franchiseId)
    {
        // Get all active units for this franchise
        $units = Unit::where('franchise_id', $franchiseId)
            ->where('status', 'active')
            ->get();

        // Create performance data for the last 12 months for each unit
        foreach ($units as $unit) {
            // Base performance varies by unit location and type
            $baseRevenue = match ($unit->city) {
                'Riyadh', 'Jeddah' => rand(37500, 225000), // Major cities: 10k-60k USD
                'Makkah', 'Madinah' => rand(28125, 168750), // Holy cities: 7.5k-45k USD
                'Dammam', 'Khobar' => rand(22500, 150000), // Eastern province: 6k-40k USD
                default => rand(18750, 112500) // Other cities: 5k-30k USD
            };

            for ($month = 11; $month >= 0; $month--) {
                $date = now()->subMonths($month);
                $monthStart = $date->copy()->startOfMonth();
                $monthEnd = $date->copy()->endOfMonth();

                // Seasonal multipliers for Saudi market
                $seasonalMultiplier = match ($date->month) {
                    12, 1, 2 => 1.4, // Winter months - higher sales
                    3, 4 => 1.6, // Ramadan/Spring - peak season
                    5, 6 => 0.7, // Early summer - lower sales
                    7, 8 => 0.6, // Peak summer - lowest sales
                    9, 10, 11 => 1.2, // Fall - recovery season
                    default => 1.0
                };

                // Weekend vs weekday patterns (Friday/Saturday are weekends in Saudi)
                $weekendDays = 8; // Approximate weekend days in month
                $weekdayDays = $date->daysInMonth - $weekendDays;

                // Calculate revenue with variations
                $monthlyRevenue = round($baseRevenue * $seasonalMultiplier * (rand(85, 115) / 100));

                // Expense breakdown (more realistic)
                $costOfGoodsSold = round($monthlyRevenue * (rand(25, 35) / 100)); // 25-35% COGS
                $laborCosts = round($monthlyRevenue * (rand(20, 30) / 100)); // 20-30% labor
                $rentUtilities = round($monthlyRevenue * (rand(8, 15) / 100)); // 8-15% rent/utilities
                $marketingCosts = round($monthlyRevenue * (rand(2, 5) / 100)); // 2-5% marketing
                $otherExpenses = round($monthlyRevenue * (rand(5, 10) / 100)); // 5-10% other

                $totalExpenses = $costOfGoodsSold + $laborCosts + $rentUtilities + $marketingCosts + $otherExpenses;
                $profit = $monthlyRevenue - $totalExpenses;

                // Customer metrics
                $averageOrderValue = rand(35, 85); // 9-23 USD per order
                $customerCount = round($monthlyRevenue / $averageOrderValue);
                $totalTransactions = round($customerCount * (rand(110, 140) / 100)); // Some customers make multiple orders

                // Customer satisfaction metrics
                $customerRating = rand(35, 50) / 10; // 3.5-5.0 rating
                $customerReviewsCount = round($customerCount * (rand(5, 15) / 100)); // 5-15% leave reviews

                // Operational metrics
                $employeeCount = rand(8, 25);
                $revenuePerEmployee = round($monthlyRevenue / $employeeCount);
                $averageServiceTime = rand(3, 8); // 3-8 minutes average service time
                $customerRetentionRate = rand(65, 85); // 65-85% retention rate

                // Growth metrics (compared to same month last year)
                $revenueGrowth = rand(-15, 25); // -15% to +25% growth
                $customerGrowth = rand(-10, 30); // -10% to +30% customer growth

                // Calculate royalties (percentage of revenue)
                $royalties = round($monthlyRevenue * 0.065); // 6.5% royalty rate

                // Additional metrics as JSON
                $additionalMetrics = [
                    'customer_count' => $customerCount,
                    'average_order_value' => round($averageOrderValue, 2),
                    'revenue_per_employee' => $revenuePerEmployee,
                    'average_service_time' => $averageServiceTime,
                    'customer_retention_rate' => $customerRetentionRate,
                    'revenue_growth' => $revenueGrowth,
                    'customer_growth' => $customerGrowth,
                    'cost_of_goods_sold' => $costOfGoodsSold,
                    'labor_costs' => $laborCosts,
                    'rent_utilities' => $rentUtilities,
                    'marketing_costs' => $marketingCosts,
                    'other_expenses' => $otherExpenses,
                    'operating_hours' => rand(8, 16),
                    'repeat_customer_rate' => rand(25, 65),
                    'cost_per_customer' => round($totalExpenses / $customerCount, 2),
                    'inventory_turnover' => rand(4, 12),
                    'waste_percentage' => rand(3, 12),
                    'peak_hour_revenue' => round($monthlyRevenue * (rand(30, 50) / 100)),
                    'online_orders_percentage' => rand(15, 45),
                    'delivery_time_avg' => rand(20, 45),
                ];

                UnitPerformance::create([
                    'unit_id' => $unit->id,
                    'franchise_id' => $franchiseId,
                    'period_type' => 'monthly',
                    'period_date' => $date->format('Y-m-01'), // First day of the month
                    'revenue' => $monthlyRevenue,
                    'expenses' => $totalExpenses,
                    'royalties' => $royalties,
                    'profit' => $profit,
                    'total_transactions' => $totalTransactions,
                    'average_transaction_value' => round($averageOrderValue, 2),
                    'customer_reviews_count' => $customerReviewsCount,
                    'customer_rating' => round($customerRating, 1),
                    'employee_count' => $employeeCount,
                    'customer_satisfaction_score' => round($customerRating, 1), // Same as rating for now
                    'growth_rate' => $revenueGrowth,
                    'additional_metrics' => json_encode($additionalMetrics),
                    'created_at' => $monthStart,
                    'updated_at' => $monthEnd,
                ]);
            }
        }
    }

    private function createTechnicalRequests(int $franchiseId): void
    {
        // Skip if technical requests already exist for this franchise
        if (TechnicalRequest::where('franchise_id', $franchiseId)->exists()) {
            return;
        }

        // Get all units for this franchise
        $units = Unit::where('franchise_id', $franchiseId)->get();

        $categories = ['hardware', 'software', 'network', 'pos_system', 'website', 'mobile_app', 'training', 'other'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $statuses = ['open', 'in_progress', 'pending_info', 'resolved', 'closed', 'cancelled'];

        $issueDescriptions = [
            'hardware' => [
                'POS terminal not working properly',
                'Receipt printer malfunction',
                'Coffee machine breakdown',
                'Refrigerator temperature issues',
                'Air conditioning system failure',
            ],
            'software' => [
                'Inventory management system update needed',
                'Order application issues',
                'Employee management system error',
                'Payment processing software bug',
                'Reporting system malfunction',
            ],
            'network' => [
                'Internet connection outage',
                'Slow network speed',
                'WiFi connectivity issues',
                'Central server connection problems',
                'Payment network issues',
            ],
            'pos_system' => [
                'POS system not working correctly',
                'Invoice printing issues',
                'Tax calculation errors',
                'Payment processing delays',
                'Sales recording problems',
            ],
            'website' => [
                'Website loading slowly',
                'Online ordering system down',
                'Payment gateway issues',
                'Mobile responsiveness problems',
                'SEO optimization needed',
            ],
            'mobile_app' => [
                'App crashes on startup',
                'Login authentication issues',
                'Push notifications not working',
                'Order tracking problems',
                'App store update required',
            ],
            'training' => [
                'Staff training on new procedures',
                'POS system training needed',
                'Customer service training request',
                'Safety protocol training',
                'Product knowledge training',
            ],
            'other' => [
                'General inquiry about operations',
                'Equipment maintenance request',
                'Documentation update needed',
                'Compliance audit support',
                'Miscellaneous technical support',
            ],
        ];

        // Create 30-50 technical requests
        for ($i = 1; $i <= rand(30, 50); $i++) {
            $unit = $units->random();
            $category = $categories[array_rand($categories)];
            $createdAt = now()->subDays(rand(1, 90));
            $status = $statuses[array_rand($statuses)];

            // Set resolved/closed dates for completed requests
            $resolvedAt = null;
            $closedAt = null;
            if ($status === 'resolved') {
                $resolvedAt = $createdAt->copy()->addDays(rand(1, 7));
            } elseif ($status === 'closed') {
                $resolvedAt = $createdAt->copy()->addDays(rand(1, 7));
                $closedAt = $resolvedAt->copy()->addDays(rand(1, 3));
            }

            // Get a random franchisee as the requester
            $franchisees = User::where('role', 'franchisee')->get();
            $requester = $franchisees->random();

            TechnicalRequest::create([
                'ticket_number' => 'TR'.date('Y').str_pad($i, 6, '0', STR_PAD_LEFT),
                'title' => 'Technical Support Request - '.ucfirst($category),
                'description' => $issueDescriptions[$category][array_rand($issueDescriptions[$category])],
                'category' => $category,
                'priority' => $priorities[array_rand($priorities)],
                'status' => $status,
                'requester_id' => $requester->id,
                'assigned_to' => $status !== 'open' ? $franchisees->random()->id : null,
                'franchise_id' => $franchiseId,
                'unit_id' => $unit->id,
                'affected_system' => $category === 'hardware' ? 'POS Terminal' : ($category === 'software' ? 'Management System' : 'Network Infrastructure'),
                'steps_to_reproduce' => 'Steps to reproduce the issue...',
                'expected_behavior' => 'Expected system behavior...',
                'actual_behavior' => 'Actual system behavior observed...',
                'browser_version' => $category === 'website' ? 'Chrome 120.0' : null,
                'operating_system' => 'Windows 11',
                'device_type' => 'Desktop',
                'attachments' => json_encode(['screenshot.jpg', 'error_log.txt']),
                'internal_notes' => 'Internal notes for technical team',
                'resolution_notes' => $status === 'resolved' ? 'Issue resolved successfully' : null,
                'first_response_at' => $status !== 'open' ? $createdAt->copy()->addHours(rand(1, 4)) : null,
                'resolved_at' => $resolvedAt,
                'closed_at' => $closedAt,
                'response_time_hours' => $status !== 'open' ? rand(1, 4) : null,
                'resolution_time_hours' => $resolvedAt ? $createdAt->diffInHours($resolvedAt) : null,
                'satisfaction_rating' => $status === 'closed' ? rand(3, 5) : null,
                'satisfaction_feedback' => $status === 'closed' ? 'Great service, issue resolved quickly' : null,
                'is_escalated' => rand(0, 1) ? 1 : 0,
                'escalated_at' => rand(0, 1) ? $createdAt->copy()->addHours(rand(24, 48)) : null,
                'created_at' => $createdAt,
                'updated_at' => $closedAt ?? $resolvedAt ?? $createdAt,
            ]);
        }
    }

    private function createReviews(int $franchiseId): void
    {
        // Skip if reviews already exist for this franchise
        if (Review::whereHas('unit', function ($query) use ($franchiseId) {
            $query->where('franchise_id', $franchiseId);
        })->exists()) {
            return;
        }

        // Get all active units for this franchise
        $units = Unit::where('franchise_id', $franchiseId)
            ->where('status', 'active')
            ->get();

        $reviewSources = ['in_person', 'phone', 'email', 'social_media', 'other'];
        $customerNames = [
            'Ahmed Mohammed', 'Fatima Ali', 'Khalid Abdullah', 'Nora Saad', 'Omar Al-Ahmad', 'Sarah Al-Zahrani',
            'Mohammed Al-Qahtani', 'Aisha Al-Ghamdi', 'Youssef Al-Otaibi', 'Maryam Al-Dosari', 'Abdulrahman Al-Shammari',
            'Hind Al-Mutairi', 'Sultan Al-Harbi', 'Reem Al-Anzi', 'Fahd Al-Rashid', 'Lujain Al-Faisal',
        ];

        $positiveComments = [
            'Excellent service and very delicious food',
            'Clean place and friendly staff',
            'Fast preparation and high quality',
            'Reasonable prices and great taste',
            'Amazing experience, highly recommend visiting',
            'Excellent customer service',
            'Fresh and delicious food',
            'Comfortable place with beautiful atmosphere',
            'Diverse variety of dishes',
            'Excellent value for money',
        ];

        $neutralComments = [
            'Good experience overall',
            'Food is good and service is acceptable',
            'Decent place',
            'Average service and acceptable food',
            'Average experience',
            'Needs some improvement',
            'Food is good but service is slow',
            'Prices are slightly high',
            'Good place but needs development',
            'Acceptable experience',
        ];

        $negativeComments = [
            'Very slow service',
            'Food was not fresh',
            'High prices compared to quality',
            'Place needs more cleaning',
            'Staff are not cooperative',
            'Long wait for order',
            'Poor food quality',
            'Bad customer service',
            'Disappointing experience',
            'Do not recommend visiting',
        ];

        // Create 200-300 reviews across all units
        for ($i = 1; $i <= rand(200, 300); $i++) {
            $unit = $units->random();
            $rating = rand(1, 5);
            $createdAt = now()->subDays(rand(1, 365));

            // Choose comment based on rating
            if ($rating >= 4) {
                $comment = $positiveComments[array_rand($positiveComments)];
            } elseif ($rating == 3) {
                $comment = $neutralComments[array_rand($neutralComments)];
            } else {
                $comment = $negativeComments[array_rand($negativeComments)];
            }

            // Get a random franchisee for this unit
            $franchisees = User::where('role', 'franchisee')->get();
            $franchisee = $franchisees->random();

            // Determine sentiment based on rating
            $sentiment = $rating >= 4 ? 'positive' : ($rating == 3 ? 'neutral' : 'negative');

            Review::create([
                'unit_id' => $unit->id,
                'franchisee_id' => $franchisee->id,
                'customer_name' => $customerNames[array_rand($customerNames)],
                'customer_email' => 'customer'.$i.'@example.com',
                'customer_phone' => '+966'.rand(500000000, 599999999),
                'rating' => $rating,
                'comment' => $comment,
                'sentiment' => $sentiment,
                'status' => 'published',
                'internal_notes' => $rating <= 3 ? 'Follow up required for improvement' : null,
                'review_source' => $reviewSources[array_rand($reviewSources)],
                'verified_purchase' => rand(0, 1),
                'review_date' => $createdAt,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }

    private function createDocuments(int $franchiseId): void
    {
        $documentTypes = [
            'contract' => 'Franchise Contracts',
            'manual' => 'Operations Manual',
            'certificate' => 'Certificates and Licenses',
            'policy' => 'Policies and Procedures',
            'training' => 'Training Materials',
            'marketing' => 'Marketing Materials',
            'financial' => 'Financial Reports',
            'legal' => 'Legal Documents',
            'compliance' => 'Compliance Documents',
            'insurance' => 'Insurance Documents',
        ];

        $documentNames = [
            'contract' => [
                'Main Franchise Agreement',
                'Licensing Agreement',
                'Lease Contract',
                'Service Agreement',
                'Supply Contract',
            ],
            'manual' => [
                'Operations Manual',
                'Inventory Management Manual',
                'Customer Service Manual',
                'Health and Safety Manual',
                'Quality Manual',
            ],
            'certificate' => [
                'Commercial License',
                'ISO Certificate',
                'Food Safety Certificate',
                'Municipal License',
                'Civil Defense Certificate',
            ],
            'policy' => [
                'Human Resources Policy',
                'Returns Policy',
                'Privacy Policy',
                'Security Policy',
                'Training Policy',
            ],
            'training' => [
                'Basic Training Program',
                'POS System Training',
                'Customer Service Training',
                'Occupational Safety Training',
                'Inventory Management Training',
            ],
            'marketing' => [
                'Brand Identity Guide',
                'Advertising Campaign Materials',
                'Product Catalog',
                'Social Media Materials',
                'Design Templates',
            ],
            'financial' => [
                'Monthly Financial Report',
                'Profit and Loss Report',
                'Cash Flow Report',
                'Balance Sheet',
                'Payroll Report',
            ],
            'legal' => [
                'Articles of Incorporation',
                'Commercial Registration',
                'Zakat and Tax Certificate',
                'Insurance Contract',
                'Non-Disclosure Agreement',
            ],
            'compliance' => [
                'Monthly Compliance Report',
                'Internal Audit Documents',
                'Risk Assessment Report',
                'Incident Log',
                'Quality Report',
            ],
            'insurance' => [
                'General Insurance Policy',
                'Civil Liability Insurance',
                'Property Insurance',
                'Workers Insurance',
                'Product Insurance',
            ],
        ];

        $fileExtensions = ['pdf', 'docx', 'xlsx', 'pptx', 'jpg', 'png'];
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
        ];

        // Create 50-80 documents for the franchise
        for ($i = 1; $i <= rand(50, 80); $i++) {
            $type = array_rand($documentTypes);
            $names = $documentNames[$type];
            $name = $names[array_rand($names)];
            $extension = $fileExtensions[array_rand($fileExtensions)];
            $fileName = 'document_'.$i.'.'.$extension;
            $filePath = 'documents/franchise_'.$franchiseId.'/'.$fileName;
            $fileSize = rand(50000, 5000000); // 50KB to 5MB
            $createdAt = now()->subDays(rand(1, 365));

            // Determine if document should have expiry date
            $hasExpiry = in_array($type, ['certificate', 'legal', 'insurance', 'compliance']);
            $expiryDate = $hasExpiry ? $createdAt->copy()->addMonths(rand(6, 36)) : null;

            // Determine confidentiality
            $isConfidential = in_array($type, ['contract', 'financial', 'legal']);

            // Create metadata based on document type
            $metadata = [];
            switch ($type) {
                case 'contract':
                    $metadata = [
                        'contract_value' => rand(50000, 500000),
                        'parties' => ['Parent Company', 'Franchisee'],
                        'jurisdiction' => 'Kingdom of Saudi Arabia',
                    ];
                    break;
                case 'financial':
                    $metadata = [
                        'period' => $createdAt->format('Y-m'),
                        'currency' => 'SAR',
                        'prepared_by' => 'Accounting Department',
                    ];
                    break;
                case 'certificate':
                    $metadata = [
                        'issuing_authority' => 'Competent Authority',
                        'certificate_number' => 'CERT-'.rand(100000, 999999),
                        'validity_period' => '12 months',
                    ];
                    break;
                default:
                    $metadata = [
                        'version' => '1.'.rand(0, 9),
                        'language' => 'en',
                        'department' => 'General Management',
                    ];
            }

            Document::create([
                'franchise_id' => $franchiseId,
                'name' => $name,
                'description' => 'Detailed description for '.$name,
                'type' => $type,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_extension' => $extension,
                'file_size' => $fileSize,
                'mime_type' => $mimeTypes[$extension],
                'status' => ['active', 'approved', 'archived'][array_rand(['active', 'approved', 'archived'])],
                'expiry_date' => $expiryDate,
                'is_confidential' => $isConfidential,
                'metadata' => $metadata,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }

    private function createProducts(int $franchiseId): void
    {
        $categories = [
            'Beverages' => [
                'Arabic Coffee', 'Turkish Coffee', 'Cappuccino', 'Latte', 'Espresso', 'Mocha',
                'Black Tea', 'Green Tea', 'Mint Tea', 'Karak Tea',
                'Orange Juice', 'Apple Juice', 'Mango Juice', 'Strawberry Juice',
                'Mineral Water', 'Soft Drinks', 'Natural Juices',
            ],
            'Main Dishes' => [
                'Beef Burger', 'Chicken Burger', 'Fish Burger', 'Beef Shawarma', 'Chicken Shawarma',
                'Kebab', 'Chicken Tikka', 'Falafel', 'Hummus', 'Fattoush', 'Tabbouleh',
                'Beef Mandi', 'Chicken Mandi', 'Kabsa', 'Madfoon', 'Biryani',
            ],
            'Desserts' => [
                'Kunafa', 'Baklava', 'Maamoul', 'Ghraybeh', 'Chocolate Cake', 'Tiramisu',
                'Vanilla Ice Cream', 'Chocolate Ice Cream', 'Muhallabia', 'Om Ali',
                'Tahini Halva', 'Stuffed Dates', 'Qatayef',
            ],
            'Appetizers' => [
                'Hummus with Tahini', 'Baba Ghanouj', 'Mutabal', 'Fatteh', 'Kibbeh',
                'Meat Sambosa', 'Cheese Sambosa', 'Kibbeh Discs', 'Zaatar Pies',
                'Cheese Pies', 'Manakish', 'Meat with Dough',
            ],
            'Salads' => [
                'Green Salad', 'Greek Salad', 'Caesar Salad', 'Tuna Salad',
                'Fattoush Salad', 'Tabbouleh Salad', 'Arugula Salad', 'Cucumber Yogurt Salad',
            ],
        ];

        $productCount = 0;
        foreach ($categories as $category => $products) {
            foreach ($products as $productName) {
                $productCount++;

                // Generate realistic pricing based on category
                $basePrice = match ($category) {
                    'Beverages' => rand(5, 25),
                    'Main Dishes' => rand(15, 65),
                    'Desserts' => rand(8, 35),
                    'Appetizers' => rand(10, 30),
                    'Salads' => rand(12, 28),
                    default => rand(10, 40)
                };

                $costPrice = $basePrice * 0.6; // 60% of selling price
                $stock = rand(0, 200);
                $minimumStock = rand(5, 20);
                $weight = rand(100, 1500) / 100; // 1-15 kg
                $dimensions = rand(10, 50).'x'.rand(10, 50).'x'.rand(5, 20).' cm';

                // Generate attributes based on category
                $attributes = [];
                switch ($category) {
                    case 'Beverages':
                        $attributes = [
                            'size' => ['Small', 'Medium', 'Large'][array_rand(['Small', 'Medium', 'Large'])],
                            'type' => ['Cold', 'Hot'][array_rand(['Cold', 'Hot'])],
                            'calories' => rand(50, 300),
                        ];
                        break;
                    case 'Main Dishes':
                        $attributes = [
                            'portion_size' => ['Small', 'Medium', 'Large', 'Family'][array_rand(['Small', 'Medium', 'Large', 'Family'])],
                            'spice_level' => ['Mild', 'Medium', 'Spicy'][array_rand(['Mild', 'Medium', 'Spicy'])],
                            'preparation_time' => rand(10, 45).' minutes',
                        ];
                        break;
                    case 'Desserts':
                        $attributes = [
                            'sugar_type' => ['Regular', 'Low Sugar', 'Sugar Free'][array_rand(['Regular', 'Low Sugar', 'Sugar Free'])],
                            'kid_friendly' => rand(0, 1) == 1,
                            'contains_nuts' => rand(0, 1) == 1,
                        ];
                        break;
                    default:
                        $attributes = [
                            'vegetarian' => rand(0, 1) == 1,
                            'gluten_free' => rand(0, 1) == 1,
                            'salt_level' => ['Light', 'Medium', 'Salty'][array_rand(['Light', 'Medium', 'Salty'])],
                        ];
                }

                $createdAt = now()->subDays(rand(1, 180));

                Product::create([
                    'franchise_id' => $franchiseId,
                    'name' => $productName,
                    'description' => 'Detailed description for '.$productName.' prepared with the finest ingredients and best traditional methods',
                    'category' => $category,
                    'unit_price' => $basePrice,
                    'stock' => $stock,
                    'status' => $stock > $minimumStock ? 'active' : ($stock > 0 ? 'active' : 'inactive'),
                    'sku' => 'SKU-'.str_pad($productCount, 4, '0', STR_PAD_LEFT),
                    'image' => 'products/'.strtolower(str_replace(' ', '_', $productName)).'.jpg',
                    'cost_price' => round($costPrice, 2),
                    'weight' => $weight,
                    'dimensions' => $dimensions,
                    'minimum_stock' => $minimumStock,
                    'attributes' => $attributes,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }

    private function createRevenues($franchisorId)
    {
        $franchise = Franchise::where('franchisor_id', $franchisorId)->first();
        if (! $franchise) {
            return;
        }

        $revenueTypes = ['sales', 'franchise_fee', 'royalty', 'marketing_fee', 'other'];
        $categories = ['product_sales', 'service_sales', 'initial_fee', 'ongoing_fee', 'commission', 'other'];
        $sources = ['online', 'in-store', 'phone', 'referral', 'delivery_app', 'walk_in'];
        $paymentMethods = ['cash', 'mada', 'credit_card', 'bank_transfer', 'stc_pay', 'sadad', 'apple_pay', 'tamara', 'tabby'];
        $statuses = ['draft', 'pending', 'verified', 'disputed', 'cancelled'];

        $customerNames = [
            'Ahmed Al-Ahmed', 'Mohammed Al-Otaibi', 'Fatima Al-Qahtani', 'Aisha Al-Zahrani',
            'Omar Al-Ghamdi', 'Khalid Al-Shamri', 'Nora Al-Dosari', 'Sara Al-Mutairi',
            'Abdullah Al-Anzi', 'Youssef Al-Harbi', 'Maryam Al-Rashid', 'Hassan Al-Saud',
            'Layla Al-Faisal', 'Ali Al-Mansour', 'Zainab Al-Khalil',
        ];

        $productCategories = ['beverages', 'main_dishes', 'desserts', 'appetizers', 'combo_meals'];
        $peakHours = [12, 13, 14, 19, 20, 21]; // Lunch and dinner rush

        $revenues = [];

        // Generate revenue data for the last 12 months with seasonal patterns
        for ($month = 0; $month < 12; $month++) {
            $monthDate = Carbon::now()->subMonths($month);
            $daysInMonth = $monthDate->daysInMonth;

            // Seasonal multiplier (higher in winter months, Ramadan, holidays)
            $seasonalMultiplier = match ($monthDate->month) {
                12, 1, 2 => 1.3, // Winter months - higher sales
                3, 4 => 1.5, // Ramadan period - varies but often higher
                6, 7, 8 => 0.8, // Summer months - lower sales
                9, 10, 11 => 1.1, // Fall months - moderate sales
                default => 1.0
            };

            // Generate 15-25 revenue entries per month
            $entriesThisMonth = rand(15, 25);

            for ($i = 0; $i < $entriesThisMonth; $i++) {
                $day = rand(1, $daysInMonth);
                $hour = rand(8, 23); // Business hours
                $date = $monthDate->copy()->day($day)->hour($hour)->minute(rand(0, 59));

                // Peak hour multiplier
                $peakMultiplier = in_array($hour, $peakHours) ? 1.4 : 1.0;

                // Weekend multiplier (Friday and Saturday in Saudi Arabia)
                $weekendMultiplier = in_array($date->dayOfWeek, [5, 6]) ? 1.2 : 1.0;

                $type = $revenueTypes[array_rand($revenueTypes)];
                $category = $categories[array_rand($categories)];

                // Generate realistic amounts based on type and multipliers
                $baseAmount = match ($type) {
                    'sales' => rand(75, 1500), // 20-400 USD
                    'franchise_fee' => rand(37500, 187500), // 10k-50k USD (less frequent)
                    'royalty' => rand(1875, 18750), // 500-5k USD
                    'marketing_fee' => rand(750, 7500), // 200-2k USD
                    'other' => rand(375, 3750), // 100-1k USD
                    default => rand(375, 1875)
                };

                $amount = round($baseAmount * $seasonalMultiplier * $peakMultiplier * $weekendMultiplier);
                $taxAmount = round($amount * 0.15, 2); // 15% VAT
                $discountAmount = rand(0, round($amount * 0.15)); // Up to 15% discount
                $netAmount = $amount + $taxAmount - $discountAmount;

                // Payment status based on type and amount
                $paymentStatus = match (true) {
                    $amount > 50000 => ['pending', 'verified'][array_rand(['pending', 'verified'])],
                    $type === 'franchise_fee' => 'verified',
                    default => ['completed', 'completed', 'completed', 'pending'][array_rand(['completed', 'completed', 'completed', 'pending'])]
                };

                $revenueNumber = 'REV-'.$date->format('Ym').'-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT);
                $invoiceNumber = 'INV-'.$date->format('Ym').'-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT);

                $revenues[] = [
                    'revenue_number' => $revenueNumber,
                    'franchise_id' => $franchise->id,
                    'user_id' => $franchisorId,
                    'type' => $type,
                    'category' => $category,
                    'amount' => $amount,
                    'currency' => 'SAR',
                    'description' => $this->generateRevenueDescription($type, $category, $productCategories),
                    'revenue_date' => $date,
                    'period_year' => $date->year,
                    'period_month' => $date->month,
                    'source' => $sources[array_rand($sources)],
                    'customer_name' => $customerNames[array_rand($customerNames)],
                    'customer_email' => strtolower(str_replace([' ', '-'], ['', ''], $customerNames[array_rand($customerNames)])).'@'.['gmail.com', 'hotmail.com', 'outlook.com'][array_rand(['gmail.com', 'hotmail.com', 'outlook.com'])],
                    'invoice_number' => $invoiceNumber,
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'payment_status' => $paymentStatus,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => $discountAmount,
                    'net_amount' => $netAmount,
                    'status' => $statuses[array_rand($statuses)],
                    'verified_by' => $franchisorId,
                    'verified_at' => $date->addHours(rand(1, 24)),
                    'recorded_at' => $date,
                    'created_at' => $date,
                    'updated_at' => $date,
                ];
            }
        }

        Revenue::insert($revenues);
    }

    private function generateRevenueDescription(string $type, string $category, array $productCategories): string
    {
        return match ($type) {
            'sales' => 'Sales revenue from '.$productCategories[array_rand($productCategories)].' - '.$category,
            'franchise_fee' => 'Initial franchise fee payment for new unit',
            'royalty' => 'Monthly royalty payment - '.date('F Y'),
            'marketing_fee' => 'Marketing fund contribution for promotional campaigns',
            'other' => 'Miscellaneous revenue - '.['equipment rental', 'training fees', 'consultation services'][array_rand(['equipment rental', 'training fees', 'consultation services'])],
            default => 'Revenue from '.$category
        };
    }

    private function createRoyalties($franchisorId)
    {
        $franchise = Franchise::where('franchisor_id', $franchisorId)->first();
        if (! $franchise) {
            return;
        }

        // Get franchisee users
        $franchisees = User::where('role', 'franchisee')->pluck('id')->toArray();
        if (empty($franchisees)) {
            return;
        }

        $royaltyTypes = ['royalty', 'marketing_fee', 'technology_fee', 'training_fee', 'equipment_fee'];
        $statuses = ['draft', 'pending', 'paid', 'overdue', 'disputed', 'partially_paid'];
        $paymentMethods = ['bank_transfer', 'mada', 'credit_card', 'sadad', 'stc_pay', 'apple_pay', 'check'];
        $adjustmentReasons = [
            'promotional_discount', 'volume_bonus', 'early_payment_discount',
            'penalty_fee', 'compliance_bonus', 'performance_incentive',
        ];

        $royalties = [];

        // Create monthly royalty data for the last 18 months (more comprehensive history)
        for ($i = 17; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);

            // Seasonal adjustments for Saudi market
            $seasonalMultiplier = match ($date->month) {
                12, 1, 2 => 1.3, // Winter peak season
                6, 7, 8 => 0.8,  // Summer low season
                3, 4, 9, 10, 11 => 1.1, // Moderate seasons
                default => 1.0
            };

            // Ramadan adjustment (approximate)
            if (in_array($date->month, [3, 4, 5])) {
                $seasonalMultiplier *= 0.7; // Reduced activity during Ramadan
            }

            // Base revenue with growth trend
            $baseRevenue = rand(280000, 600000); // 75k-160k USD converted to SAR
            $growthFactor = 1 + (0.02 * (17 - $i)); // 2% monthly growth
            $grossRevenue = round($baseRevenue * $seasonalMultiplier * $growthFactor);

            // Dynamic royalty rates based on performance
            $royaltyPercentage = match (true) {
                $grossRevenue > 525000 => 5.5, // Reduced rate for high performers
                $grossRevenue > 450000 => 6.0, // Standard rate
                $grossRevenue > 375000 => 6.5, // Slightly higher for medium performers
                default => 7.0 // Higher rate for underperformers
            };

            $royaltyAmount = round($grossRevenue * ($royaltyPercentage / 100));

            // Marketing fee calculation
            $marketingFeePercentage = 2.5;
            $marketingFeeAmount = round($grossRevenue * ($marketingFeePercentage / 100));

            // Technology fee (fixed monthly)
            $technologyFeeAmount = rand(2250, 4500); // 600-1200 USD converted to SAR

            // Training fee (quarterly)
            $trainingFeeAmount = ($date->month % 3 === 0) ? rand(3750, 7500) : 0; // 1k-2k USD quarterly

            // Equipment fee (annual)
            $equipmentFeeAmount = ($date->month === 1) ? rand(11250, 22500) : 0; // 3k-6k USD annually

            // Calculate adjustments
            $adjustmentAmount = 0;
            $adjustmentReason = null;

            if (rand(1, 100) <= 25) { // 25% chance of adjustment
                $adjustmentType = $adjustmentReasons[array_rand($adjustmentReasons)];
                $adjustmentReason = $adjustmentType;

                $adjustmentAmount = match ($adjustmentType) {
                    'promotional_discount' => -round($royaltyAmount * 0.1), // 10% discount
                    'volume_bonus' => -round($royaltyAmount * 0.05), // 5% bonus
                    'early_payment_discount' => -round($royaltyAmount * 0.02), // 2% early payment
                    'penalty_fee' => round($royaltyAmount * 0.15), // 15% penalty
                    'compliance_bonus' => -round($royaltyAmount * 0.03), // 3% compliance bonus
                    'performance_incentive' => -round($royaltyAmount * 0.08), // 8% performance incentive
                    default => 0
                };
            }

            $totalAmount = $royaltyAmount + $marketingFeeAmount + $technologyFeeAmount +
                          $trainingFeeAmount + $equipmentFeeAmount + $adjustmentAmount;

            // Status logic based on date and payment patterns
            $status = match (true) {
                $i === 0 => 'draft', // Current month
                $i === 1 => rand(1, 100) <= 70 ? 'pending' : 'paid', // Last month
                $i <= 3 => rand(1, 100) <= 85 ? 'paid' : (rand(1, 100) <= 60 ? 'overdue' : 'disputed'),
                default => rand(1, 100) <= 95 ? 'paid' : 'overdue'
            };

            // Payment details
            $dueDate = $date->copy()->endOfMonth()->addDays(15);
            $paidDate = null;
            $paymentMethod = null;
            $paymentReference = null;
            $lateFee = 0;

            if ($status === 'paid') {
                $paidDate = $dueDate->copy()->subDays(rand(0, 10));
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
                $paymentReference = 'PAY-'.$date->format('Ym').'-'.rand(100000, 999999);
            } elseif ($status === 'overdue') {
                $lateFee = round($totalAmount * 0.05); // 5% late fee
            } elseif ($status === 'partially_paid') {
                $paidDate = $dueDate->copy()->addDays(rand(1, 5));
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
                $paymentReference = 'PAY-'.$date->format('Ym').'-'.rand(100000, 999999);
                $totalAmount = round($totalAmount * 0.7); // 70% paid
            }

            $royalties[] = [
                'royalty_number' => 'ROY-'.$date->format('Y-m').'-'.str_pad($franchise->id, 3, '0', STR_PAD_LEFT),
                'franchise_id' => $franchise->id,
                'franchisee_id' => $franchisees[array_rand($franchisees)],
                'type' => 'royalty',
                'period_year' => $date->year,
                'period_month' => $date->month,
                'period_start_date' => $date->copy()->startOfMonth(),
                'period_end_date' => $date->copy()->endOfMonth(),
                'gross_revenue' => $grossRevenue,
                'royalty_percentage' => $royaltyPercentage,
                'royalty_amount' => $royaltyAmount,
                'marketing_fee_percentage' => $marketingFeePercentage,
                'marketing_fee_amount' => $marketingFeeAmount,
                'technology_fee_amount' => $technologyFeeAmount,
                'training_fee_amount' => $trainingFeeAmount,
                'equipment_fee_amount' => $equipmentFeeAmount,
                'total_amount' => $totalAmount,
                'adjustments' => $adjustmentAmount,
                'adjustment_reason' => $adjustmentReason,
                'status' => $status,
                'due_date' => $dueDate,
                'paid_date' => $paidDate,
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentReference,
                'late_fee' => $lateFee,
                'is_auto_generated' => true,
                'generated_by' => $franchisorId,
                'notes' => $this->generateRoyaltyNotes($status, $adjustmentReason, $grossRevenue),
                'created_at' => $date,
                'updated_at' => $paidDate ?? $date,
            ];
        }

        Royalty::insert($royalties);
    }

    private function generateRoyaltyNotes(?string $status, ?string $adjustmentReason, int $grossRevenue): ?string
    {
        $notes = [];

        if ($adjustmentReason) {
            $notes[] = match ($adjustmentReason) {
                'promotional_discount' => 'Promotional discount applied for Q1 campaign participation',
                'volume_bonus' => 'Volume bonus for exceeding monthly targets',
                'early_payment_discount' => 'Early payment discount - paid 5 days before due date',
                'penalty_fee' => 'Late payment penalty applied',
                'compliance_bonus' => 'Compliance bonus for maintaining quality standards',
                'performance_incentive' => 'Performance incentive for exceptional customer satisfaction',
                default => 'Adjustment applied'
            };
        }

        if ($grossRevenue > 525000) {
            $notes[] = 'Exceptional performance - revenue exceeded 525k SAR';
        } elseif ($grossRevenue < 300000) {
            $notes[] = 'Below target performance - support team contacted';
        }

        if ($status === 'disputed') {
            $notes[] = 'Revenue figures under review - accounting team investigating';
        }

        return empty($notes) ? null : implode('. ', $notes);
    }

    private function createTransactions($franchisorId)
    {
        $franchise = Franchise::where('franchisor_id', $franchisorId)->first();
        if (! $franchise) {
            return;
        }

        $transactionTypes = ['revenue', 'expense', 'royalty', 'marketing_fee', 'franchise_fee', 'refund', 'adjustment'];
        $revenueCategories = ['food_sales', 'beverage_sales', 'delivery_fees', 'catering', 'merchandise', 'gift_cards'];
        $expenseCategories = ['cost_of_goods', 'labor', 'rent', 'utilities', 'marketing', 'equipment', 'supplies', 'insurance', 'taxes', 'maintenance', 'training', 'professional_services'];
        $statuses = ['pending', 'completed', 'cancelled', 'refunded', 'processing', 'failed'];
        $paymentMethods = ['cash', 'mada', 'credit_card', 'bank_transfer', 'stc_pay', 'sadad', 'apple_pay', 'visa', 'mastercard'];

        $vendors = [
            'Al-Ahmed Food Suppliers' => ['cost_of_goods', 'supplies'],
            'Al-Otaibi Trading Co.' => ['equipment', 'maintenance'],
            'Al-Qahtani Equipment' => ['equipment', 'maintenance'],
            'Al-Zahrani Services' => ['professional_services', 'maintenance'],
            'Al-Ghamdi Logistics' => ['cost_of_goods', 'supplies'],
            'Saudi Electric Company' => ['utilities'],
            'Saudi Telecom Company' => ['utilities'],
            'Al-Rajhi Bank' => ['professional_services'],
            'SABB Bank' => ['professional_services'],
            'Almarai Company' => ['cost_of_goods'],
            'Nadec Company' => ['cost_of_goods'],
            'Al-Watania Poultry' => ['cost_of_goods'],
            'Cleaning Masters' => ['maintenance'],
            'Tech Solutions KSA' => ['professional_services'],
            'Marketing Plus' => ['marketing'],
        ];

        $customers = [
            'Ahmed Al-Ahmed', 'Mohammed Al-Otaibi', 'Fatima Al-Qahtani', 'Aisha Al-Zahrani',
            'Omar Al-Ghamdi', 'Sara Al-Mansouri', 'Khalid Al-Rashid', 'Noura Al-Harbi',
            'Abdullah Al-Saud', 'Maryam Al-Dosari', 'Fahad Al-Mutairi', 'Hala Al-Shehri',
            'Talal Al-Faisal', 'Reem Al-Qahtani', 'Saud Al-Otaibi',
        ];

        $transactions = [];

        // Create more realistic transaction patterns over 6 months
        for ($i = 1; $i <= 300; $i++) {
            $date = Carbon::now()->subDays(rand(1, 180));
            $type = $transactionTypes[array_rand($transactionTypes)];

            // Determine category based on type
            if ($type === 'revenue') {
                $category = $revenueCategories[array_rand($revenueCategories)];
            } elseif ($type === 'expense') {
                $category = $expenseCategories[array_rand($expenseCategories)];
            } else {
                $category = $type;
            }

            // Calculate realistic amounts based on type and category
            $amount = $this->calculateTransactionAmount($type, $category, $date);

            // Seasonal adjustments
            $seasonalMultiplier = match ($date->month) {
                12, 1, 2 => 1.2, // Winter peak
                6, 7, 8 => 0.9,  // Summer low
                default => 1.0
            };

            $amount = round($amount * $seasonalMultiplier);

            // Determine vendor/customer based on transaction type
            $vendorCustomer = null;
            if ($type === 'expense') {
                $availableVendors = array_filter($vendors, function ($categories) use ($category) {
                    return in_array($category, $categories);
                });
                $vendorCustomer = ! empty($availableVendors) ?
                    array_rand($availableVendors) :
                    array_rand($vendors);
            } else {
                $vendorCustomer = $customers[array_rand($customers)];
            }

            // Status logic based on type and amount
            $status = $this->determineTransactionStatus($type, $amount, $date);

            // Payment method logic
            $paymentMethod = $this->selectPaymentMethod($type, $amount);

            $transactions[] = [
                'transaction_number' => 'TXN-'.$date->format('Ym').'-'.str_pad($i, 4, '0', STR_PAD_LEFT),
                'type' => $type,
                'category' => $category,
                'amount' => $amount,
                'currency' => 'SAR',
                'description' => $this->generateTransactionDescription($type, $category, $amount),
                'franchise_id' => $franchise->id,
                'user_id' => $franchisorId,
                'transaction_date' => $date,
                'status' => $status,
                'payment_method' => $paymentMethod,
                'reference_number' => $this->generateReferenceNumber($type, $date),
                'vendor_customer' => $vendorCustomer,
                'notes' => $this->generateTransactionNotes($type, $category, $status),
                'tax_amount' => $type === 'revenue' ? round($amount * 0.15) : 0, // 15% VAT
                'discount_amount' => ($type === 'revenue' && rand(1, 100) <= 10) ? round($amount * 0.05) : 0,
                'created_at' => $date,
                'updated_at' => $date,
            ];
        }

        Transaction::insert($transactions);
    }

    private function calculateTransactionAmount(string $type, string $category, Carbon $date): int
    {
        return match ($type) {
            'revenue' => match ($category) {
                'food_sales' => rand(7500, 75000), // 2k-20k USD
                'beverage_sales' => rand(1875, 18750), // 500-5k USD
                'delivery_fees' => rand(375, 1875), // 100-500 USD
                'catering' => rand(18750, 112500), // 5k-30k USD
                'merchandise' => rand(750, 7500), // 200-2k USD
                'gift_cards' => rand(3750, 37500), // 1k-10k USD
                default => rand(3750, 37500)
            },
            'expense' => match ($category) {
                'cost_of_goods' => rand(15000, 150000), // 4k-40k USD
                'labor' => rand(22500, 112500), // 6k-30k USD
                'rent' => rand(37500, 75000), // 10k-20k USD
                'utilities' => rand(3750, 18750), // 1k-5k USD
                'marketing' => rand(7500, 37500), // 2k-10k USD
                'equipment' => rand(18750, 187500), // 5k-50k USD
                'supplies' => rand(3750, 18750), // 1k-5k USD
                'insurance' => rand(7500, 22500), // 2k-6k USD
                'taxes' => rand(11250, 56250), // 3k-15k USD
                'maintenance' => rand(3750, 15000), // 1k-4k USD
                'training' => rand(1875, 11250), // 500-3k USD
                'professional_services' => rand(7500, 37500), // 2k-10k USD
                default => rand(3750, 18750)
            },
            'royalty' => rand(18750, 56250), // 5k-15k USD
            'marketing_fee' => rand(7500, 22500), // 2k-6k USD
            'franchise_fee' => rand(187500, 375000), // 50k-100k USD
            'refund' => rand(375, 7500), // 100-2k USD
            'adjustment' => rand(-7500, 7500), // -2k to +2k USD
            default => rand(3750, 18750)
        };
    }

    private function determineTransactionStatus(string $type, int $amount, Carbon $date): string
    {
        $daysSinceTransaction = Carbon::now()->diffInDays($date);

        return match (true) {
            $daysSinceTransaction <= 1 => rand(1, 100) <= 70 ? 'pending' : 'processing',
            $daysSinceTransaction <= 3 => rand(1, 100) <= 85 ? 'completed' : 'pending',
            $daysSinceTransaction <= 7 => rand(1, 100) <= 95 ? 'completed' : (rand(1, 100) <= 80 ? 'pending' : 'failed'),
            $amount > 75000 => rand(1, 100) <= 90 ? 'completed' : 'pending', // Large amounts take longer
            default => rand(1, 100) <= 98 ? 'completed' : 'cancelled'
        };
    }

    private function selectPaymentMethod(string $type, int $amount): string
    {
        if ($type === 'expense') {
            return match (true) {
                $amount > 37500 => ['bank_transfer', 'credit_card'][array_rand(['bank_transfer', 'credit_card'])],
                $amount > 7500 => ['bank_transfer', 'credit_card', 'mada'][array_rand(['bank_transfer', 'credit_card', 'mada'])],
                default => ['cash', 'mada', 'credit_card'][array_rand(['cash', 'mada', 'credit_card'])]
            };
        }

        return match (true) {
            $amount > 18750 => ['credit_card', 'bank_transfer'][array_rand(['credit_card', 'bank_transfer'])],
            $amount > 3750 => ['mada', 'credit_card', 'stc_pay'][array_rand(['mada', 'credit_card', 'stc_pay'])],
            default => ['cash', 'mada', 'stc_pay'][array_rand(['cash', 'mada', 'stc_pay'])]
        };
    }

    private function generateTransactionDescription(string $type, string $category, int $amount): string
    {
        return match ($type) {
            'revenue' => match ($category) {
                'food_sales' => 'Food sales revenue - '.['lunch rush', 'dinner service', 'breakfast items', 'daily specials'][array_rand(['lunch rush', 'dinner service', 'breakfast items', 'daily specials'])],
                'beverage_sales' => 'Beverage sales - '.['coffee & tea', 'soft drinks', 'fresh juices', 'specialty drinks'][array_rand(['coffee & tea', 'soft drinks', 'fresh juices', 'specialty drinks'])],
                'delivery_fees' => 'Delivery service fees - '.['Talabat', 'Jahez', 'HungerStation', 'Mrsool'][array_rand(['Talabat', 'Jahez', 'HungerStation', 'Mrsool'])],
                'catering' => 'Catering service - '.['corporate event', 'wedding', 'private party', 'conference'][array_rand(['corporate event', 'wedding', 'private party', 'conference'])],
                'merchandise' => 'Merchandise sales - branded items and souvenirs',
                'gift_cards' => 'Gift card sales - customer prepayments',
                default => 'Revenue from '.$category
            },
            'expense' => match ($category) {
                'cost_of_goods' => 'Cost of goods sold - '.['fresh ingredients', 'meat & poultry', 'dairy products', 'beverages'][array_rand(['fresh ingredients', 'meat & poultry', 'dairy products', 'beverages'])],
                'labor' => 'Staff wages and benefits - '.['kitchen staff', 'service staff', 'management', 'part-time workers'][array_rand(['kitchen staff', 'service staff', 'management', 'part-time workers'])],
                'rent' => 'Monthly rent payment for restaurant premises',
                'utilities' => 'Utility bills - '.['electricity', 'water', 'gas', 'internet & phone'][array_rand(['electricity', 'water', 'gas', 'internet & phone'])],
                'marketing' => 'Marketing expenses - '.['social media ads', 'print materials', 'promotional events', 'influencer partnerships'][array_rand(['social media ads', 'print materials', 'promotional events', 'influencer partnerships'])],
                'equipment' => 'Equipment purchase/lease - '.['kitchen equipment', 'POS system', 'furniture', 'maintenance tools'][array_rand(['kitchen equipment', 'POS system', 'furniture', 'maintenance tools'])],
                'supplies' => 'Operating supplies - '.['packaging', 'cleaning supplies', 'uniforms', 'office supplies'][array_rand(['packaging', 'cleaning supplies', 'uniforms', 'office supplies'])],
                'insurance' => 'Insurance premiums - '.['general liability', 'property insurance', 'workers compensation'][array_rand(['general liability', 'property insurance', 'workers compensation'])],
                'taxes' => 'Tax payments - '.['VAT', 'municipal fees', 'business license'][array_rand(['VAT', 'municipal fees', 'business license'])],
                'maintenance' => 'Maintenance and repairs - '.['equipment servicing', 'facility maintenance', 'deep cleaning'][array_rand(['equipment servicing', 'facility maintenance', 'deep cleaning'])],
                'training' => 'Staff training and development programs',
                'professional_services' => 'Professional services - '.['accounting', 'legal consultation', 'IT support'][array_rand(['accounting', 'legal consultation', 'IT support'])],
                default => 'Expense for '.$category
            },
            'royalty' => 'Monthly royalty payment to franchisor',
            'marketing_fee' => 'Marketing fund contribution for brand promotion',
            'franchise_fee' => 'Initial franchise fee payment',
            'refund' => 'Customer refund - order cancellation or quality issue',
            'adjustment' => 'Accounting adjustment - '.['inventory correction', 'pricing adjustment', 'promotional discount'][array_rand(['inventory correction', 'pricing adjustment', 'promotional discount'])],
            default => ucfirst($type).' transaction'
        };
    }

    private function generateReferenceNumber(string $type, Carbon $date): string
    {
        $prefix = match ($type) {
            'revenue' => 'REV',
            'expense' => 'EXP',
            'royalty' => 'ROY',
            'marketing_fee' => 'MKT',
            'franchise_fee' => 'FRN',
            'refund' => 'REF',
            'adjustment' => 'ADJ',
            default => 'TXN'
        };

        return $prefix.'-'.$date->format('Ym').'-'.rand(100000, 999999);
    }

    private function generateTransactionNotes(string $type, string $category, string $status): ?string
    {
        $notes = [];

        if ($status === 'failed') {
            $notes[] = 'Transaction failed - '.['insufficient funds', 'payment declined', 'network error'][array_rand(['insufficient funds', 'payment declined', 'network error'])];
        } elseif ($status === 'cancelled') {
            $notes[] = 'Transaction cancelled by '.['customer', 'merchant', 'system'][array_rand(['customer', 'merchant', 'system'])];
        } elseif ($status === 'refunded') {
            $notes[] = 'Full refund processed - '.['customer request', 'quality issue', 'order error'][array_rand(['customer request', 'quality issue', 'order error'])];
        }

        if ($type === 'expense' && in_array($category, ['equipment', 'maintenance'])) {
            $notes[] = 'Warranty period: '.rand(6, 36).' months';
        }

        if ($type === 'revenue' && $category === 'catering') {
            $notes[] = 'Event date: '.Carbon::now()->addDays(rand(1, 30))->format('Y-m-d');
        }

        return empty($notes) ? null : implode('. ', $notes);
    }

    private function createTimelineData($franchiseId): void
    {
        $franchise = Franchise::find($franchiseId);
        if (! $franchise) {
            return;
        }

        // Timeline milestones and development activities
        $timelineItems = [
            [
                'title' => 'Franchise Agreement Signed',
                'description' => 'Initial franchise agreement has been signed and approved',
                'type' => 'onboarding',
                'status' => 'completed',
                'week' => 'Week 1',
                'icon' => 'tabler-file-check',
                'days_ago' => 45,
            ],
            [
                'title' => 'Site Location Approved',
                'description' => 'Franchise location has been approved and lease signed',
                'type' => 'onboarding',
                'status' => 'completed',
                'week' => 'Week 2',
                'icon' => 'tabler-map-pin',
                'days_ago' => 38,
            ],
            [
                'title' => 'Initial Training Completed',
                'description' => 'Franchisee has completed initial training program',
                'type' => 'training',
                'status' => 'completed',
                'week' => 'Week 4',
                'icon' => 'tabler-school',
                'days_ago' => 24,
            ],
            [
                'title' => 'Equipment Installation',
                'description' => 'Kitchen and POS equipment installation in progress',
                'type' => 'operations',
                'status' => 'scheduled',
                'week' => 'Week 6',
                'icon' => 'tabler-tools',
                'days_ago' => -3,
            ],
            [
                'title' => 'Staff Recruitment',
                'description' => 'Hiring and training of initial staff members',
                'type' => 'operations',
                'status' => 'scheduled',
                'week' => 'Week 7',
                'icon' => 'tabler-users',
                'days_ago' => -10,
            ],
            [
                'title' => 'Marketing Campaign Launch',
                'description' => 'Local marketing campaign for grand opening',
                'type' => 'marketing',
                'status' => 'scheduled',
                'week' => 'Week 8',
                'icon' => 'tabler-speakerphone',
                'days_ago' => -17,
            ],
            [
                'title' => 'Health Permit Approval',
                'description' => 'Final health department inspection and permit approval',
                'type' => 'compliance',
                'status' => 'overdue',
                'week' => 'Week 5',
                'icon' => 'tabler-certificate',
                'days_ago' => 3,
            ],
            [
                'title' => 'Grand Opening Event',
                'description' => 'Official grand opening ceremony and first day of operations',
                'type' => 'operations',
                'status' => 'scheduled',
                'week' => 'Week 9',
                'icon' => 'tabler-confetti',
                'days_ago' => -24,
            ],
            [
                'title' => 'First Month Review',
                'description' => 'Performance review and feedback session after first month',
                'type' => 'operations',
                'status' => 'scheduled',
                'week' => 'Week 13',
                'icon' => 'tabler-chart-line',
                'days_ago' => -52,
            ],
            [
                'title' => 'Quality Audit',
                'description' => 'Quarterly quality and compliance audit',
                'type' => 'compliance',
                'status' => 'scheduled',
                'week' => 'Week 25',
                'icon' => 'tabler-clipboard-check',
                'days_ago' => -136,
            ],
        ];

        $tasks = [];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $taskStatuses = [
            'completed' => 'completed',
            'scheduled' => 'pending',
            'overdue' => 'in_progress',
        ];

        foreach ($timelineItems as $index => $item) {
            $dueDate = now()->addDays($item['days_ago']);
            $createdDate = $dueDate->copy()->subDays(rand(1, 7));

            $completedAt = null;
            $startedAt = null;

            if ($item['status'] === 'completed') {
                $startedAt = $createdDate->copy()->addDays(rand(1, 3));
                $completedAt = $dueDate->copy()->subDays(rand(0, 2));
            } elseif ($item['status'] === 'overdue') {
                $startedAt = $createdDate->copy()->addDays(rand(1, 3));
            }

            $tasks[] = [
                'title' => $item['title'],
                'description' => $item['description'],
                'type' => $item['type'],
                'priority' => $priorities[array_rand($priorities)],
                'status' => $taskStatuses[$item['status']],
                'assigned_to' => $franchise->franchisor_id,
                'created_by' => $franchise->franchisor_id,
                'franchise_id' => $franchise->id,
                'unit_id' => null,
                'lead_id' => null,
                'due_date' => $dueDate->format('Y-m-d'),
                'started_at' => $startedAt?->format('Y-m-d H:i:s'),
                'completed_at' => $completedAt?->format('Y-m-d H:i:s'),
                'estimated_hours' => rand(4, 40),
                'actual_hours' => $item['status'] === 'completed' ? rand(4, 40) : null,
                'notes' => 'Timeline milestone for franchise development process',
                'completion_notes' => $item['status'] === 'completed' ? 'Successfully completed as scheduled' : null,
                'is_recurring' => false,
                'created_at' => $createdDate->format('Y-m-d H:i:s'),
                'updated_at' => ($completedAt ?? $startedAt ?? $createdDate)->format('Y-m-d H:i:s'),
            ];
        }

        Task::insert($tasks);
    }
}
