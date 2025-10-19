# Frontend API Architecture Standardization Plan

## Overview
This document outlines the plan for standardizing the frontend API architecture in the Vue.js application. The goal is to create a consistent, maintainable, and scalable API service pattern across the application.

## Current Issues Identified
1. **Mixed patterns**: TechnicalRequest uses object-based API service, Lead uses class-based API, FranchiseeDashboard uses object-based
2. **Inconsistent response handling**: Different approaches to type definitions and response processing
3. **Variable base URL patterns**: Some fixed, others dynamic based on user role
4. **Different error handling approaches**: Not standardized across services
5. **Inconsistent parameter handling**: Some use URLSearchParams, others direct params
6. **Multiple API call methods**: Some use the $api utility, others use the useApi composable

## Complete List of Current API Services
- TechnicalRequest API (`services/api/technical-request.ts`)
- Lead API (`services/api/lead.ts`) - class-based
- FranchiseeDashboard API (`services/api/franchisee-dashboard.ts`)
- Account-Settings API (`services/api/account-settings.ts`)
- Financial API (`services/api/financial.ts`)
- Royalty API (`services/api/royalty.ts`)
- Task API (`services/api/task.ts`)
- Direct API calls using `useApi` composable in Vue components

## Standardized Architecture Plan

### 1. Common API Service Template
Create a consistent structure for all API services using the following pattern:

```typescript
// services/api/[entity].ts
import { $api } from '@/utils/api'
import { getBaseURLForRole } from '@/utils/apiHelpers'

// Define base URL for the entity
const ENTITY_BASE_URL = getBaseURLForRole('[entity-plural]')

// Type definitions
export interface Entity {
  id: number | string
  // ... other standard fields
}

export interface EntityFilters {
  // ... filter parameters
}

export interface ApiResponse<T = any> {
  success: boolean
  data?: T
  message?: string
  error?: string
}

export interface ListApiResponse<T> extends ApiResponse<PaginatedResponse<T>> {}

// API Service
export const entityApi = {
  // Standard CRUD operations
  async getAll(filters?: EntityFilters): Promise<ListApiResponse<Entity>> {
    return await $api<ListApiResponse<Entity>>(ENTITY_BASE_URL, {
      method: 'GET',
      params: filters
    })
  },

  async getById(id: number | string): Promise<ApiResponse<Entity>> {
    return await $api<ApiResponse<Entity>>(`${ENTITY_BASE_URL}/${id}`, {
      method: 'GET'
    })
  },

  async create(data: Partial<Entity>): Promise<ApiResponse<Entity>> {
    return await $api<ApiResponse<Entity>>(ENTITY_BASE_URL, {
      method: 'POST',
      body: data
    })
  },

  async update(id: number | string, data: Partial<Entity>): Promise<ApiResponse<Entity>> {
    return await $api<ApiResponse<Entity>>(`${ENTITY_BASE_URL}/${id}`, {
      method: 'PUT', // or 'PATCH' depending on backend implementation
      body: data
    })
  },

  async delete(id: number | string): Promise<ApiResponse> {
    return await $api<ApiResponse>(`${ENTITY_BASE_URL}/${id}`, {
      method: 'DELETE'
    })
  },

  // Additional operations
  async bulkDelete(ids: (number | string)[]): Promise<ApiResponse> {
    return await $api<ApiResponse>(`${ENTITY_BASE_URL}/bulk-delete`, {
      method: 'POST',
      body: { ids }
    })
  }
}
```

### 2. Common API Helpers
Create a centralized helper file for common API operations:

```typescript
// utils/apiHelpers.ts
export const getBaseURLForRole = (entity: string): string => {
  const userDataCookie = useCookie('userData')
  const userData = userDataCookie.value as any
  const userRole = userData?.role

  if (!userRole) return `/v1/${entity}`

  // Define role-specific prefixes
  const rolePrefix = {
    'admin': 'admin',
    'franchisor': 'franchisor', 
    'franchisee': 'unit-manager',
    'sales': 'sales'
  }[userRole] || ''

  return rolePrefix ? `/v1/${rolePrefix}/${entity}` : `/v1/${entity}`
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface ApiResponse<T = any> {
  success: boolean
  data?: T
  message?: string
  error?: string
}

export interface ListApiResponse<T> extends ApiResponse<PaginatedResponse<T>> {}
```

