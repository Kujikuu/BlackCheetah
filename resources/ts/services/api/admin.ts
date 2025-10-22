import { $api } from '@/utils/api'
import type { ApiResponse, PaginatedResponse } from '@/types/api'

const API_URL = '/v1/admin'

// Admin dashboard types
export interface AdminDashboardStats {
  title: string
  color?: string
  icon: string
  stats: string
  height: number
  series: unknown[]
  chartOptions: unknown
}

export interface AdminChartData {
  requests: Array<{
    month: string
    requests: number
  }>
  revenue: Array<{
    month: string
    revenue: number
  }>
  users: Array<{
    month: string
    users: number
  }>
}

export interface RecentUser {
  id: number
  fullName: string
  email: string
  role: string
  status: string
  avatar: string
  joinedDate: string
  lastLogin?: string
  franchiseName?: string
  plan?: string
  location?: string
  phone?: string
  city?: string
}

export interface UserFilters {
  role?: string
  status?: string
  search?: string
  page?: number
  per_page?: number
}

export interface User {
  id: number
  fullName: string
  email: string
  role: string
  status: string
  avatar?: string
  joinedDate: string
  lastLogin?: string
  franchiseName?: string
  plan?: string
  location?: string
  phone?: string
  city?: string
}

export interface CreateUserPayload {
  fullName: string
  email: string
  password: string
  role: string
  phone?: string
  city?: string
  location?: string
}

export class AdminApi {
  private readonly baseUrl = API_URL

  /**
   * Get dashboard statistics
   */
  async getDashboardStats(): Promise<ApiResponse<AdminDashboardStats[]>> {
    return await $api(`${this.baseUrl}/dashboard/stats`)
  }

  /**
   * Get chart data for admin dashboard
   */
  async getDashboardChartData(): Promise<ApiResponse<AdminChartData>> {
    return await $api(`${this.baseUrl}/dashboard/chart-data`)
  }

  /**
   * Get recent users for dashboard
   */
  async getRecentUsers(): Promise<ApiResponse<RecentUser[]>> {
    return await $api(`${this.baseUrl}/dashboard/recent-users`)
  }

  /**
   * Get users list with filters
   */
  async getUsers(filters?: UserFilters): Promise<ApiResponse<User[]>> {
    return await $api(`${this.baseUrl}/users`, {
      method: 'GET',
      query: filters,
    })
  }

  /**
   * Get sales users list
   */
  async getSalesUsers(): Promise<ApiResponse<User[] | PaginatedResponse<User>>> {
    return await $api(`${this.baseUrl}/users/brokers`)
  }

  /**
   * Get franchisors list
   */
  async getFranchisors(): Promise<ApiResponse<User[] | PaginatedResponse<User>>> {
    return await $api(`${this.baseUrl}/users/franchisors`)
  }

  /**
   * Get franchisees list
   */
  async getFranchisees(): Promise<ApiResponse<User[] | PaginatedResponse<User>>> {
    return await $api(`${this.baseUrl}/users/franchisees`)
  }

  /**
   * Create new user (admin, sales, franchisor, or franchisee)
   */
  async createUser(userData: CreateUserPayload): Promise<ApiResponse<User>> {
    return await $api(`${this.baseUrl}/users`, {
      method: 'POST',
      body: userData,
    })
  }

  /**
   * Update user by ID
   */
  async updateUser(userId: number, userData: Partial<CreateUserPayload>): Promise<ApiResponse<User>> {
    return await $api(`${this.baseUrl}/users/${userId}`, {
      method: 'PUT',
      body: userData,
    })
  }

  /**
   * Delete user by ID
   */
  async deleteUser(userId: number): Promise<ApiResponse<null>> {
    return await $api(`${this.baseUrl}/users/${userId}`, {
      method: 'DELETE',
    })
  }

  /**
   * Reset user password
   */
  async resetUserPassword(userId: number, password?: string): Promise<ApiResponse<null>> {
    return await $api(`${this.baseUrl}/users/${userId}/reset-password`, {
      method: 'POST',
      body: password ? { password } : undefined,
    })
  }

  /**
   * Get broker users statistics
   */
  async getSalesUsersStats(): Promise<ApiResponse<any>> {
    return await $api(`${this.baseUrl}/users/brokers/stats`)
  }

  /**
   * Get franchisors statistics
   */
  async getFranchisorsStats(): Promise<ApiResponse<any>> {
    return await $api(`${this.baseUrl}/users/franchisors/stats`)
  }

  /**
   * Get franchisees statistics
   */
  async getFranchiseesStats(): Promise<ApiResponse<any>> {
    return await $api(`${this.baseUrl}/users/franchisees/stats`)
  }
}

export const adminApi = new AdminApi()
