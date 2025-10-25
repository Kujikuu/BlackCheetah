<!-- d6b74513-2458-454b-97a5-2fc66acdbdc8 d3331941-ffbc-4ae2-af51-d6cb4312d2a1 -->
# Comprehensive Frontend Validation Implementation

## Overview

Implement comprehensive form validations for all 65+ dialog components using Vuetify's built-in validation system, matching backend Laravel Request validation rules. Include automatic backend error-to-field mapping.

## Implementation Strategy

### Phase 1: Core Validation Infrastructure

#### 1.1 Create Validation Rules Composable (`resources/ts/composables/useValidationRules.ts`)

Create a centralized composable with reusable validation rule functions that mirror Laravel validation rules:

- Required field validation
- Email validation with max length
- Phone number validation (supports Saudi format)
- String length validation (min, max)
- Numeric validation (min, max, between)
- Date validation (date, after, before, after_or_equal)
- Array validation
- Enum/In validation (for dropdowns)
- Unique validation helpers
- URL validation
- Conditional validation (required_if, nullable)
- Custom regex patterns

#### 1.2 Create Backend Error Mapper (`resources/ts/utils/formErrorMapper.ts`)

Create utility to automatically map Laravel validation errors to Vuetify form fields:

- Parse Laravel 422 validation error responses
- Convert snake_case backend field names to camelCase frontend field names
- Extract error messages and map to corresponding form fields
- Support nested field errors
- Return reactive error object compatible with Vuetify

#### 1.3 Create Form Validation Composable (`resources/ts/composables/useFormValidation.ts`)

Create a composable to manage form validation state:

- Track validation state (valid/invalid)
- Handle form submission with validation
- Map backend errors automatically
- Clear errors on field change
- Reset form state
- Return validation helpers for templates

#### 1.4 Extend API Types (`resources/ts/types/api.ts`)

Add validation error type definitions:

```typescript
export interface ValidationError {
  field: string
  message: string
}

export interface ValidationErrors {
  [field: string]: string[]
}

export interface ApiErrorResponse {
  success: false
  message: string
  errors?: ValidationErrors
}
```

### Phase 2: Domain-Specific Validation Rules

Create validation rule sets organized by domain that match backend Request classes:

#### 2.1 Leads Validation Rules (`resources/ts/validation/leadsValidation.ts`)

- Map `StoreLeadRequest` and `UpdateLeadRequest` rules
- Cover fields: firstName, lastName, email, phone, company, jobTitle, nationality, city, state, source, status, priority, notes, estimatedInvestment, etc.

#### 2.2 Staff Validation Rules (`resources/ts/validation/staffValidation.ts`)

- Map `StoreStaffRequest` and `UpdateStaffRequest` rules
- Cover fields: name, email, phone, jobTitle, department, salary, hireDate, shiftStart, shiftEnd, status, employmentType, notes

#### 2.3 Tasks Validation Rules (`resources/ts/validation/tasksValidation.ts`)

- Map `StoreTaskRequest` and `UpdateTaskRequest` rules
- Cover fields: title, description, category, priority, status, assignedTo, dueDate, estimatedHours, actualHours, startDate

#### 2.4 Financial Validation Rules (`resources/ts/validation/financialValidation.ts`)

- Map `StoreFinancialDataRequest`, `StoreSaleRequest`, `StoreExpenseRequest`, `StoreRevenueRequest` rules
- Handle conditional validation for sales vs. expenses

#### 2.5 Property Validation Rules (`resources/ts/validation/propertyValidation.ts`)

- Map `StorePropertyRequest` and `UpdatePropertyRequest` rules
- Cover fields: title, description, propertyType, sizeSqm, stateProvince, city, address, monthlyRent, depositAmount, amenities, etc.

#### 2.6 Franchise Validation Rules (`resources/ts/validation/franchiseValidation.ts`)

- Map `StoreFranchiseRequest`, `UpdateFranchiseRequest`, `RegisterFranchiseRequest` rules
- Cover extensive franchise fields: businessName, industry, registrationNumber, contactInfo, fees, etc.

