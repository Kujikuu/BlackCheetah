export default [
  {
    title: 'Dashboard',
    to: 'admin-dashboard',
    icon: { icon: 'tabler-dashboard' },
    action: 'read',
    subject: 'AdminDashboard',
  },
  {
    title: 'User Management',
    icon: { icon: 'tabler-users' },
    action: 'manage',
    subject: 'User',
    children: [
      {
        title: 'Franchisors',
        to: 'admin-users-franchisors',
        icon: { icon: 'tabler-building-store' },
        action: 'manage',
        subject: 'User',
      },
      {
        title: 'Franchisees',
        to: 'admin-users-franchisees',
        icon: { icon: 'tabler-user-check' },
        action: 'manage',
        subject: 'User',
      },
      {
        title: 'Brokers',
        to: 'admin-users-brokers',
        icon: { icon: 'tabler-chart-line' },
        action: 'manage',
        subject: 'User',
      },
    ],
  },
  {
    title: 'Technical Requests',
    to: 'admin-technical-requests',
    icon: { icon: 'tabler-headset' },
    action: 'manage',
    subject: 'TechnicalRequest',
  },
]
