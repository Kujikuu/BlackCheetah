import { $api } from '@/utils/api'

// Get base API URL based on user role
const getBaseUrl = (): string => {
    const userDataCookie = useCookie('userData')
    const userData = userDataCookie.value as any
    const userRole = userData?.role

    // Sales users use /v1/sales/tasks
    if (userRole === 'sales') {
        return '/v1/sales/tasks'
    }

    // Franchisors use /v1/franchisor/tasks
    if (userRole === 'franchisor') {
        return '/v1/franchisor/tasks'
    }

    // Admin and others use /v1/tasks
    return '/v1/tasks'
}

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
    priority: 'low' | 'medium' | 'high' | 'urgent'
    status: 'pending' | 'in_progress' | 'completed' | 'cancelled' | 'on_hold'
    leadId: number | null
    leadName: string | null
}

export interface TaskStatistics {
    totalTasks: number
    completedTasks: number
    inProgressTasks: number
    dueTasks: number
}

export interface TaskListResponse {
    data: Task[]
    pagination: {
        total: number
        perPage: number
        currentPage: number
        lastPage: number
    }
}

export interface TaskFilters {
    status?: string
    priority?: string
    category?: string
    page?: number
    perPage?: number
}

// API Functions
export const taskApi = {
    /**
     * Get tasks for the current user
     */
    async getMyTasks(filters?: TaskFilters): Promise<TaskListResponse> {
        const baseUrl = getBaseUrl()
        const params = new URLSearchParams()

        if (filters?.status) params.append('status', filters.status)
        if (filters?.priority) params.append('priority', filters.priority)
        if (filters?.category) params.append('category', filters.category)
        if (filters?.page) params.append('page', filters.page.toString())
        if (filters?.perPage) params.append('per_page', filters.perPage.toString())

        const queryString = params.toString()
        const url = queryString ? `${baseUrl}?${queryString}` : baseUrl

        const response: any = await $api(url, {
            method: 'GET',
        })

        // Backend returns { success, data, pagination, message }
        return {
            data: response.data || [],
            pagination: response.pagination || { total: 0, perPage: 15, currentPage: 1, lastPage: 1 },
        }
    },

    /**
     * Get task statistics for the current user
     */
    async getStatistics(): Promise<TaskStatistics> {
        const baseUrl = getBaseUrl()

        const response: any = await $api(`${baseUrl}/statistics`, {
            method: 'GET',
        })

        return response.data || response
    },

    /**
     * Update task status
     */
    async updateTaskStatus(taskId: number, status: string): Promise<Task> {
        const baseUrl = getBaseUrl()

        const response: any = await $api(`${baseUrl}/${taskId}/status`, {
            method: 'PATCH',
            body: {
                status,
            },
        })

        return response.data || response
    },

    /**
     * Get a single task by ID
     */
    async getTask(taskId: number): Promise<Task> {
        const baseUrl = getBaseUrl()

        return await $api(`${baseUrl}/${taskId}`, {
            method: 'GET',
        })
    },
}
