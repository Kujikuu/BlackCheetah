# Fixing 413 Content Too Large Error for Image Uploads

## Problem
When uploading property images, you're getting a **413 "Content Too Large"** error. This happens because the server has limits on:
1. PHP upload size
2. Web server (nginx/Apache) request body size
3. PHP post max size

## Solutions

### For Local Development (Laravel Herd/Valet)

#### 1. Check Current PHP Settings

```bash
php -i | grep upload_max_filesize
php -i | grep post_max_size
php -i | grep max_file_uploads
```

#### 2. Update PHP Configuration

**Find your php.ini file:**
```bash
php --ini
```

**Edit the php.ini file** and update these values:
```ini
upload_max_filesize = 50M
post_max_size = 50M
max_file_uploads = 20
memory_limit = 256M
max_execution_time = 300
```

**For Laravel Herd:**
- PHP config is usually at: `~/Library/Application Support/Herd/config/php/php.ini`
- Or create/edit: `~/.config/herd/php.ini`

**After changes, restart PHP:**
```bash
# For Herd
herd restart

# For Valet
valet restart
```

#### 3. Update Nginx Configuration (if using Herd/Valet)

**For Laravel Herd:**
Edit nginx config (usually auto-managed by Herd)

**For Valet:**
```bash
# Edit nginx config
code ~/.config/valet/nginx/yoursite.test

# Add this inside the server block:
client_max_body_size 50M;

# Then restart
valet restart
```

**Or update global nginx config:**
```bash
# Find nginx config
which nginx
# Usually: /opt/homebrew/etc/nginx/nginx.conf

# Edit and add in http block:
http {
    client_max_body_size 50M;
    ...
}

# Restart nginx
brew services restart nginx
```

### For Production/Shared Hosting

#### 1. Create/Update `.htaccess` (Apache)

Add to your `public/.htaccess`:

```apache
# Increase PHP limits
php_value upload_max_filesize 50M
php_value post_max_size 50M
php_value max_execution_time 300
php_value max_input_time 300
php_value memory_limit 256M
```

**Note:** Some shared hosts don't allow php_value in .htaccess. If this causes a 500 error, remove these lines.

#### 2. Create `.user.ini` (Alternative for Apache/FastCGI)

Create `public/.user.ini`:

```ini
upload_max_filesize = 50M
post_max_size = 50M
max_file_uploads = 20
memory_limit = 256M
max_execution_time = 300
```

#### 3. Nginx Configuration

Edit your nginx site config (usually in `/etc/nginx/sites-available/`):

```nginx
server {
    # ... other config ...
    
    client_max_body_size 50M;
    client_body_timeout 300s;
    
    location ~ \.php$ {
        # ... php-fpm config ...
        
        fastcgi_param  PHP_VALUE "upload_max_filesize = 50M \n post_max_size=50M \n max_execution_time=300 \n memory_limit=256M";
    }
}
```

Then restart nginx:
```bash
sudo systemctl restart nginx
# or
sudo service nginx restart
```

### Quick Fix for Development

#### Option 1: Use .htaccess (if on Apache)

Create/update `public/.htaccess`:

```apache
<IfModule mod_php.c>
    php_value upload_max_filesize 50M
    php_value post_max_size 50M
    php_value max_execution_time 300
    php_value memory_limit 256M
</IfModule>
```

#### Option 2: Use .user.ini

Create `public/.user.ini`:

```ini
upload_max_filesize = 50M
post_max_size = 50M
max_file_uploads = 20
memory_limit = 256M
max_execution_time = 300
```

### Verification Steps

After making changes, verify the configuration:

#### 1. Create a PHP info page

Create `public/phpinfo.php`:

```php
<?php
phpinfo();
```

Visit `https://blackcheetah.test/phpinfo.php` and check:
- `upload_max_filesize`
- `post_max_size`
- `max_file_uploads`
- `memory_limit`

**⚠️ Delete this file after checking!**

#### 2. Test from command line

```bash
php -r "echo ini_get('upload_max_filesize');"
php -r "echo ini_get('post_max_size');"
```

### Recommended Settings

For your use case (up to 10 images, 5MB each):

```ini
upload_max_filesize = 50M    # Individual file size
post_max_size = 60M          # Total request size (should be larger than upload_max_filesize)
max_file_uploads = 20        # Number of files
memory_limit = 256M          # PHP memory
max_execution_time = 300     # 5 minutes
```

**Why these values?**
- 10 images × 5MB each = 50MB
- post_max_size should be slightly larger to account for form data
- Extra buffer for safety

### For Laravel Herd Specifically

**Method 1: Using Herd UI**
1. Open Laravel Herd
2. Go to Settings
3. Look for PHP settings
4. Update the limits
5. Restart Herd

**Method 2: Edit php.ini directly**

```bash
# Find Herd's PHP version
php -v

# Edit php.ini (replace 8.3 with your version)
code ~/Library/Application\ Support/Herd/config/php/83/php.ini

# Add/update these lines:
upload_max_filesize = 50M
post_max_size = 60M
max_file_uploads = 20
memory_limit = 256M
max_execution_time = 300

# Save and restart Herd
herd restart
```

