# Shared Hosting Deployment Guide

This Laravel application is configured to work on shared hosting environments where you cannot run build commands or create symlinks.

## Key Differences from Standard Laravel Setup

### 1. **No `storage:link` Command Needed**
- **Problem**: Shared hosting doesn't allow creating symlinks
- **Solution**: We use `public/uploads` directory directly instead of `storage/app/public`
- **Configuration**: See `config/filesystems.php` - added `uploads` disk
- **Implementation**: `AccountSettingsController.php` uses the `uploads` disk

### 2. **Build Files Committed to Git**
- **Problem**: Shared hosting doesn't have Node.js to run `npm run build`
- **Solution**: Built files in `public/build/` are tracked in git
- **Important**: Run `npm run build` locally before pushing to git
- **Files**: `public/build/.gitignore` and `public/build/.gitkeep` preserve structure

### 3. **Direct File Uploads**
- **Avatar Storage**: `public/uploads/avatars/`
- **Accessible via**: `https://yourdomain.com/uploads/avatars/filename.jpg`
- **No symlink required**: Files are directly in the public directory

## Deployment Steps

### Initial Setup (One Time)

1. **Clone the repository** on your shared hosting:
   ```bash
   git clone <your-repo-url> public_html
   cd public_html
   ```

2. **Set proper permissions**:
   ```bash
   chmod -R 755 storage bootstrap/cache
   chmod -R 777 public/uploads
   ```

3. **Copy environment file**:
   ```bash
   cp .env.example .env
   ```

4. **Edit `.env` file** with your database credentials:
   ```env
   APP_URL=https://yourdomain.com
   
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run composer install** (if available):
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
   
   If composer is not available, upload the `vendor` folder from your local machine.

6. **Generate application key**:
   ```bash
   php artisan key:generate
   ```

7. **Run migrations**:
   ```bash
   php artisan migrate --force
   ```

8. **Optimize for production**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Updates (Every Deployment)

1. **Pull latest changes**:
   ```bash
   git pull origin main
   ```

2. **If there are new dependencies**:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **If there are new migrations**:
   ```bash
   php artisan migrate --force
   ```

4. **Clear and rebuild caches**:
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   php artisan cache:clear
   
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## Local Development Workflow

Before pushing to shared hosting, always do the following:

1. **Make your code changes**

2. **Test locally**:
   ```bash
   php artisan test
   ```

3. **Build frontend assets**:
   ```bash
   npm run build
   ```

4. **Commit everything including build files**:
   ```bash
   git add .
   git commit -m "Your commit message"
   git push origin main
   ```

## Directory Structure

```
public/
├── build/              # Frontend built assets (tracked in git)
│   ├── .gitignore     # Allows tracking build files
│   ├── .gitkeep       # Preserves directory
│   └── manifest.json  # Vite manifest
└── uploads/           # File uploads (no symlink needed)
    ├── .gitignore     # Ignores uploaded files
    └── avatars/       # User avatars
        └── .gitkeep   # Preserves directory
```

## Troubleshooting

### Images Not Showing
- Check permissions: `chmod -R 777 public/uploads`
- Verify `.env` APP_URL matches your domain
- Check that `public/uploads/avatars/` directory exists

### 500 Error
- Check Laravel logs: `storage/logs/laravel.log`
- Verify database credentials in `.env`
- Clear all caches: `php artisan optimize:clear`

### CSS/JS Not Loading
- Ensure `public/build/` directory has all files
- Run `npm run build` locally before pushing
- Check `public/build/manifest.json` exists
- Verify APP_URL in `.env` is correct

### Database Connection Error
- Verify database credentials in `.env`
- Some shared hosting uses `127.0.0.1` instead of `localhost`
- Check if database exists and user has permissions

## Important Notes

1. **Never run `storage:link`** - It won't work and it's not needed
2. **Always build assets locally** - `npm run build` before pushing
3. **Commit build files** - They're required for the app to work
4. **Set correct permissions** - Storage and uploads need write access
5. **Use absolute URLs** - APP_URL must match your domain exactly

## File Upload Locations

| Type | Path | URL |
|------|------|-----|
| Avatars | `public/uploads/avatars/` | `/uploads/avatars/filename.jpg` |
| Documents | `storage/app/private/` | Via Laravel route |

## Security Considerations

- `public/uploads/` is publicly accessible (suitable for avatars)
- Sensitive documents should use `storage/app/private/` and be served through Laravel routes
- Always validate file types and sizes before upload
- Current avatar size limit: 800KB
- Allowed avatar types: JPEG, PNG, GIF

## Support

If you encounter issues:
1. Check `storage/logs/laravel.log`
2. Verify all environment variables in `.env`
3. Ensure directory permissions are correct
4. Clear all caches: `php artisan optimize:clear`
