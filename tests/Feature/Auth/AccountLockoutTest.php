<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountLockoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_locks_after_five_failed_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('CorrectPassword123!'),
        ]);

        // Make 5 failed login attempts
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $user->refresh();

        // Verify account is locked
        $this->assertTrue($user->isLocked());
        $this->assertEquals(5, $user->failed_login_attempts);
        $this->assertNotNull($user->locked_until);
    }

    public function test_locked_account_cannot_login_with_correct_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('CorrectPassword123!'),
            'failed_login_attempts' => 5,
            'locked_until' => now()->addMinutes(15),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'CorrectPassword123!',
        ]);

        $response->assertStatus(429); // Too Many Requests
        
        // Verify the message contains lockout information
        $this->assertStringContainsString('temporarily locked', $response->json('message'));
    }

    public function test_account_unlocks_after_lock_period_expires(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('CorrectPassword123!'),
            'failed_login_attempts' => 5,
            'locked_until' => now()->subMinute(), // Expired 1 minute ago
        ]);

        // Attempt login with correct password
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'CorrectPassword123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'accessToken',
                'userData',
                'userAbilityRules',
            ]);

        $user->refresh();

        // Verify lock has been cleared
        $this->assertFalse($user->isLocked());
        $this->assertEquals(0, $user->failed_login_attempts);
        $this->assertNull($user->locked_until);
    }

    public function test_successful_login_resets_failed_attempts_counter(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('CorrectPassword123!'),
            'failed_login_attempts' => 3,
        ]);

        // Successful login
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'CorrectPassword123!',
        ]);

        $response->assertStatus(200);

        $user->refresh();

        // Verify counter was reset
        $this->assertEquals(0, $user->failed_login_attempts);
        $this->assertNull($user->locked_until);
    }

    public function test_failed_login_increments_counter(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('CorrectPassword123!'),
            'failed_login_attempts' => 0,
        ]);

        // Failed login
        $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $user->refresh();

        $this->assertEquals(1, $user->failed_login_attempts);
        $this->assertNotNull($user->last_failed_login_at);
    }

    public function test_remaining_lock_time_calculation(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'locked_until' => now()->addMinutes(10),
        ]);

        $remainingTime = $user->remainingLockTime();

        $this->assertIsInt($remainingTime);
        $this->assertGreaterThanOrEqual(9, $remainingTime);
        $this->assertLessThanOrEqual(10, $remainingTime);
    }
}

