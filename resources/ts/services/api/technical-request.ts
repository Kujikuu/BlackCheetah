import { $api } from '@/utils/api'

// Base API URL
const API_URL = '/v1/technical-requests'

// Type definitions
export interface TechnicalRequest {
  id: number | string
  ticket_number: string
  title: string
  description: string
  category: 'hardware' | 'software' | 'network' | 'pos_system' | 'website' | 'mobile_app' | 'training' | 'other'
  priority: 'low' | 'medium' | 'high' | 'urgent'
  status: 'open' | 'in_progress' | 'pending_info' | 'resolved' | 'closed' | 'cancelled'
  requester_id: number
  requester?: {
    id: number
    name: string
    email: string
  }
  assigned_to?: number | null
  assigned_user?: {
    id: number
    name: string
    email: string
  } | null
  franchise_id?: number | null
  franchise?: {
    id: number
    business_name: string
  } | null
  unit_id?: number | null
  unit?: {
    id: number
    name: string
  } | null
  affected_system?: string | null
  steps_to_reproduce?: string | null
  expected_behavior?: string | null
  actual_behavior?: string | null
  browser_version?: string | null
  operating_system?: string | null
  device_type?: string | null
  attachments?: string[]
  internal_notes?: string | null
  resolution_notes?: string | null
  first_response_at?: string | null
  resolved_at?: string | null
  closed_at?: string | null
  response_time_hours?: number | null
  resolution_time_hours?: number | null
  satisfaction_rating?: number | null
  satisfaction_feedback?: string | null
  is_escalated: boolean
  escalated_at?: string | null
  created_at: string
  updated_at: string
  is_open?: boolean
  is_resolved?: boolean
  age_in_hours?: number
  response_time_status?: 'on_time' | 'warning' | 'overdue' | 'responded'
}

export interface TechnicalRequestFilters {
  status?: string
  category?: string
  priority?: string
  franchise_id?: number
  unit_id?: number
  assigned_to?: number
  search?: string
  sort_by?: string
  sort_order?: 'asc' | 'desc'
  per_page?: number
  page?: number
}

export interface TechnicalRequestStatistics {
  total_requests: number
  open_requests: number
  in_progress_requests: number
  resolved_requests: number
  closed_requests: number
  overdue_requests: number
  escalated_requests: number
  unassigned_requests: number
  average_response_time: number
  average_resolution_time: number
  satisfaction_average: number
  by_category: Record<string, number>
  by_priority: Record<string, number>
  by_status: Record<string, number>
}

export interface CreateTechnicalRequestData {
  title: string
  description: string
  category: string
  priority: string
  status?: string
  requester_id: number
  assigned_to?: number | null
  franchise_id?: number | null
  unit_id?: number | null
  affected_system?: string
  steps_to_reproduce?: string
  expected_behavior?: string
  actual_behavior?: string
  browser_version?: string
  operating_system?: string
  device_type?: string
  attachments?: string[]
  internal_notes?: string
}

export interface UpdateTechnicalRequestData {
  title?: string
  description?: string
  category?: string
  priority?: string
  status?: string
  assigned_to?: number | null
  affected_system?: string
  steps_to_reproduce?: string
  expected_behavior?: string
  actual_behavior?: string
  internal_notes?: string
  resolution_notes?: string
}

export interface RespondData {
  message: string
  status?: string
}

export interface ResolveData {
  resolution_notes: string
}

export interface CloseData {
  satisfaction_rating?: number
  satisfaction_feedback?: string
}

