# Marketplace Broker Assignment Guide

## Overview

This guide explains how franchisors can assign brokers to manage their franchise listings on the marketplace. The marketplace feature allows franchisors to list their franchises for sale and have dedicated brokers manage inquiries from potential buyers.

## Features Implemented

### 1. **Backend API Endpoints**

Three main API endpoints have been implemented:

- **Get Brokers** (`GET /v1/franchisor/brokers`)
  - Retrieves all available brokers for the franchisor
  
- **Assign Broker** (`PATCH /v1/franchisor/franchises/{franchise}/assign-broker`)
  - Assigns a specific broker to manage the franchise listing
  - Request body: `{ "broker_id": number }`
  
- **Toggle Marketplace Listing** (`PATCH /v1/franchisor/franchises/{franchise}/marketplace-toggle`)
  - Toggles the franchise visibility on the marketplace (listed/unlisted)

### 2. **Frontend Implementation**

#### New Marketplace Tab

A new "Marketplace" tab has been added to the "My Franchise" page (`/franchisor/my-franchise`) with the following sections:

1. **Marketplace Status Card**
   - Shows if the franchise is currently listed on the marketplace
   - Provides a toggle button to list/unlist the franchise
   - Visual indicator with color-coded status (green for listed, gray for unlisted)

2. **Broker Assignment Section**
   - Shows the currently assigned broker (if any)
   - Displays broker contact information (name, email, phone)
   - Provides buttons to assign or change the broker

3. **Marketplace Benefits Section**
   - Informative cards highlighting the benefits of listing on the marketplace
   - Includes: Reach More Buyers, Increase Visibility, Professional Support

#### Broker Selection Dialog

A modal dialog allows franchisors to:
- View all available brokers
- Select a broker from a dropdown list
- See broker details before confirming assignment
- Cancel or confirm the assignment

### 3. **Database Schema**

The `franchises` table includes two new fields:

```sql
- broker_id (nullable foreign key to users table)
- is_marketplace_listed (boolean, default: false)
```

## How to Use

### For Franchisors

#### Step 1: Access the Marketplace Tab

1. Log in as a franchisor
2. Navigate to "My Franchise" page
3. Click on the "Marketplace" tab

#### Step 2: Assign a Broker

1. In the "Assigned Broker" section, click "Assign Broker" (or "Change Broker" if one is already assigned)
2. Select a broker from the dropdown list in the dialog
3. Review the broker's contact information
4. Click "Assign Broker" to confirm

#### Step 3: List on Marketplace

1. Once a broker is assigned (recommended but not required), you can list your franchise
2. Click the "List on Marketplace" button in the Marketplace Status section
3. Your franchise will now be visible to potential buyers on the marketplace
4. To remove the listing, click "Remove from Marketplace"

### API Usage Example

#### Assigning a Broker

```typescript
import { franchiseApi } from '@/services/api'

const franchiseId = 1
const brokerId = 5

const response = await franchiseApi.assignBroker(franchiseId, brokerId)

if (response.success) {
  console.log('Broker assigned successfully', response.data)
}
```

#### Toggling Marketplace Listing

```typescript
import { franchiseApi } from '@/services/api'

const franchiseId = 1

const response = await franchiseApi.toggleMarketplaceListing(franchiseId)

if (response.success) {
  console.log('Marketplace listing toggled', response.data)
}
```

#### Getting Available Brokers

```typescript
import { franchiseApi } from '@/services/api'

const response = await franchiseApi.getBrokers()

if (response.success) {
  console.log('Available brokers:', response.data.data)
}
```

## Technical Details

### Frontend Service Methods

Three new methods were added to `FranchiseApi` class in `resources/ts/services/api/franchise.ts`:

```typescript
async assignBroker(franchiseId: number, brokerId: number): Promise<ApiResponse<any>>
async toggleMarketplaceListing(franchiseId: number): Promise<ApiResponse<any>>
async getBrokers(): Promise<ApiResponse<{ data: any[] }>>
```

