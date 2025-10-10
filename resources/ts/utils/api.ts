import { ofetch } from 'ofetch'

// Compute a scheme-safe base URL to avoid mixed content when the app is served over HTTPS
const computeBaseURL = () => {
  const base = import.meta.env.VITE_API_BASE_URL
  // Prefer relative base to inherit current origin & scheme
  if (!base || base.trim() === '') return '/api'

  // If running under HTTPS and base uses HTTP, upgrade to HTTPS
  if (typeof window !== 'undefined' && window.location?.protocol === 'https:' && base.startsWith('http://')) {
    return base.replace(/^http:\/\//, 'https://')
  }

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
        'Authorization': `Bearer ${accessToken}`,
      }
    }
  },
  async onResponseError({ response }) {
    // Log detailed error information for debugging
    console.error('API Error:', {
      status: response.status,
      statusText: response.statusText,
      url: response.url,
      data: response._data,
    })
  },
})
