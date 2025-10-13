# Avatar Upload - Shared Hosting Solution

## Summary of Changes

This document outlines all changes made to support avatar uploads on shared hosting environments where symlinks and build commands are not available.

## Problems Solved

1. ✅ **No `storage:link` symlink needed** - Avatars stored directly in `public/uploads/`
2. ✅ **Instant preview** - Users see their image immediately after selection
3. ✅ **Visual feedback** - Loading indicators and success/error messages
4. ✅ **Build files in git** - Frontend assets committed for shared hosting deployment

## File Changes

### Backend Changes

#### 1. `config/filesystems.php`
- Added new `uploads` disk pointing to `public/uploads/`
- No symlink required - files directly accessible via web

```php
'uploads' => [
    'driver' => 'local',
    'root' => public_path('uploads'),
    'url' => env('APP_URL').'/uploads',
    'visibility' => 'public',
],
```

#### 2. `app/Http/Controllers/Api/AccountSettingsController.php`
- Changed from `storage/app/public` to `public/uploads` (uploads disk)
- Updated avatar URLs from `asset('storage/...')` to `asset('uploads/...')`
- Added backward compatibility to delete old avatars from both locations
- Enhanced error handling and validation

**Key changes:**
- `Storage::disk('uploads')` instead of `Storage::disk('public')`
- `asset('uploads/'.$path)` instead of `asset('storage/'.$path)`
- Checks both `uploads` and `public` disks when deleting old avatars

### Frontend Changes

#### 3. `resources/ts/views/account-settings/AccountSettingsAccount.vue`

**Added Features:**
- **Instant Preview**: FileReader shows image immediately after selection
- **Loading Indicator**: Spinner overlay on avatar during upload
- **Status Messages**: Success/error alerts with auto-dismiss
- **Client-side Validation**: File size (800KB) and type (JPG/PNG/GIF) checked before upload
- **Better UX**: Reset button disabled when no avatar exists

**New State Variables:**
```typescript
isUploadingAvatar: ref(false)        // Separate loading state for avatar
uploadStatus: ref({ type, message }) // Success/error/info messages
```

**UX Flow:**
1. User selects image → Instant preview via FileReader
2. Show "Uploading..." message
3. Upload to server in background
4. On success: Update with server URL + show success message
5. On error: Revert to original avatar + show error message
6. Auto-dismiss messages after 3-5 seconds

### Directory Structure Changes

#### 4. Created `public/uploads/` structure
```
public/uploads/
├── .gitignore          # Ignores uploaded files (except .gitkeep)
└── avatars/
    └── .gitkeep        # Preserves directory in git
```

#### 5. Updated `public/build/` for shared hosting
```
public/build/
├── .gitignore          # Allows tracking build files (!*)
├── .gitkeep            # Preserves directory
├── manifest.json       # Vite manifest (tracked in git)
└── build/              # Built assets (tracked in git)
```

#### 6. Updated root `.gitignore`
- Commented out `/public/build` - Now tracked in git for shared hosting
- Commented out `/public/storage` - Now using `/public/uploads` instead
- Added explanation comments

### Documentation

#### 7. `SHARED_HOSTING_GUIDE.md`
Comprehensive guide covering:
- Why symlinks don't work on shared hosting
- How the uploads disk works
- Deployment steps (initial setup + updates)
- Local development workflow
- Directory structure
- Troubleshooting common issues
- Security considerations

## How It Works

### Avatar Upload Flow

