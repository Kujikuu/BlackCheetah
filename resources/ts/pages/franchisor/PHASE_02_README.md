# Franchisor Dashboard - Phase 02

This document outlines the Phase 02 implementation for the franchisor dashboard, focusing on Sales Associates and Lead Management functionality.

## Overview

Phase 02 adds comprehensive lead management capabilities including sales associate tracking, lead creation with a multi-step wizard, lead viewing with notes, and CSV import functionality.

## New Pages

### 1. Sales Associates Page (`sales-associates.vue`)
**Route:** `franchisor-sales-associates`

**Features:**
- Table-only view (no stat cards)
- Columns:
  - Sales Associate (with avatar)
  - Email
  - Phone
  - Role
  - Status
  - Assigned Leads
  - Actions
- Filters: Role, Status
- Search functionality
- Export capability
- Add Sales Associate button
- Row selection with checkboxes
- Actions menu: View, Edit, Delete

**Template:** Based on `/pages/apps/user/list/index.vue`

---

### 2. Lead Management Page (`lead-management.vue`)
**Route:** `franchisor-lead-management`

**Features:**

#### Stat Cards (3 cards):
- **Total Leads** - All time leads count
- **Qualified** - Successfully qualified leads
- **Unqualified** - Leads that didn't qualify

#### Lead Table:
- Columns:
  - **# (Index)** - Sequential row number
  - Lead Name (with avatar, clickable to view page)
  - Company
  - Email
  - Phone
  - Location (City, State)
  - Source
  - Status
  - Owner
  - Last Contacted
  - Actions (dropdown menu)

#### Filters:
- Source (Website, Referral, Social Media, Email Campaign)
- Status (Qualified, Unqualified)
- Owner (Sales Associates)

#### Actions:
- **Search** - Full-text search across leads
- **Import** - Import leads from CSV with example file download
- **Export** - Export leads to CSV
- **Add Lead** - Navigate to wizard form

#### Row Actions Menu:
- **View & Edit** - Navigate to lead view page
- **Add Note** - Open add note modal
- **Delete** - Show confirmation dialog before deletion

#### Import Dialog:
- CSV file upload
- Download example CSV button
- Example CSV format includes all lead fields

#### Delete Confirmation:
- Modal dialog with warning message
- Cancel and Delete buttons

**Template:** Based on `/pages/apps/user/list/index.vue`

---

### 3. Add Lead Page (`add-lead.vue`)
**Route:** `franchisor-add-lead`

**Features:**

Multi-step wizard with 2 steps:

#### Step 1: Basic Information
Fields:
- First Name (required)
- Last Name (required)
- Email Address (required)
- Contact Number (required)
- Country (dropdown)
- State (dropdown)
- City (required)
- Company Name (required)

#### Step 2: Additional Details
Fields:
- Lead Source (dropdown: Website, Referral, Social Media, Email Campaign, Cold Call)
- Lead Status (dropdown: New, Qualified, Unqualified, Contacted)
- Lead Owner (dropdown: Sales Associates)
- Last Contacted Date (date picker)
- Scheduled Meeting Date (date picker)
- Note (textarea)
- Attachments (file upload, multiple files)

#### Navigation:
- Previous button (disabled on first step)
- Next button (on all steps except last)
- Submit button (on last step)
- Vertical stepper sidebar showing progress

**Template:** Based on `/pages/wizard-examples/create-deal.vue`

**Components:**
- `/views/franchisor/add-lead/BasicInfo.vue`
- `/views/franchisor/add-lead/AdditionalDetails.vue`
- `/views/franchisor/add-lead/types.ts` (TypeScript interfaces)

---

### 4. Lead View Page (`lead-view-[id].vue`)
**Route:** `franchisor-lead-view-id` (dynamic route with `:id` parameter)

**Features:**

#### Header Section:
- Large avatar with initials
- Lead name and company
- Status chip (color-coded)
- Edit/Save/Cancel buttons

#### Tabs:

##### Overview Tab:
Displays all lead information in organized sections:

**Contact Information:**
- Email
- Phone
- Company

**Location:**
- City
- State
- Country

**Lead Details:**
- Source
- Owner

**Timeline:**
- Last Contacted
- Scheduled Meeting

**Notes:**
- Additional notes textarea

All fields are read-only by default. Click "Edit" to enable editing, then "Save" to persist changes.

##### Notes Tab:
- **Add Note Button** - Opens modal to create new note
- **Notes List** - Cards displaying all notes with:
  - Created by (name) at (date/time)
  - Title
  - Excerpt of description (150 characters)
  - Read More button (opens view modal)
  - Top actions: Edit, Remove

**Note Card Features:**
- Tonal variant for visual distinction
- Timestamp with creator information
- Truncated description with "Read More"
- Edit and Delete actions

**View Note Modal:**
- Full note title
- Creator and timestamp
- Complete description
- Close button

---

### 5. Add Note Modal Component (`/components/franchisor/AddNoteModal.vue`)

**Features:**
- Modal dialog (not a page)
- Fields:
  - Title (text input)
  - Description (textarea, 6 rows)
- Actions:
  - Cancel - Close modal without saving
  - Add Note - Save and emit event
