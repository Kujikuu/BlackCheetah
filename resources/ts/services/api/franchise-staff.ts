import { $api } from '@/utils/api'
import type { ApiResponse } from '@/types/api'

// Type definitions
export interface FranchiseStaff {
  id: number
  name: string
  email: string
  phone?: string | null
  jobTitle: string
  department?: string | null
  salary?: number | null
  hireDate: string
  shiftStart?: string | null
  shiftEnd?: string | null
  status: 'active' | 'on_leave' | 'terminated' | 'inactive'
  employmentType: 'full_time' | 'part_time' | 'contract' | 'temporary'
  notes?: string | null
  createdAt?: string
}

export interface StaffFilters {
  status?: string
  department?: string
  employment_type?: string
  search?: string
  sort_by?: string
  sort_order?: 'asc' | 'desc'
  per_page?: number
  page?: number
}

export interface CreateStaffPayload {
  name: string
  email: string
  phone?: string
  job_title: string
  department?: string
  salary?: number
  hire_date: string
  shift_start?: string
  shift_end?: string
  status: 'active' | 'on_leave' | 'terminated' | 'inactive'
  employment_type: 'full_time' | 'part_time' | 'contract' | 'temporary'
  notes?: string
}

export interface UpdateStaffPayload {
  name?: string
  email?: string
  phone?: string
  job_title?: string
  department?: string
  salary?: number
  hire_date?: string
  shift_start?: string
  shift_end?: string
  status?: 'active' | 'on_leave' | 'terminated' | 'inactive'
  employment_type?: 'full_time' | 'part_time' | 'contract' | 'temporary'
  notes?: string
}

export interface StaffStatistics {
  totalStaff: number
  activeStaff: number
  onLeaveStaff: number
  terminatedStaff: number
  fullTimeStaff: number
  partTimeStaff: number
  byDepartment: Record<string, number>
}

export interface PaginatedStaffResponse {
  data: FranchiseStaff[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

// API Service Class
export class FranchiseStaffApi {
  private getBaseUrl(): string {
    return '/api/v1/franchisor/franchise/staff'
  }

  /**
   * Get franchise staff with filters
   */
  async getStaff(filters?: StaffFilters): Promise<ApiResponse<PaginatedStaffResponse>> {
    return await $api(this.getBaseUrl(), {
      method: 'GET',
      query: filters,
    })
  }

  /**
   * Create new staff member
   */
  async createStaff(payload: CreateStaffPayload): Promise<ApiResponse<FranchiseStaff>> {
    return await $api(this.getBaseUrl(), {
      method: 'POST',
      body: payload,
    })
  }

  /**
   * Update existing staff member
   */
  async updateStaff(id: number, payload: UpdateStaffPayload): Promise<ApiResponse<FranchiseStaff>> {
    return await $api(`${this.getBaseUrl()}/${id}`, {
      method: 'PUT',
      body: payload,
    })
  }

  /**
   * Delete staff member
   */
  async deleteStaff(id: number): Promise<ApiResponse<void>> {
    return await $api(`${this.getBaseUrl()}/${id}`, {
      method: 'DELETE',
    })
  }

  /**
   * Get staff statistics
   */
  async getStatistics(): Promise<ApiResponse<StaffStatistics>> {
    return await $api(`${this.getBaseUrl()}/statistics`)
  }
}

export const franchiseStaffApi = new FranchiseStaffApi()

