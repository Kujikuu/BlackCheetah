import { ref } from 'vue'
import { useRouter } from 'vue-router'

export interface FranchiseStatusResponse {
  success: boolean
  data?: {
    requires_franchise_registration: boolean
    has_franchise: boolean
    franchise_id?: number
  }
}

/**
 * Composable to check if franchisor has completed franchise registration
 */
export function useFranchiseCheck() {
  const router = useRouter()
  const isChecking = ref(false)
  const requiresRegistration = ref(false)

  /**
   * Check franchise status and redirect if needed
   */
  const checkAndRedirect = async (): Promise<boolean> => {
    const userData = useCookie('userData').value

    // Only check for franchisors
    if (!userData || typeof userData !== 'object' || !('role' in userData)) {
      return false
    }

    const role = (userData as any).role
    if (role !== 'franchisor') {
      return false
    }

    isChecking.value = true

    try {
      const response = await $api<FranchiseStatusResponse>(
        '/v1/onboarding/franchise-status',
        { method: 'GET' },
      )

      if (response.success && response.data) {
        requiresRegistration.value = response.data.requires_franchise_registration

        if (response.data.requires_franchise_registration) {
          router.push('/franchisor/franchise-registration')
          return true
        }
      }

      return false
    }
    catch (error) {
      console.error('Failed to check franchise status:', error)
      return false
    }
    finally {
      isChecking.value = false
    }
  }

  return {
    isChecking,
    requiresRegistration,
    checkAndRedirect,
  }
}

