import { ofetch } from 'ofetch'
import type { ApiResponse } from '@/types/api'

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
        break
      case 500:
        userMessage = 'Server error. Please try again later'
        break
      default:
        userMessage = response._data?.message || `Error ${response.status}: ${response.statusText}`
    }

    // Show toast notification for user-facing errors (only for 4xx/5xx, skip 401 redirects)
    if (response.status >= 400 && response.status !== 401) {
      try {
        // Try to use toast if available (depends on UI framework)
        if (typeof useToast !== 'undefined') {
          const toast = useToast()
          toast({
            variant: 'destructive',
            title: 'Error',
            description: userMessage,
          })
        }
      } catch (error) {
        // Fallback to console if toast is not available
        console.error('API Error (Toast unavailable):', userMessage)
      }
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
      _statusCode: response.status 
    })

    throw response
  },
})
