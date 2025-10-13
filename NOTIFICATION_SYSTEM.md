# Notification System Documentation

## Overview
The BlackCheetah notification system has two separate but complementary components:

### 1. Notification Preferences (Account Settings)
**Location**: Account Settings → Notifications Tab  
**Purpose**: User preferences for HOW they want to receive notifications  
**Storage**: `users.preferences` JSON column → `notifications` key

#### Structure:
```json
{
  "notifications": {
    "new_user_registration": {
      "email": true,
      "browser": true,
      "app": true
    },
    "new_technical_request": {
      "email": true,
      "browser": true,
      "app": true
    },
    "technical_request_status_change": {
      "email": true,
      "browser": false,
      "app": true
    },
    "new_franchisor_application": {
      "email": true,
      "browser": true,
      "app": false
    },
    "payment_received": {
      "email": true,
      "browser": false,
      "app": false
    },
    "system_alerts": {
      "email": true,
      "browser": true,
      "app": true
    }
  }
}
```

**API Endpoint**: `PUT /api/v1/account/notifications`  
**Controller**: `AccountSettingsController::updateNotifications()`

---

### 2. Notification Records (Actual Notifications)
**Location**: Notifications Bell Icon / Notifications Page  
**Purpose**: Actual notification messages sent to users  
**Storage**: `notifications` table (Laravel's built-in notification system)

#### Structure:
```php
// notifications table
- id (uuid)
- type (notification class name)
- notifiable_type (User class)
- notifiable_id (user ID)
- data (JSON with title, subtitle, icon, color, url)
- read_at (timestamp or null)
- created_at
- updated_at
```

**API Endpoints**: 
- `GET /api/v1/notifications` - List notifications
- `GET /api/v1/notifications/stats` - Get counts
- `PATCH /api/v1/notifications/{id}/read` - Mark as read
- `DELETE /api/v1/notifications/{id}` - Delete notification

**Controller**: `NotificationController`  
**Model**: `Notification`

---

## How They Work Together

### Sending a Notification (Example)

```php
// When creating a notification, check user preferences
$user = User::find($userId);
$preferences = $user->preferences['notifications']['new_technical_request'] ?? [];

// Check if browser notifications are enabled
if ($preferences['browser'] ?? true) {
    // Send database notification (shows in bell icon)
    $user->notify(new TechnicalRequestCreated($request));
}

// Check if email notifications are enabled
if ($preferences['email'] ?? true) {
    // Send email
    Mail::to($user)->send(new TechnicalRequestEmail($request));
}

// Check if app notifications are enabled (push notifications)
if ($preferences['app'] ?? true) {
    // Send push notification (future feature)
    // PushNotification::send($user, $data);
}
```

---

## Current Implementation Status

### ✅ Implemented
1. **Notification Preferences UI** - Users can toggle preferences
2. **Notification Preferences Storage** - Saved in `users.preferences`
3. **Notification Records System** - Full CRUD for notifications
4. **Notification Display** - Bell icon shows unread count
5. **Mark as Read/Unread** - Users can manage notification status

### ⚠️ Partially Implemented
1. **Preference Checking** - Currently NOT enforced when sending notifications
2. **Email Notifications** - Laravel mail system exists but not integrated with preferences
3. **Push Notifications** - App channel is prepared but not implemented

### ❌ Not Yet Implemented
1. **Automatic Preference Enforcement** - Notification sending doesn't check preferences
2. **Email Integration** - Emails are sent regardless of preferences
3. **Push Notifications** - Mobile/web push not implemented

---

## Making Preferences Affect Notifications

To make notification preferences actually work, you need to:

### 1. Create a Notification Service

```php
// app/Services/NotificationService.php
<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function send(User $user, string $type, Notification $notification, $mailableClass = null)
    {
        $preferences = $user->preferences['notifications'][$type] ?? [
            'email' => true,
            'browser' => true,
            'app' => true,
        ];

        // Browser/Database notifications
        if ($preferences['browser'] ?? true) {
            $user->notify($notification);
        }

        // Email notifications
        if (($preferences['email'] ?? true) && $mailableClass) {
            Mail::to($user)->send(new $mailableClass($notification->getData()));
        }

        // Push notifications (future)
        if ($preferences['app'] ?? true) {
            // Send push notification
            // $this->sendPushNotification($user, $notification);
        }
    }
}
```

### 2. Use the Service

```php
// Instead of:
$user->notify(new TechnicalRequestCreated($request));

// Use:
app(NotificationService::class)->send(
    $user,
    'new_technical_request',
    new TechnicalRequestCreated($request),
    TechnicalRequestEmail::class
);
```

---

## Notification Types Reference

| Type | Description | Default Channels |
|------|-------------|------------------|
| `new_user_registration` | New user signs up | Email, Browser, App |
| `new_technical_request` | Technical request created | Email, Browser, App |
| `technical_request_status_change` | Request status updated | Email, App |
| `new_franchisor_application` | Franchisor applies | Email, Browser |
| `payment_received` | Payment processed | Email |
| `system_alerts` | System-wide alerts | Email, Browser, App |

---

## API Usage Examples

### Update Notification Preferences
```javascript
await accountSettingsApi.updateNotificationPreferences({
  new_technical_request: {
    email: true,
    browser: true,
    app: false
  },
  payment_received: {
    email: true,
    browser: false,
    app: false
  }
})
```

### Get User Notifications
```javascript
// Get unread notifications
const response = await $api('/v1/notifications?unread_only=true')

// Get all notifications with pagination
const response = await $api('/v1/notifications?per_page=20&page=1')

// Get notification stats
const stats = await $api('/v1/notifications/stats')
// Returns: { total: 50, unread: 12, read: 38 }
```

### Mark Notification as Read
```javascript
await $api(`/v1/notifications/${notificationId}/read`, {
  method: 'PATCH'
})
```

---

## Summary

**Notification Preferences** (Account Settings) control **HOW** users receive notifications across different channels (email, browser, app).

**Notification Records** (NotificationController) manage **WHAT** notifications users have received and their read status.

Currently, preferences are stored but **not yet enforced** when sending notifications. You'll need to implement the NotificationService pattern above to make preferences actually affect notification delivery.
