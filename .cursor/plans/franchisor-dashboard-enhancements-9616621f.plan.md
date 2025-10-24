<!-- 9616621f-05b8-45a0-b538-45551fd3039f 61fc79f1-5de0-451b-9118-96c2d1831617 -->
# Franchisor Dashboard Enhancements Plan

## Overview

Implement three major features for the franchisor dashboard:

1. Bidirectional task management between franchisor and franchisee
2. Franchise-level staff management (separate from unit-level staff)
3. Display franchise fees, royalty fees, and marketing fees in franchise details

## 1. Bidirectional Task Management

### Backend Changes

**Update Task Model and Permissions**

- Modify `app/Models/Task.php` to add helper methods for determining task creator/assignee relationships
- Ensure franchisees can create tasks assigned to franchisors

**Create/Update Task Request Validation**

- Update `app/Http/Requests/StoreTaskRequest.php` to allow franchisees to assign tasks to franchisors
- Update `app/Http/Requests/UpdateTaskRequest.php` similarly

**Update TaskController**

- Modify `app/Http/Controllers/Api/V1/Resources/TaskController.php`:
- Update `myTasks()` method to include tasks where franchisor is the assignee
- Ensure franchisees can create tasks for franchisors
- Add proper authorization checks

**Update Franchisee Task Routes**

- Modify `routes/api/v1/franchisee.php` to add task creation routes for franchisees

### Frontend Changes

**Update Task Service**

- Modify `resources/ts/services/api/task.ts` to support task creation with franchisor as assignee

**Update Task Management Page**

- Modify `resources/ts/pages/franchisor/tasks-management.vue` (if exists) or create it:
- Add filter to show: "Tasks I Created", "Tasks Assigned to Me", "All Tasks"
- Update task creation dialog to allow selecting assignee (franchisees or self)
- Show task creator and assignee clearly in the UI

**Create Franchisee Task Creation Feature**

- Update franchisee task management page to allow creating tasks assigned to franchisor
- Add UI to indicate who created the task and who it's assigned to

## 2. Franchise-Level Staff Management

### Backend Changes

**Database Migration**

- Create migration `add_franchise_id_to_staff_table.php`:
- Add `franchise_id` foreign key to `staff` table (nullable)
- Staff can belong to franchise OR units (via pivot), but franchise-level staff won't have unit assignments

**Update Staff Model**

- Modify `app/Models/Staff.php`:
- Add `franchise` relationship
- Add scope `scopeFranchiseLevel()` for franchise-level staff
- Add scope `scopeByFranchise()` to filter by franchise

**Update Franchise Model**

- Modify `app/Models/Franchise.php`:
- Add `staff()` relationship for franchise-level staff

**Create Staff Controllers/Routes**

- Create new routes in `routes/api/v1/franchisor.php`:
- `GET /franchisor/franchise/staff` - List franchise staff
- `POST /franchisor/franchise/staff` - Create franchise staff
- `PUT /franchisor/franchise/staff/{id}` - Update franchise staff
- `DELETE /franchisor/franchise/staff/{id}` - Delete franchise staff
- `GET /franchisor/franchise/staff/statistics` - Staff statistics

- Add methods to `app/Http/Controllers/Api/V1/Franchisor/FranchisorController.php`:
- `getFranchiseStaff()` - Get all franchise-level staff
- `createFranchiseStaff()` - Create new franchise staff
- `updateFranchiseStaff()` - Update franchise staff
- `deleteFranchiseStaff()` - Delete franchise staff
- `getFranchiseStaffStatistics()` - Get staff statistics

**Create Form Requests**

- Create `app/Http/Requests/StoreFranchiseStaffRequest.php`
- Create `app/Http/Requests/UpdateFranchiseStaffRequest.php`

### Frontend Changes

**Create Staff API Service**

- Create `resources/ts/services/api/franchise-staff.ts`:
- Type definitions for franchise staff
- Methods: `getFranchiseStaff()`, `createStaff()`, `updateStaff()`, `deleteStaff()`, `getStatistics()`

**Export Service**

- Update `resources/ts/services/api/index.ts` to export franchise staff service

**Create Staff Management Page**

