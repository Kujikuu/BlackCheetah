import { $api } from '@/utils/api'
import { getEndpoint } from '@/utils/api-router'
import type { ApiResponse, PaginatedResponse, TechnicalRequestStatus, TechnicalRequestCategory, Priority } from '@/types/api'

// Type definitions
export interface TechnicalRequest {
  id: number | string
  ticket_number: string
  title: string
  description: string
  category: `${TechnicalRequestCategory}`
  attachments?: string[]
  priority: `${Priority}`
  status: `${TechnicalRequestStatus}`
  requester_id: number
  requester?: {
    id: number
    name: string
    email: string
  }
  created_at: string
  updated_at: string
  is_open?: boolean
  is_resolved?: boolean
  age_in_hours?: number
}

export interface TechnicalRequestFilters {
  status?: string
  category?: string
  priority?: string
  search?: string
  sort_by?: string
  sort_order?: 'asc' | 'desc'
  per_page?: number
  page?: number
}

export interface TechnicalRequestStatistics {
  total_requests: number
  open_requests: number
  resolved_requests: number
  high_priority_requests: number
  requests_by_category: Record<string, number>
  requests_by_priority: Record<string, number>
  requests_by_status: Record<string, number>
}

export interface CreateTechnicalRequestData {
  title: string
  description: string
  category: string
  priority: string
  status?: string
  requester_id: number
  attachments?: File[]
}

export interface UpdateTechnicalRequestData {
  title?: string
  description?: string
  category?: string
  priority?: string
  status?: string
  attachments?: string[]
}


// API Service Class
export class TechnicalRequestApi {
  private getBaseUrl(): string {
    return getEndpoint('technical-requests')
  }
  /**
   * Get all technical requests with filters
   */
  async getTechnicalRequests(filters?: TechnicalRequestFilters) {
    return await $api<ApiResponse<PaginatedResponse<TechnicalRequest>>>(
      this.getBaseUrl(),
      {
        method: 'GET',
        params: filters,
      },
    )
  }

  /**
   * Get a single technical request by ID
   */
  async getTechnicalRequest(id: number | string): Promise<ApiResponse<TechnicalRequest>> {
    return await $api<ApiResponse<TechnicalRequest>>(`${this.getBaseUrl()}/${id}`, {
      method: 'GET',
    })
  }

  /**
   * Create a new technical request
   */
  async createTechnicalRequest(data: CreateTechnicalRequestData): Promise<ApiResponse<TechnicalRequest>> {
    // Create FormData for file uploads
    const formData = new FormData()
    formData.append('title', data.title)
    formData.append('description', data.description)
    formData.append('category', data.category)
    formData.append('priority', data.priority)
    formData.append('requester_id', data.requester_id.toString())
    
    if (data.status) {
      formData.append('status', data.status)
    }

    // Add attachments if they exist
    if (data.attachments && data.attachments.length > 0) {
      data.attachments.forEach((file) => {
        formData.append('attachments[]', file)
      })
    }

    return await $api<ApiResponse<TechnicalRequest>>(this.getBaseUrl(), {
      method: 'POST',
      body: formData,
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        // Don't set Content-Type - let browser set it for FormData with boundary
      },
    })
  }

  /**
   * Update a technical request
   */
  async updateTechnicalRequest(id: number | string, data: UpdateTechnicalRequestData): Promise<ApiResponse<TechnicalRequest>> {
    return await $api<ApiResponse<TechnicalRequest>>(`${this.getBaseUrl()}/${id}`, {
      method: 'PATCH',
      body: data,
    })
  }

  /**
   * Delete a technical request
   */
  async deleteTechnicalRequest(id: number | string): Promise<ApiResponse<void>> {
    return await $api<ApiResponse<void>>(`${this.getBaseUrl()}/${id}`, {
      method: 'DELETE',
    })
  }

  /**
   * Get technical request statistics
   */
  async getStatistics(): Promise<ApiResponse<TechnicalRequestStatistics>> {
    return await $api<ApiResponse<TechnicalRequestStatistics>>(`${this.getBaseUrl()}/statistics`, {
      method: 'GET',
    })
  }

  /**
   * Resolve a technical request
   */
  async resolve(id: number | string): Promise<ApiResponse<TechnicalRequest>> {
    return await $api<ApiResponse<TechnicalRequest>>(`${this.getBaseUrl()}/${id}/resolve`, {
      method: 'PATCH',
    })
  }

  /**
   * Close a technical request
   */
  async close(id: number | string): Promise<ApiResponse<TechnicalRequest>> {
    return await $api<ApiResponse<TechnicalRequest>>(`${this.getBaseUrl()}/${id}/close`, {
      method: 'PATCH',
    })
  }

  /**
   * Add attachments to a technical request
   */
  async addAttachments(id: number | string, attachmentUrl: string): Promise<ApiResponse<TechnicalRequest>> {
    return await $api<ApiResponse<TechnicalRequest>>(`${this.getBaseUrl()}/${id}/attachments`, {
      method: 'POST',
      body: {
        attachment_url: attachmentUrl,
      },
    })
  }

  /**
   * Bulk delete technical requests
   */
  async bulkDelete(requestIds: (number | string)[]): Promise<ApiResponse<{ count: number }>> {
    return await $api<ApiResponse<{ count: number }>>(`${this.getBaseUrl()}/bulk-delete`, {
      method: 'POST',
      body: {
        ids: requestIds,
      },
    })
  }
}

// Export class instance
export const technicalRequestApi = new TechnicalRequestApi()
