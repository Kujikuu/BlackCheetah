import { $api } from '@/utils/api'
import { getEndpoint } from '@/utils/api-router'

// Type definitions
export interface Property {
  id: number
  broker_id: number
  title: string
  description: string
  property_type: string
  size_sqm: number
  state_province: string
  city: string
  address: string
  postal_code?: string
  latitude?: number
  longitude?: number
  monthly_rent: number
  deposit_amount?: number
  lease_term_months?: number
  available_from?: string
  amenities?: string[]
  images?: string[]
  status: string
  contact_info?: string
  created_at: string
  updated_at: string
  broker?: {
    id: number
    name: string
    email: string
    phone?: string
  }
}

export interface PropertyFilters {
  search?: string
  status?: string
  property_type?: string
  state_province?: string
  city?: string
  min_rent?: number
  max_rent?: number
  min_size?: number
  max_size?: number
  page?: number
  per_page?: number
  sortBy?: string
  orderBy?: 'asc' | 'desc'
}

export interface CreatePropertyPayload {
  title: string
  description: string
  property_type: string
  size_sqm: number
  state_province: string
  city: string
  address: string
  postal_code?: string
  latitude?: number
  longitude?: number
  monthly_rent: number
  deposit_amount?: number
  lease_term_months?: number
  available_from?: string
  amenities?: string[]
  images?: string[]
  status?: string
  contact_info?: string
}

export interface UpdatePropertyPayload {
  title?: string
  description?: string
  property_type?: string
  size_sqm?: number
  state_province?: string
  city?: string
  address?: string
  postal_code?: string
  latitude?: number
  longitude?: number
  monthly_rent?: number
  deposit_amount?: number
  lease_term_months?: number
  available_from?: string
  amenities?: string[]
  images?: string[]
  status?: string
  contact_info?: string
}

export interface ApiResponse<T = any> {
  success: boolean
  data?: T
  message?: string
  errors?: any
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  per_page: number
  total: number
  last_page: number
  from: number
  to: number
}

export class PropertyApi {
  private getBaseUrl(): string {
    return getEndpoint('properties')
  }

  /**
   * Get all properties with filters
   */
  async getProperties(filters?: PropertyFilters): Promise<ApiResponse<PaginatedResponse<Property>>> {
    return await $api(this.getBaseUrl(), {
      method: 'GET',
      query: filters,
    })
  }

  /**
   * Get single property by ID
   */
  async getProperty(id: number): Promise<ApiResponse<Property>> {
    return await $api(`${this.getBaseUrl()}/${id}`)
  }

  /**
   * Create new property
   */
  async createProperty(payload: CreatePropertyPayload, images?: File[]): Promise<ApiResponse<Property>> {
    // If there are images, use FormData
    if (images && images.length > 0) {
      const formData = new FormData()

      // Append all property data
      Object.entries(payload).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          if (Array.isArray(value)) {
            formData.append(key, JSON.stringify(value))
          }
          else {
            formData.append(key, String(value))
          }
        }
      })

      // Append images
      images.forEach((image) => {
        formData.append('property_images[]', image)
      })

      return await $api(this.getBaseUrl(), {
        method: 'POST',
        body: formData,
      })
    }

    // No images, send as JSON
    return await $api(this.getBaseUrl(), {
      method: 'POST',
      body: payload,
    })
  }

  /**
   * Update existing property
   */
  async updateProperty(id: number, payload: UpdatePropertyPayload, images?: File[]): Promise<ApiResponse<Property>> {
    // If there are images, use FormData
    if (images && images.length > 0) {
      const formData = new FormData()

      // Add _method field for Laravel to recognize PUT request
      formData.append('_method', 'PUT')

      // Append all property data
      Object.entries(payload).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          if (Array.isArray(value)) {
            formData.append(key, JSON.stringify(value))
          }
          else {
            formData.append(key, String(value))
          }
        }
      })

      // Append images
      images.forEach((image) => {
        formData.append('property_images[]', image)
      })

      return await $api(`${this.getBaseUrl()}/${id}`, {
        method: 'POST',
        body: formData,
      })
    }

    // No images, send as JSON
    return await $api(`${this.getBaseUrl()}/${id}`, {
      method: 'PUT',
      body: payload,
    })
  }

  /**
   * Delete property
   */
  async deleteProperty(id: number): Promise<ApiResponse<void>> {
    return await $api(`${this.getBaseUrl()}/${id}`, {
      method: 'DELETE',
    })
  }

  /**
   * Bulk delete properties
   */
  async bulkDelete(ids: number[]): Promise<ApiResponse<void>> {
    return await $api(`${this.getBaseUrl()}/bulk-delete`, {
      method: 'POST',
      body: { ids },
    })
  }

  /**
   * Mark property as leased
   */
  async markLeased(id: number): Promise<ApiResponse<Property>> {
    return await $api(`${this.getBaseUrl()}/${id}/mark-leased`, {
      method: 'PATCH',
    })
  }
}

export const propertyApi = new PropertyApi()

