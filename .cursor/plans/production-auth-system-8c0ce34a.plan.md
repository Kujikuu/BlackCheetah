<!-- 8c0ce34a-2026-45b8-9c9d-113d453abde8 918efec2-ae69-4437-8e95-2fef650af9a1 -->
# Production-Grade Authentication Implementation

## Current Gaps Identified

- No password reset functionality (frontend exists but not wired)
- No email verification system
- No rate limiting on authentication endpoints
- Weak password validation (only 6 characters minimum)
- No failed login tracking or account lockout
- Remember me functionality not implemented
- Mail driver set to 'log' instead of SMTP

## Implementation Overview

### 1. Database Schema Enhancements

**Migration**: Add security tracking columns to users table

- `failed_login_attempts` (integer, default 0)
- `locked_until` (timestamp, nullable)
- `last_failed_login_at` (timestamp, nullable)

### 2. Password Reset System

**Backend** (`app/Http/Controllers/Api/V1/Auth/PasswordResetController.php`)

- `forgotPassword()` - Generate and send reset token via email
- `resetPassword()` - Validate token and update password
- `validateResetToken()` - Check if token is valid

**Form Requests**

- `ForgotPasswordRequest.php` - Validate email
- `ResetPasswordRequest.php` - Validate token, password, password_confirmation

**Notification** (`app/Notifications/ResetPasswordNotification.php`)

- Branded email with reset link containing token
- Link expires after 60 minutes

**Routes** (`routes/api/v1/auth.php`)

- POST `/auth/forgot-password`
- POST `/auth/reset-password`
- POST `/auth/validate-reset-token`

**Frontend Updates**

- Wire `forgot-password.vue` to backend API
- Wire `reset-password.vue` to backend API with token from URL query param
- Add proper error/success messaging

### 3. Email Verification System

**Backend** (`app/Http/Controllers/Api/V1/Auth/EmailVerificationController.php`)

- `sendVerificationEmail()` - Resend verification email
- `verify()` - Verify email with signed URL
- `checkVerificationStatus()` - Check if user is verified

**Middleware** (`app/Http/Middleware/EnsureEmailIsVerified.php`)

- Block unverified users from accessing protected routes (except profile/settings)

**Notification** (Laravel built-in `Illuminate\Auth\Notifications\VerifyEmail`)

- Customize to match branding

**User Model Updates**

- Implement `MustVerifyEmail` interface
- Override verification notification if needed

**Routes**

- POST `/auth/email/verification-notification` (resend)
- GET `/auth/email/verify/{id}/{hash}` (verify signed URL)

**Frontend**

- Create `verify-email.vue` page with resend button
- Add verification banner in dashboard for unverified users
- Redirect to verification notice after registration

### 4. Rate Limiting

**Configuration** (`bootstrap/app.php` or service provider)

- Custom rate limiter for login: 5 attempts per minute per email
- Custom rate limiter for password reset: 3 attempts per hour per IP
- Custom rate limiter for registration: 3 attempts per hour per IP
- Custom rate limiter for email verification: 6 per hour per user

**Routes Update**

- Apply `throttle` middleware to auth routes with custom limiters

### 5. Enhanced Security Features

**Password Validation**

- Minimum 8 characters
- At least one uppercase letter
- At least one lowercase letter
- At least one number
- At least one special character

**Failed Login Tracking** (in `AuthController@login`)

- Increment `failed_login_attempts` on failed login
- Lock account after 5 failed attempts for 15 minutes
- Reset counter on successful login
- Return appropriate error messages

**Remember Me** (in `AuthController@login`)

- Generate long-lived token when remember is true
- Set token expiration to 30 days

**Registration Enhancements**

- Stronger validation rules
- Send welcome email with verification link
- Set initial status to 'pending' until email verified

### 6. Hostinger SMTP Configuration

**Environment Variables** (update `.env.example`)

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=your-email@yourdomain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Mail Templates**

- Create Mailable classes for branded emails
- Custom layouts matching application theme

### 7. Frontend Enhancements

**Auth Service** (`resources/ts/services/api/auth.ts`)

- Add methods for password reset flow
- Add methods for email verification
- Proper TypeScript types

**Validation** (`resources/ts/validation/`)

