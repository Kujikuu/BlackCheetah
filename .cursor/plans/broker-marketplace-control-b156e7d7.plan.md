<!-- b156e7d7-f4b1-4385-a6ca-9a507bba7fa8 f309f161-60c2-42da-9735-5d878040d2fa -->
# Broker Marketplace Control Implementation

## Backend Changes

### 1. Create Broker Controller

**File**: `app/Http/Controllers/Api/V1/Broker/BrokerController.php`

- Create new controller extending `BaseResourceController`
- Add `getAssignedFranchises()` method - returns franchises where `broker_id = auth user id`
- Add `toggleMarketplaceListing(Franchise $franchise)` method - only if broker owns the franchise
- Include authorization checks to ensure broker is assigned to franchise

### 2. Create Broker API Routes

**File**: `routes/api/v1/brokers.php` (create if doesn't exist) or add to existing

- `GET /v1/broker/assigned-franchises` - get franchises assigned to current broker
- `PATCH /v1/broker/franchises/{franchise}/marketplace-toggle` - toggle marketplace listing

### 3. Update Franchisor Routes

**File**: `routes/api/v1/franchisor.php`

- Remove the marketplace-toggle route (lines 42-43)
- Keep the assign-broker route for franchisors

## Frontend Changes

### 4. Update My Franchise Page

**File**: `resources/ts/pages/franchisor/my-franchise.vue`

Remove:

- Marketplace tab (lines 1147-1153)
- Marketplace VWindowItem (lines 2012-2278)
- Marketplace management state variables (lines 191-192: `isTogglingMarketplace`, `isBrokerDialogVisible`)
- `toggleMarketplaceListing()` function (lines 336-361)
- `openBrokerDialog()` function (lines 364-367)

Keep:

- Broker assignment dialog (lines 2281-2358)
- `assignBroker()` function (lines 307-333)
- `loadBrokers()` function (lines 294-304)
- Broker-related state variables (lines 188-190)

Add:

- "Assign Broker" button in page header next to "Edit Franchise Details" button (around line 1022)
- Move broker dialog trigger to the new button

### 5. Create Broker Assigned Franchises Page

**File**: `resources/ts/pages/broker/assigned-franchises.vue` (new file)

- Create page with list/grid of assigned franchises
- Show franchise name, brand, status, marketplace listing status
- Add toggle switch/button for marketplace listing per franchise
- Display success/error messages via snackbar
- Include loading states

### 6. Create Broker API Service

**File**: `resources/ts/services/api/broker.ts` (new file)

- Create `BrokerApi` class
- Add `getAssignedFranchises()` method
- Add `toggleMarketplaceListing(franchiseId)` method
- Export service instance

**File**: `resources/ts/services/api/index.ts`

- Export the new broker API service

### 7. Add Broker Navigation Item

**File**: `resources/ts/navigation/vertical/brokers.ts`

- Add navigation item for "Assigned Franchises" or "Marketplace Management"
- Use icon `tabler-building-store` or `tabler-storefront`
- Route to `broker-assigned-franchises`

### 8. Create Broker Route

**File**: `resources/ts/router/routes.ts` or broker-specific route file

- Add route for `/broker/assigned-franchises` pointing to the new page component
- Set appropriate meta tags for permissions

## Testing Checklist

- Franchisor can assign brokers to franchises
- Franchisor cannot toggle marketplace listing
- Broker can view assigned franchises
- Broker can toggle marketplace listing for assigned franchises only
- Broker cannot toggle marketplace for unassigned franchises
- UI shows proper loading and error states

### To-dos

- [ ] Create BrokerController with getAssignedFranchises() and toggleMarketplaceListing() methods
- [ ] Add broker API routes for assigned franchises and marketplace toggle
- [ ] Remove marketplace-toggle route from franchisor routes
- [ ] Remove marketplace tab and add Assign Broker button to page header
- [ ] Create assigned-franchises.vue page for brokers with marketplace controls
- [ ] Create broker API service with franchise and marketplace methods
- [ ] Add navigation item for broker assigned franchises page
- [ ] Add frontend route for broker assigned franchises page