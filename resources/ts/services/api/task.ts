import { $api } from '@/utils/api'
import { getEndpoint } from '@/utils/api-router'
import type { ApiResponse, PaginatedResponse, TaskStatus, Priority } from '@/types/api'

// Type definitions
export interface Task {
  id: number
  title: string
  description: string
  category: string
  assignedTo: string
  unitName: string | null
  startDate: string | null
  dueDate: string
  priority: `${Priority}`
  status: `${TaskStatus}`
  leadId: number | null
  leadName: string | null
}

export interface TaskStatistics {
  totalTasks: number
  completedTasks: number
  inProgressTasks: number
  dueTasks: number
}

export interface TaskListResponse extends ApiResponse<Task[]> {
  pagination: {
    total: number
    perPage: number
    currentPage: number
    lastPage: number
  }
}

export interface TaskStatisticsResponse extends ApiResponse<TaskStatistics> {}

export interface TaskResponse extends ApiResponse<Task> {}

export interface TaskFilters {
  status?: string
  priority?: string
  category?: string
  filter?: 'created' | 'assigned' | 'all'
  page?: number
  perPage?: number
}

// API Service Class
export class TaskApi {
  private getBaseUrl(): string {
    return getEndpoint('tasks')
  }

  /**
   * Get tasks for the current user
   */
  async getMyTasks(filters?: TaskFilters): Promise<TaskListResponse> {
    const baseUrl = this.getBaseUrl()
    const params = new URLSearchParams()

    if (filters?.status)
      params.append('status', filters.status)
    if (filters?.priority)
      params.append('priority', filters.priority)
    if (filters?.category)
      params.append('category', filters.category)
    if (filters?.page)
      params.append('page', filters.page.toString())
    if (filters?.perPage)
      params.append('per_page', filters.perPage.toString())

    const queryString = params.toString()
    const url = queryString ? `${baseUrl}?${queryString}` : baseUrl

    return await $api<TaskListResponse>(url, {
      method: 'GET',
    })
  }

  /**
   * Get franchisor tasks with filters (supports bidirectional task management)
   */
  async getFranchisorTasks(filters?: TaskFilters): Promise<ApiResponse<{ data: any[] }>> {
    const params = new URLSearchParams()

    if (filters?.filter)
      params.append('filter', filters.filter)
    if (filters?.status)
      params.append('status', filters.status)
    if (filters?.priority)
      params.append('priority', filters.priority)
    if (filters?.category)
      params.append('category', filters.category)
    if (filters?.page)
      params.append('page', filters.page.toString())
    if (filters?.perPage)
      params.append('per_page', filters.perPage.toString())

    const queryString = params.toString()
    const url = queryString ? `/v1/franchisor/tasks?${queryString}` : '/v1/franchisor/tasks'

    return await $api(url)
  }

  /**
   * Create franchisor task
   */
  async createFranchisorTask(taskData: any): Promise<ApiResponse<any>> {
    return await $api('/v1/franchisor/tasks', {
      method: 'POST',
      body: taskData,
    })
  }

  /**
   * Get task statistics for the current user
   */
  async getStatistics(): Promise<TaskStatisticsResponse> {
    const baseUrl = this.getBaseUrl()

    return await $api<TaskStatisticsResponse>(`${baseUrl}/statistics`, {
      method: 'GET',
    })
  }

  /**
   * Update task status
   */
  async updateTaskStatus(taskId: number, status: string): Promise<TaskResponse> {
    const baseUrl = this.getBaseUrl()

    return await $api<TaskResponse>(`${baseUrl}/${taskId}/status`, {
      method: 'PATCH',
      body: {
        status,
      },
    })
  }

  /**
   * Get a single task by ID
   */
  async getTask(taskId: number): Promise<TaskResponse> {
    const baseUrl = this.getBaseUrl()

    return await $api<TaskResponse>(`${baseUrl}/${taskId}`, {
      method: 'GET',
    })
  }

  /**
   * Create new task
   */
  async createTask(taskData: any): Promise<TaskResponse> {
    const baseUrl = this.getBaseUrl()

    return await $api<TaskResponse>(baseUrl, {
      method: 'POST',
      body: taskData,
    })
  }

  /**
   * Update task
   */
  async updateTask(taskId: number, taskData: any): Promise<TaskResponse> {
    const baseUrl = this.getBaseUrl()

    return await $api<TaskResponse>(`${baseUrl}/${taskId}`, {
      method: 'PUT',
      body: taskData,
    })
  }

  /**
   * Delete task
   */
  async deleteTask(taskId: number): Promise<ApiResponse<null>> {
    const baseUrl = this.getBaseUrl()

    return await $api<ApiResponse<null>>(`${baseUrl}/${taskId}`, {
      method: 'DELETE',
    })
  }

  /**
   * Get franchisee tasks with filters (supports bidirectional task management)
   */
  async getFranchiseeTasks(filters?: TaskFilters): Promise<ApiResponse<{ data: any[] }>> {
    const params = new URLSearchParams()

    if (filters?.filter)
      params.append('filter', filters.filter)
    if (filters?.status)
      params.append('status', filters.status)
    if (filters?.priority)
      params.append('priority', filters.priority)
    if (filters?.category)
      params.append('category', filters.category)
    if (filters?.page)
      params.append('page', filters.page.toString())
    if (filters?.perPage)
      params.append('per_page', filters.perPage.toString())

    const queryString = params.toString()
    const url = queryString ? `/v1/unit-manager/my-tasks?${queryString}` : '/v1/unit-manager/my-tasks'

    return await $api(url)
  }

  /**
   * Create franchisee task (can be assigned to franchisor)
   */
  async createFranchiseeTask(taskData: any): Promise<ApiResponse<any>> {
    return await $api('/v1/unit-manager/tasks', {
      method: 'POST',
      body: taskData,
    })
  }

  /**
   * Update franchisee task
   */
  async updateFranchiseeTask(taskId: number, taskData: any): Promise<ApiResponse<any>> {
    return await $api(`/v1/unit-manager/tasks/${taskId}`, {
      method: 'PUT',
      body: taskData,
    })
  }

  /**
   * Delete franchisee task
   */
  async deleteFranchiseeTask(taskId: number): Promise<ApiResponse<void>> {
    return await $api(`/v1/unit-manager/tasks/${taskId}`, {
      method: 'DELETE',
    })
  }
}

// Export class instance
export const taskApi = new TaskApi()
