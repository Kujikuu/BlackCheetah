export default [
  {
    title: 'Assigned Franchises',
    to: 'broker-assigned-franchises',
    icon: { icon: 'tabler-building-store' },
    action: 'manage',
    subject: 'Franchise',
  },
  {
    title: 'Lead Management',
    to: 'broker-lead-management',
    icon: { icon: 'tabler-user-search' },
    action: 'manage',
    subject: 'Lead',
  },
  {
    title: 'My Tasks',
    to: 'broker-my-tasks',
    icon: { icon: 'tabler-clipboard-list' },
    action: 'read',
    subject: 'Task',
  },
  {
    title: 'Technical Requests',
    to: 'broker-technical-requests',
    icon: { icon: 'tabler-headset' },
    action: 'create',
    subject: 'TechnicalRequest',
  },
  {
    title: 'Properties',
    to: 'broker-properties',
    icon: { icon: 'tabler-map-pin' },
    action: 'manage',
    subject: 'Property',
  }
]
