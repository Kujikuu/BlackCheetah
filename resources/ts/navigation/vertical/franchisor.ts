export default [
  {
    title: 'Overview',
    to: 'franchisor',
    icon: { icon: 'tabler-dashboard' },
    action: 'read',
    subject: 'FranchisorDashboard',
  },
  {
    title: 'Dashboard',
    icon: { icon: 'tabler-building-community' },
    action: 'read',
    subject: 'FranchisorDashboard',
    children: [
      {
        title: 'Leads',
        to: 'franchisor-dashboard-leads',

        // icon: { icon: 'tabler-users' },
        action: 'read',
        subject: 'Lead',
      },
      {
        title: 'Operations',
        to: 'franchisor-dashboard-operations',

        // icon: { icon: 'tabler-list-check' },
        action: 'read',
        subject: 'FranchisorDashboard',
      },
      {
        title: 'Development Timeline',
        to: 'franchisor-dashboard-timeline',

        // icon: { icon: 'tabler-timeline' },
        action: 'read',
        subject: 'FranchisorDashboard',
      },
      {
        title: 'Finance',
        to: 'franchisor-dashboard-finance',

        // icon: { icon: 'tabler-chart-line' },
        action: 'read',
        subject: 'Revenue',
      },
    ],
  },
  {
    title: 'Brokers',
    to: 'franchisor-brokers',
    icon: { icon: 'tabler-user-star' },
    action: 'manage',
    subject: 'User',
  },
  {
    title: 'My Franchise',
    to: 'franchisor-my-franchise',
    icon: { icon: 'tabler-building-store' },
    action: 'read',
    subject: 'Franchise',
  },
  {
    title: 'My Units',
    to: 'franchisor-my-units',
    icon: { icon: 'tabler-buildings' },
    action: 'read',
    subject: 'Unit',
  },
  {
    title: 'Tasks Management',
    to: 'franchisor-tasks-management',
    icon: { icon: 'tabler-clipboard-list' },
    action: 'manage',
    subject: 'Task',
  },
  {
    title: 'Performance Management',
    to: 'franchisor-performance-management',
    icon: { icon: 'tabler-chart-line' },
    action: 'read',
    subject: 'Performance',
  },
  {
    title: 'Financial Overview',
    to: 'franchisor-financial-overview',
    icon: { icon: 'tabler-chart-pie' },
    action: 'read',
    subject: 'Revenue',
  },
  {
    title: 'Royalty Management',
    to: 'franchisor-royalty-management',
    icon: { icon: 'tabler-coins' },
    action: 'manage',
    subject: 'Royalty',
  },
  {
    title: 'Technical Requests',
    to: 'franchisor-technical-requests',
    icon: { icon: 'tabler-headset' },
    action: 'create',
    subject: 'TechnicalRequest',
  },
]