- Create `resources/ts/pages/franchisor/franchise-staff.vue`:
- Display staff list in data table with columns: Name, Email, Phone, Job Title, Department, Hire Date, Status, Actions
- Add filters: Status, Department, Employment Type, Search
- Stats cards: Total Staff, Active Staff, On Leave, By Department
- Add/Edit/Delete functionality with dialogs
- Export functionality

**Create Staff Dialogs**

- Create `resources/ts/components/dialogs/franchise/AddStaffDialog.vue`
- Create `resources/ts/components/dialogs/franchise/EditStaffDialog.vue`
- Use existing `ConfirmDeleteDialog.vue` for delete confirmation

**Update Navigation**

- Modify `resources/ts/navigation/vertical/franchisor.ts`:
- Add "Franchise Staff" menu item after "My Franchise"

**Update Router**

- Add route for franchise staff page in `resources/ts/router/routes.ts`

## 3. Franchise Financial Details Display

### Backend Changes

**Update Franchise Controller**

- Modify `app/Http/Controllers/Api/V1/Franchisor/FranchisorController.php`:
- Ensure `getFranchiseData()` returns franchise_fee, royalty_percentage, marketing_fee_percentage
- Ensure `updateFranchise()` allows updating these fields

**Update Form Requests**

- Update validation rules in franchise update request to include financial fields

### Frontend Changes

**Update Franchise Service**

- Modify `resources/ts/services/api/franchise.ts`:
- Update `FranchiseData` interface to include financial fields
- Ensure update payload supports financial fields

**Update My Franchise Page**

- Modify `resources/ts/pages/franchisor/my-franchise.vue`:
- Add new "Financial Details" section in the Overview tab (after Contact Details)
- Display fields:
  - Franchise Fee (currency format)
  - Royalty Percentage (percentage format)
  - Marketing Fee Percentage (percentage format)
- Make fields editable when in edit mode
- Use number inputs with proper formatting

**Update Interface Types**

- Update `FranchiseData` interface in my-franchise.vue to include:
- `franchiseFee: number`
- `royaltyPercentage: number`
- `marketingFeePercentage: number`

## Implementation Order

1. **Franchise Financial Details** (Simplest - mostly frontend display)
2. **Franchise-Level Staff Management** (Database + Full CRUD)
3. **Bidirectional Task Management** (Logic + Permissions updates)

## Key Files to Modify/Create

### Backend

- `database/migrations/[timestamp]_add_franchise_id_to_staff_table.php` (NEW)
- `app/Models/Staff.php`
- `app/Models/Franchise.php`
- `app/Models/Task.php`
- `app/Http/Controllers/Api/V1/Franchisor/FranchisorController.php`
- `app/Http/Controllers/Api/V1/Resources/TaskController.php`
- `app/Http/Requests/StoreFranchiseStaffRequest.php` (NEW)
- `app/Http/Requests/UpdateFranchiseStaffRequest.php` (NEW)
- `app/Http/Requests/StoreTaskRequest.php`
- `routes/api/v1/franchisor.php`
- `routes/api/v1/franchisee.php`

### Frontend

- `resources/ts/services/api/franchise-staff.ts` (NEW)
- `resources/ts/services/api/franchise.ts`
- `resources/ts/services/api/task.ts`
- `resources/ts/services/api/index.ts`
- `resources/ts/pages/franchisor/franchise-staff.vue` (NEW)
- `resources/ts/pages/franchisor/my-franchise.vue`
- `resources/ts/pages/franchisor/tasks-management.vue` (if exists, or CREATE)
- `resources/ts/components/dialogs/franchise/AddStaffDialog.vue` (NEW)
- `resources/ts/components/dialogs/franchise/EditStaffDialog.vue` (NEW)
- `resources/ts/navigation/vertical/franchisor.ts`
- `resources/ts/router/routes.ts`

### To-dos

- [ ] Add franchise financial details (fees) to My Franchise page
- [ ] Create database migration to add franchise_id to staff table
- [ ] Update Staff and Franchise models with relationships and scopes
- [ ] Create staff management backend (controllers, requests, routes)
- [ ] Create franchise staff API service on frontend
- [ ] Create franchise staff management page and dialogs
- [ ] Add franchise staff to navigation and router
- [ ] Update Task model with helper methods for bidirectional management
- [ ] Update task controllers and routes for bidirectional task management
- [ ] Update task management UI to support bidirectional tasks