# Email Verification & Registration Fixes

## Issues Fixed

### 1. Verification Emails Not Being Sent

**Problem**: Verification emails were being queued but not sent because no queue worker was running.

**Root Cause**:
- `WelcomeEmailNotification` was implementing `ShouldQueue` interface
- Emails were being added to the `jobs` table but never processed
- No queue worker was running to process the queued jobs

**Solution**:
- Removed `ShouldQueue` interface from all notification classes
- Added missing `encryption` parameter to SMTP configuration in `config/mail.php`
- Emails now send immediately instead of being queued

**Files Modified**:
- `app/Notifications/WelcomeEmailNotification.php` - Removed `implements ShouldQueue`
- `app/Notifications/NewFranchiseeCredentials.php` - Removed `implements ShouldQueue`
- `app/Notifications/ResetPasswordNotification.php` - Removed `implements ShouldQueue`
- `config/mail.php` - Added `'encryption' => env('MAIL_ENCRYPTION', 'tls')` to SMTP config

**Mail Configuration**:
```php
'smtp' => [
    'transport' => 'smtp',
    'scheme' => env('MAIL_SCHEME'),
    'url' => env('MAIL_URL'),
    'host' => env('MAIL_HOST', '127.0.0.1'),
    'port' => env('MAIL_PORT', 2525),
    'encryption' => env('MAIL_ENCRYPTION', 'tls'), // ✅ ADDED
    'username' => env('MAIL_USERNAME'),
    'password' => env('MAIL_PASSWORD'),
    'timeout' => null,
    'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
],
```

---

### 2. Registration Not Providing User Abilities

**Problem**: After registration, users were not getting their role-based permissions (abilities), causing issues with accessing protected resources.

**Root Cause**:
- Registration endpoint returned a different response format than login endpoint
- Frontend was doing a workaround by auto-logging in after registration
- This workaround was failing and setting empty ability rules

**Login Response Format**:
```json
{
  "accessToken": "token...",
  "userData": {
    "id": 1,
    "fullName": "John Doe",
    "username": "john@example.com",
    "avatar": null,
    "email": "john@example.com",
    "role": "franchisor",
    "status": "pending",
    "nationality": null
  },
  "userAbilityRules": [
    { "action": "read", "subject": "FranchisorDashboard" },
    { "action": "manage", "subject": "Franchise" },
    ...
  ],
  "requiresFranchiseRegistration": true
}
```

**Old Registration Response Format**:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "franchisor",
      "status": "pending"
    },
    "token": "token..."
  },
  "message": "Registration successful..."
}
```

**Solution**:
- Updated registration endpoint to return the same format as login endpoint
- Added user ability rules generation based on role
- Added `requiresFranchiseRegistration` flag for franchisor users
- Simplified frontend registration flow to use response directly

**Files Modified**:
- `app/Http/Controllers/Api/V1/Auth/AuthController.php` - Updated `register()` method
- `resources/ts/pages/register.vue` - Simplified registration handler

**New Registration Response Format**:
Now matches login exactly:
```json
{
  "accessToken": "token...",
  "userData": {
    "id": 1,
    "fullName": "John Doe",
    "username": "john@example.com",
    "avatar": null,
    "email": "john@example.com",
    "role": "franchisor",
    "status": "pending",
    "nationality": null
  },
  "userAbilityRules": [
    { "action": "read", "subject": "FranchisorDashboard" },
    { "action": "manage", "subject": "Franchise" },
    ...
  ],
  "requiresFranchiseRegistration": true,
  "message": "Registration successful. Please check your email to verify your account."
}
```

---

## User Ability Rules by Role

### Franchisor
- Read FranchisorDashboard
- Read & Manage Leads
- Manage Users
- Read & Manage Franchises
- Read Units
- Manage Tasks
- Read Performance & Revenue
- Manage Royalty
- Create Technical Requests

### Broker
- Read Dashboard & BrokerDashboard
- Manage Brokerage
- Manage Leads & Lead Management
- Read & Update Tasks
- Read, Create & Update Technical Requests
- Read Statistics
- Manage Notes, Properties & Franchises

### Franchisee
- Read FranchiseeDashboard
- Read Units, Tasks, Performance, Revenue, Royalty
- Create Technical Requests

### Admin
- Manage all (full access)

---

## Testing

### Test Email Sending
```bash
php artisan tinker
```

```php
// Test verification email
$user = \App\Models\User::find(1);
$user->sendEmailVerificationNotification();
// Email should send immediately

// Test password reset email
$user->notify(new \App\Notifications\ResetPasswordNotification('test-token'));
// Email should send immediately
```

### Test Registration
1. Go to `/register`
2. Select a role (Franchisor or Broker)
3. Fill in the registration form
4. Submit
5. Check that:
   - User is created with correct role
   - Verification email is sent immediately
   - User is redirected to verify-email page
   - User has proper ability rules set in cookies
   - User can access role-specific pages

---

## Queue Configuration (Optional for Production)

If you want to use queues for better performance in production:

### 1. Keep `ShouldQueue` in Notifications
Restore the `implements ShouldQueue` in notification classes.

### 2. Ensure Queue Worker is Running
```bash
# Development
php artisan queue:work

# Production (using Supervisor)
# Add to /etc/supervisor/conf.d/laravel-worker.conf
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
stopwaitsecs=3600
```

### 3. Monitor Queue
```bash
# Check pending jobs
php artisan queue:work --once

# Failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

---

## Current Mail Configuration

Your `.env` file has:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=hello@afifistudio.com
MAIL_PASSWORD='c1u2v9Maz#'
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=hello@afifistudio.com
MAIL_FROM_NAME="${APP_NAME}"
```

This configuration is now working correctly with:
- SSL encryption on port 465
- Hostinger SMTP
- Immediate email sending (no queue)

---

## Summary

✅ **Email Verification Fixed**:
- Emails now send immediately instead of being queued
- SMTP encryption properly configured
- All notification classes updated

✅ **Registration Abilities Fixed**:
- Registration returns same format as login
- Users get proper role-based permissions immediately
- Frontend simplified, no auto-login workaround needed
- Consistent user experience between registration and login

✅ **All Email Notifications Working**:
- Welcome/Verification emails
- Password reset emails
- New franchisee credentials emails

---

## Date
October 26, 2025

