import { $api } from '@/utils/api'
import type { ApiResponse } from '@/types/api'

// Base API URL
const API_URL = '/v1/account'

// Type definitions
export interface UserProfile {
  id: number
  name: string
  email: string
  phone: string | null
  avatar: string | null
  role: string
  status: string
  date_of_birth: string | null
  gender: string | null
  nationality: string | null
  state: string | null
  city: string | null
  address: string | null
  preferences: UserPreferences | null
}

export interface UserPreferences {
  language?: string
  notifications?: NotificationPreferences
}

export interface NotificationPreferences {
  new_user_registration?: NotificationChannels
  new_technical_request?: NotificationChannels
  technical_request_status_change?: NotificationChannels
  new_franchisor_application?: NotificationChannels
  payment_received?: NotificationChannels
  system_alerts?: NotificationChannels
}

export interface NotificationChannels {
  email: boolean
  browser: boolean
  app: boolean
}

export interface UpdateProfilePayload {
  name?: string
  email?: string
  phone?: string | null
  date_of_birth?: string | null
  gender?: string | null
  nationality?: string | null
  state?: string | null
  city?: string | null
  address?: string | null
  preferences?: UserPreferences
}

export interface UpdatePasswordPayload {
  current_password: string
  password: string
  password_confirmation: string
}

export interface UpdateAvatarPayload {
  avatar: File
}

// API Service Class
export class AccountSettingsApi {
  private readonly baseUrl = API_URL

  /**
   * Get current user profile
   */
  async getProfile(): Promise<ApiResponse<UserProfile>> {
    return await $api<ApiResponse<UserProfile>>(`${this.baseUrl}/profile`, {
      method: 'GET',
    })
  }

  /**
   * Update user profile
   */
  async updateProfile(payload: UpdateProfilePayload): Promise<ApiResponse<UserProfile>> {
    return await $api<ApiResponse<UserProfile>>(`${this.baseUrl}/profile`, {
      method: 'PUT',
      body: payload,
    })
  }

  /**
   * Update user password
   */
  async updatePassword(payload: UpdatePasswordPayload): Promise<ApiResponse<void>> {
    return await $api<ApiResponse<void>>(`${this.baseUrl}/password`, {
      method: 'PUT',
      body: payload,
    })
  }

  /**
   * Upload user avatar
   */
  async uploadAvatar(file: File): Promise<ApiResponse<{ avatar: string }>> {
    const formData = new FormData()
    formData.append('avatar', file)

    return await $api<ApiResponse<{ avatar: string }>>(`${this.baseUrl}/avatar`, {
      method: 'POST',
      body: formData,
    })
  }

  /**
   * Delete user avatar
   */
  async deleteAvatar(): Promise<ApiResponse<void>> {
    return await $api<ApiResponse<void>>(`${this.baseUrl}/avatar`, {
      method: 'DELETE',
    })
  }

  /**
   * Update notification preferences
   */
  async updateNotificationPreferences(preferences: NotificationPreferences): Promise<ApiResponse<UserProfile>> {
    return await $api<ApiResponse<UserProfile>>(`${this.baseUrl}/notifications`, {
      method: 'PUT',
      body: { notifications: preferences },
    })
  }
}

// Export class instance
export const accountSettingsApi = new AccountSettingsApi()
