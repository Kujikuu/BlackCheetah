# Franchisor Dashboard - Phase 01

This directory contains the franchisor dashboard implementation for the BlackCheetah project.

## Overview

The franchisor dashboard provides comprehensive tools for franchise management, including lead tracking, operations management, development timeline monitoring, and financial analytics.

## Pages

All pages are located in `/pages/franchisor/dashboard/`

### 1. Leads Page (`leads.vue`)
**Route:** `franchisor-dashboard-leads`

**Features:**
- 4 Stat Cards:
  - Total Leads
  - Closed and Won
  - Closed and Lost
  - Pending Leads
- Leads Table with columns:
  - Lead Name
  - Email
  - Phone
  - Source
  - Status
  - Created Date
  - Actions
- Filters: Source, Status
- Search functionality
- Export capability
- Add new lead button

**Template:** Based on `/pages/apps/user/list/index.vue`

### 2. Operations Page (`operations.vue`)
**Route:** `franchisor-dashboard-operations`

**Features:**
- 3 Tabs:
  - Franchisee
  - Broker
  - Staff
- Each tab includes:
  - 4 Stat Cards (Total, Completed, In Progress, Due)
  - Operations table with columns:
    - Task
    - Assigned To
    - Priority
    - Status
    - Due Date
    - Actions
- Filters: Priority, Status
- Search functionality
- Export capability
- Add new task button

**Template:** Based on `/pages/apps/user/list/index.vue`

### 3. Development Timeline Page (`timeline.vue`)
**Route:** `franchisor-dashboard-timeline`

**Features:**
- 4 Stat Cards:
  - Total Milestones
  - Completed
  - Scheduled
  - Overdue
- Timeline view with:
  - Week indicators
  - Status badges
  - Action buttons
- Filters:
  - All
  - Completed
  - Scheduled
  - Overdue
- Timeline items include:
  - Title
  - Description
  - Week/Date
  - Status
  - Icon
  - Action buttons

**Template:** Based on `/views/demos/components/timeline/TimelineWithIcons.vue`

### 4. Finance Page (`finance.vue`)
**Route:** `franchisor-dashboard-finance`

**Features:**
- 4 Stat Cards:
  - Total Sales
  - Total Expenses
  - Total Royalties
  - Total Profit
- Charts:
  - Top 5 Stores by Monthly Sales (Horizontal Bar Chart)
  - Top 5 Stores by Monthly Royalty (Horizontal Bar Chart)
  - Financial Summary Chart (Line Chart with 4 series)
- Summary Table:
  - Monthly breakdown
  - Sales, Expenses, Royalties, Profit columns
  - Color-coded values

**Template:** Based on `/pages/apps/logistics/dashboard.vue`

## Navigation

The franchisor navigation is defined in `/navigation/vertical/franchisor.ts` and includes:
- Main section: "Franchisor Dashboard"
- Badge: "Phase 01"
- Nested structure with Dashboard submenu containing all 4 pages

## Data Structure

All pages currently use mock data. To integrate with real APIs:

1. Replace mock data refs with API calls using `useApi` or `$api`
2. Update the data fetching logic in each component
3. Implement proper error handling
4. Add loading states

## Styling

- Uses Vuetify components
- Custom styles for stat cards with hover effects
- Responsive design for mobile and tablet views
- ApexCharts for data visualization

## Dependencies

- Vue 3
- Vuetify 3
- VueApexCharts
- TypeScript

## Next Steps (Future Phases)

- Connect to backend APIs
- Add real-time data updates
- Implement user permissions
- Add data export functionality
- Create detailed view pages for each entity
- Add form validation
- Implement advanced filtering and sorting
- Add notification system

## Notes

- Some linting warnings exist for missing utility functions (`prefixWithPlus`, `avatarText`)
- These utilities should be imported from the appropriate helper files
- The `$vuetify` property access in timeline.vue may need proper typing
