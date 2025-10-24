<!-- ab916bee-b10f-489e-b368-0d7bc6be9f59 e0f7d22b-1786-4042-b194-62509cad784d -->
# Marketplace Page Implementation

## Overview

Create a comprehensive marketplace system with:

1. **Property Model** - Real estate listings managed by brokers
2. **Broker Property CRUD** - Brokers manage real estate properties
3. **Franchisor Assignment** - Franchisors assign franchises to brokers and toggle marketplace visibility
4. **Public Marketplace** - Display franchise opportunities and available properties

## Backend Implementation

### 1. Create Property Model and Migration

**Migration**: `database/migrations/xxxx_create_properties_table.php`

```php
Schema::create('properties', function (Blueprint $table) {
    $table->id();
    $table->foreignId('broker_id')->constrained('users')->onDelete('cascade');
    $table->string('title');
    $table->text('description');
    $table->string('property_type'); // retail, office, kiosk, food_court, standalone
    $table->decimal('size_sqft', 10, 2);
    $table->string('country');
    $table->string('state_province');
    $table->string('city');
    $table->string('address');
    $table->string('postal_code')->nullable();
    $table->decimal('latitude', 10, 8)->nullable();
    $table->decimal('longitude', 11, 8)->nullable();
    $table->decimal('monthly_rent', 12, 2);
    $table->decimal('deposit_amount', 12, 2)->nullable();
    $table->integer('lease_term_months')->nullable();
    $table->date('available_from')->nullable();
    $table->json('amenities')->nullable();
    $table->json('images')->nullable();
    $table->enum('status', ['available', 'under_negotiation', 'leased', 'unavailable'])->default('available');
    $table->text('contact_info')->nullable();
    $table->timestamps();
});
```

**Model**: `app/Models/Property.php`

- Fillable, casts, HasFactory
- `broker()` relationship to User
- Scopes: `scopeAvailable()`, `scopeByType()`, `scopeByLocation()`

### 2. Update Franchise Model

**Migration**: `database/migrations/xxxx_add_broker_and_marketplace_to_franchises_table.php`

```php
Schema::table('franchises', function (Blueprint $table) {
    $table->foreignId('broker_id')->nullable()->after('franchisor_id')
          ->constrained('users')->onDelete('set null');
    $table->boolean('is_marketplace_listed')->default(false)->after('status');
});
```

Update `app/Models/Franchise.php`:

- Add `broker_id`, `is_marketplace_listed` to fillable
- Add `broker()` relationship
- Add `scopeMarketplaceListed()` scope

### 3. Create Property Controller

**File**: `app/Http/Controllers/Api/V1/Broker/PropertyController.php`

Extend `BaseResourceController`, implement:

- `index(Request)` - List broker's properties with filters, pagination
- `store(StorePropertyRequest)` - Create property (auto-assign broker_id from auth user)
- `show(Property)` - Get single property
- `update(UpdatePropertyRequest, Property)` - Update property
- `destroy(Property)` - Delete property
- `bulkDelete(Request)` - Bulk delete properties
- `markLeased(Property)` - Mark property as leased

**Form Requests**:

- `app/Http/Requests/StorePropertyRequest.php`
- `app/Http/Requests/UpdatePropertyRequest.php`

### 4. Update Franchisor Controller

**File**: `app/Http/Controllers/Api/V1/Franchisor/FranchisorController.php`

Add methods:

- `assignBroker(Request, Franchise)` - Assign broker to franchise
- `toggleMarketplaceListing(Franchise)` - Toggle `is_marketplace_listed` flag

### 5. Create Marketplace Controller

**File**: `app/Http/Controllers/Api/V1/Public/MarketplaceController.php`

Extend base Controller, implement:

- `getFranchises(Request)` - Active + marketplace-listed franchises with filters, pagination
- `getFranchiseDetails($id)` - Single franchise with eager loading (franchisor, broker)
- `getProperties(Request)` - Available properties with filters, pagination
- `getPropertyDetails($id)` - Single property with eager loading (broker)
- `submitInquiry(StoreMarketplaceInquiryRequest)` - Create inquiry

### 6. Create Marketplace Inquiry Model

**Migration**: `database/migrations/xxxx_create_marketplace_inquiries_table.php`

```php
Schema::create('marketplace_inquiries', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->string('phone');
    $table->enum('inquiry_type', ['franchise', 'property']);
    $table->foreignId('franchise_id')->nullable()->constrained()->onDelete('set null');
    $table->foreignId('property_id')->nullable()->constrained()->onDelete('set null');
    $table->text('message');
    $table->string('investment_budget')->nullable();
    $table->string('preferred_location')->nullable();
    $table->string('timeline')->nullable();
    $table->enum('status', ['new', 'contacted', 'closed'])->default('new');
    $table->timestamps();
});
```

**Model**: `app/Models/MarketplaceInquiry.php` with relationships, scopes

**Form Request**: `app/Http/Requests/StoreMarketplaceInquiryRequest.php`

### 7. Create API Routes

**File**: `routes/api/v1/broker.php` (new or update)

```php
Route::middleware(['auth:sanctum'])->prefix('broker')->group(function () {
    Route::apiResource('properties', PropertyController::class);
    Route::post('properties/bulk-delete', [PropertyController::class, 'bulkDelete']);
    Route::patch('properties/{property}/mark-leased', [PropertyController::class, 'markLeased']);
});
```

**File**: `routes/api/v1/franchisor.php` (update)