### Backend Controller Methods

Two controller methods in `FranchisorController.php`:

```php
public function assignBroker(Request $request, Franchise $franchise): JsonResponse
public function toggleMarketplaceListing(Franchise $franchise): JsonResponse
```

### Data Flow

1. **Frontend Request** → `franchiseApi.assignBroker(franchiseId, brokerId)`
2. **API Call** → `PATCH /v1/franchisor/franchises/{franchise}/assign-broker`
3. **Controller** → Validates broker exists and has broker role
4. **Database** → Updates `franchises.broker_id`
5. **Response** → Returns updated franchise with broker details
6. **Frontend Update** → Reloads franchise data to show new broker

## Validation & Security

### Backend Validation

- Ensures the franchise belongs to the authenticated franchisor
- Verifies the selected user has the "broker" role
- Validates that the broker exists in the database
- Uses Laravel's Form Request validation

### Authorization

- All endpoints require authentication via Sanctum
- Role middleware ensures only franchisors can access these endpoints
- Ownership verification ensures franchisors can only modify their own franchises

## Error Handling

The implementation includes comprehensive error handling:

- **404 Errors**: When franchise or broker is not found
- **403 Errors**: When franchisor doesn't own the franchise
- **422 Errors**: When validation fails (e.g., invalid broker ID)
- **500 Errors**: For unexpected server errors

All errors are displayed to the user via toast notifications (snackbar).

## UI Components

### Marketplace Tab Components

- `VCard` - Main container for marketplace settings
- `VAlert` - Informational messages
- `VBtn` - Action buttons (assign broker, toggle listing)
- `VDialog` - Broker selection modal
- `VSelect` - Broker dropdown list
- `VAvatar` - User/status icons
- `VIcon` - Tabler icons for visual indicators

### Styling

- Color-coded status indicators (success = green, secondary = gray)
- Responsive layout (works on mobile, tablet, desktop)
- Consistent spacing and typography with the rest of the application
- Tonal variants for subtle backgrounds

## Benefits of the Implementation

1. **User-Friendly Interface**: Intuitive UI with clear visual indicators
2. **Secure**: Proper authorization and validation at every level
3. **Flexible**: Easy to assign, change, or remove brokers
4. **Informative**: Shows broker contact information and marketplace status
5. **Professional**: Clean, modern design consistent with the application theme

## Future Enhancements

Potential improvements for future versions:

1. **Broker Performance Metrics**: Show statistics for each broker (leads converted, response time, etc.)
2. **Multiple Brokers**: Allow assignment of multiple brokers per franchise
3. **Broker Availability**: Show broker availability status and workload
4. **Automated Assignment**: AI-powered broker assignment based on performance and availability
5. **Communication Hub**: Direct messaging between franchisor and broker
6. **Marketplace Analytics**: Dashboard showing marketplace listing performance

## Troubleshooting

### Common Issues

1. **No Brokers Available**
   - Ensure brokers have been created in the system
   - Check that users have the "broker" role assigned

2. **Cannot Assign Broker**
   - Verify the franchise belongs to the logged-in franchisor
   - Ensure the broker ID is valid
   - Check that the migration has been run

3. **Marketplace Toggle Not Working**
   - Verify API endpoint is accessible
   - Check network tab for API errors
   - Ensure franchise ID is valid

### Debug Commands

```bash
# Check migration status
php artisan migrate:status

# Run migrations if needed
php artisan migrate

# Check for broker users
php artisan tinker
>>> User::where('role', 'broker')->count()
```

## Conclusion

The marketplace broker assignment feature provides franchisors with a powerful tool to manage their franchise listings and work with dedicated brokers to find potential buyers. The implementation is secure, user-friendly, and follows Laravel and Vue.js best practices.

For any questions or issues, please contact the development team.

