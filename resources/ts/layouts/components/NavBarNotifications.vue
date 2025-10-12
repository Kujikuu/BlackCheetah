<script lang="ts" setup>
import type { Notification } from '@layouts/types'
import { useNotifications } from '@/composables/useNotifications'

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

const removeNotification = async (notificationId: number) => {
  try {
    await removeNotificationApi(notificationId)
  } catch (error) {
    console.error('Failed to remove notification:', error)
  }
}

const markRead = async (notificationIds: number[]) => {
  try {
    await markAsRead(notificationIds)
  } catch (error) {
    console.error('Failed to mark notifications as read:', error)
  }
}

const markUnRead = async (notificationIds: number[]) => {
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
  <Notifications
    :notifications="notifications"
    @remove="removeNotification"
    @read="markRead"
    @unread="markUnRead"
    @click:notification="handleNotificationClick"
  />
</template>