**Method 3: Create custom nginx config**

Herd usually handles this automatically, but if needed:

```bash
# Check if custom nginx config exists
ls -la ~/.config/herd/Nginx/

# If you need to add custom config, Herd will guide you
```

### Client-Side Improvements

While fixing the server, you can also optimize the client-side:

#### Add Image Compression Before Upload

Update the image upload handlers to compress images:

```typescript
// Helper function to compress image
const compressImage = async (file: File, maxSizeMB = 2): Promise<File> => {
  return new Promise((resolve) => {
    const reader = new FileReader()
    
    reader.onload = (e) => {
      const img = new Image()
      img.onload = () => {
        const canvas = document.createElement('canvas')
        const ctx = canvas.getContext('2d')!
        
        // Calculate new dimensions (max 1920px width)
        let width = img.width
        let height = img.height
        const maxWidth = 1920
        
        if (width > maxWidth) {
          height = (height * maxWidth) / width
          width = maxWidth
        }
        
        canvas.width = width
        canvas.height = height
        ctx.drawImage(img, 0, 0, width, height)
        
        // Compress to JPEG with quality 0.8
        canvas.toBlob(
          (blob) => {
            if (blob) {
              const compressedFile = new File([blob], file.name, {
                type: 'image/jpeg',
                lastModified: Date.now(),
              })
              resolve(compressedFile)
            } else {
              resolve(file)
            }
          },
          'image/jpeg',
          0.8
        )
      }
      img.src = e.target?.result as string
    }
    
    reader.readAsDataURL(file)
  })
}
```

### Troubleshooting

#### Still getting 413 error?

1. **Check ALL PHP limits:**
   ```bash
   php -i | grep -E "(upload_max_filesize|post_max_size|memory_limit|max_file_uploads)"
   ```

2. **Check nginx limits:**
   ```bash
   # For Herd/Valet
   cat ~/.config/valet/nginx/*.conf | grep client_max_body_size
   
   # For system nginx
   cat /etc/nginx/nginx.conf | grep client_max_body_size
   ```

3. **Check if changes took effect:**
   - Clear browser cache
   - Restart PHP-FPM/nginx
   - Try in incognito mode

4. **Check Laravel logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

5. **Check web server error logs:**
   ```bash
   # Herd
   herd log
   
   # Valet
   tail -f ~/.config/valet/Log/nginx-error.log
   
   # System nginx
   tail -f /var/log/nginx/error.log
   ```

### Common Issues

**Issue:** Changes to php.ini not taking effect
**Solution:** Make sure you're editing the correct php.ini (CLI vs FPM might be different)

```bash
# Check which php.ini is loaded
php --ini

# For FPM, check:
php-fpm -i | grep "Configuration File"
```

**Issue:** .htaccess changes causing 500 error
**Solution:** Remove php_value directives, use .user.ini instead

**Issue:** Still 413 after all changes
**Solution:** There might be a load balancer or proxy in front. Check with hosting provider.

### Quick Test

After making changes, test with this curl command:

```bash
# Create a test file (10MB)
dd if=/dev/zero of=test.jpg bs=1M count=10

# Test upload
curl -X POST \
  -F "title=Test Property" \
  -F "description=Test" \
  -F "property_type=retail" \
  -F "size_sqm=100" \
  -F "monthly_rent=1000" \
  -F "state_province=Riyadh" \
  -F "city=Riyadh" \
  -F "address=Test Address" \
  -F "property_images[]=@test.jpg" \
  https://blackcheetah.test/api/v1/broker/properties \
  -H "Authorization: Bearer YOUR_TOKEN"

# Clean up
rm test.jpg
```

### For Your Specific Setup (blackcheetah.test)

Since you're using `blackcheetah.test`, you're likely using Laravel Herd or Valet:

```bash
# Option 1: Quick fix with .user.ini
echo "upload_max_filesize = 50M
post_max_size = 60M
max_file_uploads = 20
memory_limit = 256M
max_execution_time = 300" > public/.user.ini

# Option 2: Edit PHP ini for Herd (replace 8.3 with your PHP version)
php_version=$(php -r "echo PHP_MAJOR_VERSION . PHP_MINOR_VERSION;")
ini_path="$HOME/Library/Application Support/Herd/config/php/$php_version/php.ini"

# Check if file exists
if [ -f "$ini_path" ]; then
    # Backup first
    cp "$ini_path" "$ini_path.backup"
    
    # Update values
    sed -i '' 's/upload_max_filesize = .*/upload_max_filesize = 50M/' "$ini_path"
    sed -i '' 's/post_max_size = .*/post_max_size = 60M/' "$ini_path"
    sed -i '' 's/memory_limit = .*/memory_limit = 256M/' "$ini_path"
    
    # Restart Herd
    herd restart
fi
```

## Summary

**Immediate fix for development:**
1. Create `public/.user.ini` with the settings above
2. Restart Herd/Valet
3. Test the upload again

**Permanent fix:**
1. Update PHP configuration (php.ini)
2. Update web server configuration (nginx/Apache)
3. Restart services
4. Verify with phpinfo()

The error should be resolved once the upload and post size limits are increased to accommodate your 10 × 5MB images (50MB total).

