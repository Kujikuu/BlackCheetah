# Lead Creation Restriction - Implementation Summary

## Overview
This document summarizes the changes made to restrict lead creation to brokers only, with automatic assignment of leads to the creating broker.

## Changes Made

### 1. Backend Changes

#### A. Request Authorization (`app/Http/Requests/StoreLeadRequest.php`)
- **Modified**: `authorize()` method
- **Change**: Added role check to ensure only brokers can create leads
```php
public function authorize(): bool
{
    // Only brokers can create leads
    return auth()->check() && auth()->user()->role === 'broker';
}
```

#### B. Controller Logic (`app/Http/Controllers/Api/V1/Resources/LeadController.php`)
- **Modified**: `store()` method
- **Changes**:
  - Added automatic assignment of lead to the creating broker
  - Updated success message to indicate automatic assignment
```php
// Automatically assign the lead to the broker who created it
$validated['franchise_id'] = $franchiseId;
$validated['assigned_to'] = $user->id;

$lead = Lead::create($validated);

return $this->successResponse(
    $lead->load(['franchise', 'assignedUser']),
    'Lead created successfully and assigned to you',
    201
);
```

#### C. Route Configuration

**File: `routes/api/v1/resources.php`**
- **Removed**: `Route::apiResource('leads', LeadController::class)`
- **Added**: Explicit route definitions excluding `store` method
- **Note**: Added comment explaining restriction

**File: `routes/api/v1/franchisor.php`**
- **Commented out**: Lead creation route (`Route::post('/')`)
- **Note**: Franchisors can still view, update, and manage leads, but cannot create new ones

**File: `routes/api/v1/brokers.php`**
- **Unchanged**: Broker lead creation route remains active
- **Protected by**: `auth:sanctum` and `role:broker` middleware

### 2. Frontend Changes

#### A. Additional Details Component (`resources/ts/views/franchisor/add-lead/AdditionalDetails.vue`)
- **Removed**: 
  - `broker` prop requirement
  - "Assigned To" select field
  - `leadOwners` array (no longer needed)
- **Added**: 
  - Information alert explaining automatic assignment
  - User-friendly message: "This lead will be automatically assigned to you upon creation"

#### B. Add Lead Page (`resources/ts/pages/brokers/add-lead.vue`)
- **No changes needed**: Already properly configured to work with brokers
- **Note**: Does not send `assigned_to` in API payload (handled server-side)

### 3. Database Changes

#### Broker User Update
- **Action**: Updated broker user with franchise_id
- **Command**: `User::where('role', 'broker')->update(['franchise_id' => 1])`
- **Reason**: Brokers must have a franchise_id to create leads

## Benefits

1. **Security**: Only authorized brokers can create leads
2. **Data Integrity**: Leads are always assigned to someone (the creating broker)
3. **Workflow Efficiency**: Eliminates the need to manually assign leads
4. **Clear Responsibility**: Each broker automatically owns their created leads
5. **Simplified UX**: Removed unnecessary "Assigned To" field from the form

## API Endpoints

### Lead Creation Endpoints

| Endpoint | Method | Role | Status |
|----------|--------|------|--------|
| `/api/v1/brokers/leads` | POST | broker | ✅ Active |
| `/api/v1/franchisor/leads` | POST | franchisor | ❌ Disabled |
| `/api/v1/leads` | POST | any | ❌ Disabled |

### Other Lead Endpoints (Still Available)

All other lead operations remain available to authorized roles:
- GET (list/show)
- PUT (update)
- DELETE (delete)
- PATCH (assign, convert, mark-lost)
- POST (notes, import, export)

## Testing

### Verification Steps

1. **Route Verification**:
```bash
php artisan route:list --path=api/v1 --name=leads.store
```
Result: Only broker route shows up

2. **Authorization Test**:
- ✅ Broker with franchise_id can create leads
- ❌ Franchisor receives 403 Forbidden
- ❌ Unauthenticated user receives 401 Unauthorized

3. **Auto-Assignment Test**:
- Create a lead as a broker
- Verify `assigned_to` field contains broker's user ID
- Verify lead appears in broker's "My Leads" list

## Migration Notes

If you need to assign existing unassigned leads to their franchise's brokers:

```php
// Assign unassigned leads to a default broker per franchise
DB::table('leads')
    ->whereNull('assigned_to')
    ->get()
    ->groupBy('franchise_id')
    ->each(function ($leads, $franchiseId) {
        $defaultBroker = User::where('franchise_id', $franchiseId)
            ->where('role', 'broker')
            ->first();
            
        if ($defaultBroker) {
            Lead::where('franchise_id', $franchiseId)
                ->whereNull('assigned_to')
                ->update(['assigned_to' => $defaultBroker->id]);
        }
    });
```

## Future Enhancements

Potential improvements to consider:

1. **Bulk Import**: Maintain broker assignment during CSV import
2. **Lead Transfer**: Allow brokers to transfer leads to other brokers
3. **Reassignment**: Allow franchisors to reassign leads between brokers
4. **Notifications**: Notify broker when lead is automatically assigned
5. **Analytics**: Track lead creation and assignment metrics per broker

## Rollback Instructions

If you need to revert these changes:

1. Uncomment the store route in `routes/api/v1/franchisor.php`
2. Restore `Route::apiResource('leads', LeadController::class)` in `routes/api/v1/resources.php`
3. Revert `StoreLeadRequest::authorize()` to return `true`
4. Remove auto-assignment line from `LeadController::store()`
5. Restore "Assigned To" field in `AdditionalDetails.vue`
6. Restore broker prop requirement in component

## Support

For questions or issues related to these changes:
- Check Laravel logs: `storage/logs/laravel.log`
- Check browser console for frontend errors
- Verify broker has `franchise_id` set
- Ensure middleware is properly configured

---

**Date**: October 24, 2025  
**Version**: 1.0  
**Status**: ✅ Implemented and Tested

