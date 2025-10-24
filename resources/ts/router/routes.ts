import type { RouteRecordRaw } from 'vue-router'

const routes: RouteRecordRaw[] = [
  // Root redirect - Show landing page for all
  {
    path: '/',
    name: 'index',
    component: () => import('@/pages/front-pages/landing-page/index.vue'),
    meta: {
      layout: 'blank',
      public: true,
    },
  },

  // Authentication Routes
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/login.vue'),
    meta: {
      layout: 'blank',
      public: true,
      unauthenticatedOnly: true,
    },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/pages/register.vue'),
    meta: {
      layout: 'blank',
      public: true,
      unauthenticatedOnly: true,
    },
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: () => import('@/pages/forgot-password.vue'),
    meta: {
      layout: 'blank',
      public: true,
      unauthenticatedOnly: true,
    },
  },
  {
    path: '/reset-password',
    name: 'reset-password',
    component: () => import('@/pages/reset-password.vue'),
    meta: {
      layout: 'blank',
      public: true,
      unauthenticatedOnly: true,
    },
  },
  {
    path: '/two-steps',
    name: 'two-steps',
    component: () => import('@/pages/two-steps.vue'),
    meta: {
      layout: 'blank',
      public: true,
    },
  },
  {
    path: '/verify-email',
    name: 'verify-email',
    component: () => import('@/pages/verify-email.vue'),
    meta: {
      layout: 'blank',
      public: true,
    },
  },

  // Marketplace - Public
  {
    path: '/marketplace',
    name: 'marketplace',
    component: () => import('@/pages/front-pages/marketplace.vue'),
    meta: {
      layout: 'blank',
      public: true,
    },
  },
  {
    path: '/marketplace/franchise/:id',
    name: 'marketplace-franchise-details',
    component: () => import('@/pages/front-pages/marketplace-franchise-details.vue'),
    meta: {
      layout: 'blank',
      public: true,
    },
  },
  {
    path: '/marketplace/property/:id',
    name: 'marketplace-property-details',
    component: () => import('@/pages/front-pages/marketplace-property-details.vue'),
    meta: {
      layout: 'blank',
      public: true,
    },
  },

  // Onboarding
  {
    path: '/onboarding',
    name: 'onboarding',
    component: () => import('@/pages/onboarding.vue'),
    meta: {
      layout: 'blank',
    },
  },

  // Admin Routes
  {
    path: '/admin/dashboard',
    name: 'admin-dashboard',
    component: () => import('@/pages/admin/dashboard.vue'),
    meta: {
      action: 'read',
      subject: 'AdminDashboard',
    },
  },
  {
    path: '/admin/technical-requests',
    name: 'admin-technical-requests',
    component: () => import('@/pages/admin/technical-requests.vue'),
    meta: {
      action: 'manage',
      subject: 'TechnicalRequest',
    },
  },
  {
    path: '/admin/users/franchisors',
    name: 'admin-users-franchisors',
    component: () => import('@/pages/admin/users/franchisors.vue'),
    meta: {
      action: 'manage',
      subject: 'User',
    },
  },
  {
    path: '/admin/users/franchisees',
    name: 'admin-users-franchisees',
    component: () => import('@/pages/admin/users/franchisees.vue'),
    meta: {
      action: 'manage',
      subject: 'User',
    },
  },
  {
    path: '/admin/users/brokers',
    name: 'admin-users-brokers',
    component: () => import('@/pages/admin/users/brokers.vue'),
    meta: {
      action: 'manage',
      subject: 'User',
    },
  },

  // Franchisor Routes
  {
    path: '/franchisor',
    name: 'franchisor',
    component: () => import('@/pages/franchisor/index.vue'),
    meta: {
      action: 'read',
      subject: 'FranchisorDashboard',
    },
  },
  {
    path: '/franchisor/franchise-registration',
    name: 'franchisor-franchise-registration',
    component: () => import('@/pages/franchisor/franchise-registration.vue'),
    meta: {
      action: 'manage',
      subject: 'Franchise',
    },
  },
  {
    path: '/franchisor/add-lead',
    name: 'franchisor-add-lead',
    component: () => import('@/pages/franchisor/add-lead.vue'),
    meta: {
      action: 'manage',
      subject: 'Lead',
    },
  },
  {
    path: '/franchisor/lead-management',
    name: 'franchisor-lead-management',
    component: () => import('@/pages/franchisor/lead-management.vue'),
    meta: {
      action: 'manage',
      subject: 'Lead',
    },
  },
  {
    path: '/franchisor/leads/:id',
    name: 'franchisor-leads-id',
    component: () => import('@/pages/franchisor/leads/[id].vue'),
    meta: {
      action: 'manage',
      subject: 'Lead',
    },
  },
  {
    path: '/franchisor/my-franchise',
    name: 'franchisor-my-franchise',
    component: () => import('@/pages/franchisor/my-franchise.vue'),
    meta: {
      action: 'read',
      subject: 'Franchise',
    },
  },
  {
    path: '/franchisor/franchise-staff',
    name: 'franchisor-franchise-staff',
    component: () => import('@/pages/franchisor/franchise-staff.vue'),
    meta: {
      action: 'read',
      subject: 'Franchise',
    },
  },
  {
    path: '/franchisor/my-units',
    name: 'franchisor-my-units',
    component: () => import('@/pages/franchisor/my-units.vue'),
    meta: {
      action: 'read',
      subject: 'Unit',
    },
  },
  {
    path: '/franchisor/units/:id',
    name: 'franchisor-units-id',
    component: () => import('@/pages/franchisor/units/[id].vue'),
    meta: {
      action: 'read',
      subject: 'Unit',
    },
  },
  {
    path: '/franchisor/brokers',
    name: 'franchisor-brokers',
    component: () => import('@/pages/franchisor/brokers.vue'),
    meta: {
      action: 'manage',
      subject: 'User',
    },
  },
  {
    path: '/franchisor/tasks-management',
    name: 'franchisor-tasks-management',
    component: () => import('@/pages/franchisor/tasks-management.vue'),
    meta: {
      action: 'manage',
      subject: 'Task',
    },
  },
  {
    path: '/franchisor/performance-management',
    name: 'franchisor-performance-management',
    component: () => import('@/pages/franchisor/performance-management.vue'),
    meta: {
      action: 'read',
      subject: 'Performance',
    },
  },
  {
    path: '/franchisor/royalty-management',
    name: 'franchisor-royalty-management',
    component: () => import('@/pages/franchisor/royalty-management.vue'),
    meta: {
      action: 'manage',
      subject: 'Royalty',
    },
  },
  {
    path: '/franchisor/financial-overview',
    name: 'franchisor-financial-overview',
    component: () => import('@/pages/franchisor/financial-overview.vue'),
    meta: {
      action: 'read',
      subject: 'Revenue',
    },
  },
  {
    path: '/franchisor/technical-requests',
    name: 'franchisor-technical-requests',
    component: () => import('@/pages/franchisor/technical-requests.vue'),
    meta: {
      action: 'create',
      subject: 'TechnicalRequest',
    },
  },
  {
    path: '/franchisor/dashboard/leads',
    name: 'franchisor-dashboard-leads',
    component: () => import('@/pages/franchisor/dashboard/leads.vue'),
    meta: {
      action: 'read',
      subject: 'Lead',
    },
  },
  {
    path: '/franchisor/dashboard/operations',
    name: 'franchisor-dashboard-operations',
    component: () => import('@/pages/franchisor/dashboard/operations.vue'),
    meta: {
      action: 'read',
      subject: 'FranchisorDashboard',
    },
  },
  {
    path: '/franchisor/dashboard/finance',
    name: 'franchisor-dashboard-finance',
    component: () => import('@/pages/franchisor/dashboard/finance.vue'),
    meta: {
      action: 'read',
      subject: 'Revenue',
    },
  },
  {
    path: '/franchisor/dashboard/timeline',
    name: 'franchisor-dashboard-timeline',
    component: () => import('@/pages/franchisor/dashboard/timeline.vue'),
    meta: {
      action: 'read',
      subject: 'FranchisorDashboard',
    },
  },

  // Franchisee Routes
  {
    path: '/franchisee/dashboard/sales',
    name: 'franchisee-dashboard-sales',
    component: () => import('@/pages/franchisee/dashboard/sales.vue'),
    meta: {
      action: 'read',
      subject: 'FranchiseeDashboard',
    },
  },
  {
    path: '/franchisee/dashboard/operations',
    name: 'franchisee-dashboard-operations',
    component: () => import('@/pages/franchisee/dashboard/operations.vue'),
    meta: {
      action: 'read',
      subject: 'FranchiseeDashboard',
    },
  },
  {
    path: '/franchisee/dashboard/finance',
    name: 'franchisee-dashboard-finance',
    component: () => import('@/pages/franchisee/dashboard/finance.vue'),
    meta: {
      action: 'read',
      subject: 'Revenue',
    },
  },
  {
    path: '/franchisee/unit-operation',
    name: 'franchisee-unit-operation',
    component: () => import('@/pages/franchisee/unit-operation.vue'),
    meta: {
      action: 'read',
      subject: 'Unit',
    },
  },
  {
    path: '/franchisee/my-tasks',
    name: 'franchisee-my-tasks',
    component: () => import('@/pages/franchisee/my-tasks.vue'),
    meta: {
      action: 'read',
      subject: 'Task',
    },
  },
  {
    path: '/franchisee/performance-management',
    name: 'franchisee-performance-management',
    component: () => import('@/pages/franchisee/performance-management.vue'),
    meta: {
      action: 'read',
      subject: 'Performance',
    },
  },
  {
    path: '/franchisee/royalty-management',
    name: 'franchisee-royalty-management',
    component: () => import('@/pages/franchisee/royalty-management.vue'),
    meta: {
      action: 'read',
      subject: 'Royalty',
    },
  },
  {
    path: '/franchisee/financial-overview',
    name: 'franchisee-financial-overview',
    component: () => import('@/pages/franchisee/financial-overview.vue'),
    meta: {
      action: 'read',
      subject: 'Revenue',
    },
  },
  {
    path: '/franchisee/technical-requests',
    name: 'franchisee-technical-requests',
    component: () => import('@/pages/franchisee/technical-requests.vue'),
    meta: {
      action: 'create',
      subject: 'TechnicalRequest',
    },
  },

  // Broker Routes
  {
    path: '/brokers/properties',
    name: 'broker-properties',
    component: () => import('@/pages/broker/properties.vue'),
    meta: {
      action: 'manage',
      subject: 'Property',
    },
  },
  {
    path: '/brokers/lead-management',
    name: 'broker-lead-management',
    component: () => import('@/pages/brokers/lead-management.vue'),
    meta: {
      action: 'manage',
      subject: 'Lead',
    },
  },
  {
    path: '/brokers/add-lead',
    name: 'broker-add-lead',
    component: () => import('@/pages/brokers/add-lead.vue'),
    meta: {
      action: 'manage',
      subject: 'Lead',
    },
  },
  {
    path: '/brokers/leads/:id',
    name: 'broker-leads-id',
    component: () => import('@/pages/brokers/leads/[id].vue'),
    meta: {
      action: 'manage',
      subject: 'Lead',
    },
  },
  {
    path: '/brokers/my-tasks',
    name: 'broker-my-tasks',
    component: () => import('@/pages/brokers/my-tasks.vue'),
    meta: {
      action: 'read',
      subject: 'Task',
    },
  },
  {
    path: '/brokers/technical-requests',
    name: 'broker-technical-requests',
    component: () => import('@/pages/brokers/technical-requests.vue'),
    meta: {
      action: 'create',
      subject: 'TechnicalRequest',
    },
  },

  // General Pages
  {
    path: '/account-settings/:tab',
    name: 'pages-account-settings-tab',
    component: () => import('@/pages/account-settings/[tab].vue'),
    // meta: {
    //   action: 'manage',
    //   subject: 'AccountSettings',
    // },
  },
  {
    path: '/account-settings',
    name: 'pages-account-settings',
    redirect: () => ({ name: 'pages-account-settings-tab', params: { tab: 'account' } }),
  },
  {
    path: '/access-control',
    name: 'access-control',
    component: () => import('@/pages/access-control.vue'),
    meta: {
      action: 'read',
      subject: 'AccessControl',
    },
  },
  {
    path: '/not-authorized',
    name: 'not-authorized',
    component: () => import('@/pages/not-authorized.vue'),
    meta: {
      layout: 'blank',
      public: true,
    },
  },

  // Error Pages
  {
    path: '/:pathMatch(.*)*',
    name: 'error',
    component: () => import('@/pages/[...error].vue'),
    meta: {
      layout: 'blank',
      public: true,
    },
  },
]

export default routes

