import { ref, onMounted } from 'vue'
import { $api } from '@/utils/api'

interface CountryItem {
  name: string
  code?: string
}

const CACHE_KEY = 'countriesCacheV1'
const CACHE_TTL_MS = 7 * 24 * 60 * 60 * 1000

export function useCountries(ttlMs: number = CACHE_TTL_MS) {
  const countries = ref<string[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  function readCache(): string[] | null {
    try {
      const raw = localStorage.getItem(CACHE_KEY)
      if (!raw) return null
      const { ts, data } = JSON.parse(raw)
      if (Date.now() - ts > ttlMs) return null
      return data as string[]
    } catch {
      return null
    }
  }

  function writeCache(data: string[]) {
    try {
      localStorage.setItem(CACHE_KEY, JSON.stringify({ ts: Date.now(), data }))
    } catch {}
  }

  async function load() {
    const cached = readCache()
    if (cached) {
      countries.value = cached
      return
    }

    isLoading.value = true
    error.value = null
    try {
      const response = await $api<{ success: boolean; data: string[] }>('/v1/countries', {
        method: 'GET',
      })
      
      if (response.success && Array.isArray(response.data)) {
        countries.value = response.data
        writeCache(response.data)
      } else {
        throw new Error('Invalid response format')
      }
    } catch (e: any) {
      error.value = e?.message || 'Failed to load countries'
      // Minimal fallback
      countries.value = [
        'Saudi Arabia',
        'United States',
        'United Kingdom',
        'Canada',
        'Australia',
        'Germany',
        'France',
        'Italy',
        'Spain',
        'Japan',
      ]
    } finally {
      isLoading.value = false
    }
  }

  onMounted(load)

  return { countries, isLoading, error, refresh: load }
}