export interface AssignData {
  assigned_to: number
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

// API Service
export const technicalRequestApi = {
  /**
   * Get all technical requests with filters
   */
  async getTechnicalRequests(filters?: TechnicalRequestFilters) {
    return await $api<{ success: boolean; data: PaginatedResponse<TechnicalRequest>; message: string }>(
      API_URL,
      {
        method: 'GET',
        params: filters,
      },
    )
  },

  /**
   * Get a single technical request by ID
   */
  async getTechnicalRequest(id: number | string) {
    return await $api<{ success: boolean; data: TechnicalRequest; message: string }>(
      `${API_URL}/${id}`,
      {
        method: 'GET',
      },
    )
  },

  /**
   * Create a new technical request
   */
  async createTechnicalRequest(data: CreateTechnicalRequestData) {
    return await $api<{ success: boolean; data: TechnicalRequest; message: string }>(
      API_URL,
      {
        method: 'POST',
        body: data,
      },
    )
  },

  /**
   * Update a technical request
   */
  async updateTechnicalRequest(id: number | string, data: UpdateTechnicalRequestData) {
    return await $api<{ success: boolean; data: TechnicalRequest; message: string }>(
      `${API_URL}/${id}`,
      {
        method: 'PATCH',
        body: data,
      },
    )
  },

  /**
   * Delete a technical request
   */
  async deleteTechnicalRequest(id: number | string) {
    return await $api<{ success: boolean; message: string }>(
      `${API_URL}/${id}`,
      {
        method: 'DELETE',
      },
    )
  },

  /**
   * Get technical request statistics
   */
  async getStatistics(filters?: { franchise_id?: number; unit_id?: number }) {
    return await $api<{ success: boolean; data: TechnicalRequestStatistics; message: string }>(
      `${API_URL}/statistics`,
      {
        method: 'GET',
        params: filters,
      },
    )
  },

  /**
   * Respond to a technical request
   */
  async respond(id: number | string, data: RespondData) {
    return await $api<{ success: boolean; data: TechnicalRequest; message: string }>(
      `${API_URL}/${id}/respond`,
      {
        method: 'POST',
        body: data,
      },
    )
  },

  /**
   * Resolve a technical request
   */
  async resolve(id: number | string, data: ResolveData) {
    return await $api<{ success: boolean; data: TechnicalRequest; message: string }>(
      `${API_URL}/${id}/resolve`,
      {
        method: 'PATCH',
        body: data,
      },
    )
  },

  /**
   * Close a technical request
   */
  async close(id: number | string, data: CloseData) {
    return await $api<{ success: boolean; data: TechnicalRequest; message: string }>(
      `${API_URL}/${id}/close`,
      {
        method: 'PATCH',
        body: data,
      },
    )
  },

  /**
   * Escalate a technical request
   */
  async escalate(id: number | string) {
    return await $api<{ success: boolean; data: TechnicalRequest; message: string }>(
      `${API_URL}/${id}/escalate`,
      {
        method: 'PATCH',
      },
    )
  },

  /**
   * Assign a technical request to a user
   */
  async assign(id: number | string, data: AssignData) {
    return await $api<{ success: boolean; data: TechnicalRequest; message: string }>(
      `${API_URL}/${id}/assign`,
      {
        method: 'PATCH',
        body: data,
      },
    )
  },

  /**
   * Add attachments to a technical request
   */
  async addAttachments(id: number | string, files: File[]) {
    const formData = new FormData()

    files.forEach((file, index) => {
      formData.append(`attachments[${index}]`, file)
    })

    return await $api<{ success: boolean; data: TechnicalRequest; message: string }>(
      `${API_URL}/${id}/attachments`,
      {
        method: 'POST',
        body: formData,
      },
    )
  },

  /**
   * Get franchisor technical requests
   */
  async getFranchisorRequests(filters?: TechnicalRequestFilters) {
    return await $api<{ success: boolean; data: PaginatedResponse<TechnicalRequest>; message: string }>(
      '/v1/franchisor/technical-requests',
      {
        method: 'GET',
        params: filters,
      },
    )
  },

  /**
   * Get admin technical requests
   */
  async getAdminRequests(filters?: TechnicalRequestFilters) {
    return await $api<{ success: boolean; data: PaginatedResponse<TechnicalRequest>; message: string }>(
      '/v1/admin/technical-requests',
      {
        method: 'GET',
        params: filters,
      },
    )
  },

  /**
   * Get employee technical requests
   */
  async getEmployeeRequests(filters?: TechnicalRequestFilters) {
    return await $api<{ success: boolean; data: PaginatedResponse<TechnicalRequest>; message: string }>(
      '/v1/employee/technical-requests',
      {
        method: 'GET',
        params: filters,
      },
    )
  },

  /**
   * Bulk assign technical requests (admin only)
   */
  async bulkAssign(requestIds: number[], assignedTo: number) {
    return await $api<{ success: boolean; message: string }>(
      '/v1/admin/technical-requests/bulk-assign',
      {
        method: 'POST',
        body: {
          request_ids: requestIds,
          assigned_to: assignedTo,
        },
      },
    )
  },

  /**
   * Bulk resolve technical requests (admin only)
   */
  async bulkResolve(requestIds: number[], resolutionNotes: string) {
    return await $api<{ success: boolean; message: string }>(
      '/v1/admin/technical-requests/bulk-resolve',
      {
        method: 'POST',
        body: {
          request_ids: requestIds,
          resolution_notes: resolutionNotes,
        },
      },
    )
  },

  /**
   * Bulk delete technical requests
   */
  async bulkDelete(requestIds: (number | string)[]) {
    return await $api<{ success: boolean; message: string; count: number }>(
      '/v1/technical-requests/bulk-delete',
      {
        method: 'POST',
        body: {
          ids: requestIds,
        },
      },
    )
  },
}
