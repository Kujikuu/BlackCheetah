import admin from '@/navigation/vertical/admin'
import franchisee from '@/navigation/vertical/franchisee'
import franchisor from '@/navigation/vertical/franchisor'
import sales from '@/navigation/vertical/sales'
import type { VerticalNavItems } from '@layouts/types'

/**
 * Composable to get navigation items based on user role
 */
export const useNavigation = () => {
  const userData = useCookie<any>('userData')

  // Reactive navigation items that update when user data changes
  const navigationItems = computed(() => {
    const userRole = userData.value?.role
    const hasUserData = !!userData.value

    if (!hasUserData || !userRole)
      return [] as VerticalNavItems

    switch (userRole) {
      case 'admin':
        return [...admin] as VerticalNavItems
      case 'franchisor':
        return [...franchisor] as VerticalNavItems
      case 'franchisee':
        return [...franchisee] as VerticalNavItems
      case 'sales':
        return [...sales] as VerticalNavItems
      default:
        return [] as VerticalNavItems
    }
  })

  return {
    navigationItems,
  }
}