- Props:
  - `isDialogVisible` - Controls modal visibility
  - `leadId` - ID of the lead to attach note to
- Emits:
  - `update:isDialogVisible` - Two-way binding for visibility
  - `noteAdded` - Triggered after successful note creation

**Usage:**
```vue
<AddNoteModal
  v-model:is-dialog-visible="isAddNoteModalVisible"
  :lead-id="leadId"
  @note-added="onNoteAdded"
/>
```

---

## Navigation Structure

Updated `/navigation/vertical/franchisor.ts`:

```
Franchisor Dashboard
├── Dashboard (Phase 01 badge)
│   ├── Leads
│   ├── Operations
│   ├── Development Timeline
│   └── Finance
├── Sales Associates
└── Lead Management (Phase 02 badge)
    ├── All Leads
    └── Add Lead
```

**Note:** Lead View page is not in navigation as it's accessed via clicking on leads in the table.

---

## Data Structure

### Lead Object
```typescript
{
  id: number
  firstName: string
  lastName: string
  email: string
  phone: string
  company: string
  country: string
  state: string
  city: string
  source: string
  status: 'qualified' | 'unqualified'
  owner: string
  lastContacted: string (ISO date)
  scheduledMeeting: string (ISO date)
  note: string
}
```

### Note Object
```typescript
{
  id: number
  title: string
  description: string
  createdBy: string
  createdAt: string (ISO datetime)
}
```

### Sales Associate Object
```typescript
{
  id: number
  name: string
  email: string
  phone: string
  role: string
  status: 'active' | 'inactive'
  assignedLeads: number
  avatar: string | null
}
```

---

## CSV Import Format

Example CSV structure for lead import:

```csv
First Name,Last Name,Email,Phone,Company,Country,State,City,Lead Source,Lead Status,Lead Owner
John,Doe,john.doe@example.com,+1234567890,Example Corp,USA,California,Los Angeles,Website,Qualified,Sarah Johnson
```

---

## API Integration Points

All pages currently use mock data. To integrate with backend APIs:

### Lead Management:
- `GET /api/franchisor/leads` - Fetch leads with pagination and filters
- `POST /api/franchisor/leads` - Create new lead
- `PUT /api/franchisor/leads/:id` - Update lead
- `DELETE /api/franchisor/leads/:id` - Delete lead
- `POST /api/franchisor/leads/import` - Import leads from CSV

### Notes:
- `GET /api/franchisor/leads/:id/notes` - Fetch notes for a lead
- `POST /api/franchisor/leads/:id/notes` - Create new note
- `PUT /api/franchisor/notes/:id` - Update note
- `DELETE /api/franchisor/notes/:id` - Delete note

### Sales Associates:
- `GET /api/franchisor/sales-associates` - Fetch sales associates
- `POST /api/franchisor/sales-associates` - Create new associate
- `PUT /api/franchisor/sales-associates/:id` - Update associate
- `DELETE /api/franchisor/sales-associates/:id` - Delete associate

---

## File Structure

```
resources/ts/
├── pages/franchisor/
│   ├── sales-associates.vue
│   ├── lead-management.vue
│   ├── add-lead.vue
│   ├── lead-view-[id].vue
│   └── PHASE_02_README.md
├── views/franchisor/
│   └── add-lead/
│       ├── types.ts
│       ├── BasicInfo.vue
│       └── AdditionalDetails.vue
├── components/franchisor/
│   └── AddNoteModal.vue
└── navigation/vertical/
    └── franchisor.ts (updated)
```

---

## Styling & Components

- **Vuetify Components** - VCard, VDataTable, VTabs, VDialog, etc.
- **Custom Components** - AppTextField, AppSelect, AppTextarea, AppDateTimePicker
- **Icons** - Tabler icons throughout
- **Color Coding**:
  - Qualified status: Success (green)
  - Unqualified status: Error (red)
  - Active status: Success (green)
  - Inactive status: Secondary (gray)

---

## Known Issues & Linting

Some linting warnings exist:
- Missing utility functions (`avatarText`, `prefixWithPlus`) - should be imported from helpers
- `$vuetify` property access needs proper typing
- Some unused variables in lead-view page
- VDataTable import from labs may need adjustment based on Vuetify version

---

## Next Steps (Future Phases)

- Add form validation with error messages
- Implement real-time notifications for new leads
- Add lead scoring and qualification workflow
- Create lead assignment automation
- Add email integration for communication tracking
- Implement activity timeline on lead view page
- Add bulk actions (assign, delete, export selected)
- Create lead conversion tracking
- Add analytics dashboard for lead performance
- Implement advanced search with filters
- Add lead duplication detection
- Create custom fields for leads
- Add document management for attachments
- Implement lead nurturing campaigns

---

## Testing Checklist

- [ ] Create new lead through wizard
- [ ] Edit existing lead
- [ ] Delete lead with confirmation
- [ ] Add note to lead
- [ ] View and edit notes
- [ ] Delete note
- [ ] Import leads from CSV
- [ ] Export leads to CSV
- [ ] Search and filter leads
- [ ] Navigate between pages
- [ ] Test responsive design on mobile
- [ ] Verify all form validations
- [ ] Test file upload for attachments
