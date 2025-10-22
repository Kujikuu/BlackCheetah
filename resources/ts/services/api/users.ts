import { $api } from '@/utils/api'
import { getEndpoint } from '@/utils/api-router'
import type { ApiResponse } from '@/types/api'

// User types
export interface User {
  id: number
  name: string
  email: string
  role: string
  status: string
  avatar?: string
  created_at: string
  updated_at: string
  franchise_name?: string
  phone?: string
  location?: string
}

export interface Broker {
  id: number
  name: string
  email: string
  role: string
  status: string
  avatar?: string
  created_at: string
}

export interface UserFilters {
  role?: string
  status?: string
  search?: string
  page?: number
  per_page?: number
}

export interface CreateUserPayload {
  name: string
  email: string
  password: string
  role: string
  franchise_id?: number
}

export interface UpdateUserPayload {
  id: number
  name?: string
  email?: string
  password?: string
  role?: string
  status?: string
  franchise_id?: number
}

export class UsersApi {
  private getBaseUrl(): string {
    return getEndpoint('users')
  }

  /**
   * Get brokers list for franchisor
   */
  async getBrokers(): Promise<ApiResponse<Broker[]>> {
    return await $api('/v1/franchisor/brokers')
  }

  /**
   * Get users list with filters
   */
  async getUsers(filters?: UserFilters): Promise<ApiResponse<User[]>> {
    return await $api(this.getBaseUrl(), {
      method: 'GET',
      query: filters,
    })
  }

  /**
   * Get user by ID
   */
  async getUser(id: number): Promise<ApiResponse<User>> {
    return await $api(`${this.getBaseUrl()}/${id}`)
  }

  /**
   * Create new user
   */
  async createUser(payload: CreateUserPayload): Promise<ApiResponse<User>> {
    return await $api(this.getBaseUrl(), {
      method: 'POST',
      body: payload,
    })
  }

  /**
   * Update user
   */
  async updateUser(payload: UpdateUserPayload): Promise<ApiResponse<User>> {
    return await $api(`${this.getBaseUrl()}/${payload.id}`, {
      method: 'PUT',
      body: payload,
    })
  }

  /**
   * Delete user
   */
  async deleteUser(id: number): Promise<ApiResponse<null>> {
    return await $api(`${this.getBaseUrl()}/${id}`, {
      method: 'DELETE',
    })
  }

  /**
   * Get users by role
   */
  async getUsersByRole(role: string, filters?: UserFilters): Promise<ApiResponse<User[]>> {
    return await $api(`${this.getBaseUrl()}/by-role/${role}`, {
      method: 'GET',
      query: filters,
    })
  }

  /**
   * Get brokers with filters (for franchisor)
   */
  async getBrokersWithFilters(filters: { 
    search?: string; 
    status?: string; 
    order_by?: string; 
    sort_by?: string; 
    page?: number; 
    per_page?: number;
  } = {}): Promise<ApiResponse<{ data: any[]; total: number; per_page: number; current_page: number; last_page: number }>> {
    const params = new URLSearchParams()
    if (filters.search) params.append('search', filters.search)
    if (filters.status) params.append('status', filters.status)
    if (filters.order_by) params.append('order_by', filters.order_by)
    if (filters.sort_by) params.append('sort_by', filters.sort_by)
    if (filters.page) params.append('page', filters.page.toString())
    if (filters.per_page) params.append('per_page', filters.per_page.toString())

    return await $api(`/v1/franchisor/brokers?${params.toString()}`)
  }

  /**
   * Delete broker
   */
  async deleteBroker(brokerId: number): Promise<ApiResponse<null>> {
    return await $api(`/v1/franchisor/brokers/${brokerId}`, {
      method: 'DELETE',
    })
  }

  /**
   * Get detailed broker info
   */
  async getBrokerDetails(brokerId: number): Promise<ApiResponse<any>> {
    return await $api(`/v1/franchisor/brokers/${brokerId}`)
  }

  /**
   * Create new broker
   */
  async createBroker(payload: any): Promise<ApiResponse<any>> {
    return await $api('/v1/franchisor/brokers', {
      method: 'POST',
      body: payload,
    })
  }

  /**
   * Update broker
   */
  async updateBroker(brokerId: number, payload: any): Promise<ApiResponse<any>> {
    return await $api(`/v1/franchisor/brokers/${brokerId}`, {
      method: 'PUT',
      body: payload,
    })
  }
}

export const usersApi = new UsersApi()
