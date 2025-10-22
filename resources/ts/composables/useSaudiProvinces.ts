import { ref, computed, onMounted } from 'vue'
import { $api } from '@/utils/api'

interface Province {
  name: string
  cities: string[]
}

const CACHE_KEY = 'saudiProvincesCacheV1'
const CACHE_TTL_MS = 7 * 24 * 60 * 60 * 1000 // 7 days

// Shared state across all instances
const provinces = ref<Province[]>([])
const isLoading = ref(false)
const error = ref<string | null>(null)
const isFetched = ref(false)

function readCache(): Province[] | null {
  try {
    const raw = localStorage.getItem(CACHE_KEY)
    if (!raw) return null
    const { ts, data } = JSON.parse(raw)
    if (Date.now() - ts > CACHE_TTL_MS) return null
    return data as Province[]
  } catch {
    return null
  }
}

function writeCache(data: Province[]) {
  try {
    localStorage.setItem(CACHE_KEY, JSON.stringify({ ts: Date.now(), data }))
  } catch {}
}

async function loadProvinces() {
  // If already fetched or loading, don't fetch again
  if (isFetched.value || isLoading.value) return

  const cached = readCache()
  if (cached) {
    provinces.value = cached
    isFetched.value = true
    return
  }

  isLoading.value = true
  error.value = null
  
  try {
    const response = await $api<{ success: boolean; data: Province[] }>('/v1/provinces', {
      method: 'GET',
    })
    
    if (response.success && Array.isArray(response.data)) {
      provinces.value = response.data
      writeCache(response.data)
      isFetched.value = true
    } else {
      throw new Error('Invalid response format')
    }
  } catch (e: any) {
    error.value = e?.message || 'Failed to load provinces'
    // Minimal fallback
    provinces.value = [
      { name: 'Riyadh', cities: ['Riyadh'] },
      { name: 'Makkah', cities: ['Mecca', 'Jeddah'] },
      { name: 'Madinah', cities: ['Medina'] },
      { name: 'Eastern Province', cities: ['Dammam', 'Al Khobar'] },
    ]
    isFetched.value = true
  } finally {
    isLoading.value = false
  }
}

export function useSaudiProvinces() {
  // Load on first mount
  onMounted(() => {
    if (!isFetched.value) {
      loadProvinces()
    }
  })

  // Get province names for dropdown
  const provinceNames = computed(() => provinces.value.map(p => p.name))

  // Function to get cities for a specific province
  const getCitiesForProvince = (provinceName: string): string[] => {
    if (!provinceName) return []
    const province = provinces.value.find(p => p.name === provinceName)
    return province?.cities || []
  }

  return {
    provinces: provinceNames,
    getCitiesForProvince,
    isLoading,
    error,
    refresh: loadProvinces,
  }
}

