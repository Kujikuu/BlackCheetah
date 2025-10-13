<script lang="ts" setup>
import type { NotificationPreferences } from '@/services/api/account-settings'
import { accountSettingsApi } from '@/services/api/account-settings'

const isLoading = ref(false)
const isSaving = ref(false)
const successMessage = ref('')

const notificationSettings = ref([
  {
    key: 'new_user_registration',
    type: 'New user registration',
    email: true,
    browser: true,
    app: true,
  },
  {
    key: 'new_technical_request',
    type: 'New technical request',
    email: true,
    browser: true,
    app: true,
  },
  {
    key: 'technical_request_status_change',
    type: 'Technical request status change',
    email: true,
    browser: false,
    app: true,
  },
  {
    key: 'new_franchisor_application',
    type: 'New franchisor application',
    email: true,
    browser: true,
    app: false,
  },
  {
    key: 'payment_received',
    type: 'Payment received',
    email: true,
    browser: false,
    app: false,
  },
  {
    key: 'system_alerts',
    type: 'System alerts',
    email: true,
    browser: true,
    app: true,
  },
])

// Load notification preferences
const loadNotifications = async () => {
  try {
    isLoading.value = true
    const response = await accountSettingsApi.getProfile()
    if (response.success && response.data.preferences?.notifications) {
      const prefs = response.data.preferences.notifications
      notificationSettings.value.forEach(setting => {
        const key = setting.key as keyof NotificationPreferences
        if (prefs[key]) {
          setting.email = prefs[key].email
          setting.browser = prefs[key].browser
          setting.app = prefs[key].app
        }
      })
    }
  }
  catch (error) {
    console.error('Error loading notifications:', error)
  }
  finally {
    isLoading.value = false
  }
}

const saveNotifications = async () => {
  try {
    isSaving.value = true
    successMessage.value = ''

    // Build preferences object
    const preferences: NotificationPreferences = {}
    notificationSettings.value.forEach(setting => {
      const key = setting.key as keyof NotificationPreferences
      preferences[key] = {
        email: setting.email,
        browser: setting.browser,
        app: setting.app,
      }
    })

    await accountSettingsApi.updateNotificationPreferences(preferences)
    successMessage.value = 'Notification preferences saved successfully'

    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
  }
  catch (error) {
    console.error('Error saving notifications:', error)
  }
  finally {
    isSaving.value = false
  }
}

// Load on mount
onMounted(() => {
  loadNotifications()
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard :loading="isLoading">
        <VCardItem>
          <VCardTitle>Notification Preferences</VCardTitle>
          <p class="text-body-1 mb-0">
            Choose how you want to receive notifications. We need permission from your browser to show notifications.
            <span class="text-primary cursor-pointer">Request Permission</span>
          </p>
        </VCardItem>

        <!-- Success Message -->
        <VCardText v-if="successMessage">
          <VAlert variant="tonal" color="success" closable @click:close="successMessage = ''">
            {{ successMessage }}
          </VAlert>
        </VCardText>

        <VCardText class="px-0">
          <VDivider />
          <VTable class="text-no-wrap rounded">
            <thead>
              <tr>
                <th scope="col">
                  Type
                </th>
                <th scope="col">
                  EMAIL
                </th>
                <th scope="col">
                  BROWSER
                </th>
                <th scope="col">
                  APP
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="notification in notificationSettings" :key="notification.type">
                <td class="text-body-1 text-high-emphasis">
                  {{ notification.type }}
                </td>
                <td>
                  <VCheckbox v-model="notification.email" />
                </td>
                <td>
                  <VCheckbox v-model="notification.browser" />
                </td>
                <td>
                  <VCheckbox v-model="notification.app" />
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCardText>

        <VDivider />

        <VCardText>
          <VBtn :loading="isSaving" :disabled="isLoading" @click="saveNotifications">
            Save Changes
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
