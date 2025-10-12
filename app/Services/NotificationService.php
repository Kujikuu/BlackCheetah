<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Send a new user registration notification to admins.
     */
    public function sendNewUserRegistrationNotification(User $newUser): void
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(new \Illuminate\Notifications\DatabaseNotification([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'App\\Notifications\\NewUserRegistration',
                'notifiable_type' => User::class,
                'notifiable_id' => $admin->id,
                'data' => [
                    'title' => 'New User Registration',
                    'subtitle' => "New user {$newUser->name} has registered",
                    'icon' => 'tabler-user-plus',
                    'color' => 'success',
                    'url' => "/admin/users/{$newUser->id}",
                    'user_id' => $newUser->id,
                    'user_name' => $newUser->name,
                    'user_email' => $newUser->email,
                ],
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Send a technical request status change notification.
     */
    public function sendTechnicalRequestStatusNotification(User $user, string $requestTitle, string $status): void
    {
        $statusColors = [
            'pending' => 'warning',
            'in_progress' => 'info',
            'completed' => 'success',
            'rejected' => 'error',
        ];

        $statusIcons = [
            'pending' => 'tabler-clock',
            'in_progress' => 'tabler-progress',
            'completed' => 'tabler-check',
            'rejected' => 'tabler-x',
        ];

        $user->notify(new \Illuminate\Notifications\DatabaseNotification([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\TechnicalRequestStatus',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'title' => 'Technical Request Update',
                'subtitle' => "Your request '{$requestTitle}' is now {$status}",
                'icon' => $statusIcons[$status] ?? 'tabler-bell',
                'color' => $statusColors[$status] ?? 'primary',
                'url' => '/technical-requests',
                'request_title' => $requestTitle,
                'status' => $status,
            ],
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    /**
     * Send a system alert notification to all users.
     */
    public function sendSystemAlertNotification(string $title, string $message, string $type = 'info'): void
    {
        $users = User::all();

        $typeColors = [
            'info' => 'info',
            'warning' => 'warning',
            'error' => 'error',
            'success' => 'success',
        ];

        $typeIcons = [
            'info' => 'tabler-info-circle',
            'warning' => 'tabler-alert-triangle',
            'error' => 'tabler-alert-circle',
            'success' => 'tabler-check-circle',
        ];

        foreach ($users as $user) {
            $user->notify(new \Illuminate\Notifications\DatabaseNotification([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'App\\Notifications\\SystemAlert',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => [
                    'title' => $title,
                    'subtitle' => $message,
                    'icon' => $typeIcons[$type] ?? 'tabler-bell',
                    'color' => $typeColors[$type] ?? 'primary',
                    'url' => null,
                    'alert_type' => $type,
                ],
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Send a franchise approval notification.
     */
    public function sendFranchiseApprovalNotification(User $user, string $franchiseName, bool $approved): void
    {
        $status = $approved ? 'approved' : 'rejected';
        $color = $approved ? 'success' : 'error';
        $icon = $approved ? 'tabler-check' : 'tabler-x';

        $user->notify(new \Illuminate\Notifications\DatabaseNotification([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\FranchiseApproval',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'title' => 'Franchise Application Update',
                'subtitle' => "Your franchise application for '{$franchiseName}' has been {$status}",
                'icon' => $icon,
                'color' => $color,
                'url' => '/franchises',
                'franchise_name' => $franchiseName,
                'approved' => $approved,
            ],
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    /**
     * Send a document approval notification.
     */
    public function sendDocumentApprovalNotification(User $user, string $documentName, bool $approved): void
    {
        $status = $approved ? 'approved' : 'rejected';
        $color = $approved ? 'success' : 'error';
        $icon = $approved ? 'tabler-file-check' : 'tabler-file-x';

        $user->notify(new \Illuminate\Notifications\DatabaseNotification([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\DocumentApproval',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'title' => 'Document Review Complete',
                'subtitle' => "Your document '{$documentName}' has been {$status}",
                'icon' => $icon,
                'color' => $color,
                'url' => '/documents',
                'document_name' => $documentName,
                'approved' => $approved,
            ],
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    /**
     * Send a payment reminder notification.
     */
    public function sendPaymentReminderNotification(User $user, string $paymentType, float $amount, string $dueDate): void
    {
        $user->notify(new \Illuminate\Notifications\DatabaseNotification([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\PaymentReminder',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'title' => 'Payment Reminder',
                'subtitle' => "{$paymentType} payment of $".number_format($amount, 2)." is due on {$dueDate}",
                'icon' => 'tabler-credit-card',
                'color' => 'warning',
                'url' => '/payments',
                'payment_type' => $paymentType,
                'amount' => $amount,
                'due_date' => $dueDate,
            ],
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    /**
     * Send a task assignment notification.
     */
    public function sendTaskAssignmentNotification(User $user, string $taskTitle): void
    {
        $user->notify(new \Illuminate\Notifications\DatabaseNotification([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\TaskAssignment',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'title' => 'New Task Assigned',
                'subtitle' => "You have been assigned a new task: '{$taskTitle}'",
                'icon' => 'tabler-clipboard-check',
                'color' => 'info',
                'url' => '/tasks',
                'task_title' => $taskTitle,
            ],
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }
}
