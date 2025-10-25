import { ref, reactive } from 'vue'

export type SnackbarVariant = 'success' | 'error' | 'info' | 'warning'

interface SnackbarState {
  visible: boolean
  message: string
  variant: SnackbarVariant
  timeout: number
}

// Global snackbar state shared across the app
const snackbarState = reactive<SnackbarState>({
  visible: false,
  message: '',
  variant: 'info',
  timeout: 4000,
})

/**
 * Composable for managing global snackbar notifications
 * Can be used from any component to show success, error, info, or warning messages
 */
export function useSnackbar() {
  const show = (message: string, variant: SnackbarVariant = 'info', timeout: number = 4000) => {
    snackbarState.message = message
    snackbarState.variant = variant
    snackbarState.timeout = timeout
    snackbarState.visible = true
  }

  const showSuccess = (message: string, timeout: number = 4000) => {
    show(message, 'success', timeout)
  }

  const showError = (message: string, timeout: number = 4000) => {
    show(message, 'error', timeout)
  }

  const showInfo = (message: string, timeout: number = 4000) => {
    show(message, 'info', timeout)
  }

  const showWarning = (message: string, timeout: number = 4000) => {
    show(message, 'warning', timeout)
  }

  const hide = () => {
    snackbarState.visible = false
  }

  return {
    // State
    state: snackbarState,
    
    // Methods
    show,
    showSuccess,
    showError,
    showInfo,
    showWarning,
    hide,
  }
}

