<!-- 1e766673-1366-46ec-9883-663153e699b5 91f23390-f1da-436c-b900-573d5ad929a9 -->
# Replace Frontend Stats Calculations with Real Backend Data

## Changes Overview

Currently, the franchisor dashboard uses frontend calculations for unit and task statistics. We'll replace these with accurate backend queries.

## Implementation Steps

### 1. Backend - Update Dashboard Stats Endpoint

**File**: `app/Http/Controllers/Api/V1/Franchisor/DashboardController.php`

In the `stats()` method (lines 29-101), add real queries for:

- **Unit statistics**: Query units by status (active/inactive)
  - Active units: `status = 'active'` or `status = 'operational'`
  - Inactive units: other statuses

- **Task statistics**: Query tasks by status
  - Completed tasks: `status = 'completed'`
  - Pending tasks: `status = 'pending'`
  - In-progress tasks: `status = 'in_progress'`
  - Total tasks: count all tasks

Add these fields to the response array at line 95.

### 2. Frontend - Update TypeScript Interface

**File**: `resources/ts/services/api/franchise.ts`

Update `FranchisorDashboardStats` interface (line 99) to include:

```typescript
activeUnits: number
inactiveUnits: number
completedTasks: number
pendingTasks: number
totalTasks: number
```

### 3. Frontend - Update Export

**File**: `resources/ts/services/api/index.ts`

Ensure the updated `FranchisorDashboardStats` type is properly exported (already at line 156).

### 4. Frontend - Update Dashboard Page

**File**: `resources/ts/pages/franchisor/index.vue`

- Remove calculated `unitStats` computed property (lines 67-72)
- Remove calculated `taskStats` computed property (lines 75-81)
- Update `franchiseStats` computed (line 56) to include all new fields from API
- Pass real backend values to `UnitStats` component (line 168)
- Pass real backend values to `TaskTracker` component (line 180)

## Expected Result

All widget stats will display accurate, real-time data from the database instead of frontend estimates.

### To-dos

- [ ] Add detailed unit and task queries to backend dashboard stats endpoint
- [ ] Update FranchisorDashboardStats TypeScript interface with new fields
- [ ] Remove frontend calculations and use real backend data in dashboard page