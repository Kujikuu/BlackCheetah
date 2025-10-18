<script lang="ts" setup>
import AccountSettingsAccount from '@/views/account-settings/AccountSettingsAccount.vue'
import AccountSettingsNotification from '@/views/account-settings/AccountSettingsNotification.vue'
import AccountSettingsSecurity from '@/views/account-settings/AccountSettingsSecurity.vue'

definePage({
  meta: {
    action: 'manage',
  },
})

const route = useRoute('account-settings-tab')

const activeTab = computed({
  get: () => route.params.tab,
  set: () => route.params.tab,
})

// tabs
const tabs = [
  { title: 'Account', icon: 'tabler-user', tab: 'account' },
  { title: 'Security', icon: 'tabler-lock', tab: 'security' },
  { title: 'Notifications', icon: 'tabler-bell', tab: 'notifications' },
]
</script>

<template>
  <div>
    <!-- Page Header -->
    <div class="mb-6">
      <h2 class="text-h2 mb-1">
        Account Settings
      </h2>
      <p class="text-body-1 text-medium-emphasis">
        Manage your account preferences and settings
      </p>
    </div>

    <VTabs
      v-model="activeTab"
      class="v-tabs-pill"
    >
      <VTab
        v-for="item in tabs"
        :key="item.icon"
        :value="item.tab"
        :to="{ name: 'account-settings-tab', params: { tab: item.tab } }"
      >
        <VIcon
          size="20"
          start
          :icon="item.icon"
        />
        {{ item.title }}
      </VTab>
    </VTabs>

    <VWindow
      v-model="activeTab"
      class="mt-6 disable-tab-transition"
      :touch="false"
    >
      <!-- Account -->
      <VWindowItem value="account">
        <AccountSettingsAccount />
      </VWindowItem>

      <!-- Security -->
      <VWindowItem value="security">
        <AccountSettingsSecurity />
      </VWindowItem>

      <!-- Notifications -->
      <VWindowItem value="notifications">
        <AccountSettingsNotification />
      </VWindowItem>
    </VWindow>
  </div>
</template>
