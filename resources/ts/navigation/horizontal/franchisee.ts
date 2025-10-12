export default [
  {
    title: 'Dashboard',
    icon: { icon: 'tabler-building-community' },
    action: 'read',
    subject: 'FranchiseeDashboard',
    children: [
      {
        title: 'Sales',
        to: 'franchisee-dashboard-sales',

        // icon: { icon: 'tabler-users' },
        action: 'read',
        subject: 'FranchiseeDashboard',
      },
      {
        title: 'Operations',
        to: 'franchisee-dashboard-operations',

        // icon: { icon: 'tabler-list-check' },
        action: 'read',
        subject: 'FranchiseeDashboard',
      },
      {
        title: 'Finance',
        to: 'franchisee-dashboard-finance',

        // icon: { icon: 'tabler-chart-line' },
        action: 'read',
        subject: 'Revenue',
      },
    ],
  },
  {
    title: 'Unit Operation',
    to: 'franchisee-unit-operation',
    icon: { icon: 'tabler-buildings' },
    action: 'read',
    subject: 'Unit',
  },
  {
    title: 'My Tasks',
    to: 'franchisee-my-tasks',
    icon: { icon: 'tabler-clipboard-list' },
    action: 'read',
    subject: 'Task',
  },
  {
    title: 'Performance Management',
    to: 'franchisee-performance-management',
    icon: { icon: 'tabler-chart-line' },
    action: 'read',
    subject: 'Performance',
  },
  {
    title: 'Financial Overview',
    to: 'franchisee-financial-overview',
    icon: { icon: 'tabler-chart-pie' },
    action: 'read',
    subject: 'Revenue',
  },
  {
    title: 'Royalty Management',
    to: 'franchisee-royalty-management',
    icon: { icon: 'tabler-coins' },
    action: 'read',
    subject: 'Royalty',
  },
  {
    title: 'Technical Requests',
    to: 'franchisee-technical-requests',
    icon: { icon: 'tabler-headset' },
    action: 'create',
    subject: 'TechnicalRequest',
  },
]
