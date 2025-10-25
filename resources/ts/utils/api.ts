import { ofetch } from 'ofetch'
import type { ApiResponse } from '@/types/api'
import { useSnackbar } from '@/composables/useSnackbar'

// Compute a scheme-safe base URL to avoid mixed content when the app is served over HTTPS
const computeBaseURL = () => {
  const base = import.meta.env.VITE_API_BASE_URL

  // Prefer relative base to inherit current origin & scheme
  if (!base || base.trim() === '')
    return '/api'

  // If running under HTTPS and base uses HTTP, upgrade to HTTPS
  if (typeof window !== 'undefined' && window.location?.protocol === 'https:' && base.startsWith('http://'))
    return base.replace(/^http:\/\//, 'https://')

  return base
}

export const $api = ofetch.create({
  baseURL: computeBaseURL(),
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
  credentials: 'include',
  async onRequest({ options }) {
    const accessToken = useCookie('accessToken').value
    if (accessToken) {
      // Ensure headers exist and add authorization
      options.headers = {
        ...(options.headers as any),
        Authorization: `Bearer ${accessToken}`,
      }
    }
    else {
      console.warn('No access token found in cookies for request:', options.method)
    }
  },
  async onResponse({ response, options }) {
    // Show success notifications for create/update/delete operations
    const method = options.method?.toUpperCase()
    if (method && ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
      const { showSuccess } = useSnackbar()
      const message = response._data?.message || 'Operation completed successfully'
      showSuccess(message)
    }
  },
  async onResponseError({ response }) {
    const error = {
      status: response.status,
      statusText: response.statusText,
      url: response.url,
      data: response._data,
    }

    // Log detailed error information for debugging
    console.error('API Error:', error)

    // Transform error to consistent format and show user-friendly messages
    let userMessage = 'An unexpected error occurred'
    
    // Preserve validation errors from 422 responses
    let validationErrors = null
    
    switch (response.status) {
      case 401:
        userMessage = 'You are not authorized to perform this action'
        // Could redirect to login here if needed
        break
      case 403:
        userMessage = 'Access denied. You do not have permission for this action'
        break
      case 404:
        userMessage = 'The requested resource was not found'
        break
      case 422:
        userMessage = response._data?.message || 'Validation failed. Please check your input'
        // Preserve validation errors for field-level display
        validationErrors = response._data?.errors || null
        break
      case 500:
        userMessage = 'Server error. Please try again later'
        break
      default:
        userMessage = response._data?.message || `Error ${response.status}: ${response.statusText}`
    }

    // Show snackbar notification for user-facing errors (only for 4xx/5xx, skip 401 and 422)
    // Skip 422 because validation errors are handled at the field level
    if (response.status >= 400 && response.status !== 401 && response.status !== 422) {
      const { showError } = useSnackbar()
      showError(userMessage)
    }

    // Create a consistent error response format
    const errorResponse: ApiResponse<null> = {
      success: false,
      data: null,
      message: userMessage,
    }

    // Attach error details for debugging while maintaining consistent response shape
    Object.assign(response._data, errorResponse, { 
      _errorDetails: error,
      _statusCode: response.status,
      errors: validationErrors, // Preserve validation errors
    })

    throw response
  },
})
