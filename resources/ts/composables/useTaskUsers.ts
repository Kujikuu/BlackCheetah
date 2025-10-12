import type { Ref } from 'vue'

export interface TaskUser {
  id: number
  name: string
  email: string
  role: string
  status: string
}

export const useTaskUsers = () => {
  const franchisees = ref<TaskUser[]>([])
  const salesUsers = ref<TaskUser[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  /**
   * Fetch franchisees for task assignment
   */
  const fetchFranchisees = async () => {
    try {
      loading.value = true
      error.value = null

      const response = await $api<{ success: boolean; data: any }>('/v1/franchisor/franchisees')

      if (response.success && response.data?.data) {
        // API returns paginated data, so we access response.data.data for the actual users array
        franchisees.value = response.data.data.map((user: any) => ({
          id: user.id,
          name: user.name,
          email: user.email,
          role: 'franchisee',
          status: user.status,
        }))
      } else {
        franchisees.value = []
      }
    } catch (err: any) {
      console.error('Failed to fetch franchisees:', err)
      error.value = err?.data?.message || 'Failed to fetch franchisees'
      franchisees.value = []
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch sales users for task assignment
   */
  const fetchSalesUsers = async () => {
    try {
      loading.value = true
      error.value = null

      const response = await $api<{ success: boolean; data: any }>('/v1/franchisor/sales-associates')

      if (response.success && response.data) {
        salesUsers.value = response.data.map((user: any) => ({
          id: user.id,
          name: user.name,
          email: user.email,
          role: 'sales',
          status: user.status,
        }))
      } else {
        salesUsers.value = []
      }
    } catch (err: any) {
      console.error('Failed to fetch sales users:', err)
      error.value = err?.data?.message || 'Failed to fetch sales users'
      salesUsers.value = []
    } finally {
      loading.value = false
    }
  }

  /**
   * Get users formatted for select component
   */
  const getUsersForSelect = (userType: 'franchisee' | 'sales') => {
    const users = userType === 'franchisee' ? franchisees.value : salesUsers.value
    
    return users
      .filter(user => user.status === 'active') // Only show active users
      .map(user => ({
        title: `${user.name} (${user.email})`,
        value: user.id,
        subtitle: user.role,
      }))
  }

  /**
   * Get user name by ID
   */
  const getUserNameById = (userId: number, userType: 'franchisee' | 'sales') => {
    const users = userType === 'franchisee' ? franchisees.value : salesUsers.value
    const user = users.find(u => u.id === userId)
    return user ? user.name : 'Unknown User'
  }

  /**
   * Initialize data based on user type
   */
  const initializeUsers = async (userType: 'franchisee' | 'sales' | 'both' = 'both') => {
    if (userType === 'franchisee' || userType === 'both') {
      await fetchFranchisees()
    }
    
    if (userType === 'sales' || userType === 'both') {
      await fetchSalesUsers()
    }
  }

  return {
    franchisees: readonly(franchisees),
    salesUsers: readonly(salesUsers),
    loading: readonly(loading),
    error: readonly(error),
    fetchFranchisees,
    fetchSalesUsers,
    getUsersForSelect,
    getUserNameById,
    initializeUsers,
  }
}