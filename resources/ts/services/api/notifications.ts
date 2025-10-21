import { $api } from '@/utils/api'
import type { ApiResponse, PaginatedResponse } from '@/types/api'

// Notification types
export interface BackendNotification {
  id: string
  type: string
  notifiable_type: string
  notifiable_id: number
  data: {
    title?: string
    message?: string
    subtitle?: string
    icon?: string
    img?: string
    text?: string
    color?: string
  } | string // Can be object or JSON string from API
  read_at: string | null
  created_at: string
  updated_at: string
}

export interface Notification {
  id: string
  title: string
  subtitle: string
  time: string
  color: string
  isSeen: boolean
  icon: string
  img?: string
  text?: string
  data: any
  created_at: string
  read_at: string | null
}

export interface NotificationStats {
  total: number
  unread: number
  read: number
}

export interface NotificationFilters {
  unread_only?: boolean
  page?: number
  per_page?: number
}

export interface MarkNotificationsPayload {
  notification_ids: (number | string)[]
}

export class NotificationsApi {
  private readonly baseUrl = '/v1/notifications'

  /**
   * Get notifications with optional filters
   */
  async getNotifications(filters?: NotificationFilters): Promise<ApiResponse<PaginatedResponse<BackendNotification>>> {
    return await $api(this.baseUrl, {
      method: 'GET',
      query: filters,
    })
  }

  /**
   * Get notification statistics
   */
  async getStats(): Promise<ApiResponse<NotificationStats>> {
    return await $api(`${this.baseUrl}/stats`)
  }

  /**
   * Mark single notification as read
   */
  async markAsRead(notificationId: number | string): Promise<ApiResponse<null>> {
    return await $api(`${this.baseUrl}/${notificationId}/read`, {
      method: 'PATCH',
    })
  }

  /**
   * Mark single notification as unread
   */
  async markAsUnread(notificationId: number | string): Promise<ApiResponse<null>> {
    return await $api(`${this.baseUrl}/${notificationId}/unread`, {
      method: 'PATCH',
    })
  }

  /**
   * Mark multiple notifications as read
   */
  async markMultipleAsRead(payload: MarkNotificationsPayload): Promise<ApiResponse<null>> {
    return await $api(`${this.baseUrl}/mark-multiple-read`, {
      method: 'PATCH',
      body: payload,
    })
  }

  /**
   * Mark multiple notifications as unread
   */
  async markMultipleAsUnread(payload: MarkNotificationsPayload): Promise<ApiResponse<null>> {
    return await $api(`${this.baseUrl}/mark-multiple-unread`, {
      method: 'PATCH',
      body: payload,
    })
  }

  /**
   * Mark all notifications as read
   */
  async markAllAsRead(): Promise<ApiResponse<null>> {
    return await $api(`${this.baseUrl}/mark-all-read`, {
      method: 'PATCH',
    })
  }

  /**
   * Delete notification
   */
  async deleteNotification(notificationId: number | string): Promise<ApiResponse<null>> {
    return await $api(`${this.baseUrl}/${notificationId}`, {
      method: 'DELETE',
    })
  }
}

export const notificationsApi = new NotificationsApi()
