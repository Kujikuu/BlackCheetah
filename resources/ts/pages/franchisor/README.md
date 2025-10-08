# Franchisor Dashboard - Complete Implementation

This directory contains the complete franchisor dashboard implementation for the BlackCheetah project, including Phase 01 and Phase 02.

## Project Structure

```
resources/ts/pages/franchisor/
├── dashboard/                      # Phase 01 - Dashboard pages
│   ├── leads.vue                  # Dashboard leads overview
│   ├── operations.vue             # Operations management with tabs
│   ├── timeline.vue               # Development timeline
│   ├── finance.vue                # Financial analytics
│   └── README.md                  # Phase 01 documentation
├── sales-associates.vue           # Phase 02 - Sales associates management
├── lead-management.vue            # Phase 02 - Lead management system
├── add-lead.vue                   # Phase 02 - Add lead wizard
├── lead-view-[id].vue            # Phase 02 - Lead detail view
├── README.md                      # This file
└── PHASE_02_README.md            # Phase 02 detailed documentation
```

## Phase 01: Franchisor Dashboard Setup 01

### Pages Created:
1. **Leads** (`dashboard/leads.vue`) - Lead tracking with stats and table
2. **Operations** (`dashboard/operations.vue`) - Task management with 3 tabs (Franchisee, Sales Associate, Staff)
3. **Development Timeline** (`dashboard/timeline.vue`) - Project milestones and timeline
4. **Finance** (`dashboard/finance.vue`) - Financial analytics with charts

**Routes:**
- `franchisor-dashboard-leads`
- `franchisor-dashboard-operations`
- `franchisor-dashboard-timeline`
- `franchisor-dashboard-finance`

**Badge:** Phase 01 (Primary color)

---

## Phase 02: Franchisor Dashboard Setup 02

### Pages Created:
1. **Sales Associates** (`sales-associates.vue`) - Sales team management
2. **Lead Management** (`lead-management.vue`) - Comprehensive lead tracking system
3. **Add Lead** (`add-lead.vue`) - Multi-step wizard for creating leads
4. **Lead View** (`lead-view-[id].vue`) - Detailed lead view with tabs (Overview, Notes)

### Components Created:
- **AddNoteModal** (`/components/franchisor/AddNoteModal.vue`) - Modal for adding notes to leads

### Supporting Files:
- `/views/franchisor/add-lead/BasicInfo.vue` - Wizard step 1
- `/views/franchisor/add-lead/AdditionalDetails.vue` - Wizard step 2
- `/views/franchisor/add-lead/types.ts` - TypeScript interfaces

**Routes:**
- `franchisor-sales-associates`
- `franchisor-lead-management`
- `franchisor-add-lead`
- `franchisor-lead-view-id` (dynamic)

**Badge:** Phase 02 (Success color)

---

## Navigation Structure

The franchisor navigation is organized as follows:

```
📁 Franchisor Dashboard
├── 📊 Dashboard [Phase 01]
│   ├── 👥 Leads
│   ├── ✅ Operations
│   ├── 📅 Development Timeline
│   └── 💰 Finance
├── ⭐ Sales Associates
└── 🔍 Lead Management [Phase 02]
    ├── 📋 All Leads
    └── ➕ Add Lead
```

---

## Key Features

### Phase 01 Features:
- ✅ Dashboard stat cards with change indicators
- ✅ Data tables with sorting, filtering, and pagination
- ✅ Tab-based operations management
- ✅ Timeline visualization with status filters
- ✅ Financial charts (bar charts, line charts)
- ✅ Export functionality
- ✅ Responsive design

### Phase 02 Features:
- ✅ Sales associate management
- ✅ Lead management with 3 stat cards
- ✅ Multi-step wizard for lead creation
- ✅ CSV import with example file download
- ✅ Lead view with editable fields
- ✅ Notes system with CRUD operations
- ✅ Delete confirmations
- ✅ Dynamic routing for lead details
- ✅ Modal-based note management

---

## Technologies Used