#### 2.7 Unit Validation Rules (`resources/ts/validation/unitValidation.ts`)

- Map `StoreUnitRequest` and `UpdateUnitRequest` rules
- Cover fields: unitName, unitType, address, city, state, phone, email, sizeSqft, initialInvestment, status, dates, etc.

#### 2.8 Document Validation Rules (`resources/ts/validation/documentValidation.ts`)

- Map `StoreDocumentRequest` and `UpdateDocumentRequest` rules

#### 2.9 Review Validation Rules (`resources/ts/validation/reviewValidation.ts`)

- Map `StoreReviewRequest` and `UpdateReviewRequest` rules

#### 2.10 Notes Validation Rules (`resources/ts/validation/notesValidation.ts`)

- Map `AddLeadNoteRequest` rules

#### 2.11 Product Validation Rules (`resources/ts/validation/productValidation.ts`)

- Map `StoreProductRequest` and `UpdateProductRequest` rules

#### 2.12 Royalty Validation Rules (`resources/ts/validation/royaltyValidation.ts`)

- Map `StoreRoyaltyRequest` rules

#### 2.13 Transaction Validation Rules (`resources/ts/validation/transactionValidation.ts`)

- Map `StoreTransactionRequest` rules

#### 2.14 Technical Request Validation Rules (`resources/ts/validation/technicalRequestValidation.ts`)

- Map `StoreTechnicalRequestRequest` and `UpdateTechnicalRequestRequest` rules

#### 2.15 User/Broker Validation Rules (`resources/ts/validation/userValidation.ts`)

- Map `CreateUserRequest`, `UpdateUserRequest`, `StoreBrokerRequest`, `UpdateBrokerRequest`, `CompleteProfileRequest` rules

### Phase 3: Update All Dialog Components

Update all 65+ dialog components with comprehensive validations. Organized by directory:

#### 3.1 Leads Dialogs (7 dialogs)

- `leads/EditLeadDialog.vue` - Apply UpdateLeadRequest validation
- `leads/AssignLeadDialog.vue` - Apply AssignLeadRequest validation
- `leads/ConvertLeadDialog.vue` - Apply validation
- `leads/DeleteLeadDialog.vue` - No validation needed (confirmation only)
- `leads/ImportLeadsDialog.vue` - Apply file upload validation
- `leads/MarkAsLostDialog.vue` - Apply MarkLeadAsLostRequest validation
- `leads/ViewLeadDialog.vue` - Read-only, no validation

#### 3.2 Staff Dialogs (6 dialogs)

- `staff/AddStaffDialog.vue` - Apply StoreStaffRequest validation
- `staff/EditStaffDialog.vue` - Apply UpdateStaffRequest validation
- `staff/ViewStaffDialog.vue` - Read-only, no validation
- `franchise/AddStaffDialog.vue` - Apply StoreFranchiseStaffRequest validation
- `franchise/EditStaffDialog.vue` - Apply UpdateFranchiseStaffRequest validation

#### 3.3 Tasks Dialogs (5 dialogs)

- `tasks/CreateTaskDialog.vue` - Apply StoreTaskRequest validation
- `tasks/EditTaskDialog.vue` - Apply UpdateTaskRequest validation
- `tasks/DeleteTaskDialog.vue` - No validation needed
- `tasks/StatusChangeTaskDialog.vue` - Apply UpdateTaskStatusRequest validation
- `tasks/ViewTaskDialog.vue` - Read-only, no validation

#### 3.4 Financial Dialogs (4 dialogs)

- `financial/AddDataDialog.vue` - Apply StoreFinancialDataRequest validation (conditional)
- `financial/ImportFinancialDialog.vue` - Apply file upload validation
- `financial/ViewExpenseDetailsDialog.vue` - Read-only, no validation
- `financial/ViewSaleDetailsDialog.vue` - Read-only, no validation

#### 3.5 Broker/Property Dialogs (3 dialogs)

