# Marketplace Broker Management Guide

## Overview

This guide explains the broker-controlled marketplace system. Franchisors assign brokers to their franchises, and brokers have exclusive control over marketplace listings. This ensures professional management of franchise sales opportunities through dedicated broker specialists.

## Features Implemented

### 1. **Backend API Endpoints**

#### Franchisor Endpoints
- **Get Brokers** (`GET /v1/franchisor/brokers`)
  - Retrieves all available brokers for the franchisor
  
- **Assign Broker** (`PATCH /v1/franchisor/franchises/{franchise}/assign-broker`)
  - Assigns a specific broker to manage the franchise
  - Request body: `{ "broker_id": number }`

#### Broker Endpoints
- **Get Assigned Franchises** (`GET /v1/broker/assigned-franchises`)
  - Retrieves all franchises assigned to the authenticated broker
  
- **Toggle Marketplace Listing** (`PATCH /v1/broker/franchises/{franchise}/marketplace-toggle`)
  - Toggles the franchise visibility on the marketplace (listed/unlisted)
  - Only works for franchises assigned to the broker

### 2. **Frontend Implementation**

#### For Franchisors - My Franchise Page (`/franchisor/my-franchise`)

**Broker Assignment Button** (in page header)
- "Assign Broker" or "Change Broker" button next to "Edit Franchise Details"
- Opens a dialog to select/change the assigned broker
- Shows broker details before confirming assignment

**Broker Selection Dialog**
A modal dialog allows franchisors to:
- View all available brokers
- Select a broker from a dropdown list
- See broker details (name, email) before confirming assignment
- Cancel or confirm the assignment

#### For Brokers - Assigned Franchises Page (`/brokers/assigned-franchises`)

**New dedicated page** with the following features:

1. **Stats Cards**
   - Total franchises assigned to the broker
   - Number of franchises listed on marketplace
   - Number of active franchises

2. **Franchise Cards Grid**
   - Card-based layout showing each assigned franchise
   - Franchise logo, brand name, business name
   - Industry, franchise fee, royalty percentage
   - Total and active units count
   - Franchisor contact information
   - **Marketplace toggle switch** to list/unlist franchise

3. **Empty State**
   - Friendly message when no franchises are assigned
   - Guides broker to contact administrator

### 3. **Database Schema**

The `franchises` table includes two new fields:

```sql
- broker_id (nullable foreign key to users table)
- is_marketplace_listed (boolean, default: false)
```

## How to Use

### For Franchisors

#### Assign a Broker to Your Franchise

1. Log in as a franchisor
2. Navigate to "My Franchise" page
3. Click the "Assign Broker" button in the page header (next to "Edit Franchise Details")
4. Select a broker from the dropdown list in the dialog
5. Review the broker's contact information
6. Click "Assign Broker" to confirm

**Note**: Franchisors can only assign brokers. They cannot directly list franchises on the marketplace. The assigned broker will handle marketplace listing decisions.

### For Brokers

#### View Your Assigned Franchises

1. Log in as a broker
2. Navigate to "Franchise Opportunities" page (from the side menu)
3. View all franchises assigned to you in a card-based grid

#### List a Franchise on the Marketplace

1. On the "Assigned Franchises" page, locate the franchise you want to list
2. Toggle the marketplace switch to "ON" (switches to green)
3. The franchise is now visible to potential buyers on the public marketplace
4. To remove from marketplace, toggle the switch back to "OFF"

### API Usage Examples

#### For Franchisors - Assigning a Broker

```typescript
import { franchiseApi } from '@/services/api'

const franchiseId = 1
const brokerId = 5

const response = await franchiseApi.assignBroker(franchiseId, brokerId)

if (response.success) {
  console.log('Broker assigned successfully', response.data)
}
```

#### For Franchisors - Getting Available Brokers

```typescript
import { franchiseApi } from '@/services/api'

const response = await franchiseApi.getBrokers()

if (response.success) {
  console.log('Available brokers:', response.data.data)
}
```

#### For Brokers - Getting Assigned Franchises

```typescript
import { brokerApi } from '@/services/api'

const response = await brokerApi.getAssignedFranchises()

if (response.success) {
  console.log('Assigned franchises:', response.data.data)
}
```