```
┌─────────────────────────────────────────────────────────────┐
│ 1. User selects image file                                   │
└────────────────┬────────────────────────────────────────────┘
                 ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. Frontend validates (size, type)                           │
└────────────────┬────────────────────────────────────────────┘
                 ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. FileReader creates instant preview (base64)               │
│    - User sees image immediately                             │
└────────────────┬────────────────────────────────────────────┘
                 ▼
┌─────────────────────────────────────────────────────────────┐
│ 4. Upload to server (background)                             │
│    - Show loading indicator                                  │
│    - Show "Uploading..." message                             │
└────────────────┬────────────────────────────────────────────┘
                 ▼
┌─────────────────────────────────────────────────────────────┐
│ 5. Backend stores in public/uploads/avatars/                 │
│    - Filename: avatar_{timestamp}_{userId}.{ext}             │
│    - Deletes old avatar if exists                            │
│    - Updates user.avatar in database                         │
└────────────────┬────────────────────────────────────────────┘
                 ▼
┌─────────────────────────────────────────────────────────────┐
│ 6. Frontend receives response                                │
│    - Updates preview with server URL                         │
│    - Updates cookie (triggers menu update)                   │
│    - Shows success message                                   │
└─────────────────────────────────────────────────────────────┘
```

### File Access

**Old Way (Doesn't work on shared hosting):**
```
storage/app/public/avatars/avatar.jpg
         ↓ (symlink required)
public/storage/avatars/avatar.jpg
         ↓
https://domain.com/storage/avatars/avatar.jpg
```

**New Way (Works on shared hosting):**
```
public/uploads/avatars/avatar.jpg
         ↓ (direct access, no symlink)
https://domain.com/uploads/avatars/avatar.jpg
```

## Testing Checklist

### Avatar Upload
- [ ] Select an image - Should see instant preview
- [ ] Upload completes - Should see success message
- [ ] Avatar updates in menu immediately
- [ ] Try file > 800KB - Should see error message
- [ ] Try non-image file - Should see error message
- [ ] Upload another image - Old one should be deleted
- [ ] Click Reset - Avatar should be removed with confirmation

### Shared Hosting Compatibility
- [ ] No `php artisan storage:link` needed
- [ ] Avatars accessible at `/uploads/avatars/filename.jpg`
- [ ] Built assets in git (`public/build/`)
- [ ] App works without running `npm run build` on server

### User Experience
- [ ] Loading spinner shows during upload
- [ ] Status messages appear and auto-dismiss
- [ ] Reset button disabled when no avatar
- [ ] Upload button disabled during upload
- [ ] Error messages are clear and helpful

## Deployment to Shared Hosting

### First Time Setup

1. **Upload files** (via FTP/git)
2. **Set permissions**:
   ```bash
   chmod -R 777 public/uploads
   chmod -R 755 storage bootstrap/cache
   ```
3. **Configure `.env`** with your database
4. **Run migrations**: `php artisan migrate --force`

### Every Update

1. **Pull/upload latest code**
2. **Before pushing from local**:
   ```bash
   npm run build    # Build frontend assets
   git add .        # Include build files
   git commit       # Commit everything
   git push         # Deploy
   ```

## Security Notes

- `public/uploads/` is publicly accessible (suitable for avatars)
- Max file size: 800KB
- Allowed types: JPEG, PNG, GIF
- Unique filenames prevent overwriting
- Old avatars automatically deleted

## Benefits

1. **No Server Dependencies**: Works without Node.js, npm, or symlink permissions
2. **Better UX**: Instant preview and clear feedback
3. **Backward Compatible**: Checks both old and new storage locations
4. **Production Ready**: Built assets committed to git
5. **Easy Deployment**: Just pull code, no build steps needed on server

## Migration Notes

If you have existing avatars in `storage/app/public/avatars/`:
1. The code will still find and delete them when users upload new avatars
2. Consider moving them to `public/uploads/avatars/` manually
3. Update database URLs from `storage/` to `uploads/`

## Related Files

- `config/filesystems.php` - Disk configuration
- `app/Http/Controllers/Api/AccountSettingsController.php` - Upload logic
- `resources/ts/views/account-settings/AccountSettingsAccount.vue` - Frontend UI
- `SHARED_HOSTING_GUIDE.md` - Deployment instructions
- `.gitignore` - Updated to track build files
