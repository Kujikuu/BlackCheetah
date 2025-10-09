import { ofetch } from 'ofetch'

export const $api = ofetch.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
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
