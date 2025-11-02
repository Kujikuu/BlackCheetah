# SMTP Configuration for Hostinger

This document provides instructions for configuring email functionality with Hostinger SMTP.

## Environment Variables

Add the following variables to your `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=your-email@yourdomain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Hostinger SMTP Settings

- **Server**: smtp.hostinger.com
- **Port**: 465 (SSL) or 587 (TLS)
- **Encryption**: SSL or TLS
- **Authentication**: Required

## Setup Steps

### 1. Create Email Account in Hostinger

1. Log into your Hostinger control panel
2. Navigate to Email Accounts
3. Create a new email account (e.g., noreply@yourdomain.com)
4. Set a strong password

### 2. Configure Laravel

Update your `.env` file with the credentials from step 1:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your-secure-password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Cheetah"
```

### 3. Test Email Functionality

Run the following command to test email sending:

```bash
php artisan tinker
```

Then execute:

```php
Mail::raw('Test email', function ($message) {
    $message->to('test@example.com')
            ->subject('Test Email');
});
```

## Troubleshooting

### Issue: Connection Timeout

**Solution**: 
- Verify your Hostinger email account credentials
- Check if port 465 or 587 is blocked by your firewall
- Try switching between SSL (port 465) and TLS (port 587)

```env
# Try TLS instead
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

### Issue: Authentication Failed

**Solution**:
- Double-check your email and password
- Ensure the email account exists in Hostinger
- Try recreating the email account password

### Issue: Emails Going to Spam

**Solution**:
- Set up SPF records in your domain DNS
- Set up DKIM authentication
- Use a professional from address
- Avoid spam trigger words in subject/body

## Email Features Implemented

1. **Password Reset**: Users can request password reset links
2. **Email Verification**: New users receive verification emails
3. **Welcome Email**: Sent upon successful registration
4. **Rate Limiting**: Protection against email abuse

## Queue Configuration (Optional)

For better performance in production, configure email queue:

1. Update `.env`:

```env
QUEUE_CONNECTION=database
```

2. Run migrations:

```bash
php artisan queue:table
php artisan migrate
```

3. Start queue worker:

```bash
php artisan queue:work
```

## Production Checklist

- [ ] Configure real SMTP credentials
- [ ] Test all email flows (registration, password reset)
- [ ] Set up email queue for better performance
- [ ] Configure SPF and DKIM records
- [ ] Monitor email delivery rates
- [ ] Set up email logs monitoring
- [ ] Consider using a backup SMTP server

