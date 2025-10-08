export default [
  {
    title: 'Admin',
    icon: { icon: 'tabler-shield-lock' },
    children: [
      {
        title: 'Dashboard',
        to: 'admin-dashboard',
      },
      {
        title: 'Franchisors',
        to: 'admin-users-franchisors',
      },
      {
        title: 'Franchisees',
        to: 'admin-users-franchisees',
      },
      {
        title: 'Sales Team',
        to: 'admin-users-sales',
      },
      {
        title: 'Technical Requests',
        to: 'admin-technical-requests',
      },
    ],
  },
]