### 3. Role-Based API Service Pattern
For services that need to support different endpoints based on user roles:

```typescript
// services/api/franchisor-dashboard.ts
import { $api } from '@/utils/api'

// For services that directly call role-specific endpoints
const BASE_URL = '/v1/franchisor'

export interface FranchisorFinanceData {
  // ... specific data structure
}

export interface ApiResponse<T = any> {
  success: boolean
  data?: T
  message?: string
}

export const franchisorDashboardApi = {
  async getFinanceData(): Promise<ApiResponse<FranchisorFinanceData>> {
    return await $api<ApiResponse<FranchisorFinanceData>>(`${BASE_URL}/dashboard/finance`)
  },
  
  async getLeadsData(): Promise<ApiResponse<any>> {
    return await $api<ApiResponse<any>>(`${BASE_URL}/dashboard/leads`)
  },
  
  async getOperationsData(): Promise<ApiResponse<any>> {
    return await $api<ApiResponse<any>>(`${BASE_URL}/dashboard/operations`)
  },
  
  async getTimelineData(): Promise<ApiResponse<any>> {
    return await $api<ApiResponse<any>>(`${BASE_URL}/dashboard/timeline`)
  }
}
```

## Implementation Tasks

### Phase 1: Foundation Setup (Day 1)
- [ ] **Task 1.1**: Create the `apiHelpers.ts` file with common functions
- [ ] **Task 1.2**: Verify the base API utility (`$api`) works correctly with the new patterns
- [ ] **Task 1.3**: Define standard interface types in the helpers file
- [ ] **Task 1.4**: Analyze all existing API services to catalog their usage patterns
- [ ] **Task 1.5**: Identify all components that directly use the `useApi` composable

### Phase 2: Service Refactoring (Days 2-5)
- [ ] **Task 2.1**: Refactor TechnicalRequest service to follow the new pattern
  - Convert to object-based approach with standardized types
  - Update type definitions to match standard pattern
  - Implement role-based URL handling if necessary
  - Add proper error handling

- [ ] **Task 2.2**: Refactor Lead service from class-based to object-based
  - Convert LeadApi class to object-based service
  - Integrate with common API helpers
  - Ensure camelCase to snake_case transformations are handled consistently

- [ ] **Task 2.3**: Refactor FranchiseeDashboard service to follow new standards
  - Update to use common patterns and URL helpers
  - Standardize response types
  - Ensure all operations follow the new template

- [ ] **Task 2.4**: Refactor Account-Settings service
  - Update service to match standard pattern
  - Ensure all endpoints use consistent response format

- [ ] **Task 2.5**: Refactor Financial service
  - Standardize the API service structure
  - Ensure proper role-based URL handling

- [ ] **Task 2.6**: Refactor Royalty service
  - Update to use the new standardized pattern

- [ ] **Task 2.7**: Refactor Task service
  - Ensure consistent architecture across all task-related operations

- [ ] **Task 2.8**: Create missing services for FranchisorDashboard
  - Create finance service for franchisor
  - Create leads service for franchisor
  - Create operations service for franchisor
  - Create timeline service for franchisor

### Phase 3: Direct API Usage Conversion (Days 6-7)
- [ ] **Task 3.1**: Update NavSearchBar component to use new service pattern
  - Replace direct `useApi` call with standardized service
  - Ensure type safety is maintained

- [ ] **Task 3.2**: Create dedicated services for dashboard components
  - Create franchisor dashboard finance service
  - Create franchisor dashboard leads service
  - Create franchisor dashboard operations service
  - Create franchisor dashboard timeline service

