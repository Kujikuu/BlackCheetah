import type { RouteRecordRaw } from 'vue-router/auto'

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

      // Redirect franchisor to their dashboard/landing
      if (userRole === 'franchisor')
        return { name: 'franchisor' }

      // Redirect franchisee to their dashboard/landing
      if (userRole === 'franchisee')
        return { name: 'franchisee-dashboard-sales' }

      // Redirect sales to lead management
      if (userRole === 'sales')
        return { name: 'sales-lead-management' }

      return { name: 'login', query: to.query }
    },
  },
]

export const routes: RouteRecordRaw[] = [

]
