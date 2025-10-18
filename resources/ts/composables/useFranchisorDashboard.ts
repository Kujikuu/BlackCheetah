import { $api } from '@/utils/api'

export interface DashboardStats {
  totalFranchisees: number
  totalUnits: number
  totalLeads: number
  activeTasks: number
  currentMonthRevenue: number
  revenueChange: number
  pendingRoyalties: number
}

export interface SalesAssociate {
  id: number
  name: string
  email: string
  phone?: string
  status: 'active' | 'inactive'
  leads_count: number
  created_at: string
}

export interface RecentActivity {
  id: number
  type: 'lead' | 'operation' | 'team' | 'revenue' | 'task' | 'technical_request'
  title: string
  description?: string
  time: string
  icon: string
  color: string
  user?: string
  amount?: number
}

export interface Lead {
  id: number
  firstName: string
  lastName: string
  email: string
  phone?: string
  company?: string
  country?: string
  state?: string
  city?: string
  source: string
  status: 'new' | 'contacted' | 'qualified' | 'converted' | 'lost'
  owner?: string
  lastContacted?: string
  scheduledMeeting?: string
  created_at?: string
  updated_at?: string
}

export interface ProfileCompletionStatus {
  is_complete: boolean
  completion_percentage: number
  completed_fields: number
  total_fields: number
  missing_fields: string[]
}