- Password strength validator
- Consistent validation rules with backend

**User Experience**

- Loading states during API calls
- Clear error messages
- Success confirmations
- Password strength indicator
- Auto-redirect after successful operations

### 8. Testing

**Feature Tests**

- Password reset complete flow
- Email verification flow
- Rate limiting enforcement
- Account lockout functionality
- Email sending (use Mail::fake())

**Request Tests**

- Validation rule tests for all form requests

## Files to Create

- `app/Http/Controllers/Api/V1/Auth/PasswordResetController.php`
- `app/Http/Controllers/Api/V1/Auth/EmailVerificationController.php`
- `app/Http/Requests/ForgotPasswordRequest.php`
- `app/Http/Requests/ResetPasswordRequest.php`
- `app/Http/Requests/ResendVerificationRequest.php`
- `app/Http/Middleware/EnsureEmailIsVerified.php`
- `app/Notifications/ResetPasswordNotification.php`
- `app/Notifications/WelcomeEmailNotification.php`
- `database/migrations/YYYY_MM_DD_add_security_columns_to_users_table.php`
- `resources/ts/pages/verify-email.vue`
- `resources/ts/services/api/auth.ts` (enhance existing or create)
- `tests/Feature/Auth/PasswordResetTest.php`
- `tests/Feature/Auth/EmailVerificationTest.php`
- `tests/Feature/Auth/RateLimitingTest.php`
- `tests/Feature/Auth/AccountLockoutTest.php`

## Files to Modify

- `app/Http/Controllers/Api/V1/Auth/AuthController.php` (add lockout logic, remember me)
- `app/Models/User.php` (add MustVerifyEmail, new fillable columns, helper methods)
- `routes/api/v1/auth.php` (add new routes with rate limiting)
- `bootstrap/app.php` (configure custom rate limiters)
- `resources/ts/pages/forgot-password.vue` (wire to backend)
- `resources/ts/pages/reset-password.vue` (wire to backend)
- `resources/ts/pages/register.vue` (redirect to verification notice)
- `resources/ts/pages/login.vue` (handle remember me, show lockout messages)
- `config/mail.php` (already configured, just needs .env update)
- `.env.example` (add SMTP settings documentation)

## Security Best Practices Applied

✓ Token-based password reset with expiration

✓ Signed URLs for email verification

✓ Rate limiting on sensitive endpoints

✓ Account lockout after failed attempts

✓ Strong password requirements

✓ HTTPS-only cookies for tokens

✓ CSRF protection via Sanctum

✓ SQL injection prevention via Eloquent

✓ XSS prevention via Vue escaping

### To-dos

- [ ] Create migration to add security tracking columns (failed_login_attempts, locked_until, last_failed_login_at) to users table
- [ ] Update User model: implement MustVerifyEmail, add fillable columns, create helper methods for lockout/security
- [ ] Configure custom rate limiters in bootstrap/app.php for login, registration, password reset, and email verification
- [ ] Create PasswordResetController with forgotPassword, resetPassword, and validateResetToken methods
- [ ] Create ForgotPasswordRequest and ResetPasswordRequest form request classes with validation rules
- [ ] Create ResetPasswordNotification with branded email template
- [ ] Create EmailVerificationController with send, verify, and status check methods
- [ ] Create EnsureEmailIsVerified middleware to protect routes requiring verified email
- [ ] Create WelcomeEmailNotification with email verification link
- [ ] Enhance AuthController: add failed login tracking, account lockout logic, remember me functionality
- [ ] Update auth routes with password reset, email verification endpoints, and apply rate limiting middleware
- [ ] Create/enhance auth.ts service with methods for password reset and email verification flows
- [ ] Wire forgot-password.vue to backend API with proper error handling
- [ ] Wire reset-password.vue to backend API with token extraction from URL
- [ ] Create verify-email.vue page with resend verification button and status display
- [ ] Update register.vue to redirect to email verification notice and show stronger password requirements
- [ ] Update login.vue to handle remember me checkbox and display account lockout messages
- [ ] Update .env.example with Hostinger SMTP configuration documentation
- [ ] Create comprehensive feature tests for password reset, email verification, rate limiting, and account lockout