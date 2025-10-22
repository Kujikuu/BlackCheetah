import { $api } from '@/utils/api'
import type { ApiResponse } from '@/types/api'

const API_URL = '/v1/auth'

// Auth types
export interface LoginPayload {
  email: string
  password: string
  remember?: boolean
}

export interface RegisterPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export interface LoginResponse {
  user: {
    id: number
    name: string
    email: string
    role: string
    avatar?: string
    nationality?: string | null
  }
  access_token: string
  token_type: string
  expires_in: number
}

export interface RegisterResponse {
  user: {
    id: number
    name: string
    email: string
    role: string
    status: string
  }
  token: string
}

export interface RefreshTokenResponse {
  access_token: string
  token_type: string
  expires_in: number
}

export interface UserProfile {
  id: number
  name: string
  email: string
  role: string
  avatar?: string
  nationality?: string | null
  created_at: string
  updated_at: string
}

export class AuthApi {
  private readonly baseUrl = API_URL

  /**
   * Register user
   */
  async register(payload: RegisterPayload): Promise<ApiResponse<RegisterResponse>> {
    return await $api(`${this.baseUrl}/register`, {
      method: 'POST',
      body: payload,
    })
  }

  /**
   * Login user
   */
  async login(payload: LoginPayload): Promise<ApiResponse<LoginResponse>> {
    return await $api(`${this.baseUrl}/login`, {
      method: 'POST',
      body: payload,
    })
  }

  /**
   * Logout user
   */
  async logout(): Promise<ApiResponse<null>> {
    return await $api(`${this.baseUrl}/logout`, {
      method: 'POST',
    })
  }

  /**
   * Refresh access token
   */
  async refreshToken(): Promise<ApiResponse<RefreshTokenResponse>> {
    return await $api(`${this.baseUrl}/refresh`, {
      method: 'POST',
    })
  }

  /**
   * Get current user profile
   */
  async me(): Promise<ApiResponse<UserProfile>> {
    return await $api(`${this.baseUrl}/me`)
  }

  /**
   * Verify password for current user
   */
  async verifyPassword(password: string): Promise<ApiResponse<{ valid: boolean }>> {
    return await $api(`${this.baseUrl}/verify-password`, {
      method: 'POST',
      body: { password },
    })
  }

  /**
   * Change password for current user
   */
  async changePassword(payload: {
    current_password: string
    new_password: string
    new_password_confirmation: string
  }): Promise<ApiResponse<null>> {
    return await $api(`${this.baseUrl}/change-password`, {
      method: 'PUT',
      body: payload,
    })
  }
}

export const authApi = new AuthApi()
