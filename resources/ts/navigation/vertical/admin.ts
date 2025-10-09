export default [
  {
    title: 'Admin',
    icon: { icon: 'tabler-shield-lock' },
    children: [
      {
        title: 'Dashboard',
        to: 'admin-dashboard',
        icon: { icon: 'tabler-dashboard' },
      },
      {
        title: 'User Management',
        icon: { icon: 'tabler-users' },
        children: [
          {
            title: 'Franchisors',
            to: 'admin-users-franchisors',
            icon: { icon: 'tabler-building-store' },
          },
          {
            title: 'Franchisees',
            to: 'admin-users-franchisees',
            icon: { icon: 'tabler-user-check' },
          },
          {
            title: 'Sales Team',
            to: 'admin-users-sales',
            icon: { icon: 'tabler-chart-line' },
          },
        ],
      },
      {
        title: 'Technical Requests',
        to: 'admin-technical-requests',
        icon: { icon: 'tabler-headset' },
      },
    ]
  },
]
