<!-- b4f0f963-002d-4fca-aa6f-c27cd5662cf2 a79f2e69-1ff0-499a-b95b-0d6e56d48443 -->
# Franchise Static Documents Implementation

## Overview

Update the franchise document system to support 4 required static documents during registration, with the ability to edit/replace them later.

## Required Changes

### 1. Update Type Definitions

**File**: `resources/ts/views/wizard-examples/franchise-registration/types.ts`

- Update `DocumentUploadData` interface to include: `fdd`, `franchiseAgreement`, `financialStudy`, `franchiseKit` (all required File | null)
- Keep `additionalDocuments: File[] | null` for optional documents

### 2. Update Document Upload Component  

**File**: `resources/ts/views/wizard-examples/franchise-registration/DocumentUpload.vue`

- Replace existing fields with 4 required document fields:
- Franchise Disclosure Document (FDD) - required
- Franchise Agreement - required  
- Financial Study - required
- Franchise Kit - required
- Keep "Additional Documents" field as optional multiple file upload
- Update all v-model bindings to match new interface
- Mark all 4 static documents as required in the UI

### 3. Update Registration Page Logic

**File**: `resources/ts/pages/franchisor/franchise-registration.vue`

- Update `franchiseRegistrationData.documents` initialization to include: `fdd`, `franchiseAgreement`, `financialStudy`, `franchiseKit`, `additionalDocuments`
- Update `onSubmit` function's document upload logic:
- Upload FDD with type 'fdd'
- Upload Franchise Agreement with type 'franchise_agreement'
- Upload Financial Study with type 'financial_study'
- Upload Franchise Kit with type 'franchise_kit'
- Loop through `additionalDocuments` and upload each with type 'other'

### 4. Update Backend Validation

**File**: `app/Http/Requests/StoreDocumentRequest.php`

- Update the `type` validation rule to include new types: `'fdd', 'franchise_agreement', 'financial_study', 'franchise_kit'`
- Keep existing types for backward compatibility: `'contract', 'agreement', 'manual', 'certificate', 'report', 'other'`

### 5. Update My Franchise Documents Section

**File**: `resources/ts/pages/franchisor/my-franchise.vue`

- Create a dedicated section for the 4 static required documents showing:
- Document name (FDD, Franchise Agreement, Financial Study, Franchise Kit)
- Current filename if uploaded
- Edit/Replace button for each document
- Add a separate section below for "Additional Documents" with add/delete functionality
- Update `loadDocuments` to separate static documents from additional ones based on type
- Add function to handle document replacement (upload new file for specific document type)
- Ensure document replace functionality updates the existing document record rather than creating a new one

### 6. Create Document Replace/Edit Dialog (Optional Enhancement)

**File**: `resources/ts/components/dialogs/franchise/EditFranchiseDocumentDialog.vue` (new file)

- Create reusable dialog for editing/replacing individual static documents
- Accept document type and current document data as props
- Show file upload field and allow replacing the file
- Emit event on successful update

## Key Implementation Details

- All 4 static documents use specific document types in the database: `'fdd'`, `'franchise_agreement'`, `'financial_study'`, `'franchise_kit'`
- Additional optional documents use type `'other'`
- Keep simple VFileInput fields (not fancy drag-drop styling from screenshots)
- Static documents can be edited/replaced in My Franchise page after registration
- Validation ensures all 4 static documents are required during registration

### To-dos

- [ ] Update DocumentUploadData interface with 4 static document fields
- [ ] Update DocumentUpload.vue component with new required fields
- [ ] Update franchise registration page upload logic for new document types
- [ ] Update StoreDocumentRequest validation to include new document types
- [ ] Update My Franchise documents section with static documents UI and edit functionality