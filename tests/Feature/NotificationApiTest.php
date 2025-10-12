<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_fetch_notifications(): void
    {
        // Create test notifications
        Notification::factory()->count(3)->create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'subtitle',
                            'icon',
                            'color',
                            'time',
                            'isSeen',
                            'url',
                            'created_at',
                        ],
                    ],
                    'current_page',
                    'last_page',
                ],
            ]);
    }

    public function test_can_fetch_unread_notifications_only(): void
    {
        // Create read and unread notifications
        Notification::factory()->create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'read_at' => now(),
        ]);

        Notification::factory()->count(2)->create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'read_at' => null,
        ]);

        $response = $this->getJson('/api/v1/notifications?unread_only=true');

        $response->assertStatus(200);
        $this->assertEquals(2, count($response->json('data.data')));
    }

    public function test_can_show_specific_notification(): void
    {
        $notification = Notification::factory()->create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
        ]);

        $response = $this->getJson("/api/v1/notifications/{$notification->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'type',
                    'title',
                    'subtitle',
                    'icon',
                    'color',
                    'time',
                    'isSeen',
                    'url',
                    'created_at',
                    'data',
                ],
            ]);
    }

    public function test_can_mark_notification_as_read(): void
    {
        $notification = Notification::factory()->create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'read_at' => null,
        ]);

        $response = $this->patchJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertStatus(200);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_can_mark_notification_as_unread(): void
    {
        $notification = Notification::factory()->create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'read_at' => now(),
        ]);

        $response = $this->patchJson("/api/v1/notifications/{$notification->id}/unread");

        $response->assertStatus(200);
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_can_mark_multiple_notifications_as_read(): void
    {
        $notifications = Notification::factory()->count(3)->create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'read_at' => null,
        ]);

        $notificationIds = $notifications->pluck('id')->toArray();

        $response = $this->patchJson('/api/v1/notifications/mark-multiple-read', [
            'notification_ids' => $notificationIds,
        ]);

        $response->assertStatus(200);

        foreach ($notifications as $notification) {
            $this->assertNotNull($notification->fresh()->read_at);
        }
    }

    public function test_can_mark_all_notifications_as_read(): void
    {
        Notification::factory()->count(5)->create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'read_at' => null,
        ]);

        $response = $this->patchJson('/api/v1/notifications/mark-all-read');

        $response->assertStatus(200);

        $unreadCount = $this->user->unreadNotifications()->count();
        $this->assertEquals(0, $unreadCount);
    }

    public function test_can_delete_notification(): void
    {
        $notification = Notification::factory()->create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/v1/notifications/{$notification->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_can_get_notification_stats(): void
    {
        // Create read and unread notifications
        Notification::factory()->count(3)->create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'read_at' => now(),
        ]);

        Notification::factory()->count(2)->create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'read_at' => null,
        ]);

        $response = $this->getJson('/api/v1/notifications/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total',
                    'unread',
                    'read',
                ],
            ]);

        $stats = $response->json('data');
        $this->assertEquals(5, $stats['total']);
        $this->assertEquals(2, $stats['unread']);
        $this->assertEquals(3, $stats['read']);
    }

    public function test_cannot_access_other_users_notifications(): void
    {
        $otherUser = User::factory()->create();
        $notification = Notification::factory()->create([
            'notifiable_type' => User::class,
            'notifiable_id' => $otherUser->id,
        ]);

        $response = $this->getJson("/api/v1/notifications/{$notification->id}");

        $response->assertStatus(404);
    }
}
