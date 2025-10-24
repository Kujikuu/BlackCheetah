import { $api } from '@/utils/api'
import type { ApiResponse } from '@/types/api'

// Type definitions for broker service
export interface AssignedFranchise {
  id: number
  brand_name: string
  business_name: string
  industry: string
  status: string
  is_marketplace_listed: boolean
  logo: string | null
  franchise_fee: number
  royalty_percentage: number
  established_date: string
  total_units: number
  active_units: number
  franchisor: {
    id: number
    name: string
    email: string
  } | null
}

export interface AssignedFranchisesResponse {
  data: AssignedFranchise[]
  total: number
}

export class BrokerApi {
  /**
   * Get franchises assigned to the authenticated broker
   */
  async getAssignedFranchises(): Promise<ApiResponse<AssignedFranchisesResponse>> {
    return await $api('/v1/broker/assigned-franchises', {
      method: 'GET',
    })
  }

  /**
   * Toggle marketplace listing status for an assigned franchise
   */
  async toggleMarketplaceListing(franchiseId: number): Promise<ApiResponse<any>> {
    return await $api(`/v1/broker/franchises/${franchiseId}/marketplace-toggle`, {
      method: 'PATCH',
    })
  }
}

export const brokerApi = new BrokerApi()

