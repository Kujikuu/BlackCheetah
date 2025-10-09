<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Franchise;
use App\Models\Lead;
use App\Models\Task;
use App\Models\Revenue;
use App\Models\Royalty;
use App\Models\Transaction;
use Carbon\Carbon;

class FranchisorDashboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first franchisor for testing
        $franchisor = User::where('role', 'franchisor')->first();

        if (!$franchisor) {
            $this->command->error('No franchisor found. Please run AdminDashboardSeeder first.');
            return;
        }

        $franchise = Franchise::where('franchisor_id', $franchisor->id)->first();

        // Create Leads
        $this->createLeads($franchisor->id);

        // Create Tasks
        $this->createTasks($franchisor->id);

        // Create Revenues
        $this->createRevenues($franchise->id ?? 1);

        // Create Royalties
        $this->createRoyalties($franchise->id ?? 1);

        // Create Transactions
        $this->createTransactions($franchise->id ?? 1);

        $this->command->info('Franchisor dashboard test data created successfully!');
    }

    private function createLeads($franchisorId)
    {
        $franchise = Franchise::where('franchisor_id', $franchisorId)->first();
        if (!$franchise) return;

        // Get actual user IDs to assign leads to
        $users = User::pluck('id')->toArray();
        if (empty($users)) $users = [$franchisorId];

        $leadSources = ['website', 'referral', 'social_media', 'advertisement', 'cold_call', 'event', 'other'];
        $leadStatuses = ['new', 'contacted', 'qualified', 'proposal_sent', 'negotiating', 'closed_won', 'closed_lost'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $countries = ['Saudi Arabia'];
        $cities = ['Riyadh', 'Jeddah', 'Dammam', 'Tabuk', 'Hail', 'Makkah', 'Madinah'];

        $leads = [];
        for ($i = 1; $i <= 50; $i++) {
            $createdAt = Carbon::now()->subDays(rand(1, 90));
            $leads[] = [
                'franchise_id' => $franchise->id,
                'assigned_to' => $users[array_rand($users)], // Assign to actual user IDs
                'first_name' => 'Lead',
                'last_name' => 'User ' . $i,
                'email' => 'lead' . $i . '@example.com',
                'phone' => '+1234567' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'company_name' => 'Company ' . $i,
                'job_title' => 'Manager',
                'country' => $countries[array_rand($countries)],
                'city' => $cities[array_rand($cities)],
                'lead_source' => $leadSources[array_rand($leadSources)],
                'status' => $leadStatuses[array_rand($leadStatuses)],
                'priority' => $priorities[array_rand($priorities)],
                'estimated_investment' => rand(50000, 200000),
                'franchise_fee_quoted' => rand(25000, 75000),
                'notes' => 'Sample notes for lead ' . $i,
                'contact_attempts' => rand(0, 5),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        Lead::insert($leads);
    }

    private function createTasks($franchisorId)
    {
        $franchise = Franchise::where('franchisor_id', $franchisorId)->first();
        if (!$franchise) return;

        $taskTypes = ['onboarding', 'training', 'compliance', 'maintenance', 'marketing', 'operations', 'finance', 'support'];
        $taskStatuses = ['pending', 'in_progress', 'completed', 'cancelled', 'on_hold'];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        // Get some users to assign tasks to
        $users = User::whereIn('role', ['franchisor', 'franchisee', 'sales'])->pluck('id')->toArray();
        if (empty($users)) $users = [$franchisorId];

        $tasks = [];
        for ($i = 1; $i <= 30; $i++) {
            $createdAt = Carbon::now()->subDays(rand(1, 60));
            $dueDate = Carbon::now()->addDays(rand(-10, 30)); // Some overdue, some future
            $status = $taskStatuses[array_rand($taskStatuses)];

            $tasks[] = [
                'title' => 'Task ' . $i . ' - ' . ucfirst($taskTypes[array_rand($taskTypes)]) . ' Related',
                'description' => 'Description for task ' . $i . '. This is a sample task for testing purposes.',
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
                'notes' => 'Sample notes for task ' . $i,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        Task::insert($tasks);
    }

    private function createRevenues($franchisorId)
    {
        $franchise = Franchise::where('franchisor_id', $franchisorId)->first();
        if (!$franchise) return;

        $revenueTypes = ['sales', 'franchise_fee', 'royalty', 'marketing_fee', 'other'];
        $categories = ['product_sales', 'service_sales', 'initial_fee', 'ongoing_fee', 'commission', 'other'];
        $sources = ['online', 'in-store', 'phone', 'referral'];
        $paymentMethods = ['cash', 'credit_card', 'debit_card', 'bank_transfer', 'check'];
        $statuses = ['draft', 'pending', 'verified', 'disputed'];

        $revenues = [];
        for ($i = 1; $i <= 50; $i++) {
            $date = Carbon::now()->subDays(rand(1, 365));
            $amount = rand(1000, 50000);
            $taxAmount = $amount * 0.1; // 10% tax
            $discountAmount = rand(0, $amount * 0.1); // Up to 10% discount
            $netAmount = $amount + $taxAmount - $discountAmount;

            $revenues[] = [
                'revenue_number' => 'REV-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'franchise_id' => $franchise->id,
                'user_id' => $franchisorId,
                'type' => $revenueTypes[array_rand($revenueTypes)],
                'category' => $categories[array_rand($categories)],
                'amount' => $amount,
                'currency' => 'USD',
                'description' => 'Revenue entry ' . $i . ' for testing purposes',
                'revenue_date' => $date,
                'period_year' => $date->year,
                'period_month' => $date->month,
                'source' => $sources[array_rand($sources)],
                'customer_name' => 'Customer ' . $i,
                'customer_email' => 'customer' . $i . '@example.com',
                'invoice_number' => 'INV-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'payment_status' => 'completed',
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

        Revenue::insert($revenues);
    }

    private function createRoyalties($franchisorId)
    {
        $franchise = Franchise::where('franchisor_id', $franchisorId)->first();
        if (!$franchise) return;

        // Get franchisee users
        $franchisees = User::where('role', 'franchisee')->pluck('id')->toArray();
        if (empty($franchisees)) return;

        $royaltyTypes = ['royalty', 'marketing_fee', 'technology_fee', 'other'];
        $statuses = ['draft', 'pending', 'paid', 'overdue', 'disputed'];
        $paymentMethods = ['bank_transfer', 'check', 'credit_card', 'ach', 'wire'];

        $royalties = [];

        // Create monthly royalty data for the last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $grossRevenue = rand(80000, 150000);
            $royaltyPercentage = 6.00; // 6% royalty rate
            $royaltyAmount = $grossRevenue * ($royaltyPercentage / 100);
            $marketingFeePercentage = 2.00; // 2% marketing fee
            $marketingFeeAmount = $grossRevenue * ($marketingFeePercentage / 100);
            $technologyFeeAmount = rand(500, 1500);
            $totalAmount = $royaltyAmount + $marketingFeeAmount + $technologyFeeAmount;
            $status = $statuses[array_rand($statuses)];

            $royalties[] = [
                'royalty_number' => 'ROY-' . $date->format('Y-m') . '-' . str_pad($franchise->id, 3, '0', STR_PAD_LEFT),
                'franchise_id' => $franchise->id,
                'franchisee_id' => $franchisees[array_rand($franchisees)],
                'type' => 'royalty',
                'period_year' => $date->year,
                'period_month' => $date->month,
                'period_start_date' => $date->startOfMonth(),
                'period_end_date' => $date->endOfMonth(),
                'gross_revenue' => $grossRevenue,
                'royalty_percentage' => $royaltyPercentage,
                'royalty_amount' => $royaltyAmount,
                'marketing_fee_percentage' => $marketingFeePercentage,
                'marketing_fee_amount' => $marketingFeeAmount,
                'technology_fee_amount' => $technologyFeeAmount,
                'total_amount' => $totalAmount,
                'adjustments' => 0,
                'status' => $status,
                'due_date' => $date->endOfMonth()->addDays(15),
                'paid_date' => $status === 'paid' ? $date->endOfMonth()->addDays(rand(1, 10)) : null,
                'payment_method' => $status === 'paid' ? $paymentMethods[array_rand($paymentMethods)] : null,
                'payment_reference' => $status === 'paid' ? 'PAY-' . rand(100000, 999999) : null,
                'late_fee' => $status === 'overdue' ? rand(100, 500) : 0,
                'is_auto_generated' => true,
                'generated_by' => $franchisorId,
                'created_at' => $date,
                'updated_at' => $date,
            ];
        }

        Royalty::insert($royalties);
    }

    private function createTransactions($franchisorId)
    {
        $franchise = Franchise::where('franchisor_id', $franchisorId)->first();
        if (!$franchise) return;

        $transactionTypes = ['revenue', 'expense', 'royalty', 'marketing_fee', 'franchise_fee'];
        $categories = ['sales', 'cost_of_goods', 'labor', 'rent', 'utilities', 'marketing', 'equipment', 'supplies', 'insurance', 'taxes', 'other'];
        $statuses = ['pending', 'completed', 'cancelled', 'refunded'];
        $paymentMethods = ['cash', 'credit_card', 'debit_card', 'bank_transfer', 'check', 'digital_wallet'];

        $transactions = [];
        for ($i = 1; $i <= 100; $i++) {
            $date = Carbon::now()->subDays(rand(1, 90));
            $type = $transactionTypes[array_rand($transactionTypes)];
            $amount = $type === 'revenue' ? rand(1000, 10000) : rand(500, 5000);

            $transactions[] = [
                'transaction_number' => 'TXN-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'type' => $type,
                'category' => $categories[array_rand($categories)],
                'amount' => $amount,
                'currency' => 'USD',
                'description' => ucfirst($type) . ' transaction ' . $i . ' for testing',
                'franchise_id' => $franchise->id,
                'user_id' => $franchisorId,
                'transaction_date' => $date,
                'status' => $statuses[array_rand($statuses)],
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'reference_number' => 'REF-' . rand(100000, 999999),
                'vendor_customer' => $type === 'expense' ? 'Vendor ' . rand(1, 20) : 'Customer ' . rand(1, 50),
                'notes' => 'Sample transaction notes for testing',
                'created_at' => $date,
                'updated_at' => $date,
            ];
        }

        Transaction::insert($transactions);
    }
}
