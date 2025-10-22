import { franchiseApi, usersApi } from '@/services/api'

export interface TaskUser {
  id: number
  name: string
  email: string
  role: string
  status: string
}

export const useTaskUsers = () => {
  const franchisees = ref<TaskUser[]>([])
  const brokers = ref<TaskUser[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  /**
   * Fetch franchisees for task assignment
   */
  const fetchFranchisees = async () => {
    try {
      loading.value = true
      error.value = null

      const response = await franchiseApi.getFranchisees()

      if (response.success && response.data?.data) {
        // API returns paginated data, so we access response.data.data for the actual users array
        franchisees.value = response.data.data.map((user: any) => ({
          id: user.id,
          name: user.name,
          email: user.email,
          role: 'franchisee',
          status: user.status,
        }))
      }
      else {
        franchisees.value = []
      }
    }
    catch (err: any) {
      console.error('Failed to fetch franchisees:', err)
      error.value = err?.data?.message || 'Failed to fetch franchisees'
      franchisees.value = []
    }
    finally {
      loading.value = false
    }
  }

  /**
   * Fetch broker users for task assignment
   */
  const fetchBrokers = async () => {
    try {
      loading.value = true
      error.value = null

      const response = await usersApi.getBrokers()

      if (response.success && response.data) {
        // API returns paginated data, handle both array and paginated response
        const usersArray = Array.isArray(response.data) ? response.data : response.data.data || []
        
        brokers.value = usersArray.map((user: any) => ({
          id: user.id,
          name: user.name,
          email: user.email,
          role: 'broker',
          status: user.status,
        }))
      }
      else {
        brokers.value = []
      }
    }
    catch (err: any) {
      console.error('Failed to fetch brokers:', err)
      error.value = err?.data?.message || 'Failed to fetch brokers'
      brokers.value = []
    }
    finally {
      loading.value = false
    }
  }

  /**
   * Get users formatted for select component
   */
  const getUsersForSelect = (userType: 'franchisee' | 'broker') => {
    const users = userType === 'franchisee' ? franchisees.value : brokers.value

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
  const getUserNameById = (userId: number, userType: 'franchisee' | 'broker') => {
    const users = userType === 'franchisee' ? franchisees.value : brokers.value
    const user = users.find(u => u.id === userId)

    return user ? user.name : 'Unknown User'
  }

  /**
   * Initialize data based on user type
   */
  const initializeUsers = async (userType: 'franchisee' | 'broker' | 'both' = 'both') => {
    if (userType === 'franchisee' || userType === 'both')
      await fetchFranchisees()

    if (userType === 'broker' || userType === 'both')
      await fetchBrokers()
  }

  return {
    franchisees: readonly(franchisees),
    brokers: readonly(brokers),
    loading: readonly(loading),
    error: readonly(error),
    fetchFranchisees,
    fetchBrokers,
    getUsersForSelect,
    getUserNameById,
    initializeUsers,
  }
}
