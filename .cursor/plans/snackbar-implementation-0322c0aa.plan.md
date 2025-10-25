<!-- 0322c0aa-2b6a-4c26-8a2c-2ccca9d518c7 b615d34b-920e-4afb-b732-e20d635c213a -->
# Implement Global Snackbar System

## Overview

Create a Vuetify-based snackbar notification system that automatically displays success/error messages for API responses and can be manually triggered from any component.

## Implementation Steps

### 1. Create Snackbar Composable

**File**: `resources/ts/composables/useSnackbar.ts`

Create a global composable to manage snackbar state:

- Define snackbar state (visibility, message, color/variant)
- Export functions: `showSuccess(message)`, `showError(message)`, `showInfo(message)`, `showWarning(message)`
- Use reactive state that can be shared across components

### 2. Create Global Snackbar Component

**File**: `resources/ts/components/SnackbarNotification.vue`

Create the actual snackbar UI component:

- Use VSnackbar with v-model binding to composable state
- Support different variants (success, error, info, warning)
- Add close action button
- Auto-hide after timeout (configurable, default 4000ms)
- Use appropriate colors: success (green), error (red), info (blue), warning (orange)

### 3. Integrate with API Interceptor

**File**: `resources/ts/utils/api.ts`

Modify the existing `onResponseError` interceptor (lines 40-114):

- Replace the commented-out toast logic (lines 85-92) with `useSnackbar()`
- Add `onResponse` interceptor for successful responses:
  - Show success message for POST, PUT, PATCH, DELETE operations
  - Extract success message from `response._data.message` or use defaults
  - Skip GET requests (no notification needed for read operations)

### 4. Add Component to App Layout

**File**: `resources/ts/App.vue`

Add the SnackbarNotification component:

- Import and include `<SnackbarNotification />` at the root level
- Place it after the RouterView so it renders on top of all content

## Key Features

- Automatic error notifications for 403, 404, 500, etc.
- Automatic success notifications for create/update/delete operations
- Manual trigger capability from any component using the composable
- Skip 401 (handled by auth redirect) and 422 (field-level validation)
- Consistent styling with Vuetify theme
- Non-blocking UI with auto-dismiss

## Example Usage

```typescript
// Automatic - already works after integration
await franchiseApi.createFranchise(data) // Shows success automatically

// Manual trigger
const { showSuccess, showError } = useSnackbar()
showSuccess('Operation completed successfully!')
showError('Something went wrong')
```

### To-dos

- [x] Create useSnackbar composable with state management and trigger functions
- [x] Create SnackbarNotification component with VSnackbar and styling
- [x] Integrate useSnackbar into API error interceptor (onResponseError)
- [x] Add API success interceptor (onResponse) for POST/PUT/PATCH/DELETE
- [x] Add SnackbarNotification component to App.vue