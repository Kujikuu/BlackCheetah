<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear rate limiters before each test
        RateLimiter::clear('login');
        RateLimiter::clear('password-reset');
        RateLimiter::clear('registration');
        RateLimiter::clear('email-verification');
    }

    public function test_login_is_rate_limited(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Make 5 requests (the limit)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);

            $response->assertStatus(401);
        }

        // The 6th request should be rate limited
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(429); // Too Many Requests
    }

    public function test_password_reset_is_rate_limited(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Make 3 requests (the limit)
        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/api/v1/auth/forgot-password', [
                'email' => 'test@example.com',
            ]);

            $response->assertStatus(200);
        }

        // The 4th request should be rate limited
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(429);
    }

    public function test_registration_is_rate_limited(): void
    {
        // Fake notifications to avoid route errors
        \Illuminate\Support\Facades\Notification::fake();

        // Note: Rate limiting is 3 per hour per IP
        // In testing environment, we need to make more than 3 requests
        $responses = [];
        
        for ($i = 0; $i < 4; $i++) {
            $responses[] = $this->postJson('/api/v1/auth/register', [
                'name' => "Test User {$i}",
                'email' => "ratelimit{$i}@example.com",
                'password' => 'UniqueTestP@ssw0rd!R' . $i . 'L',
                'password_confirmation' => 'UniqueTestP@ssw0rd!R' . $i . 'L',
                'role' => 'broker',
            ]);
        }

        // At least one of the requests should be rate limited
        $rateLimitedCount = collect($responses)->filter(fn($r) => $r->status() === 429)->count();
        $this->assertGreaterThan(0, $rateLimitedCount, 'At least one request should be rate limited');
    }

    public function test_email_verification_resend_is_rate_limited(): void
    {
        // Fake notifications to avoid route errors
        \Illuminate\Support\Facades\Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Make 6 requests (the limit)
        for ($i = 0; $i < 6; $i++) {
            $response = $this->actingAs($user, 'sanctum')
                ->postJson('/api/v1/auth/email/verification-notification');

            $response->assertStatus(200);
        }

        // The 7th request should be rate limited
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/email/verification-notification');

        $response->assertStatus(429);
    }
}