export const useFranchisorDashboard = () => {
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Dashboard stats
  const dashboardStats = ref<DashboardStats | null>(null)
  const salesAssociates = ref<SalesAssociate[]>([])
  const recentActivities = ref<RecentActivity[]>([])
  const leads = ref<Lead[]>([])

  // Profile completion
  const profileCompletionStatus = ref<ProfileCompletionStatus | null>(null)
  const franchiseExists = ref<boolean>(true) // Track if franchise exists

  // Track if banner was dismissed for current session only
  const bannerDismissed = ref(false)

  /**
   * Format time ago helper
   */
  const formatTimeAgo = (dateString: string): string => {
    if (!dateString)
      return 'Unknown'

    const date = new Date(dateString)

    // Check if date is valid
    if (Number.isNaN(date.getTime())) {
      console.warn('Invalid date string:', dateString)

      return 'Unknown'
    }

    const now = new Date()
    const diffInMs = now.getTime() - date.getTime()
    const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60))

    if (diffInHours < 1)
      return 'Just now'
    if (diffInHours < 24)
      return `${diffInHours} hours ago`

    const diffInDays = Math.floor(diffInHours / 24)
    if (diffInDays === 1)
      return '1 day ago'
    if (diffInDays < 7)
      return `${diffInDays} days ago`
    if (diffInDays < 30)
      return `${Math.floor(diffInDays / 7)} weeks ago`

    return date.toLocaleDateString()
  }

  const isProfileComplete = computed(() => {
    // If banner was dismissed for current session, hide it temporarily
    if (bannerDismissed.value)
      return true

    // Show banner only when no franchise exists (not based on completion percentage)
    return franchiseExists.value
  })

  /**
   * Fetch dashboard statistics
   */
  const fetchDashboardStats = async () => {
    try {
      isLoading.value = true
      error.value = null

      const response = await $api<{ success: boolean; data: DashboardStats }>('/v1/franchisor/dashboard/stats')

      if (response.success)
        dashboardStats.value = response.data
      else
        throw new Error('Failed to fetch dashboard stats')
    }
    catch (err: any) {
      error.value = err.message || 'Failed to fetch dashboard statistics'
      console.error('Dashboard stats error:', err)
    }
    finally {
      isLoading.value = false
    }
  }

  /**
   * Fetch sales associates
   */
  const fetchSalesAssociates = async () => {
    try {
      const response = await $api<{ success: boolean; data: SalesAssociate[] }>('/v1/franchisor/sales-associates')

      if (response.success && Array.isArray(response.data))
        salesAssociates.value = response.data
      else
        salesAssociates.value = []
    }
    catch (err: any) {
      console.error('Sales associates error:', err)
      salesAssociates.value = [] // Ensure salesAssociates is always an array
    }
  }

  /**
   * Fetch leads
   */
  const fetchLeads = async (limit = 10) => {
    try {
      const response = await $api<{ success: boolean; data: Lead[] }>(`/v1/franchisor/leads?limit=${limit}`)

      if (response.success && Array.isArray(response.data))
        leads.value = response.data
      else
        leads.value = []
    }
    catch (err: any) {
      console.error('Leads error:', err)
      leads.value = [] // Ensure leads is always an array
    }
  }

  /**
   * Fetch profile completion status
   */
  const fetchProfileCompletion = async () => {
    try {
      const response = await $api<{ success: boolean; data?: ProfileCompletionStatus; message?: string }>('/v1/franchisor/profile/completion-status')

      if (response.success) {
        profileCompletionStatus.value = response.data!
        franchiseExists.value = true // Franchise exists if we get a successful response
      }
      else {
        // API returned success: false, which means no franchise exists
        franchiseExists.value = false
        profileCompletionStatus.value = null
      }
    }
    catch (err: any) {
      // Check if it's a 404 error (no franchise exists)
      if (err.status === 404 || (err.data && err.data.success === false)) {
        franchiseExists.value = false // No franchise exists
        profileCompletionStatus.value = null
      }
      else {
        // For other errors, assume franchise exists to be safe
        franchiseExists.value = true
        console.error('Profile completion error:', err)
      }
    }
  }

  /**
   * Dismiss the onboarding banner for current session only
   * The banner will show again on next visit if profile is still incomplete
   */
  const dismissBanner = () => {
    bannerDismissed.value = true
  }

  /**
   * Generate recent activities from various data sources
   */
  const generateRecentActivities = () => {
    const activities: RecentActivity[] = []

    // Add recent leads - ensure leads.value exists before calling slice
    if (Array.isArray(leads.value) && leads.value.length > 0) {
      leads.value.slice(0, 3).forEach(lead => {
        const fullName = `${lead.firstName || ''} ${lead.lastName || ''}`.trim() || 'Unknown Lead'
        const createdAt = lead.created_at || new Date().toISOString()

        activities.push({
          id: lead.id,
          type: 'lead',
          title: `New lead: ${fullName}`,
          time: formatTimeAgo(createdAt),
          icon: 'tabler-user-plus',
          color: 'primary',
        })
      })
    }

    // Add sales associate activities - ensure salesAssociates.value exists before calling slice
    if (Array.isArray(salesAssociates.value) && salesAssociates.value.length > 0) {
      salesAssociates.value.slice(0, 2).forEach(associate => {
        if (associate.status === 'active') {
          const createdAt = associate.created_at || new Date().toISOString()

          activities.push({
            id: associate.id,
            type: 'team',
            title: `${associate.name} is managing ${associate.leads_count} leads`,
            time: formatTimeAgo(createdAt),
            icon: 'tabler-user-check',
            color: 'info',
          })
        }
      })
    }

    // Add revenue activity if available
    if (dashboardStats.value?.currentMonthRevenue) {
      activities.push({
        id: Date.now(),
        type: 'revenue',
        title: `Monthly revenue: $${dashboardStats.value.currentMonthRevenue.toLocaleString()}`,
        time: 'This month',
        icon: 'tabler-currency-riyal',
        color: 'warning',
      })
    }

    // Sort by most recent and limit to 5
    // Only sort by date if we have valid dates, otherwise keep original order
    recentActivities.value = activities
      .filter(activity => activity.time !== 'Invalid Date')
      .slice(0, 5)
  }

  /**
   * Initialize dashboard data
   */
  const initializeDashboard = async () => {
    await Promise.all([
      fetchDashboardStats(),
      fetchSalesAssociates(),
      fetchLeads(10),
      fetchProfileCompletion(),
    ])

    generateRecentActivities()
  }

  /**
   * Refresh all dashboard data
   */
  const refreshDashboard = async () => {
    await initializeDashboard()
  }

  const checkFranchiseExists = async (): Promise<boolean> => {
    try {
      const response = await $api('/v1/franchisor/profile/completion-status')

      // If we get a successful response, the franchise exists
      return response.success === true
    }
    catch (err: any) {
      // If we get a 404, it means no franchise exists
      if (err.status === 404)
        return false

      // For other errors, assume franchise exists to be safe
      console.error('Error checking franchise existence:', err)

      return true
    }
  }

  return {
    // State
    isLoading: readonly(isLoading),
    error: readonly(error),
    dashboardStats: readonly(dashboardStats),
    salesAssociates: readonly(salesAssociates),
    recentActivities: readonly(recentActivities),
    leads: readonly(leads),
    profileCompletionStatus: readonly(profileCompletionStatus),
    isProfileComplete: readonly(isProfileComplete),
    franchiseExists: readonly(franchiseExists),

    // Methods
    fetchDashboardStats,
    fetchSalesAssociates,
    fetchLeads,
    fetchProfileCompletion,
    dismissBanner,
    initializeDashboard,
    refreshDashboard,
    generateRecentActivities,
    checkFranchiseExists,
  }
}
