import { createFetch } from '@vueuse/core'
import { destr } from 'destr'

// Compute a scheme-safe base URL to avoid mixed content when the app is served over HTTPS
const computeBaseURL = () => {
  const base = import.meta.env.VITE_API_BASE_URL
  if (!base || base.trim() === '')
    return '/api'
  if (typeof window !== 'undefined' && window.location?.protocol === 'https:' && base.startsWith('http://'))
    return base.replace(/^http:\/\//, 'https://')

  return base
}

export const useApi = createFetch({
  baseUrl: computeBaseURL(),
  fetchOptions: {
    headers: {
      Accept: 'application/json',
    },
  },
  options: {
    refetch: true,
    async beforeFetch({ options }) {
      const accessToken = useCookie('accessToken').value

      if (accessToken) {
        options.headers = {
          ...options.headers,
          Authorization: `Bearer ${accessToken}`,
        }
      }

      return { options }
    },
    afterFetch(ctx) {
      const { data, response } = ctx

      // Parse data if it's JSON

      let parsedData = null
      try {
        parsedData = destr(data)
      }
      catch (error) {
        console.error(error)
      }

      return { data: parsedData, response }
    },
  },
})