```php
// Add these routes
Route::patch('franchises/{franchise}/assign-broker', [FranchisorController::class, 'assignBroker']);
Route::patch('franchises/{franchise}/marketplace-toggle', [FranchisorController::class, 'toggleMarketplaceListing']);
```

**File**: `routes/api/v1/marketplace.php` (new)

```php
Route::prefix('marketplace')->group(function () {
    Route::get('franchises', [MarketplaceController::class, 'getFranchises']);
    Route::get('franchises/{id}', [MarketplaceController::class, 'getFranchiseDetails']);
    Route::get('properties', [MarketplaceController::class, 'getProperties']);
    Route::get('properties/{id}', [MarketplaceController::class, 'getPropertyDetails']);
    Route::post('inquiries', [MarketplaceController::class, 'submitInquiry']);
});
```

Register in `routes/api.php`:

```php
require __DIR__ . '/api/v1/broker.php';
require __DIR__ . '/api/v1/marketplace.php';
```

## Frontend Implementation

### 8. Create Property API Service

**File**: `resources/ts/services/api/property.ts`

Following project pattern:

```typescript
export interface Property {
  id: number
  broker_id: number
  title: string
  description: string
  property_type: string
  size_sqft: number
  country: string
  state_province: string
  city: string
  address: string
  monthly_rent: number
  status: string
  images?: string[]
  broker?: User
  // ... other fields
}

export class PropertyApi {
  private getBaseUrl(): string {
    return '/api/v1/broker/properties'
  }

  async getProperties(filters?: PropertyFilters): Promise<ApiResponse<PaginatedResponse<Property>>>
  async getProperty(id: number): Promise<ApiResponse<Property>>
  async createProperty(payload: CreatePropertyPayload): Promise<ApiResponse<Property>>
  async updateProperty(id: number, payload: UpdatePropertyPayload): Promise<ApiResponse<Property>>
  async deleteProperty(id: number): Promise<ApiResponse<void>>
  async bulkDelete(ids: number[]): Promise<ApiResponse<void>>
  async markLeased(id: number): Promise<ApiResponse<Property>>
}

export const propertyApi = new PropertyApi()
```

### 9. Create Marketplace API Service

**File**: `resources/ts/services/api/marketplace.ts`

```typescript
export class MarketplaceApi {
  private getBaseUrl(): string {
    return '/api/v1/marketplace'
  }

  async getFranchises(filters?: MarketplaceFilters): Promise<ApiResponse<PaginatedResponse<Franchise>>>
  async getFranchiseDetails(id: number): Promise<ApiResponse<Franchise>>
  async getProperties(filters?: MarketplaceFilters): Promise<ApiResponse<PaginatedResponse<Property>>>
  async getPropertyDetails(id: number): Promise<ApiResponse<Property>>
  async submitInquiry(payload: InquiryPayload): Promise<ApiResponse<MarketplaceInquiry>>
}

export const marketplaceApi = new MarketplaceApi()
```

### 10. Create Broker Property Management Page

**File**: `resources/ts/pages/broker/properties.vue`

Full CRUD page following project pattern:

- Data table with filters
- Create/Edit/Delete dialogs
- Bulk actions
- Status management

### 11. Create Marketplace Page

**File**: `resources/ts/pages/front-pages/marketplace.vue`

Main page with VTabs:

- Tab 1: Franchise Opportunities
- Tab 2: Available Properties
- Shared filter sidebar

### 12. Create Marketplace View Components

**Files** in `resources/ts/views/front-pages/marketplace/`:

- `franchise-opportunities.vue` - Grid of franchise cards
- `franchise-card.vue` - Individual franchise opportunity card
- `available-properties.vue` - Grid of property cards
- `property-card.vue` - Individual property listing card
- `marketplace-filters.vue` - Filter sidebar (location, type, price range)
- `inquiry-dialog.vue` - Contact/inquiry form modal
- `franchise-details-dialog.vue` - Full franchise details modal
- `property-details-dialog.vue` - Full property details modal

### 13. Add Routes

**File**: `resources/ts/router/routes.ts`

```typescript
// Broker routes
{
  path: '/broker/properties',
  name: 'broker-properties',
  component: () => import('@/pages/broker/properties.vue'),
  meta: { action: 'manage', subject: 'BrokerProperties' }
},

// Public marketplace
{
  path: '/marketplace',
  name: 'marketplace',
  component: () => import('@/pages/front-pages/marketplace.vue'),
  meta: { layout: 'blank', public: true }
}
```

### 14. Update Navigation

**File**: `resources/ts/views/front-pages/front-page-navbar.vue`

- Add "Marketplace" link

**Broker Navigation**: Add "Properties" menu item

## Key Features

### Filtering Capabilities

- **Franchises**: Industry, franchise fee range, location, total units
- **Properties**: Property type, location, size range, rent range, availability date

### Display Information

- **Franchises**: Brand name, logo, industry, description, fees, royalty %, locations, assigned broker
- **Properties**: Title, type, size, location, rent, deposit, images, availability, broker contact

### Permissions

- Brokers: Full CRUD on their own properties
- Franchisors: Assign brokers to franchises, toggle marketplace visibility
- Public: View marketplace-listed franchises and available properties

### To-dos

- [ ] Create marketplace API routes file and register in api.php
- [ ] Create marketplace_inquiries migration and model
- [ ] Create MarketplaceController with all required methods
- [ ] Create StoreMarketplaceInquiryRequest validation
- [ ] Create marketplace API service with TypeScript types
- [ ] Create main marketplace page component
- [ ] Create all marketplace view components (cards, filters, dialogs)
- [ ] Add marketplace route to router configuration
- [ ] Add marketplace link to front-page navbar