<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Franchise;
use App\Models\Inventory;
use App\Models\Lead;
use App\Models\MarketplaceInquiry;
use App\Models\Note;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Property;
use App\Models\Revenue;
use App\Models\Review;
use App\Models\Royalty;
use App\Models\Staff;
use App\Models\Task;
use App\Models\TechnicalRequest;
use App\Models\Transaction;
use App\Models\Unit;
use App\Models\UnitPerformance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MinimalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting minimal data seeding...');

        // 1. Create 4 Users with 4 Roles
        $this->command->info('Creating users...');

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@cheetah.com.sa',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+966500000001',
            'status' => 'active',
            'email_verified_at' => now(),
            'nationality' => 'Saudi Arabia',
            'city' => 'Riyadh',
            'state' => 'Riyadh Province',
            'address' => 'King Fahd Road, Al Olaya District',
        ]);

        $franchisor = User::create([
            'name' => 'Franchisor User',
            'email' => 'franchisor@cheetah.com.sa',
            'password' => Hash::make('password'),
            'role' => 'franchisor',
            'phone' => '+966500000002',
            'status' => 'active',
            'email_verified_at' => now(),
            'nationality' => 'Saudi Arabia',
            'city' => 'Riyadh',
            'state' => 'Riyadh Province',
            'address' => 'King Fahd Road, Al Olaya District',
        ]);

        $franchisee = User::create([
            'name' => 'Franchisee User',
            'email' => 'franchisee@cheetah.com.sa',
            'password' => Hash::make('password'),
            'role' => 'franchisee',
            'phone' => '+966500000003',
            'status' => 'active',
            'email_verified_at' => now(),
            'nationality' => 'Saudi Arabia',
            'city' => 'Riyadh',
            'state' => 'Riyadh Province',
            'address' => 'King Fahd Road, Al Olaya District',
        ]);

        $broker = User::create([
            'name' => 'Broker User',
            'email' => 'broker@cheetah.com.sa',
            'password' => Hash::make('password'),
            'role' => 'broker',
            'phone' => '+966500000004',
            'status' => 'active',
            'email_verified_at' => now(),
            'nationality' => 'Saudi Arabia',
            'city' => 'Riyadh',
            'state' => 'Riyadh Province',
            'address' => 'King Fahd Road, Al Olaya District',
        ]);

        $this->command->info('✅ Created 4 users');

        // Create 5 Brokers related to franchisor
        $this->command->info('Creating brokers...');

        $brokers = [];
        $brokerData = [
            ['name' => 'Ahmed Al-Salem', 'email' => 'ahmed.salem@cheetah.com.sa'],
            ['name' => 'Fatima Al-Khalid', 'email' => 'fatima.khalid@cheetah.com.sa'],
            ['name' => 'Mohammed Al-Rashid', 'email' => 'mohammed.rashid@cheetah.com.sa'],
            ['name' => 'Layla Al-Hassan', 'email' => 'layla.hassan@cheetah.com.sa'],
            ['name' => 'Omar Al-Mansour', 'email' => 'omar.mansour@cheetah.com.sa'],
        ];

        foreach ($brokerData as $index => $data) {
            $brokers[] = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'broker',
                'phone' => '+96650000' . str_pad($index + 5, 4, '0', STR_PAD_LEFT),
                'status' => 'active',
                'email_verified_at' => now(),
                'nationality' => 'Saudi Arabia',
                'city' => 'Riyadh',
                'state' => 'Riyadh Province',
                'address' => 'King Fahd Road, Al Olaya District',
            ]);
        }

        $this->command->info('✅ Created 5 brokers');

        // 2. Create Franchise
        $this->command->info('Creating franchise...');

        $franchise = Franchise::create([
            'franchisor_id' => $franchisor->id,
            'broker_id' => $brokers[0]->id,

            // Franchise Details
            'business_name' => 'Cheetah International',
            'brand_name' => 'Cheetah',
            'website' => 'https://cheetah.com.sa',
            'logo' => null, // Can be updated later
            'description' => 'Premium fast-food restaurant franchise specializing in gourmet burgers and artisan coffee. We offer a unique dining experience with high-quality ingredients and exceptional customer service.',

            // Legal Details
            'business_registration_number' => 'BR-' . rand(100000, 999999),
            'tax_id' => 'TAX-' . rand(100000, 999999),
            'business_type' => 'llc', // Business structure: llc, corporation, partnership, sole_proprietorship
            'industry' => 'Food & Beverage',
            'established_date' => Carbon::now()->subYears(3),

            // Contact Details (Headquarters)
            'headquarters_country' => 'Saudi Arabia',
            'headquarters_city' => 'Riyadh',
            'headquarters_address' => 'King Fahd Road, Al Olaya District, Riyadh 12345',
            'contact_phone' => '+966112345678',
            'contact_email' => 'info@cheetah.com.sa',

            // Financial Details
            'franchise_fee' => 50000.00,
            'royalty_percentage' => 10.00,
            'marketing_fee_percentage' => 3.00,

            // Social Media & Business Hours (JSON fields)
            'social_media' => json_encode([
                'facebook' => 'https://facebook.com/cheetah',
                'instagram' => 'https://instagram.com/cheetah',
                'twitter' => 'https://twitter.com/cheetah',
                'linkedin' => 'https://linkedin.com/company/cheetah',
            ]),
            'business_hours' => json_encode([
                'monday' => ['open' => '08:00', 'close' => '22:00'],
                'tuesday' => ['open' => '08:00', 'close' => '22:00'],
                'wednesday' => ['open' => '08:00', 'close' => '22:00'],
                'thursday' => ['open' => '08:00', 'close' => '22:00'],
                'friday' => ['open' => '14:00', 'close' => '23:00'],
                'saturday' => ['open' => '08:00', 'close' => '23:00'],
                'sunday' => ['open' => '08:00', 'close' => '22:00'],
            ]),

            // Operational
            'total_units' => 1,
            'active_units' => 1,
            'status' => 'active',
            'is_marketplace_listed' => true,
        ]);

        $this->command->info('✅ Created franchise');

        // Link brokers and franchisee to franchise
        $this->command->info('Linking users to franchise...');

        foreach ($brokers as $broker) {
            $broker->update(['franchise_id' => $franchise->id]);
        }

        $franchisee->update(['franchise_id' => $franchise->id]);
        $broker->update(['franchise_id' => $franchise->id]);

        $this->command->info('✅ Linked brokers and franchisee to franchise');

        // Create 5 Properties for brokers
        $this->command->info('Creating properties...');

        $properties = [];
        $propertyData = [
            [
                'title' => 'Prime Retail Space - Riyadh Mall',
                'property_type' => 'retail',
                'city' => 'Riyadh',
                'size_sqm' => 250.00,
                'monthly_rent' => 35000.00,
                'status' => 'available',
            ],
            [
                'title' => 'Food Court Location - Jeddah Plaza',
                'property_type' => 'food_court',
                'city' => 'Jeddah',
                'size_sqm' => 120.00,
                'monthly_rent' => 25000.00,
                'status' => 'available',
            ],
            [
                'title' => 'Kiosk Space - Dammam Center',
                'property_type' => 'kiosk',
                'city' => 'Dammam',
                'size_sqm' => 30.00,
                'monthly_rent' => 12000.00,
                'status' => 'available',
            ],
            [
                'title' => 'Standalone Building - Khobar District',
                'property_type' => 'standalone',
                'city' => 'Khobar',
                'size_sqm' => 400.00,
                'monthly_rent' => 55000.00,
                'status' => 'under_negotiation',
            ],
            [
                'title' => 'Office Space - Medina Business Park',
                'property_type' => 'office',
                'city' => 'Medina',
                'size_sqm' => 150.00,
                'monthly_rent' => 20000.00,
                'status' => 'available',
            ],
        ];

        foreach ($propertyData as $index => $data) {
            $properties[] = Property::create([
                'broker_id' => $brokers[$index]->id,
                'title' => $data['title'],
                'description' => 'Excellent location for franchise business. High foot traffic area with great visibility.',
                'property_type' => $data['property_type'],
                'size_sqm' => $data['size_sqm'],
                'state_province' => 'Riyadh Province',
                'city' => $data['city'],
                'address' => 'Main Street, Commercial District',
                'postal_code' => rand(10000, 99999),
                'latitude' => 24.7136 + (rand(-100, 100) / 100),
                'longitude' => 46.6753 + (rand(-100, 100) / 100),
                'monthly_rent' => $data['monthly_rent'],
                'deposit_amount' => $data['monthly_rent'] * 2,
                'lease_term_months' => 36,
                'available_from' => Carbon::now()->addDays(rand(7, 60)),
                'amenities' => json_encode(['parking', 'security', 'wifi', 'air_conditioning']),
                'images' => json_encode([
                    ['url' => 'properties/property_' . ($index + 1) . '_1.jpg', 'alt' => 'Main View'],
                    ['url' => 'properties/property_' . ($index + 1) . '_2.jpg', 'alt' => 'Interior'],
                ]),
                'status' => $data['status'],
                'contact_info' => $brokers[$index]->phone . ' | ' . $brokers[$index]->email,
            ]);
        }

        $this->command->info('✅ Created 5 properties');

        // Create 5 Marketplace Inquiries
        $this->command->info('Creating marketplace inquiries...');

        $inquiryData = [
            [
                'name' => 'Abdullah Al-Rashid',
                'email' => 'abdullah.rashid@example.com',
                'phone' => '+966501234567',
                'type' => 'franchise',
                'message' => 'I am interested in opening a Cheetah franchise in Riyadh. Please provide more details.',
                'status' => 'new',
            ],
            [
                'name' => 'Sara Al-Masri',
                'email' => 'sara.masri@example.com',
                'phone' => '+966502345678',
                'type' => 'property',
                'message' => 'I would like to know more about the retail space in Riyadh Mall.',
                'status' => 'contacted',
            ],
            [
                'name' => 'Mohammed Al-Faisal',
                'email' => 'mohammed.faisal@example.com',
                'phone' => '+966503456789',
                'type' => 'franchise',
                'message' => 'Looking to invest in a franchise opportunity in Jeddah.',
                'status' => 'contacted',
            ],
            [
                'name' => 'Nora Al-Hassan',
                'email' => 'nora.hassan@example.com',
                'phone' => '+966504567890',
                'type' => 'property',
                'message' => 'Interested in the food court location in Jeddah Plaza.',
                'status' => 'new',
            ],
            [
                'name' => 'Khaled Al-Zahrani',
                'email' => 'khaled.zahrani@example.com',
                'phone' => '+966505678901',
                'type' => 'franchise',
                'message' => 'Please send franchise information and investment requirements.',
                'status' => 'closed',
            ],
        ];

        foreach ($inquiryData as $index => $data) {
            $inquiryType = $data['type'];

            MarketplaceInquiry::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'inquiry_type' => $inquiryType,
                'franchise_id' => $inquiryType === 'franchise' ? $franchise->id : null,
                'property_id' => $inquiryType === 'property' ? $properties[($index < 2 ? 0 : 1)]->id : null,
                'message' => $data['message'],
                'investment_budget' => rand(100000, 500000) . ' SAR',
                'preferred_location' => ['Riyadh', 'Jeddah', 'Dammam', 'Any location'][rand(0, 3)],
                'timeline' => ['Immediate', 'Within 1 month', 'Within 3 months', 'Within 6 months'][rand(0, 3)],
                'status' => $data['status'],
            ]);
        }

        $this->command->info('✅ Created 5 marketplace inquiries');

        // 3. Create 1 Unit linked to franchisee
        $this->command->info('Creating unit...');

        $unit = Unit::create([
            'franchise_id' => $franchise->id,
            'franchisee_id' => $franchisee->id,
            'unit_name' => 'Riyadh Downtown Branch',
            'unit_code' => 'RYD-001',
            'unit_type' => 'store',
            'nationality' => 'Saudi Arabia',
            'state_province' => 'Riyadh Province',
            'city' => 'Riyadh',
            'address' => 'King Fahd Road, Al Olaya District',
            'postal_code' => '12345',
            'phone' => '+966112345679',
            'email' => 'riyadh@cheetah.com.sa',
            'size_sqft' => 2700.00, // ~250 sqm
            'monthly_rent' => 15000.00,
            'initial_investment' => 300000.00,
            'lease_start_date' => Carbon::now()->subMonths(6),
            'lease_end_date' => Carbon::now()->addYears(5),
            'opening_date' => Carbon::now()->subMonths(3),
            'status' => 'active',
            'employee_count' => 5,
        ]);

        $this->command->info('✅ Created unit');

        // Create 5 Monthly Unit Performance Records
        $this->command->info('Creating unit performance records...');

        for ($i = 0; $i < 5; $i++) {
            $periodDate = Carbon::now()->subMonths($i)->startOfMonth();
            $revenue = rand(80000, 150000);
            $expenses = rand(40000, 80000);
            $royalties = $revenue * 0.10;
            $profit = $revenue - $expenses - $royalties;
            $totalTransactions = rand(150, 400);

            UnitPerformance::create([
                'franchise_id' => $franchise->id,
                'unit_id' => $unit->id,
                'period_type' => 'monthly',
                'period_date' => $periodDate,
                'revenue' => $revenue,
                'expenses' => $expenses,
                'royalties' => $royalties,
                'profit' => max($profit, 0),
                'total_transactions' => $totalTransactions,
                'average_transaction_value' => round($revenue / $totalTransactions, 2),
                'customer_reviews_count' => rand(20, 60),
                'customer_rating' => round(rand(35, 50) / 10, 1), // 3.5 to 5.0
                'employee_count' => 5,
                'customer_satisfaction_score' => round(rand(70, 98) / 10, 1), // 7.0 to 9.8 out of 10
                'growth_rate' => round(rand(-50, 200) / 10, 1), // -5.0 to 20.0
                'additional_metrics' => null,
            ]);
        }

        $this->command->info('✅ Created 5 unit performance records');

        // 4. Create 5 Products
        $this->command->info('Creating products...');

        $products = [];
        $productNames = [
            ['name' => 'Classic Burger', 'price' => 45.00, 'category' => 'Main Course'],
            ['name' => 'Spicy Chicken Wings', 'price' => 35.00, 'category' => 'Appetizer'],
            ['name' => 'Caesar Salad', 'price' => 28.00, 'category' => 'Salad'],
            ['name' => 'Chocolate Lava Cake', 'price' => 25.00, 'category' => 'Dessert'],
            ['name' => 'Fresh Orange Juice', 'price' => 15.00, 'category' => 'Beverage'],
        ];

        foreach ($productNames as $productData) {
            $product = Product::create([
                'franchise_id' => $franchise->id,
                'name' => $productData['name'],
                'description' => 'Delicious ' . $productData['name'],
                'category' => $productData['category'],
                'unit_price' => $productData['price'],
                'cost_price' => $productData['price'] * 0.4, // 40% cost
                'stock' => 100,
                'minimum_stock' => 20,
                'sku' => 'SKU-' . strtoupper(substr(str_replace(' ', '', $productData['name']), 0, 6)),
                'status' => 'active',
            ]);
            $products[] = $product;
        }

        $this->command->info('✅ Created 5 products');

        // 5. Create Inventory Management for 5 products
        $this->command->info('Creating inventory...');

        $inventoryRecords = [];
        foreach ($products as $index => $product) {
            $inventoryRecords[] = Inventory::create([
                'unit_id' => $unit->id,
                'product_id' => $product->id,
                'quantity' => 100 + ($index * 20), // 100, 120, 140, 160, 180
                'reorder_level' => 30,
            ]);
        }

        $this->command->info('✅ Created inventory for 5 products');

        // 6. Create 5 Sales (Revenues) for each inventory product
        $this->command->info('Creating sales for unit inventory...');

        foreach ($products as $i => $product) {
            $quantity = rand(2, 5);
            $unitPrice = $product->unit_price;
            $grossAmount = $unitPrice * $quantity;

            // Create revenue record
            Revenue::create([
                'franchise_id' => $franchise->id,
                'unit_id' => $unit->id,
                'user_id' => $franchisee->id,
                'revenue_number' => 'REV-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'type' => 'sales',
                'amount' => $grossAmount,
                'tax_amount' => $grossAmount * 0.15, // 15% VAT
                'net_amount' => $grossAmount * 1.15,
                'revenue_date' => Carbon::now()->subDays(rand(1, 30)),
                'description' => $product->name . ' - Unit Inventory Sale',
                'customer_name' => 'Customer ' . ($i + 1),
                'customer_email' => 'customer' . ($i + 1) . '@example.com',
                'payment_method' => ['cash', 'credit_card', 'mada'][rand(0, 2)],
                'status' => 'verified',
            ]);

            // Reduce inventory quantity using DB update
            DB::table('unit_product_inventories')
                ->where('unit_id', $unit->id)
                ->where('product_id', $product->id)
                ->decrement('quantity', $quantity);
        }

        $this->command->info('✅ Created 5 sales for unit inventory products and reduced stock');

        // 7. Create 5 Expenses
        $this->command->info('Creating expenses...');

        $expenseCategories = [
            'utilities',
            'labor',
            'marketing',
            'equipment',
            'supplies',
        ];

        foreach ($expenseCategories as $i => $category) {
            $amount = rand(1000, 5000);

            Transaction::create([
                'franchise_id' => $franchise->id,
                'unit_id' => $unit->id,
                'user_id' => $franchisee->id,
                'transaction_number' => 'EXP-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'type' => 'expense',
                'category' => $category,
                'amount' => $amount,
                'currency' => 'SAR',
                'transaction_date' => Carbon::now()->subDays(rand(1, 30)),
                'description' => ucfirst($category) . ' payment for ' . Carbon::now()->format('F Y'),
                'payment_method' => ['bank_transfer', 'credit_card', 'cash'][rand(0, 2)],
                'vendor_customer' => 'Vendor ' . ($i + 1),
                'status' => 'completed',
            ]);
        }

        $this->command->info('✅ Created 5 expenses');

        // 8. Create 5 Royalties
        $this->command->info('Creating royalties...');

        for ($i = 1; $i <= 5; $i++) {
            $periodStart = Carbon::now()->subMonths($i);
            $periodEnd = $periodStart->copy()->endOfMonth();
            $grossRevenue = rand(50000, 100000);
            $royaltyAmount = $grossRevenue * 0.10; // 10%

            Royalty::create([
                'franchise_id' => $franchise->id,
                'franchisee_id' => $franchisee->id,
                'unit_id' => $unit->id,
                'period_start_date' => $periodStart,
                'period_end_date' => $periodEnd,
                'period_year' => $periodStart->year,
                'period_month' => $periodStart->month,
                'gross_revenue' => $grossRevenue,
                'royalty_percentage' => 10.00,
                'royalty_amount' => $royaltyAmount,
                'adjustments' => 0,
                'total_amount' => $royaltyAmount,
                'status' => ['paid', 'pending'][rand(0, 1)],
                'due_date' => $periodEnd->copy()->addDays(15),
                'paid_date' => rand(0, 1) ? $periodEnd->copy()->addDays(10) : null,
            ]);
        }

        $this->command->info('✅ Created 5 royalties');

        // 9. Create 5 Technical Requests (for franchisor, franchisee, broker - not admin)
        $this->command->info('Creating technical requests...');

        $requesters = [$franchisor, $franchisee, $broker, $franchisee, $broker];
        $issues = [
            'POS System not working',
            'Internet connection issues',
            'Printer malfunction',
            'Software update needed',
            'Equipment repair required',
        ];

        foreach ($requesters as $i => $requester) {
            TechnicalRequest::create([
                'requester_id' => $requester->id,
                'title' => $issues[$i],
                'description' => 'Detailed description of ' . $issues[$i],
                'category' => ['hardware', 'software', 'network'][rand(0, 2)],
                'priority' => ['low', 'medium', 'high'][rand(0, 2)],
                'status' => ['open', 'in_progress', 'resolved'][rand(0, 2)],
            ]);
        }

        $this->command->info('✅ Created ' . count($requesters) . ' technical requests');

        // Create 7 Leads (2 for main broker user, 5 for brokers)
        $this->command->info('Creating leads...');
        $leadNames = [
            ['Ahmed', 'Al-Rashid'],
            ['Fatima', 'Hassan'],
            ['Mohammed', 'Abdullah'],
            ['Sara', 'Al-Masri'],
            ['Omar', 'Al-Hashimi'],
            ['Khaled', 'Al-Faisal'],
            ['Nora', 'Al-Zahrani'],
        ];

        foreach ($leadNames as $i => $name) {
            // Assign first 2 leads to main broker user, rest to brokers
            if ($i < 2) {
                $assignedBroker = $broker;
            } else {
                $assignedBroker = $brokers[($i - 2) % count($brokers)];
            }

            // Create leads with varied timestamps (from 1 hour to 30 days ago)
            $daysAgo = min($i, 15); // Varied days ago
            $createdAt = Carbon::now()->subDays($daysAgo)->subHours(rand(0, 23));

            Lead::create([
                'franchise_id' => $franchise->id,
                'assigned_to' => $assignedBroker->id,
                'first_name' => $name[0],
                'last_name' => $name[1],
                'email' => strtolower($name[0]) . '.' . strtolower($name[1]) . '@example.com',
                'phone' => '+966' . rand(500000000, 599999999),
                'nationality' => 'Saudi Arabia',
                'city' => ['Riyadh', 'Jeddah', 'Dammam', 'Mecca', 'Medina'][rand(0, 4)],
                'lead_source' => ['website', 'referral', 'social_media'][rand(0, 2)],
                'status' => ['new', 'contacted', 'qualified'][rand(0, 2)],
                'priority' => ['low', 'medium', 'high'][rand(0, 2)],
                'estimated_investment' => rand(100000, 500000),
                'notes' => 'Interested in franchise opportunity',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $this->command->info('✅ Created 7 leads (2 for main broker user, 5 for brokers)');

        // Create 5 Notes for Leads
        $this->command->info('Creating notes for leads...');

        $noteData = [
            ['title' => 'Initial Contact', 'description' => 'Called the lead and had a positive conversation. Interested in franchise details.'],
            ['title' => 'Follow-up Meeting', 'description' => 'Scheduled a meeting for next week to discuss investment requirements.'],
            ['title' => 'Financial Discussion', 'description' => 'Discussed financial aspects. Lead has sufficient capital for investment.'],
            ['title' => 'Site Visit Planned', 'description' => 'Lead wants to visit existing franchise location before making decision.'],
            ['title' => 'Document Sent', 'description' => 'Sent franchise disclosure document and contract template for review.'],
        ];

        $leads = Lead::where('franchise_id', $franchise->id)->get();

        foreach ($noteData as $index => $data) {
            if ($index < count($leads)) {
                Note::create([
                    'lead_id' => $leads[$index]->id,
                    'user_id' => $leads[$index]->assigned_to,
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'attachments' => null,
                ]);
            }
        }

        $this->command->info('✅ Created 5 notes for leads');

        // Create 5 Documents
        $this->command->info('Creating documents...');
        $docNames = [
            'FDD',
            'Franchise Agreement',
            'Financial Study',
            'Franchise Kit',
            'Other',
        ];

        foreach ($docNames as $i => $docName) {
            Document::create([
                'franchise_id' => $franchise->id,
                'name' => $docName,
                'description' => 'Important document: ' . $docName,
                'type' => ['fdd', 'franchise_agreement', 'financial_study', 'franchise_kit', 'other'][$i],
                'file_path' => 'documents/' . str_replace(' ', '_', strtolower($docName)) . '.pdf',
                'file_name' => str_replace(' ', '_', strtolower($docName)) . '.pdf',
                'file_extension' => 'pdf',
                'file_size' => rand(100000, 1000000),
                'mime_type' => 'application/pdf',
                'status' => 'active',
            ]);
        }

        $this->command->info('✅ Created 5 documents');

        // Create 5 Staff members and link them to the unit
        $this->command->info('Creating staff...');
        $staffData = [
            ['Khalid Ahmed', 'khalid@example.com', 'Store Manager', 'manager'],
            ['Layla Mohammed', 'layla@example.com', 'Assistant Manager', 'assistant_manager'],
            ['Yousef Ali', 'yousef@example.com', 'Broker', 'broker'],
            ['Nora Hassan', 'nora@example.com', 'Cashier', 'cashier'],
            ['Hassan Omar', 'hassan@example.com', 'Inventory Clerk', 'broker'],
        ];

        foreach ($staffData as $index => $data) {
            $staff = Staff::create([
                'name' => $data[0],
                'email' => $data[1],
                'phone' => '+966' . rand(500000000, 599999999),
                'job_title' => $data[2],
                'department' => 'Operations',
                'salary' => rand(3000, 8000),
                'hire_date' => Carbon::now()->subMonths(rand(1, 12)),
                'employment_type' => 'full_time',
                'status' => 'active',
            ]);

            // Link staff to unit through pivot table
            DB::table('staff_unit')->insert([
                'staff_id' => $staff->id,
                'unit_id' => $unit->id,
                'role' => $data[3],
                'assigned_date' => Carbon::now()->subMonths(rand(1, 12)),
                'is_primary' => $index === 0, // First staff member is primary (manager)
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ Created 5 staff members and linked them to unit');

        // Create 5 Franchise Staff (franchise-level staff, not unit-specific)
        $this->command->info('Creating franchise staff...');
        $franchiseStaffData = [
            ['Sara Al-Rashid', 'sara.rashid@cheetah.com.sa', 'Franchise Manager', 'Management'],
            ['Mohammed Al-Hassan', 'mohammed.hassan@cheetah.com.sa', 'Operations Director', 'Operations'],
            ['Fatima Al-Zahrani', 'fatima.zahrani@cheetah.com.sa', 'Finance Manager', 'Finance'],
            ['Ahmed Al-Mahmoud', 'ahmed.mahmoud@cheetah.com.sa', 'Marketing Director', 'Marketing'],
            ['Layla Al-Qasimi', 'layla.qasimi@cheetah.com.sa', 'HR Manager', 'Human Resources'],
        ];

        $franchiseStaff = [];
        foreach ($franchiseStaffData as $index => $data) {
            $franchiseStaff[] = Staff::create([
                'franchise_id' => $franchise->id,
                'name' => $data[0],
                'email' => $data[1],
                'phone' => '+966' . rand(500000000, 599999999),
                'job_title' => $data[2],
                'department' => $data[3],
                'salary' => rand(8000, 15000),
                'hire_date' => Carbon::now()->subMonths(rand(6, 24)),
                'employment_type' => 'full_time',
                'status' => 'active',
                'shift_start' => Carbon::createFromTime(9, 0, 0),
                'shift_end' => Carbon::createFromTime(17, 0, 0),
            ]);
        }

        $this->command->info('✅ Created 5 franchise staff members');

        // Create 5 Customer Reviews
        $this->command->info('Creating reviews...');
        $reviewComments = [
            'Excellent service and products!',
            'Great experience, will come back again.',
            'Good quality, fast service.',
            'Very satisfied with the purchase.',
            'Friendly staff and clean store.',
        ];

        foreach ($reviewComments as $i => $comment) {
            Review::create([
                'unit_id' => $unit->id,
                'franchisee_id' => $franchisee->id,
                'customer_name' => 'Customer ' . ($i + 1),
                'customer_email' => 'customer' . ($i + 1) . '@example.com',
                'customer_phone' => '+966' . rand(500000000, 599999999),
                'rating' => rand(3, 5),
                'comment' => $comment,
                'sentiment' => ['positive', 'positive', 'positive', 'neutral', 'positive'][$i],
                'status' => 'published',
                'review_source' => ['in_person', 'phone', 'email'][$i % 3],
                'verified_purchase' => true,
                'review_date' => Carbon::now()->subDays(rand(1, 30)),
            ]);
        }

        $this->command->info('✅ Created 5 reviews');

        // Create 5 Tasks (franchisor assigns to broker and franchisee)
        $this->command->info('Creating tasks...');
        $taskData = [
            ['Complete Training Module 1', 'Complete the initial training module', $broker->id],
            ['Submit Monthly Report', 'Submit the monthly sales report', $franchisee->id],
            ['Schedule Inspection', 'Schedule the quarterly inspection', $franchisee->id],
            ['Update Marketing Materials', 'Update all marketing materials', $broker->id],
            ['Review Contract Terms', 'Review and sign contract amendments', $franchisee->id],
        ];

        $priorities = ['low', 'medium', 'high', 'urgent', 'medium'];
        $statuses = ['pending', 'in_progress', 'completed', 'cancelled', 'on_hold'];
        $types = ['onboarding', 'training', 'compliance', 'maintenance', 'marketing'];

        foreach ($taskData as $i => $data) {
            Task::create([
                'title' => $data[0],
                'description' => $data[1],
                'type' => $types[$i],
                'priority' => $priorities[$i],
                'status' => $statuses[$i],
                'assigned_to' => $data[2],
                'created_by' => $franchisor->id,
                'franchise_id' => $franchise->id,
                'unit_id' => $unit->id,
                'due_date' => Carbon::now()->addDays(rand(7, 30)),
            ]);
        }

        $this->command->info('✅ Created 5 tasks');

        // Create 4 Tasks assigned to Franchisor
        $this->command->info('Creating franchisor tasks...');
        $franchisorTaskData = [
            ['Review Franchise Performance', 'Review monthly performance metrics and prepare quarterly report', 'operations'],
            ['Approve Marketing Budget', 'Review and approve the Q4 marketing budget proposal', 'finance'],
            ['Conduct Staff Evaluation', 'Complete annual performance evaluations for franchise staff', 'support'],
            ['Update Franchise Policies', 'Review and update franchise operation policies and procedures', 'compliance'],
        ];

        $franchisorPriorities = ['high', 'medium', 'medium', 'low'];
        $franchisorStatuses = ['pending', 'in_progress', 'pending', 'completed'];

        foreach ($franchisorTaskData as $i => $data) {
            Task::create([
                'title' => $data[0],
                'description' => $data[1],
                'type' => $data[2],
                'priority' => $franchisorPriorities[$i],
                'status' => $franchisorStatuses[$i],
                'assigned_to' => $franchisor->id,
                'created_by' => $franchisor->id,
                'franchise_id' => $franchise->id,
                'unit_id' => null, // Franchisor tasks are not unit-specific
                'due_date' => Carbon::now()->addDays(rand(3, 14)),
            ]);
        }

        $this->command->info('✅ Created 4 franchisor tasks');

        // Create notifications for each role (2-5 per role)
        $this->command->info('Creating notifications...');

        // Admin notifications (3 notifications)
        $adminNotifications = [
            ['type' => 'App\\Notifications\\SystemNotification', 'title' => 'System Update', 'message' => 'System maintenance scheduled for tonight.'],
            ['type' => 'App\\Notifications\\ReportNotification', 'title' => 'Monthly Report Ready', 'message' => 'Your monthly system report is now available.'],
            ['type' => 'App\\Notifications\\AlertNotification', 'title' => 'New User Registration', 'message' => 'A new franchisee has registered in the system.'],
        ];

        foreach ($adminNotifications as $notification) {
            Notification::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => $notification['type'],
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $admin->id,
                'data' => json_encode([
                    'title' => $notification['title'],
                    'message' => $notification['message'],
                ]),
                'read_at' => null,
            ]);
        }

        // Franchisor notifications (4 notifications)
        $franchisorNotifications = [
            ['type' => 'App\\Notifications\\RoyaltyNotification', 'title' => 'Royalty Payment Received', 'message' => 'Royalty payment from Riyadh Downtown Branch received.'],
            ['type' => 'App\\Notifications\\UnitNotification', 'title' => 'New Unit Performance Report', 'message' => 'Monthly performance report for all units is ready.'],
            ['type' => 'App\\Notifications\\LeadNotification', 'title' => 'High Priority Lead', 'message' => 'A high priority lead requires your attention.'],
            ['type' => 'App\\Notifications\\TaskNotification', 'title' => 'Task Completion Update', 'message' => 'Multiple tasks have been completed this week.'],
        ];

        foreach ($franchisorNotifications as $notification) {
            Notification::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => $notification['type'],
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $franchisor->id,
                'data' => json_encode([
                    'title' => $notification['title'],
                    'message' => $notification['message'],
                ]),
                'read_at' => null,
            ]);
        }

        // Franchisee notifications (5 notifications)
        $franchiseeNotifications = [
            ['type' => 'App\\Notifications\\WelcomeNotification', 'title' => 'Welcome to Cheetah', 'message' => 'Your franchise account has been activated.'],
            ['type' => 'App\\Notifications\\RoyaltyNotification', 'title' => 'Royalty Payment Due', 'message' => 'Your monthly royalty payment is due in 5 days.'],
            ['type' => 'App\\Notifications\\TaskNotification', 'title' => 'Task Assigned', 'message' => 'You have been assigned a new task: Submit Monthly Report.'],
            ['type' => 'App\\Notifications\\TechnicalRequestNotification', 'title' => 'Technical Request Update', 'message' => 'Your technical request has been updated to In Progress.'],
            ['type' => 'App\\Notifications\\InventoryNotification', 'title' => 'Low Stock Alert', 'message' => 'Some products are running low in stock. Please reorder.'],
        ];

        foreach ($franchiseeNotifications as $notification) {
            Notification::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => $notification['type'],
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $franchisee->id,
                'data' => json_encode([
                    'title' => $notification['title'],
                    'message' => $notification['message'],
                ]),
                'read_at' => null,
            ]);
        }

        // Broker notifications (4 notifications)
        $brokerNotifications = [
            ['type' => 'App\\Notifications\\TaskNotification', 'title' => 'New Task Assigned', 'message' => 'You have a new task to follow up with leads.'],
            ['type' => 'App\\Notifications\\LeadNotification', 'title' => 'New Lead Assigned', 'message' => 'You have been assigned 5 new leads to follow up.'],
            ['type' => 'App\\Notifications\\TrainingNotification', 'title' => 'Training Required', 'message' => 'Please complete Training Module 1 by the end of this week.'],
            ['type' => 'App\\Notifications\\TechnicalRequestNotification', 'title' => 'Technical Issue Resolved', 'message' => 'Your technical request has been resolved.'],
        ];

        foreach ($brokerNotifications as $notification) {
            Notification::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => $notification['type'],
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $broker->id,
                'data' => json_encode([
                    'title' => $notification['title'],
                    'message' => $notification['message'],
                ]),
                'read_at' => null,
            ]);
        }

        $totalNotifications = count($adminNotifications) + count($franchisorNotifications) + count($franchiseeNotifications) + count($brokerNotifications);
        $this->command->info('✅ Created ' . $totalNotifications . ' notifications (Admin: ' . count($adminNotifications) . ', Franchisor: ' . count($franchisorNotifications) . ', Franchisee: ' . count($franchiseeNotifications) . ', Broker: ' . count($brokerNotifications) . ')');

        // Summary
        $this->command->info('');
        $this->command->info('🎉 Minimal data seeding completed successfully!');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('  - 4 Users (admin, franchisor, franchisee, broker)');
        $this->command->info('  - 5 Brokers (related to franchisor)');
        $this->command->info('  - 1 Franchise (with marketplace listing)');
        $this->command->info('  - 1 Unit');
        $this->command->info('  - 5 Unit Performance records');
        $this->command->info('  - 5 Products');
        $this->command->info('  - 5 Inventory records');
        $this->command->info('  - 5 Sales');
        $this->command->info('  - 5 Expenses');
        $this->command->info('  - 5 Royalties');
        $this->command->info('  - 5 Technical Requests');
        $this->command->info('  - 7 Leads (2 for broker user, 5 for brokers)');
        $this->command->info('  - 5 Notes (attached to leads)');
        $this->command->info('  - 5 Documents');
        $this->command->info('  - 5 Unit Staff');
        $this->command->info('  - 5 Franchise Staff');
        $this->command->info('  - 5 Customer Reviews');
        $this->command->info('  - 5 Tasks (assigned to brokers/franchisees)');
        $this->command->info('  - 4 Franchisor Tasks (assigned to franchisor)');
        $this->command->info('  - 5 Properties (distributed across brokers)');
        $this->command->info('  - 5 Marketplace Inquiries (3 franchise, 2 property)');
        $this->command->info('  - 16 Notifications (Admin: 3, Franchisor: 4, Franchisee: 5, Broker: 4)');
        $this->command->info('');
        $this->command->info('🔑 Login Credentials:');
        $this->command->info('  Admin:      admin@cheetah.com.sa / password');
        $this->command->info('  Franchisor: franchisor@cheetah.com.sa / password');
        $this->command->info('  Franchisee: franchisee@cheetah.com.sa / password');
        $this->command->info('  Broker:     broker@cheetah.com.sa / password');
        $this->command->info('');
        $this->command->info('🔑 Brokers Credentials:');
        $this->command->info('  Ahmed:      ahmed.salem@cheetah.com.sa / password');
        $this->command->info('  Fatima:     fatima.khalid@cheetah.com.sa / password');
        $this->command->info('  Mohammed:   mohammed.rashid@cheetah.com.sa / password');
        $this->command->info('  Layla:      layla.hassan@cheetah.com.sa / password');
        $this->command->info('  Omar:       omar.mansour@cheetah.com.sa / password');
    }
}
