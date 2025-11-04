<script lang="ts" setup>
import { useNotifications } from '@/composables/useNotifications'
import type { Notification } from '@layouts/types'

const {
  notifications,
  fetchNotifications,
  fetchStats,
  markAsRead,
  markAsUnread,
  removeNotification: removeNotificationApi,
  handleNotificationClick: handleNotificationClickApi,
} = useNotifications()

// Load notifications on component mount
onMounted(async () => {
  try {
    // Check if user is authenticated before fetching
    const accessToken = useCookie('accessToken').value
    if (!accessToken) {
      // Silently skip notification fetch for unauthenticated users
      return
    }

    await fetchNotifications()
    await fetchStats()
  }
  catch (error: any) {
    // Handle 401 errors gracefully (user not authenticated)
    if (error?.status === 401 || error?._statusCode === 401) {
      // Silently skip notification fetch for unauthenticated users
      return
    }
    console.error('Failed to load notifications:', error)
  }
})

const removeNotification = async (notificationId: number | string) => {
  try {
    await removeNotificationApi(notificationId)
  }
  catch (error) {
    console.error('Failed to remove notification:', error)
  }
}

const markRead = async (notificationIds: (number | string)[]) => {
  try {
    await markAsRead(notificationIds)
  }
  catch (error) {
    console.error('Failed to mark notifications as read:', error)
  }
}

const markUnRead = async (notificationIds: (number | string)[]) => {
  try {
    await markAsUnread(notificationIds)
  }
  catch (error) {
    console.error('Failed to mark notifications as unread:', error)
  }
}

const handleNotificationClick = async (notification: Notification) => {
  try {
    await handleNotificationClickApi(notification)
  }
  catch (error) {
    console.error('Failed to handle notification click:', error)
  }
}
</script>

<template>
  <Notifications
    :notifications="notifications"
    @remove="removeNotification"
    @read="markRead"
    @unread="markUnRead"
    @click:notification="handleNotificationClick"
  />
</template>
