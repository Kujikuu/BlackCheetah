export default [
  {
    title: 'Dashboard',
    icon: { icon: 'tabler-building-community' },
    children: [
      {
        title: 'Sales',
        to: 'franchisee-dashboard-sales',
        // icon: { icon: 'tabler-users' },
      },
      {
        title: 'Operations',
        to: 'franchisee-dashboard-operations',
        // icon: { icon: 'tabler-list-check' },
      },
      {
        title: 'Finance',
        to: 'franchisee-dashboard-finance',
        // icon: { icon: 'tabler-chart-line' },
      },
    ],
  },
  {
    title: 'Unit Operation',
    to: 'franchisee-unit-operation',
    icon: { icon: 'tabler-buildings' },
  },
  {
    title: 'My Tasks',
    to: 'franchisee-my-tasks',
    icon: { icon: 'tabler-clipboard-list' },
  },
  {
    title: 'Performance Management',
    to: 'franchisee-performance-management',
    icon: { icon: 'tabler-chart-line' },
  },
  {
    title: 'Financial Overview',
    to: 'franchisee-financial-overview',
    icon: { icon: 'tabler-chart-pie' },
  },
  {
    title: 'Royalty Management',
    to: 'franchisee-royalty-management',
    icon: { icon: 'tabler-coins' },
  },
  {
    title: 'Technical Requests',
    to: 'franchisee-technical-requests',
    icon: { icon: 'tabler-headset' },
  }
]
