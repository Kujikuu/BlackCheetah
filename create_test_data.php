<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Franchise;
use App\Models\Revenue;
use App\Models\Royalty;
use App\Models\Transaction;
use Carbon\Carbon;

$franchisor = User::where('role', 'franchisor')->first();
$franchise = Franchise::where('franchisor_id', $franchisor->id)->first();

// Create a franchisee if none exists
$franchisee = User::where('role', 'franchisee')->first();
if (!$franchisee) {
    $franchisee = User::create([
        'name' => 'Test Franchisee',
        'email' => 'franchisee@test.com',
        'password' => bcrypt('password'),
        'role' => 'franchisee',
        'franchise_id' => $franchise->id,
    ]);
}

// Create Royalty data for last 12 months
for ($i = 1; $i <= 12; $i++) {
    $date = Carbon::now()->subMonths($i);
    $grossRevenue = rand(80000, 150000);
    $royaltyAmount = $grossRevenue * 0.06;
    $marketingFee = $grossRevenue * 0.02;
    
    Royalty::create([
        'royalty_number' => 'ROY-' . $date->format('Y-m') . '-001',
        'franchise_id' => $franchise->id,
        'franchisee_id' => $franchisee->id,
        'type' => 'royalty',
        'period_year' => $date->year,
        'period_month' => $date->month,
        'period_start_date' => $date->startOfMonth(),
        'period_end_date' => $date->endOfMonth(),
        'gross_revenue' => $grossRevenue,
        'royalty_percentage' => 6.00,
        'royalty_amount' => $royaltyAmount,
        'marketing_fee_percentage' => 2.00,
        'marketing_fee_amount' => $marketingFee,
        'technology_fee_amount' => 500,
        'total_amount' => $royaltyAmount + $marketingFee + 500,
        'adjustments' => 0,
        'status' => 'paid',
        'due_date' => $date->endOfMonth()->addDays(15),
        'paid_date' => $date->endOfMonth()->addDays(5),
        'payment_method' => 'bank_transfer',
        'payment_reference' => 'PAY-' . rand(100000, 999999),
        'late_fee' => 0,
        'is_auto_generated' => true,
        'generated_by' => $franchisor->id,
        'created_at' => $date,
        'updated_at' => $date,
    ]);
}

// Create Transaction data for expenses
for ($i = 1; $i <= 50; $i++) {
    $date = Carbon::now()->subDays(rand(1, 365));
    $amount = rand(5000, 25000);
    
    Transaction::create([
        'transaction_number' => 'TXN-' . str_pad($i, 6, '0', STR_PAD_LEFT),
        'type' => 'expense',
        'category' => ['rent', 'utilities', 'marketing', 'supplies', 'labor'][array_rand(['rent', 'utilities', 'marketing', 'supplies', 'labor'])],
        'amount' => $amount,
        'currency' => 'SAR',
        'description' => 'Expense transaction ' . $i,
        'franchise_id' => $franchise->id,
        'user_id' => $franchisor->id,
        'transaction_date' => $date,
        'status' => 'completed',
        'payment_method' => 'bank_transfer',
        'reference_number' => 'REF-' . rand(100000, 999999),
        'vendor_customer' => 'Vendor ' . rand(1, 20),
        'notes' => 'Sample expense transaction',
        'created_at' => $date,
        'updated_at' => $date,
    ]);
}

echo "Created test data successfully!\n";
echo "Royalties: " . Royalty::count() . "\n";
echo "Revenues: " . Revenue::count() . "\n";
echo "Transactions: " . Transaction::count() . "\n";