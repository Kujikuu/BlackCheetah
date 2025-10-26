<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_password_reset_link(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_forgot_password_requires_valid_email(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_forgot_password_with_nonexistent_email_still_returns_success(): void
    {
        // Security best practice: don't reveal if email exists
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(200);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('OldP@ssw0rd!Test'),
        ]);

        // Request password reset
        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'test@example.com',
        ]);

        // Get the token from database
        $tokenRecord = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', 'test@example.com')
            ->first();

        $this->assertNotNull($tokenRecord);

        // Generate a plain token for testing
        $plainToken = \Illuminate\Support\Str::random(64);
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', 'test@example.com')
            ->update(['token' => Hash::make($plainToken)]);

        // Reset password
        $response = $this->withoutMiddleware(\Illuminate\Routing\Middleware\ValidateSignature::class)
            ->postJson('/api/v1/auth/reset-password', [
                'token' => $plainToken,
                'email' => 'test@example.com',
                'password' => 'UniqueNewP@ssw0rd!Test2024',
                'password_confirmation' => 'UniqueNewP@ssw0rd!Test2024',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('UniqueNewP@ssw0rd!Test2024', $user->password));

        // Verify token was deleted
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(422);
    }

    public function test_reset_password_requires_strong_password(): void
    {
        $user = User::factory()->create();

        $response = $this->withoutMiddleware(\Illuminate\Routing\Middleware\ValidateSignature::class)
            ->postJson('/api/v1/auth/reset-password', [
                'token' => 'some-token',
                'email' => $user->email,
                'password' => 'weak',
                'password_confirmation' => 'weak',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_reset_password_requires_password_confirmation(): void
    {
        $user = User::factory()->create();

        $response = $this->withoutMiddleware(\Illuminate\Routing\Middleware\ValidateSignature::class)
            ->postJson('/api/v1/auth/reset-password', [
                'token' => 'some-token',
                'email' => $user->email,
                'password' => 'UniqueTestP@ssw0rd!2024A',
                'password_confirmation' => 'UniqueTestP@ssw0rd!2024B',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_expired_token_cannot_be_used(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $plainToken = \Illuminate\Support\Str::random(64);

        // Create an expired token (created 65 minutes ago to ensure it's well past the 60-minute limit)
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->insert([
            'email' => 'test@example.com',
            'token' => Hash::make($plainToken),
            'created_at' => now()->subMinutes(65)->format('Y-m-d H:i:s'),
        ]);

        $response = $this->withoutMiddleware(\Illuminate\Routing\Middleware\ValidateSignature::class)
            ->postJson('/api/v1/auth/reset-password', [
                'token' => $plainToken,
                'email' => 'test@example.com',
                'password' => 'UniqueNewP@ssw0rd!Test2024C',
                'password_confirmation' => 'UniqueNewP@ssw0rd!Test2024C',
            ]);

        $response->assertStatus(422);

        // Verify the error message contains expiration information
        $this->assertTrue(
            str_contains($response->json('message') ?? '', 'expired') ||
            isset($response->json('errors')['token'])
        );
    }
}

