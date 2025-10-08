import type { RouteRecordRaw } from 'vue-router/auto'

const emailRouteComponent = () => import('@/pages/apps/email/index.vue')

// 👉 Redirects
export const redirects: RouteRecordRaw[] = [
  // ℹ️ We are redirecting to different pages based on role.
  // NOTE: Role is just for UI purposes. ACL is based on abilities.
  {
    path: '/',
    name: 'index',
    redirect: to => {
      // TODO: Get type from backend
      const userData = useCookie<Record<string, unknown> | null | undefined>('userData')
      const userRole = userData.value?.role

      if (userRole === 'admin')
        return { name: 'admin-dashboard' }
      if (userRole === 'client')
        return { name: 'access-control' }

      return { name: 'login', query: to.query }
    },
  },
  {
    path: '/pages/user-profile',
    name: 'pages-user-profile',
    redirect: () => ({ name: 'pages-user-profile-tab', params: { tab: 'profile' } }),
  },
  {
    path: '/pages/account-settings',
    name: 'pages-account-settings',
    redirect: () => ({ name: 'pages-account-settings-tab', params: { tab: 'account' } }),
  },
]

export const routes: RouteRecordRaw[] = [
  // Email filter
  {
    path: '/apps/email/filter/:filter',
    name: 'apps-email-filter',
    component: emailRouteComponent,
    meta: {
      navActiveLink: 'apps-email',
      layoutWrapperClasses: 'layout-content-height-fixed',
    },
  },

  // Email label
  {
    path: '/apps/email/label/:label',
    name: 'apps-email-label',
    component: emailRouteComponent,
    meta: {
      // contentClass: 'email-application',
      navActiveLink: 'apps-email',
      layoutWrapperClasses: 'layout-content-height-fixed',
    },
  },

  {
    path: '/dashboards/logistics',
    name: 'dashboards-logistics',
    component: () => import('@/pages/apps/logistics/dashboard.vue'),
  },
  {
    path: '/dashboards/academy',
    name: 'dashboards-academy',
    component: () => import('@/pages/apps/academy/dashboard.vue'),
  },
  {
    path: '/apps/ecommerce/dashboard',
    name: 'apps-ecommerce-dashboard',
    component: () => import('@/pages/dashboards/ecommerce.vue'),
  },

  // Franchisor Dashboard - Phase 01
  {
    path: '/franchisor/dashboard/leads',
    name: 'franchisor-dashboard-leads',
    component: () => import('@/pages/franchisor/dashboard/leads.vue'),
  },
  {
    path: '/franchisor/dashboard/operations',
    name: 'franchisor-dashboard-operations',
    component: () => import('@/pages/franchisor/dashboard/operations.vue'),
  },
  {
    path: '/franchisor/dashboard/timeline',
    name: 'franchisor-dashboard-timeline',
    component: () => import('@/pages/franchisor/dashboard/timeline.vue'),
  },
  {
    path: '/franchisor/dashboard/finance',
    name: 'franchisor-dashboard-finance',
    component: () => import('@/pages/franchisor/dashboard/finance.vue'),
  },

  // Franchisor - Phase 02
  {
    path: '/franchisor/sales-associates',
    name: 'franchisor-sales-associates',
    component: () => import('@/pages/franchisor/sales-associates.vue'),
  },
  {
    path: '/franchisor/lead-management',
    name: 'franchisor-lead-management',
    component: () => import('@/pages/franchisor/lead-management.vue'),
  },
  {
    path: '/franchisor/add-lead',
    name: 'franchisor-add-lead',
    component: () => import('@/pages/franchisor/add-lead.vue'),
  },
  {
    path: '/franchisor/lead/:id',
    name: 'franchisor-lead-view-id',
    component: () => import('@/pages/franchisor/lead-view-[id].vue'),
  },
]
