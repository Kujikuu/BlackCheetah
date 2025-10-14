<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Franchise;
use App\Models\Inventory;
use App\Models\Lead;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Revenue;
use App\Models\Review;
use App\Models\Royalty;
use App\Models\Staff;
use App\Models\Task;
use App\Models\TechnicalRequest;
use App\Models\Transaction;
use App\Models\Unit;
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
            'email' => 'admin@cheetah.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+966500000001',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $franchisor = User::create([
            'name' => 'Franchisor User',
            'email' => 'franchisor@cheetah.com',
            'password' => Hash::make('password'),
            'role' => 'franchisor',
            'phone' => '+966500000002',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $franchisee = User::create([
            'name' => 'Franchisee User',
            'email' => 'franchisee@cheetah.com',
            'password' => Hash::make('password'),
            'role' => 'franchisee',
            'phone' => '+966500000003',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $sales = User::create([
            'name' => 'Sales User',
            'email' => 'sales@cheetah.com',
            'password' => Hash::make('password'),
            'role' => 'sales',
            'phone' => '+966500000004',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Created 4 users');

        // Create 5 Sales Associates related to franchisor
        $this->command->info('Creating sales associates...');

        $salesAssociates = [];
        $salesAssociateData = [
            ['name' => 'Ahmed Al-Salem', 'email' => 'ahmed.salem@cheetah.com'],
            ['name' => 'Fatima Al-Khalid', 'email' => 'fatima.khalid@cheetah.com'],
            ['name' => 'Mohammed Al-Rashid', 'email' => 'mohammed.rashid@cheetah.com'],
            ['name' => 'Layla Al-Hassan', 'email' => 'layla.hassan@cheetah.com'],
            ['name' => 'Omar Al-Mansour', 'email' => 'omar.mansour@cheetah.com'],
        ];

        foreach ($salesAssociateData as $index => $data) {
            $salesAssociates[] = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'sales',
                'phone' => '+96650000' . str_pad($index + 5, 4, '0', STR_PAD_LEFT),
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
        }

        $this->command->info('✅ Created 5 sales associates');

        // 2. Create Franchise
        $this->command->info('Creating franchise...');

        $franchise = Franchise::create([
            'franchisor_id' => $franchisor->id,
            'business_name' => 'Black Cheetah International',
            'brand_name' => 'Black Cheetah',
            'industry' => 'Food & Beverage',
            'description' => 'Premium fast-food restaurant franchise',
            'website' => 'https://blackcheetah.com',
            'business_registration_number' => 'BR-' . rand(100000, 999999),
            'tax_id' => 'TAX-' . rand(100000, 999999),
            'business_type' => 'llc',
            'established_date' => Carbon::now()->subYears(3),
            'headquarters_country' => 'Saudi Arabia',
            'headquarters_city' => 'Riyadh',
            'headquarters_address' => 'King Fahd Road, Riyadh 12345',
            'contact_phone' => '+966112345678',
            'contact_email' => 'info@blackcheetah.com',
            'franchise_fee' => 50000.00,
            'royalty_percentage' => 10.00,
            'marketing_fee_percentage' => 3.00,
            'total_units' => 1,
            'active_units' => 1,
            'status' => 'active',
        ]);

        $this->command->info('✅ Created franchise');

        // Link sales associates and franchisee to franchise
        $this->command->info('Linking users to franchise...');

        foreach ($salesAssociates as $associate) {
            $associate->update(['franchise_id' => $franchise->id]);
        }

        $franchisee->update(['franchise_id' => $franchise->id]);
        $sales->update(['franchise_id' => $franchise->id]);

        $this->command->info('✅ Linked sales associates and franchisee to franchise');

        // 3. Create 1 Unit linked to franchisee
        $this->command->info('Creating unit...');

        $unit = Unit::create([
            'franchise_id' => $franchise->id,
            'franchisee_id' => $franchisee->id,
            'unit_name' => 'Riyadh Downtown Branch',
            'unit_code' => 'RYD-001',
            'unit_type' => 'store',
            'country' => 'Saudi Arabia',
            'state_province' => 'Riyadh Province',
            'city' => 'Riyadh',
            'address' => 'King Fahd Road, Al Olaya District',
            'postal_code' => '12345',
            'phone' => '+966112345679',
            'email' => 'riyadh@blackcheetah.com',
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

        // 9. Create 5 Technical Requests (for franchisor, franchisee, sales - not admin)
        $this->command->info('Creating technical requests...');

        $requesters = [$franchisor, $franchisee, $sales, $franchisee, $sales];
        $issues = [
            'POS System not working',
            'Internet connection issues',
            'Printer malfunction',
            'Software update needed',
            'Equipment repair required',
        ];

        foreach ($requesters as $i => $requester) {
            TechnicalRequest::create([
                'franchise_id' => $franchise->id,
                'unit_id' => $unit->id,
                'requester_id' => $requester->id,
                'title' => $issues[$i],
                'description' => 'Detailed description of ' . $issues[$i],
                'category' => ['hardware', 'software', 'network'][rand(0, 2)],
                'priority' => ['low', 'medium', 'high'][rand(0, 2)],
                'status' => ['open', 'in_progress', 'resolved'][rand(0, 2)],
            ]);
        }

        $this->command->info('✅ Created ' . count($requesters) . ' technical requests');

        // Create 5 Leads (assigned to sales associates)
        $this->command->info('Creating leads...');
        $leadNames = [
            ['Ahmed', 'Al-Rashid'],
            ['Fatima', 'Hassan'],
            ['Mohammed', 'Abdullah'],
            ['Sara', 'Al-Masri'],
            ['Omar', 'Al-Hashimi'],
        ];

        foreach ($leadNames as $i => $name) {
            // Assign each lead to a different sales associate
            $assignedSales = $salesAssociates[$i % count($salesAssociates)];

            // Create leads with varied timestamps (from 1 hour to 30 days ago)
            $daysAgo = [0, 1, 3, 7, 15][$i]; // Varied days ago
            $createdAt = Carbon::now()->subDays($daysAgo)->subHours(rand(0, 23));

            Lead::create([
                'franchise_id' => $franchise->id,
                'assigned_to' => $assignedSales->id,
                'first_name' => $name[0],
                'last_name' => $name[1],
                'email' => strtolower($name[0]) . '.' . strtolower($name[1]) . '@example.com',
                'phone' => '+966' . rand(500000000, 599999999),
                'country' => 'Saudi Arabia',
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

        $this->command->info('✅ Created 5 leads (distributed among sales associates)');

        // Create 5 Documents
        $this->command->info('Creating documents...');
        $docNames = [
            'Franchise Agreement',
            'Operations Manual',
            'Training Certificate',
            'Business License',
            'Insurance Policy',
        ];

        foreach ($docNames as $i => $docName) {
            Document::create([
                'franchise_id' => $franchise->id,
                'name' => $docName,
                'description' => 'Important document: ' . $docName,
                'type' => ['contract', 'manual', 'certificate', 'license', 'policy'][$i],
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
            ['Yousef Ali', 'yousef@example.com', 'Sales Associate', 'sales_associate'],
            ['Nora Hassan', 'nora@example.com', 'Cashier', 'cashier'],
            ['Hassan Omar', 'hassan@example.com', 'Inventory Clerk', 'sales_associate'],
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

        // Create 5 Tasks (franchisor assigns to sales and franchisee)
        $this->command->info('Creating tasks...');
        $taskData = [
            ['Complete Training Module 1', 'Complete the initial training module', $sales->id],
            ['Submit Monthly Report', 'Submit the monthly sales report', $franchisee->id],
            ['Schedule Inspection', 'Schedule the quarterly inspection', $franchisee->id],
            ['Update Marketing Materials', 'Update all marketing materials', $sales->id],
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
            ['type' => 'App\\Notifications\\WelcomeNotification', 'title' => 'Welcome to Black Cheetah', 'message' => 'Your franchise account has been activated.'],
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

        // Sales notifications (4 notifications)
        $salesNotifications = [
            ['type' => 'App\\Notifications\\TaskNotification', 'title' => 'New Task Assigned', 'message' => 'You have a new task to follow up with leads.'],
            ['type' => 'App\\Notifications\\LeadNotification', 'title' => 'New Lead Assigned', 'message' => 'You have been assigned 5 new leads to follow up.'],
            ['type' => 'App\\Notifications\\TrainingNotification', 'title' => 'Training Required', 'message' => 'Please complete Training Module 1 by the end of this week.'],
            ['type' => 'App\\Notifications\\TechnicalRequestNotification', 'title' => 'Technical Issue Resolved', 'message' => 'Your technical request has been resolved.'],
        ];

        foreach ($salesNotifications as $notification) {
            Notification::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => $notification['type'],
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $sales->id,
                'data' => json_encode([
                    'title' => $notification['title'],
                    'message' => $notification['message'],
                ]),
                'read_at' => null,
            ]);
        }

        $totalNotifications = count($adminNotifications) + count($franchisorNotifications) + count($franchiseeNotifications) + count($salesNotifications);
        $this->command->info('✅ Created ' . $totalNotifications . ' notifications (Admin: ' . count($adminNotifications) . ', Franchisor: ' . count($franchisorNotifications) . ', Franchisee: ' . count($franchiseeNotifications) . ', Sales: ' . count($salesNotifications) . ')');

        // Summary
        $this->command->info('');
        $this->command->info('🎉 Minimal data seeding completed successfully!');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('  - 4 Users (admin, franchisor, franchisee, sales)');
        $this->command->info('  - 5 Sales Associates (related to franchisor)');
        $this->command->info('  - 1 Franchise');
        $this->command->info('  - 1 Unit');
        $this->command->info('  - 5 Products');
        $this->command->info('  - 5 Inventory records');
        $this->command->info('  - 5 Sales');
        $this->command->info('  - 5 Expenses');
        $this->command->info('  - 5 Royalties');
        $this->command->info('  - 5 Technical Requests');
        $this->command->info('  - 5 Leads (distributed among sales associates)');
        $this->command->info('  - 5 Documents');
        $this->command->info('  - 5 Staff');
        $this->command->info('  - 5 Customer Reviews');
        $this->command->info('  - 5 Tasks');
        $this->command->info('  - 16 Notifications (Admin: 3, Franchisor: 4, Franchisee: 5, Sales: 4)');
        $this->command->info('');
        $this->command->info('🔑 Login Credentials:');
        $this->command->info('  Admin:      admin@cheetah.com / password');
        $this->command->info('  Franchisor: franchisor@cheetah.com / password');
        $this->command->info('  Franchisee: franchisee@cheetah.com / password');
        $this->command->info('  Sales:      sales@cheetah.com / password');
        $this->command->info('');
        $this->command->info('🔑 Sales Associates Credentials:');
        $this->command->info('  Ahmed:      ahmed.salem@cheetah.com / password');
        $this->command->info('  Fatima:     fatima.khalid@cheetah.com / password');
        $this->command->info('  Mohammed:   mohammed.rashid@cheetah.com / password');
        $this->command->info('  Layla:      layla.hassan@cheetah.com / password');
        $this->command->info('  Omar:       omar.mansour@cheetah.com / password');
    }
}
