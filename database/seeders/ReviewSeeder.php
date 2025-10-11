<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing units to associate reviews with
        $units = Unit::all();

        if ($units->isEmpty()) {
            // Create a sample unit first if none exist
            $unit = Unit::create([
                'franchise_id' => 1,
                'unit_name' => 'Sample Coffee Shop',
                'unit_code' => 'SMP-001',
                'unit_type' => 'store',
                'address' => '123 Main Street',
                'city' => 'Sample City',
                'state_province' => 'Sample State',
                'postal_code' => '12345',
                'country' => 'US',
                'status' => 'active',
            ]);
            $units = collect([$unit]);
        }

        $sampleReviews = [
            [
                'franchisee_id' => 1, // Will be updated if user exists
                'customer_name' => 'Sarah Johnson',
                'customer_email' => 'sarah.j@email.com',
                'customer_phone' => '+1-555-0101',
                'rating' => 5,
                'comment' => 'Absolutely fantastic experience! The staff was incredibly friendly and the atmosphere was perfect. The coffee was exceptional - rich and perfectly brewed. Will definitely be coming back!',
                'sentiment' => 'positive',
                'status' => 'published',
                'review_source' => 'in_person',
                'verified_purchase' => true,
                'internal_notes' => 'Regular customer, always satisfied with service.',
                'review_date' => now()->subDays(3),
            ],
            [
                'franchisee_id' => 1,
                'customer_name' => 'Michael Chen',
                'customer_email' => 'm.chen@email.com',
                'customer_phone' => '+1-555-0102',
                'rating' => 4,
                'comment' => 'Great coffee and friendly service. The only issue was that it was a bit crowded during my visit, but that\'s a testament to how good it is!',
                'sentiment' => 'positive',
                'status' => 'published',
                'review_source' => 'in_person',
                'verified_purchase' => true,
                'internal_notes' => 'First-time visitor, mentioned crowding issue.',
                'review_date' => now()->subDays(7),
            ],
            [
                'franchisee_id' => 1,
                'customer_name' => 'Emma Wilson',
                'customer_email' => 'emma.w@email.com',
                'rating' => 5,
                'comment' => 'This is my go-to coffee shop! The consistency in quality is amazing, and the baristas remember my name and usual order. It feels like family.',
                'sentiment' => 'positive',
                'status' => 'published',
                'review_source' => 'in_person',
                'verified_purchase' => true,
                'internal_notes' => 'Regular morning customer, loyal patron.',
                'review_date' => now()->subDays(14),
            ],
            [
                'franchisee_id' => 1,
                'customer_name' => 'David Martinez',
                'customer_email' => 'd.martinez@email.com',
                'customer_phone' => '+1-555-0103',
                'rating' => 3,
                'comment' => 'Good coffee but the wait time was longer than expected. Service was friendly once I got to the counter.',
                'sentiment' => 'neutral',
                'status' => 'published',
                'review_source' => 'phone',
                'verified_purchase' => true,
                'internal_notes' => 'Called in feedback about wait times.',
                'review_date' => now()->subDays(21),
            ],
            [
                'franchisee_id' => 1,
                'customer_name' => 'Lisa Thompson',
                'customer_email' => 'lisa.t@email.com',
                'rating' => 2,
                'comment' => 'Unfortunately, my experience wasn\'t great. The coffee was lukewarm and the pastries seemed stale. The staff seemed overwhelmed.',
                'sentiment' => 'negative',
                'status' => 'published',
                'review_source' => 'email',
                'verified_purchase' => true,
                'internal_notes' => 'Negative experience, followed up with compensation offer.',
                'review_date' => now()->subDays(30),
            ],
            [
                'franchisee_id' => 1,
                'customer_name' => 'Robert Kim',
                'customer_email' => 'r.kim@email.com',
                'rating' => 4,
                'comment' => 'Solid coffee shop with good variety. The vegan options are much appreciated. Keep up the good work!',
                'sentiment' => 'positive',
                'status' => 'draft',
                'review_source' => 'social_media',
                'verified_purchase' => true,
                'internal_notes' => 'Draft until we get permission to use full name.',
                'review_date' => now()->subDays(2),
            ],
            [
                'franchisee_id' => 1,
                'customer_name' => 'Amanda Foster',
                'customer_email' => 'amanda.f@email.com',
                'rating' => 5,
                'comment' => 'Perfect place for remote work! Great WiFi, plenty of outlets, and the staff doesn\'t mind if you stay for hours. Coffee is consistently excellent.',
                'sentiment' => 'positive',
                'status' => 'published',
                'review_source' => 'in_person',
                'verified_purchase' => true,
                'internal_notes' => 'Remote worker, visits 3-4 times per week.',
                'review_date' => now()->subDays(1),
            ],
            [
                'franchisee_id' => 1,
                'customer_name' => 'James Rodriguez',
                'customer_email' => 'j.rodriguez@email.com',
                'rating' => 4,
                'comment' => 'Love the atmosphere and the coffee is top-notch. The prices are a bit high, but the quality justifies it.',
                'sentiment' => 'positive',
                'status' => 'draft',
                'review_source' => 'other',
                'verified_purchase' => true,
                'internal_notes' => 'Customer feedback on pricing - needs review.',
                'review_date' => now()->subHours(12),
            ],
        ];

        // Create reviews for each unit
        foreach ($units as $unit) {
            foreach ($sampleReviews as $reviewData) {
                Review::create(array_merge($reviewData, [
                    'unit_id' => $unit->id,
                ]));
            }
        }
    }
}
