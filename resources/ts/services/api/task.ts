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
    success: boolean
    data: Task[]
    pagination: {
        total: number
        perPage: number
        currentPage: number
        lastPage: number
    }
    message: string
}

export interface TaskStatisticsResponse {
    success: boolean
    data: TaskStatistics
    message: string
}

export interface TaskResponse {
    success: boolean
    data: Task
    message: string
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

        return await $api<TaskListResponse>(url, {
            method: 'GET',
        })
    },

    /**
     * Get task statistics for the current user
     */
    async getStatistics(): Promise<TaskStatisticsResponse> {
        const baseUrl = getBaseUrl()

        return await $api<TaskStatisticsResponse>(`${baseUrl}/statistics`, {
            method: 'GET',
        })
    },

    /**
     * Update task status
     */
    async updateTaskStatus(taskId: number, status: string): Promise<TaskResponse> {
        const baseUrl = getBaseUrl()

        return await $api<TaskResponse>(`${baseUrl}/${taskId}/status`, {
            method: 'PATCH',
            body: {
                status,
            },
        })
    },

    /**
     * Get a single task by ID
     */
    async getTask(taskId: number): Promise<TaskResponse> {
        const baseUrl = getBaseUrl()

        return await $api<TaskResponse>(`${baseUrl}/${taskId}`, {
            method: 'GET',
        })
    },
}