#### For Brokers - Toggling Marketplace Listing

```typescript
import { brokerApi } from '@/services/api'

const franchiseId = 1

const response = await brokerApi.toggleMarketplaceListing(franchiseId)

if (response.success) {
  console.log('Marketplace listing toggled', response.data)
}
```

## Technical Details

### Frontend Service Methods

**Franchisor Service** (`resources/ts/services/api/franchise.ts`):
```typescript
async assignBroker(franchiseId: number, brokerId: number): Promise<ApiResponse<any>>
async getBrokers(): Promise<ApiResponse<{ data: any[] }>>
```

**Broker Service** (`resources/ts/services/api/broker.ts`):
```typescript
async getAssignedFranchises(): Promise<ApiResponse<AssignedFranchisesResponse>>
async toggleMarketplaceListing(franchiseId: number): Promise<ApiResponse<any>>
```

### Backend Controller Methods

**FranchisorController.php**:
```php
public function assignBroker(Request $request, Franchise $franchise): JsonResponse
```

**BrokerController.php**:
```php
public function getAssignedFranchises(): JsonResponse
public function toggleMarketplaceListing(Franchise $franchise): JsonResponse
```

### Data Flow

#### Franchisor Assigns Broker
1. **Frontend Request** → `franchiseApi.assignBroker(franchiseId, brokerId)`
2. **API Call** → `PATCH /v1/franchisor/franchises/{franchise}/assign-broker`
3. **Controller** → Validates broker exists and has broker role
4. **Database** → Updates `franchises.broker_id`
5. **Response** → Returns updated franchise with broker details
6. **Frontend Update** → Reloads franchise data to show new broker

#### Broker Toggles Marketplace Listing
1. **Frontend Request** → `brokerApi.toggleMarketplaceListing(franchiseId)`
2. **API Call** → `PATCH /v1/broker/franchises/{franchise}/marketplace-toggle`
3. **Controller** → Verifies broker owns the franchise
4. **Database** → Toggles `franchises.is_marketplace_listed`
5. **Response** → Returns updated marketplace status
6. **Frontend Update** → Reloads franchises list to show new status

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

### Franchisor Components (My Franchise Page)

- `VBtn` - Assign/Change Broker button in page header
- `VDialog` - Broker selection modal
- `VSelect` - Broker dropdown list
- `VAvatar` - Broker avatar icons
- `VIcon` - Visual indicators

### Broker Components (Assigned Franchises Page)

- `VCard` - Franchise cards and stats cards
- `VSwitch` - Marketplace toggle switch
- `VAvatar` - Franchise logos and icons
- `VChip` - Status badges
- `VSnackbar` - Success/error notifications

### Styling

- Color-coded status indicators (success = green, secondary = gray, info = blue)
- Card-based grid layout (responsive: 1 col mobile, 2 cols tablet, 3 cols desktop)
- Consistent spacing and typography with the rest of the application
- Tonal variants for subtle backgrounds
- Toggle switches for intuitive on/off control

## Benefits of the Implementation

1. **Role Separation**: Clear separation of responsibilities - franchisors assign brokers, brokers control marketplace
2. **Professional Management**: Dedicated broker specialists manage marketplace listings
3. **Secure**: Proper authorization ensures brokers can only manage their assigned franchises
4. **User-Friendly**: Intuitive interfaces for both franchisors and brokers
5. **Flexible**: Easy broker assignment and marketplace control
6. **Scalable**: Brokers can manage multiple franchises from a single dashboard

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

1. **No Brokers Available** (Franchisor)
   - Ensure brokers have been created in the system
   - Check that users have the "broker" role assigned
   - Contact administrator to create broker accounts

2. **Cannot Assign Broker** (Franchisor)
   - Verify the franchise belongs to the logged-in franchisor
   - Ensure the broker ID is valid
   - Check that the migration has been run

3. **No Franchises Showing** (Broker)
   - Verify that franchises have been assigned to you
   - Contact the franchisor to request franchise assignment
   - Check with administrator for role permissions

4. **Marketplace Toggle Not Working** (Broker)
   - Ensure the franchise is assigned to you
   - Verify API endpoint is accessible
   - Check browser console for errors
   - Confirm you have broker role permissions

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

