/**
 * Centralized Role-Based API Router
 * Provides consistent endpoint resolution based on user role
 */

// Supported resources that have role-based routing
export type ApiResource = 
  | 'leads' 
  | 'tasks' 
  | 'royalties' 
  | 'technical-requests' 
  | 'units' 
  | 'financial'
  | 'franchise'
  | 'users'

// User roles from the application
export type UserRole = 'admin' | 'franchisor' | 'sales' | 'unit-manager' | 'franchisee'

/**
 * Get the base API endpoint for a resource based on user role
 */
export function getEndpoint(resource: ApiResource, role?: UserRole): string {
  const userRole = role || getCurrentUserRole()

  // Define role-based routing patterns
  switch (resource) {
    case 'leads':
      switch (userRole) {
        case 'sales':
          return '/v1/sales/leads'
        case 'franchisor':
          return '/v1/franchisor/leads'
        default:
          return '/v1/leads'
      }

    case 'tasks':
      switch (userRole) {
        case 'sales':
          return '/v1/sales/tasks'
        case 'franchisor':
          return '/v1/franchisor/tasks'
        default:
          return '/v1/tasks'
      }

    case 'royalties':
      switch (userRole) {
        case 'franchisor':
          return '/v1/franchisor/royalties'
        case 'unit-manager':
        case 'franchisee':
          return '/v1/unit-manager/royalties'
        default:
          return '/v1/royalties'
      }

    case 'technical-requests':
      // Technical requests don't have role-based routing currently
      return '/v1/technical-requests'

    case 'units':
      switch (userRole) {
        case 'unit-manager':
        case 'franchisee':
          return '/v1/unit-manager'
        case 'franchisor':
          return '/v1/franchisor/units'
        default:
          return '/v1/units'
      }

    case 'financial':
      switch (userRole) {
        case 'franchisor':
          return '/v1/franchisor/financial'
        case 'unit-manager':
        case 'franchisee':
          return '/v1/unit-manager'
        default:
          return '/v1/financial'
      }

    case 'franchise':
      switch (userRole) {
        case 'franchisor':
          return '/v1/franchisor/franchise'
        default:
          return '/v1/franchise'
      }

    case 'users':
      switch (userRole) {
        case 'franchisor':
          return '/v1/franchisor'
        case 'admin':
          return '/v1/admin'
        default:
          return '/v1/users'
      }

    default:
      return `/v1/${resource}`
  }
}

/**
 * Get the current user role from the userData cookie
 */
function getCurrentUserRole(): UserRole {
  try {
    const userDataCookie = useCookie('userData')
    const userData = userDataCookie.value as any
    
    if (!userData?.role) {
      console.warn('User role not found in userData cookie, defaulting to admin')
      return 'admin'
    }

    return userData.role as UserRole
  } catch (error) {
    console.error('Error getting user role from cookie:', error)
    return 'admin' // Default fallback
  }
}

/**
 * Build an endpoint URL with optional path segments
 */
export function buildApiUrl(resource: ApiResource, pathSegments?: string | string[], role?: UserRole): string {
  const baseEndpoint = getEndpoint(resource, role)
  
  if (!pathSegments) {
    return baseEndpoint
  }

  const segments = Array.isArray(pathSegments) ? pathSegments : [pathSegments]
  const path = segments.filter(Boolean).join('/')
  
  return path ? `${baseEndpoint}/${path}` : baseEndpoint
}
