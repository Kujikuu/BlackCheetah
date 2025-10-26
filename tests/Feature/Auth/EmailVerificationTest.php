<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_resend_verification_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/email/verification-notification');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_user_cannot_resend_verification_if_already_verified(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/email/verification-notification');

        $response->assertStatus(400);
    }

    public function test_guest_cannot_resend_verification_email(): void
    {
        $response = $this->postJson('/api/v1/auth/email/verification-notification');

        $response->assertStatus(401);
    }

    public function test_user_can_verify_email_with_valid_signed_url(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $parsedUrl = parse_url($verificationUrl);
        parse_str($parsedUrl['query'] ?? '', $queryParams);

        $response = $this->getJson(
            "/api/v1/auth/email/verify/{$user->id}/" . sha1($user->email) .
            "?expires={$queryParams['expires']}&signature={$queryParams['signature']}"
        );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_user_cannot_verify_with_invalid_signature(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->getJson(
            "/api/v1/auth/email/verify/{$user->id}/" . sha1($user->email) .
            "?expires=" . now()->addMinutes(60)->timestamp .
            "&signature=invalid-signature"
        );

        $response->assertStatus(403); // Laravel returns 403 for invalid signatures

        $user->refresh();
        $this->assertNull($user->email_verified_at);
    }

    public function test_user_can_check_verification_status(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/email/verification-status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_verified' => true,
                    'email' => $user->email,
                ],
            ]);
    }

    public function test_unverified_user_sees_unverified_status(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/email/verification-status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_verified' => false,
                ],
            ]);
    }
}

