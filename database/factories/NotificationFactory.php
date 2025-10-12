<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $notificationTypes = [
            'user_registration' => [
                'title' => 'New User Registration',
                'subtitle' => 'A new user has registered on the platform',
                'icon' => 'tabler-user-plus',
                'color' => 'success',
            ],
            'technical_request' => [
                'title' => 'New Technical Request',
                'subtitle' => 'A new technical request has been submitted',
                'icon' => 'tabler-tool',
                'color' => 'info',
            ],
            'technical_request_status' => [
                'title' => 'Technical Request Status Change',
                'subtitle' => 'Technical request status has been updated',
                'icon' => 'tabler-refresh',
                'color' => 'warning',
            ],
            'franchisor_application' => [
                'title' => 'New Franchisor Application',
                'subtitle' => 'A new franchisor application has been received',
                'icon' => 'tabler-building-store',
                'color' => 'primary',
            ],
            'payment_received' => [
                'title' => 'Payment Received',
                'subtitle' => 'A payment has been successfully processed',
                'icon' => 'tabler-credit-card',
                'color' => 'success',
            ],
            'system_alert' => [
                'title' => 'System Alert',
                'subtitle' => 'Important system notification',
                'icon' => 'tabler-alert-triangle',
                'color' => 'error',
            ],
        ];

        $type = $this->faker->randomElement(array_keys($notificationTypes));
        $typeData = $notificationTypes[$type];

        return [
            'id' => $this->faker->uuid(),
            'type' => 'App\\Notifications\\'.ucfirst(str_replace('_', '', $type)).'Notification',
            'notifiable_type' => User::class,
            'notifiable_id' => User::factory(),
            'data' => [
                'title' => $typeData['title'],
                'subtitle' => $typeData['subtitle'],
                'icon' => $typeData['icon'],
                'color' => $typeData['color'],
                'time' => $this->faker->dateTimeBetween('-1 week', 'now')->format('M d'),
                'url' => $this->faker->optional()->url(),
            ],
            'read_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 week', 'now'),
            'created_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'updated_at' => function (array $attributes) {
                return $attributes['created_at'];
            },
        ];
    }

    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => $this->faker->dateTimeBetween($attributes['created_at'], 'now'),
        ]);
    }
}