- **Vue 3** - Composition API with `<script setup>`
- **TypeScript** - Type-safe development
- **Vuetify 3** - Material Design components
- **VueApexCharts** - Data visualization (Phase 01)
- **Vue Router** - Navigation and routing
- **Tabler Icons** - Icon library

---

## Quick Start

### Accessing the Dashboard:

1. Navigate to the Franchisor Dashboard section in the sidebar
2. Select from Phase 01 dashboard pages or Phase 02 lead management

### Creating a New Lead:

1. Go to **Lead Management** → **Add Lead**
2. Fill in **Basic Information** (Step 1)
3. Complete **Additional Details** (Step 2)
4. Click **Submit**

### Managing Leads:

1. Go to **Lead Management** → **All Leads**
2. Use filters to narrow down results
3. Click on a lead name to view details
4. Use the actions menu for View/Edit, Add Note, or Delete

### Adding Notes:

1. Open a lead from the Lead Management page
2. Switch to the **Notes** tab
3. Click **Add Note** button
4. Fill in title and description
5. Click **Add Note** to save

---

## Data Flow

### Lead Management Flow:
```
Lead Management Page
    ↓
[Add Lead] → Add Lead Wizard → Submit → Back to Lead Management
    ↓
[View Lead] → Lead View Page
    ↓
    ├── Overview Tab (Edit lead details)
    └── Notes Tab
        ↓
        [Add Note] → Add Note Modal → Save → Refresh notes
```

---

## API Integration Guide

All pages currently use mock data. To connect to your backend:

1. Replace `ref()` data with `useApi()` or `$api()` calls
2. Implement proper error handling
3. Add loading states
4. Update data fetching logic in computed properties
5. Add success/error notifications

### Example API Integration:

```typescript
// Before (Mock data)
const leadsData = ref({
  leads: [...],
  total: 3
})

// After (API integration)
const { data: leadsData, execute: fetchLeads } = await useApi<any>(
  createUrl('/api/franchisor/leads', {
    query: {
      q: searchQuery,
      status: selectedStatus,
      page,
      itemsPerPage,
    },
  })
)
```

---

## Customization

### Adding New Stat Cards:
Edit the `widgetData` or `statsData` ref in the respective page.

### Adding Table Columns:
Update the `headers` array with new column definitions.

### Adding Filters:
Add new filter refs and include them in the filters section.

### Modifying Wizard Steps:
Edit the step components in `/views/franchisor/add-lead/` and update the wizard configuration.

---

## Troubleshooting

### Common Issues:

1. **Missing utility functions** (`avatarText`, `prefixWithPlus`)
   - Import from your project's utility/helper files

2. **VDataTable import errors**
   - Ensure Vuetify is properly configured
   - Check if using labs version: `import { VDataTableServer } from 'vuetify/labs/VDataTable'`

3. **$vuetify property errors**
   - Add proper TypeScript definitions for Vuetify

4. **Route not found**
   - Ensure routes are registered in your router configuration
   - Check route names match navigation file

---

## Documentation

- **Phase 01 Details:** See `dashboard/README.md`
- **Phase 02 Details:** See `PHASE_02_README.md`

---

## Future Enhancements

### Planned Features:
- Lead scoring and qualification automation
- Email integration for communication tracking
- Advanced analytics and reporting
- Bulk operations for leads
- Custom fields and forms
- Document management
- Lead nurturing campaigns
- Mobile app integration
- Real-time notifications
- Activity feeds and audit logs

---

## Support & Maintenance

### Code Quality:
- All components use TypeScript for type safety
- Composition API with `<script setup>` syntax
- Consistent naming conventions
- Modular component structure

### Performance:
- Lazy loading for routes
- Computed properties for derived data
- Efficient data table rendering
- Optimized chart rendering

---

## Version History

- **Phase 01** - Initial dashboard setup with 4 core pages
- **Phase 02** - Lead management system with sales associates

---

## Contributing

When adding new features:
1. Follow existing code structure and patterns
2. Use TypeScript interfaces for data types
3. Add proper documentation
4. Test responsive design
5. Update navigation if adding new pages
6. Update this README with changes

---

## License

Part of the BlackCheetah project.
