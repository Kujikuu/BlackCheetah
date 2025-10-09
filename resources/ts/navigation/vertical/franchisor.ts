export default [
  {
    title: 'Franchisor Dashboard',
    icon: { icon: 'tabler-building-community' },
    children: [
      {
        title: 'Overview',
        to: 'franchisor',
        // icon: { icon: 'tabler-dashboard' },
      },
      {
        title: 'Dashboard',
        icon: { icon: 'tabler-building-community' },
        children: [
          {
            title: 'Leads',
            to: 'franchisor-dashboard-leads',
            // icon: { icon: 'tabler-users' },
          },
          {
            title: 'Operations',
            to: 'franchisor-dashboard-operations',
            // icon: { icon: 'tabler-list-check' },
          },
          {
            title: 'Development Timeline',
            to: 'franchisor-dashboard-timeline',
            // icon: { icon: 'tabler-timeline' },
          },
          {
            title: 'Finance',
            to: 'franchisor-dashboard-finance',
            // icon: { icon: 'tabler-chart-line' },
          },
        ],
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
      },
      {
        title: 'My Franchise',
        to: 'franchisor-my-franchise',
        icon: { icon: 'tabler-building-store' },
      },
      {
        title: 'My Units',
        to: 'franchisor-my-units',
        icon: { icon: 'tabler-buildings' },
      },
      {
        title: 'Tasks Management',
        to: 'franchisor-tasks-management',
        icon: { icon: 'tabler-clipboard-list' },
      },
      {
        title: 'Performance Management',
        to: 'franchisor-performance-management',
        icon: { icon: 'tabler-chart-line' },
      },
      {
        title: 'Financial Overview',
        to: 'franchisor-financial-overview',
        icon: { icon: 'tabler-chart-pie' },
      },
      {
        title: 'Royalty Management',
        to: 'franchisor-royalty-management',
        icon: { icon: 'tabler-coins' },
      },
      {
        title: 'Technical Requests',
        to: 'franchisor-technical-requests',
        icon: { icon: 'tabler-headset' },
      }
    ],
  },
]
