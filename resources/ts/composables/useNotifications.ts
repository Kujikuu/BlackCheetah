import { $api } from '@/utils/api'
import type { Notification } from '@layouts/types'

interface NotificationStats {
  total: number
  unread: number
  read: number
}

interface BackendNotification {
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
  } | string  // Can be object or JSON string from API
  read_at: string | null
  created_at: string
  updated_at: string
}

interface NotificationResponse {
  data: BackendNotification[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

interface ApiResponse<T> {
  success: boolean
  data: T
  message?: string
}

export const useNotifications = () => {
  const notifications = ref<Notification[]>([])
  const loading = ref(false)
  const stats = ref<NotificationStats>({ total: 0, unread: 0, read: 0 })

  // Transform backend notification to frontend format
  const transformNotification = (backendNotification: BackendNotification): any => {
    // Parse data if it's a string (sometimes comes as JSON string from API)
    let parsedData: any = backendNotification.data
    if (typeof parsedData === 'string') {
      try {
        parsedData = JSON.parse(parsedData)
      } catch (e) {
        console.error('Failed to parse notification data:', e)
        parsedData = {}
      }
    }
    const data = parsedData || {}

    return {
      id: backendNotification.id,
      title: data.title || 'Notification',
      subtitle: data.message || data.subtitle || '',
      time: formatTime(backendNotification.created_at),
      color: data.color || 'primary',
      isSeen: !!backendNotification.read_at,
      icon: data.icon || 'tabler-bell',
      img: data.img,
      text: data.text,
      data: data,
      created_at: backendNotification.created_at,
      read_at: backendNotification.read_at,
    }
  }

  // Format time for display
  const formatTime = (dateString: string): string => {
    const date = new Date(dateString)
    const now = new Date()
    const diffInHours = Math.floor((now.getTime() - date.getTime()) / (1000 * 60 * 60))

    if (diffInHours < 1) {
      return 'Just now'
    } else if (diffInHours < 24) {
      return `${diffInHours}h ago`
    } else if (diffInHours < 48) {
      return 'Yesterday'
    } else {
      return date.toLocaleDateString()
    }
  }

  // Fetch notifications
  const fetchNotifications = async (unreadOnly = false) => {
    try {
      loading.value = true
      const params = unreadOnly ? { unread_only: true } : {}
      const response = await $api<ApiResponse<NotificationResponse>>('/v1/notifications', {
        method: 'GET',
        params,
      })

      notifications.value = response.data.data.map(transformNotification)
      return response.data
    } catch (error) {
      console.error('Error fetching notifications:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  // Get notification stats
  const fetchStats = async () => {
    try {
      const response = await $api<ApiResponse<NotificationStats>>('/v1/notifications/stats', {
        method: 'GET',
      })

      stats.value = response.data
      return response.data
    } catch (error) {
      console.error('Error fetching notification stats:', error)
      throw error
    }
  }

  // Mark single notification as read
  const markAsRead = async (notificationIds: (number | string)[]) => {
    try {
      if (notificationIds.length === 1) {
        await $api(`/v1/notifications/${notificationIds[0]}/read`, {
          method: 'PATCH',
        })
      } else {
        await $api('/v1/notifications/mark-multiple-read', {
          method: 'PATCH',
          body: { notification_ids: notificationIds },
        })
      }

      // Update local state
      notifications.value.forEach(notification => {
        if (notificationIds.includes(notification.id)) {
          notification.isSeen = true
        }
      })

      // Update stats
      await fetchStats()
    } catch (error) {
      console.error('Error marking notifications as read:', error)
      throw error
    }
  }

  // Mark single notification as unread
  const markAsUnread = async (notificationIds: (number | string)[]) => {
    try {
      if (notificationIds.length === 1) {
        await $api(`/v1/notifications/${notificationIds[0]}/unread`, {
          method: 'PATCH',
        })
      } else {
        // Use the mark multiple as unread endpoint
        await $api('/v1/notifications/mark-multiple-unread', {
          method: 'PATCH',
          body: { notification_ids: notificationIds },
        })
      }

      // Update local state
      notifications.value.forEach(notification => {
        if (notificationIds.includes(notification.id)) {
          notification.isSeen = false
        }
      })

      // Update stats
      await fetchStats()
    } catch (error) {
      console.error('Error marking notifications as unread:', error)
      throw error
    }
  }

  // Mark all notifications as read
  const markAllAsRead = async () => {
    try {
      await $api('/v1/notifications/mark-all-read', {
        method: 'PATCH',
      })

      // Update local state
      notifications.value.forEach(notification => {
        notification.isSeen = true
      })

      // Update stats
      await fetchStats()
    } catch (error) {
      console.error('Error marking all notifications as read:', error)
      throw error
    }
  }

  // Remove notification
  const removeNotification = async (notificationId: number | string) => {
    try {
      await $api(`/v1/notifications/${notificationId}`, {
        method: 'DELETE',
      })

      // Update local state
      const index = notifications.value.findIndex(n => n.id === notificationId)
      if (index > -1) {
        notifications.value.splice(index, 1)
      }

      // Update stats
      await fetchStats()
    } catch (error) {
      console.error('Error removing notification:', error)
      throw error
    }
  }

  // Handle notification click
  const handleNotificationClick = async (notification: any) => {
    if (!notification.isSeen) {
      await markAsRead([notification.id])
    }
  }

  return {
    notifications: readonly(notifications),
    loading: readonly(loading),
    stats: readonly(stats),
    fetchNotifications,
    fetchStats,
    markAsRead,
    markAsUnread,
    markAllAsRead,
    removeNotification,
    handleNotificationClick,
  }
}
