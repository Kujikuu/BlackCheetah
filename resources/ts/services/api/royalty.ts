import { $api } from '@/utils/api'
import { getEndpoint } from '@/utils/api-router'
import type { ApiResponse, PaginatedResponse, PeriodFilter, RoyaltyStatus } from '@/types/api'

// Type definitions
export interface RoyaltyRecord {
  id: string | number
  royalty_number?: string
  billing_period: string
  franchisee_name: string
  store_location: string
  due_date: string
  gross_sales: number
  royalty_percentage: number
  amount: number
  status: 'pending' | 'paid' | 'overdue'
  franchise_id?: number
  unit_id?: number
  franchisee_id?: number
  paid_date?: string
  payment_method?: string
  payment_reference?: string
  attachments?: string[]
  notes?: string
}

export interface PaymentData {
  amount_paid: number
  payment_date: string
  payment_type: string
  payment_reference?: string
  notes?: string
  attachment?: File | null
}

export interface RoyaltyStatistics {
  royalty_collected_till_date: number
  upcoming_royalties: number
  total_royalties: number
  pending_amount: number
  paid_amount: number
  overdue_amount: number
  overdue_count: number
}

export interface RoyaltyFilters {
  period?: PeriodFilter
  status?: 'all' | 'paid' | 'pending' | 'overdue'
  search?: string
  page?: number
  per_page?: number
  sort_by?: string
  sort_order?: 'asc' | 'desc'
  franchise_id?: number
  unit_id?: number
}

export interface ExportOptions {
  format: 'csv' | 'excel'
  data_type: 'all' | 'paid' | 'pending' | 'overdue'
  period: PeriodFilter
}

// API Service Class
export class RoyaltyApi {
  private getBaseUrl(): string {
    return getEndpoint('royalties')
  }

  // Get all royalties with filters
  async getRoyalties(filters?: RoyaltyFilters): Promise<ApiResponse<PaginatedResponse<RoyaltyRecord>>> {
    return await $api<ApiResponse<PaginatedResponse<RoyaltyRecord>>>(this.getBaseUrl(), {
      params: filters,
    })
  }

  // Get single royalty by ID
  async getRoyalty(id: string | number): Promise<ApiResponse<RoyaltyRecord>> {
    return await $api<ApiResponse<RoyaltyRecord>>(`${this.getBaseUrl()}/${id}`)
  }

  // Get royalty statistics
  async getStatistics(filters?: RoyaltyFilters): Promise<ApiResponse<RoyaltyStatistics>> {
    return await $api<ApiResponse<RoyaltyStatistics>>(`${this.getBaseUrl()}/statistics`, {
      params: filters,
    })
  }

  // Mark royalty as paid/completed
  async markAsPaid(id: string | number, paymentData: PaymentData): Promise<ApiResponse<RoyaltyRecord>> {
    const formData = new FormData()

    formData.append('amount_paid', (paymentData.amount_paid || 0).toString())
    formData.append('payment_date', paymentData.payment_date || '')
    formData.append('payment_method', paymentData.payment_type || '')

    if (paymentData.payment_reference)
      formData.append('payment_reference', paymentData.payment_reference)

    if (paymentData.notes)
      formData.append('notes', paymentData.notes)

    if (paymentData.attachment)
      formData.append('attachment', paymentData.attachment)

    // Use POST with _method override for file uploads, as PATCH doesn't work well with FormData
    formData.append('_method', 'PATCH')

    return await $api<ApiResponse<RoyaltyRecord>>(`${this.getBaseUrl()}/${id}/mark-paid`, {
      method: 'POST',
      body: formData,
      headers: {
        // Let the browser set the Content-Type with boundary for FormData
        'Content-Type': undefined as any,
      },
    })
  }

  // Export royalty data
  async exportRoyalties(options: ExportOptions): Promise<Blob> {
    return await $api<Blob>(`${this.getBaseUrl()}/export`, {
      params: options,
      parseResponse: data => data,
    })
  }

  // Create new royalty
  async createRoyalty(data: Partial<RoyaltyRecord>): Promise<ApiResponse<RoyaltyRecord>> {
    return await $api<ApiResponse<RoyaltyRecord>>(`${this.getBaseUrl()}`, {
      method: 'POST',
      body: data,
    })
  }

  // Update royalty
  async updateRoyalty(id: string | number, data: Partial<RoyaltyRecord>): Promise<ApiResponse<RoyaltyRecord>> {
    return await $api<ApiResponse<RoyaltyRecord>>(`${this.getBaseUrl()}/${id}`, {
      method: 'PUT',
      body: data,
    })
  }

  // Delete royalty
  async deleteRoyalty(id: string | number): Promise<void> {
    await $api(`${this.getBaseUrl()}/${id}`, {
      method: 'DELETE',
    })
  }

  // Add adjustment to royalty
  async addAdjustment(id: string | number, amount: number, reason: string): Promise<ApiResponse<RoyaltyRecord>> {
    return await $api<ApiResponse<RoyaltyRecord>>(`${this.getBaseUrl()}/${id}/adjustments`, {
      method: 'POST',
      body: {
        adjustment_amount: amount,
        adjustment_reason: reason,
      },
    })
  }

  // Calculate late fee
  async calculateLateFee(id: string | number): Promise<ApiResponse<{ royalty: RoyaltyRecord; late_fee: number; days_overdue: number }>> {
    return await $api<ApiResponse<{ royalty: RoyaltyRecord; late_fee: number; days_overdue: number }>>(`${this.getBaseUrl()}/${id}/late-fee`, {
      method: 'POST',
    })
  }
}

// Export class instance
export const royaltyApi = new RoyaltyApi()
