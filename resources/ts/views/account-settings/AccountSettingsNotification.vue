<script lang="ts" setup>
const notificationSettings = ref([
  {
    type: 'New user registration',
    email: true,
    browser: true,
    app: true,
  },
  {
    type: 'New technical request',
    email: true,
    browser: true,
    app: true,
  },
  {
    type: 'Technical request status change',
    email: true,
    browser: false,
    app: true,
  },
  {
    type: 'New franchisor application',
    email: true,
    browser: true,
    app: false,
  },
  {
    type: 'Payment received',
    email: true,
    browser: false,
    app: false,
  },
  {
    type: 'System alerts',
    email: true,
    browser: true,
    app: true,
  },
])

const saveNotifications = () => {
  console.log('Notification settings saved:', notificationSettings.value)

  // Implement API call here
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardItem>
          <VCardTitle>Notification Preferences</VCardTitle>
          <p class="text-body-1 mb-0">
            Choose how you want to receive notifications. We need permission from your browser to show notifications.
            <span class="text-primary cursor-pointer">Request Permission</span>
          </p>
        </VCardItem>

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
              <tr
                v-for="notification in notificationSettings"
                :key="notification.type"
              >
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
          <VBtn @click="saveNotifications">
            Save Changes
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
