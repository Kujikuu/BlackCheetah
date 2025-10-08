export default [
  {
    title: 'Franchisor Dashboard',
    icon: { icon: 'tabler-building-community' },
    children: [
      {
        title: 'Dashboard',
        icon: { icon: 'tabler-building-community' },
        children: [
          {
            title: 'Leads',
            to: 'franchisor-dashboard-leads',
            icon: { icon: 'tabler-users' },
          },
          {
            title: 'Operations',
            to: 'franchisor-dashboard-operations',
            icon: { icon: 'tabler-list-check' },
          },
          {
            title: 'Development Timeline',
            to: 'franchisor-dashboard-timeline',
            icon: { icon: 'tabler-timeline' },
          },
          {
            title: 'Finance',
            to: 'franchisor-dashboard-finance',
            icon: { icon: 'tabler-chart-line' },
          },
        ],
        badgeContent: 'Phase 01',
        badgeClass: 'bg-primary',
      },
      {
        title: 'Sales Associates',
        to: 'franchisor-sales-associates',
        icon: { icon: 'tabler-user-star' },
      },
      {
        title: 'Lead Management',
        to: 'franchisor-lead-management',
        icon: { icon: 'tabler-user-search' },
        badgeContent: 'Phase 02',
        badgeClass: 'bg-success',
      },
    ],
  },
]