- `broker/CreatePropertyDialog.vue` - Apply StorePropertyRequest validation
- `broker/EditPropertyDialog.vue` - Apply UpdatePropertyRequest validation
- `broker/DeletePropertyDialog.vue` - No validation needed

#### 3.6 Notes Dialogs (4 dialogs)

- `notes/AddNoteDialog.vue` - Apply AddLeadNoteRequest validation
- `notes/EditNoteDialog.vue` - Apply validation
- `notes/DeleteNoteDialog.vue` - No validation needed
- `notes/ViewNoteDialog.vue` - Read-only, no validation

#### 3.7 Document Dialogs (3 dialogs)

- `AddDocumentModal.vue` - Apply StoreDocumentRequest validation
- `franchise/AddDocumentDialog.vue` - Apply StoreDocumentRequest validation
- `DocumentActionModal.vue` - Apply validation

#### 3.8 Review Dialogs (3 dialogs)

- `reviews/AddReviewDialog.vue` - Apply StoreReviewRequest validation
- `reviews/EditReviewDialog.vue` - Apply UpdateReviewRequest validation
- `reviews/ViewReviewDialog.vue` - Read-only, no validation

#### 3.9 Product Dialogs (3 dialogs)

- `products/AddProductToInventoryDialog.vue` - Apply StoreProductRequest validation
- `products/EditProductDialog.vue` - Apply UpdateProductRequest validation
- `products/ViewProductDialog.vue` - Read-only, no validation

#### 3.10 Royalty Dialogs (3 dialogs)

- `royalty/ExportRoyaltyDialog.vue` - Apply export validation
- `royalty/MarkCompletedRoyaltyDialog.vue` - Apply validation
- `royalty/ViewRoyaltyDetailsDialog.vue` - Read-only, no validation

#### 3.11 Performance Dialogs (1 dialog)

- `performance/ExportPerformanceDialog.vue` - Apply export validation

#### 3.12 Unit Dialogs (3 dialogs)

- `units/AddFranchiseeDialog.vue` - Apply CreateFranchiseeWithUnitRequest validation
- `units/ChangeStatusDialog.vue` - Apply validation
- `units/EditUnitDialog.vue` - Apply UpdateUnitRequest validation

#### 3.13 Common/Misc Dialogs (20+ dialogs)

- `AddFranchiseeModal.vue` - Apply validation
- `AssignLeadModal.vue` - Apply AssignLeadRequest validation
- `ConvertLeadModal.vue` - Apply validation
- `CreateTaskModal.vue` - Apply StoreTaskRequest validation
- `SimpleNoteModal.vue` - Apply validation
- `MarkAsLostModal.vue` - Apply MarkLeadAsLostRequest validation
- `AddEditAddressDialog.vue` - Apply address validation
- `AddEditPermissionDialog.vue` - Apply validation
- `AddEditRoleDialog.vue` - Apply validation
- `AddPaymentMethodDialog.vue` - Apply payment validation
- `CardAddEditDialog.vue` - Apply card validation
- `UserInfoEditDialog.vue` - Apply UpdateUserRequest validation
- `common/ConfirmDeleteDialog.vue` - No validation needed
- `common/ImportDataDialog.vue` - Apply file upload validation
- And other utility dialogs with appropriate validations

### Phase 4: Enhanced Error Handling

#### 4.1 Update API Utility (`resources/ts/utils/api.ts`)

Enhance the `onResponseError` interceptor to:

- Extract and preserve validation errors from 422 responses
- Pass validation errors to components in standardized format
- Keep the current toast notification for general errors
- Add field-level error extraction

#### 4.2 Create Error Display Component (Optional)

Create a reusable `ValidationErrorAlert.vue` component for displaying validation errors at the form level when needed.

### Phase 5: Testing and Documentation

#### 5.1 Manual Testing Checklist

- Test each dialog's validation on submit
- Verify backend error mapping works correctly
- Test required fields block submission
- Test email/phone format validation
- Test numeric min/max validation
- Test date validation and comparisons
- Test conditional validation (required_if scenarios)
- Test array/file validations

