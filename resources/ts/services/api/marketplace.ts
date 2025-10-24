import { $api } from '@/utils/api'
import type { Property } from './property'

// Type definitions
export interface Franchise {
  id: number
  franchisor_id: number
  broker_id?: number
  business_name: string
  brand_name: string
  industry: string
  description?: string
  website?: string
  logo?: string
  business_registration_number?: string
  tax_id?: string
  business_type?: string
  established_date?: string
  headquarters_country: string
  headquarters_city: string
  headquarters_address: string
  contact_phone: string
  contact_email: string
  franchise_fee: number
  royalty_percentage?: number
  marketing_fee_percentage?: number
  total_units: number
  active_units: number
  status: string
  is_marketplace_listed: boolean
  created_at: string
  updated_at: string
  franchisor?: {
    id: number
    name: string
    email: string
    phone?: string
  }
  broker?: {
    id: number
    name: string
    email: string
    phone?: string
  }
  units?: Array<{
    id: number
    franchise_id: number
    unit_name: string
    city: string
    state_province: string
    nationality: string
    status: string
  }>
}

export interface MarketplaceFilters {
  search?: string
  industry?: string
  country?: string
  city?: string
  min_franchise_fee?: number
  max_franchise_fee?: number
  has_broker?: string
  property_type?: string
  state_province?: string
  min_rent?: number
  max_rent?: number
  min_size?: number
  max_size?: number
  available_from?: string
  page?: number
  per_page?: number
  sortBy?: string
  orderBy?: 'asc' | 'desc'
}

export interface MarketplaceInquiry {
  id: number
  name: string
  email: string
  phone: string
  inquiry_type: 'franchise' | 'property'
  franchise_id?: number
  property_id?: number
  message: string
  investment_budget?: string
  preferred_location?: string
  timeline?: string
  status: string
  created_at: string
  updated_at: string
  franchise?: {
    id: number
    brand_name: string
    business_name: string
  }
  property?: {
    id: number
    title: string
    city: string
  }
}

export interface InquiryPayload {
  name: string
  email: string
  phone: string
  inquiry_type: 'franchise' | 'property'
  franchise_id?: number
  property_id?: number
  message: string
  investment_budget?: string
  preferred_location?: string
  timeline?: string
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

export class MarketplaceApi {
  private getBaseUrl(): string {
    return '/api/v1/marketplace'
  }

  /**
   * Get all marketplace franchises with filters
   */
  async getFranchises(filters?: MarketplaceFilters): Promise<ApiResponse<PaginatedResponse<Franchise>>> {
    return await $api(`${this.getBaseUrl()}/franchises`, {
      method: 'GET',
      query: filters,
    })
  }

  /**
   * Get single franchise details
   */
  async getFranchiseDetails(id: number): Promise<ApiResponse<Franchise>> {
    return await $api(`${this.getBaseUrl()}/franchises/${id}`)
  }

  /**
   * Get all marketplace properties with filters
   */
  async getProperties(filters?: MarketplaceFilters): Promise<ApiResponse<PaginatedResponse<Property>>> {
    return await $api(`${this.getBaseUrl()}/properties`, {
      method: 'GET',
      query: filters,
    })
  }

  /**
   * Get single property details
   */
  async getPropertyDetails(id: number): Promise<ApiResponse<Property>> {
    return await $api(`${this.getBaseUrl()}/properties/${id}`)
  }

  /**
   * Submit marketplace inquiry
   */
  async submitInquiry(payload: InquiryPayload): Promise<ApiResponse<MarketplaceInquiry>> {
    return await $api(`${this.getBaseUrl()}/inquiries`, {
      method: 'POST',
      body: payload,
    })
  }
}

export const marketplaceApi = new MarketplaceApi()