- [ ] **Task 3.3**: Update dashboard components to use new services
  - Update `franchisor/dashboard/finance.vue`
  - Update `franchisor/dashboard/leads.vue`
  - Update `franchisor/dashboard/operations.vue`
  - Update `franchisor/dashboard/timeline.vue`

- [ ] **Task 3.4**: Update data table components
  - Update `AnalyticsProjectTable.vue` 
  - Update `EcommerceInvoiceTable.vue`
  - Update `BillingHistoryTable.vue`

### Phase 4: Component Updates (Days 8-9)
- [ ] **Task 4.1**: Update all Vue components that use TechnicalRequest services
  - Update import statements to use new service structure
  - Ensure proper error handling in UI
  - Verify loading states work correctly

- [ ] **Task 4.2**: Update components using Lead services
  - Update component references to use refactored service
  - Verify all functionality remains intact

- [ ] **Task 4.3**: Update components using FranchiseeDashboard services
  - Update component API calls
  - Ensure proper type safety is maintained

- [ ] **Task 4.4**: Update components using other refactored services
  - Audit all components for API service usage
  - Update all references to match new patterns

- [ ] **Task 4.5**: Ensure proper loading states and error handling
  - Verify all components properly show loading indicators
  - Confirm error handling works consistently across all components

### Phase 5: Testing and Validation (Day 10)
- [ ] **Task 5.1**: Test all TechnicalRequest endpoints
  - Verify all CRUD operations work correctly
  - Ensure role-based access works properly
  - Check pagination and filtering functionality

- [ ] **Task 5.2**: Test all Lead management endpoints
  - Verify create, read, update, delete operations
  - Ensure role-specific endpoints work correctly
  - Test bulk operations

- [ ] **Task 5.3**: Test all FranchiseeDashboard endpoints
  - Verify dashboard statistics work
  - Test all CRUD operations for units, staff, etc.
  - Ensure data transformations work properly

- [ ] **Task 5.4**: Test all FranchisorDashboard endpoints
  - Verify finance data loading and display
  - Test leads, operations, and timeline data
  - Ensure dashboard functionality remains intact

- [ ] **Task 5.5**: Test all other refactored endpoints
  - Validate Account-Settings functionality
  - Verify Financial operations work correctly
  - Confirm Royalty and Task operations function properly

- [ ] **Task 5.6**: End-to-end testing
  - Test user workflows from UI to API and back
  - Ensure no feature regressions
  - Verify type safety is maintained throughout

- [ ] **Task 5.7**: Performance validation
  - Ensure refactored services don't introduce performance regressions
  - Verify memory usage hasn't increased significantly

## API Services to Create/Refactor

### 1. Admin APIs
- User management (franchisees, franchisors, sales)
- Technical requests management
- System management

### 2. Franchisor APIs
- Dashboard finance
- Dashboard leads
- Dashboard operations
- Dashboard timeline
- Financial overview
- Lead management
- Sales associates
- Tasks management
- Technical requests
- Performance management
- Royalty management
- My franchise
- My units
- Franchise registration

### 3. Franchisee APIs
- Dashboard finance
- Dashboard operations
- Dashboard sales
- Performance management
- Royalty management
- Financial overview
- Technical requests
- Unit operations
- My tasks

### 4. Sales APIs
- Lead management
- Add lead
- My tasks
- Technical requests

### 5. Common APIs
- Account settings
- Authentication
- Notifications
- Search

## Benefits of Standardized Approach
1. **Consistency**: All API services follow the same pattern
2. **Maintainability**: Easier to maintain and update services
3. **Scalability**: New services can be added following the template
4. **Readability**: Consistent code structure across the codebase
5. **Type Safety**: Unified response and error handling
6. **Developer Experience**: Faster onboarding and development with clear patterns

## Success Metrics
- All API services follow the same architectural pattern
- Consistent error handling and response format across all services
- Improved maintainability and readability
- No loss of functionality during the refactoring process
- Faster development of new API services using the standardized template
- Elimination of direct `useApi` composable usage in favor of service objects
- Consistent handling of role-based API endpoints