#### 5.2 Create Validation Documentation

Document the validation system in `VALIDATION_GUIDE.md`:

- How to use validation rules composable
- How to add validation to new dialogs
- How backend errors are automatically mapped
- Common validation patterns
- Troubleshooting guide

## Technical Details

### Vuetify Validation Pattern

Each form field will use Vuetify's `:rules` prop:

```vue
<VTextField
  v-model="formData.email"
  label="Email"
  :rules="[rules.required, rules.email, rules.maxLength(255)]"
  :error-messages="backendErrors.email"
/>
```

### Form Submission Pattern

```typescript
const { validate, mapBackendErrors, clearErrors } = useFormValidation()

const onSubmit = async () => {
  if (!await validate(formRef.value)) return
  
  try {
    await api.createResource(formData.value)
    emit('success')
  } catch (error) {
    mapBackendErrors(error, formData)
  }
}
```

### Backend Error Mapping

Laravel returns errors like:

```json
{
  "message": "Validation failed",
  "errors": {
    "first_name": ["First name is required."],
    "email": ["Email must be valid.", "Email already exists."]
  }
}
```

Will be mapped to:

```typescript
{
  firstName: "First name is required.",
  email: "Email must be valid. Email already exists."
}
```

## Files to Create/Modify

### New Files (18)

1. `resources/ts/composables/useValidationRules.ts`
2. `resources/ts/composables/useFormValidation.ts`
3. `resources/ts/utils/formErrorMapper.ts`
4. `resources/ts/validation/leadsValidation.ts`
5. `resources/ts/validation/staffValidation.ts`
6. `resources/ts/validation/tasksValidation.ts`
7. `resources/ts/validation/financialValidation.ts`
8. `resources/ts/validation/propertyValidation.ts`
9. `resources/ts/validation/franchiseValidation.ts`
10. `resources/ts/validation/unitValidation.ts`
11. `resources/ts/validation/documentValidation.ts`
12. `resources/ts/validation/reviewValidation.ts`
13. `resources/ts/validation/notesValidation.ts`
14. `resources/ts/validation/productValidation.ts`
15. `resources/ts/validation/royaltyValidation.ts`
16. `resources/ts/validation/transactionValidation.ts`
17. `resources/ts/validation/technicalRequestValidation.ts`
18. `resources/ts/validation/userValidation.ts`

### Modified Files (65+ dialogs)

All dialog components in `resources/ts/components/dialogs/` and related subdirectories.

### Updated Files (2)

1. `resources/ts/types/api.ts` - Add validation error types
2. `resources/ts/utils/api.ts` - Enhanced error handling

## Benefits

- **Consistency**: Frontend validation matches backend exactly
- **User Experience**: Immediate feedback on form submission
- **Error Clarity**: Backend errors automatically mapped to fields
- **Maintainability**: Centralized validation rules, easy to update
- **Type Safety**: Full TypeScript support
- **Reusability**: Validation rules shared across components

### To-dos

- [ ] Create core validation infrastructure (composables and utilities)
- [ ] Create domain-specific validation rule sets matching backend Request classes
- [ ] Update all Leads dialogs with comprehensive validation
- [ ] Update all Staff dialogs with comprehensive validation
- [ ] Update all Tasks dialogs with comprehensive validation
- [ ] Update all Financial dialogs with comprehensive validation
- [ ] Update all Property/Broker dialogs with comprehensive validation
- [ ] Update all Notes dialogs with comprehensive validation
- [ ] Update all Document dialogs with comprehensive validation
- [ ] Update all Review dialogs with comprehensive validation
- [ ] Update all Product dialogs with comprehensive validation
- [ ] Update all Royalty dialogs with comprehensive validation
- [ ] Update all Unit dialogs with comprehensive validation
- [ ] Update all remaining common and miscellaneous dialogs with comprehensive validation
- [ ] Enhance API error handling and create error display components
- [ ] Test all dialogs for proper validation behavior and backend error mapping