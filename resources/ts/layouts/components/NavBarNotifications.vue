<script lang="ts" setup>
import { useNotifications } from '@/composables/useNotifications'
import type { Notification } from '@layouts/types'

const {
  notifications,
  loading,
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
    await fetchNotifications()
    await fetchStats()
  } catch (error) {
    console.error('Failed to load notifications:', error)
  }
})

const removeNotification = async (notificationId: number | string) => {
  try {
    await removeNotificationApi(notificationId)
  } catch (error) {
    console.error('Failed to remove notification:', error)
  }
}

const markRead = async (notificationIds: (number | string)[]) => {
  try {
    await markAsRead(notificationIds)
  } catch (error) {
    console.error('Failed to mark notifications as read:', error)
  }
}

const markUnRead = async (notificationIds: (number | string)[]) => {
  try {
    await markAsUnread(notificationIds)
  } catch (error) {
    console.error('Failed to mark notifications as unread:', error)
  }
}

const handleNotificationClick = async (notification: Notification) => {
  try {
    await handleNotificationClickApi(notification)
  } catch (error) {
    console.error('Failed to handle notification click:', error)
  }
}
</script>

<template>
  <Notifications :notifications="notifications" @remove="removeNotification" @read="markRead" @unread="markUnRead"
    @click:notification="handleNotificationClick" />
</template>
