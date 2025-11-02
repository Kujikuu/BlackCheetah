<!-- 5e0bf5c9-f3e3-4a29-9b1a-d7d6abe78902 07c4aafa-4ae4-43ea-817a-77d0e3ad8699 -->
# Fix Franchise Logo Upload and Display

## Issues Identified

1. **Logo not persisting after update**: When `updateFranchiseData()` is called, the logo URL may not be included in the payload if it's already set but no new file is selected
2. **Logo disappears after refresh**: The update payload structure might be overwriting the logo with undefined/null
3. **VImg display issue**: VImg nested inside VAvatar has `display: none` - likely a component compatibility issue

## Implementation Plan

### 1. Fix Logo Persistence in Update Payload

- **File**: `resources/ts/pages/franchisor/my-franchise.vue`
- **Issue**: Logo URL needs to be explicitly included in update payload even when no new file is uploaded
- **Fix**: Ensure `franchiseDetails.franchiseDetails.logo` is always included if it exists, not just when `logoFile.value` is set

### 2. Fix VImg Display Issue

- **File**: `resources/ts/pages/franchisor/my-franchise.vue` (lines 1210-1218)
- **Issue**: VImg nested in VAvatar causes display issues
- **Fix**: Replace VAvatar+VImg with VAvatar using `:image` prop or use VImg directly with proper styling

### 3. Ensure Logo URL is Always Sent in Update

- **File**: `resources/ts/pages/franchisor/my-franchise.vue` (lines 389-426)
- **Fix**: Always include the current logo URL in the update payload if it exists, regardless of whether a new file is being uploaded

### 4. Add Error Handling for Image Load Failures

- **File**: `resources/ts/pages/franchisor/my-franchise.vue`
- **Fix**: Add error handling and fallback display for logo images that fail to load

## Files to Modify

1. `resources/ts/pages/franchisor/my-franchise.vue`

- Fix `updateFranchiseData()` to always include logo URL
- Fix VImg/VAvatar display component
- Add proper error handling

### To-dos

- [ ] Fix updateFranchiseData() to always include logo URL in payload, even when no new file is uploaded
- [ ] Replace VAvatar+VImg nested structure with proper VAvatar :image prop or standalone VImg component
- [ ] Add error handling and fallback for logo image load failures