<script setup lang="ts">
import { useFranchisorDashboard } from '@/composables/useFranchisorDashboard'

// 👉 Add route meta for CASL protection
definePage({
  meta: {
    action: 'read',
    subject: 'FranchisorDashboard',
  },
})

// 👉 Router
const router = useRouter()

// 👉 Dashboard composable
const {
  isLoading,
  error,
  dashboardStats: apiStats,
  salesAssociates,
  recentActivities,
  profileCompletionStatus,
  isProfileComplete,
  dismissBanner,
  initializeDashboard,
  refreshDashboard,
} = useFranchisorDashboard()

// 👉 Dashboard stats computed from API data
const dashboardStats = computed(() => {
  if (!apiStats.value) {
    return [
      { title: 'Total Leads', value: '0', change: 0, desc: 'Active leads in pipeline', icon: 'tabler-users', iconColor: 'primary' },
      { title: 'Active Tasks', value: '0', change: 0, desc: 'Ongoing operations', icon: 'tabler-settings', iconColor: 'success' },
      { title: 'Sales Associates', value: '0', change: 0, desc: 'Active team members', icon: 'tabler-user-check', iconColor: 'info' },
      { title: 'Monthly Revenue', value: '$0', change: 0, desc: 'This month\'s revenue', icon: 'tabler-currency-dollar', iconColor: 'warning' },
    ]
  }

  return [
    {
      title: 'Total Leads',
      value: apiStats.value.totalLeads.toString(),
      change: 0, // We could calculate this if we had previous period data
      desc: 'Active leads in pipeline',
      icon: 'tabler-users',
      iconColor: 'primary',
    },
    {
      title: 'Active Tasks',
      value: apiStats.value.activeTasks.toString(),
      change: 0,
      desc: 'Ongoing operations',
      icon: 'tabler-settings',
      iconColor: 'success',
    },
    {
      title: 'Sales Associates',
      value: salesAssociates.value.length.toString(),
      change: 0,
      desc: 'Active team members',
      icon: 'tabler-user-check',
      iconColor: 'info',
    },
    {
      title: 'Monthly Revenue',
      value: `$${apiStats.value.currentMonthRevenue.toLocaleString()}`,
      change: apiStats.value.revenueChange,
      desc: 'This month\'s revenue',
      icon: 'tabler-currency-dollar',
      iconColor: 'warning',
    },
  ]
})

// 👉 Quick actions
const quickActions = [
  { title: 'Add New Lead', icon: 'tabler-user-plus', color: 'primary', to: '/franchisor/add-lead' },
  { title: 'Manage Leads', icon: 'tabler-users', color: 'success', to: '/franchisor/lead-management' },
  { title: 'View Operations', icon: 'tabler-settings', color: 'info', to: '/franchisor/dashboard/operations' },
  { title: 'Sales Team', icon: 'tabler-user-check', color: 'warning', to: '/franchisor/sales-associates' },
]

// 👉 Navigate to onboarding
const startOnboarding = () => {
  router.push('/franchisor/franchise-registration')
}

// 👉 Initialize dashboard data on mount
onMounted(async () => {
  await initializeDashboard()
})
</script>

<template>
  <section>
    <!-- Error Alert -->
    <VAlert
      v-if="error"
      type="error"
      variant="tonal"
      class="mb-6"
      closable
      @click:close="error = null"
    >
      <VAlertTitle class="mb-2">
        Error Loading Dashboard
      </VAlertTitle>
      <p class="mb-3">
        {{ error }}
      </p>
      <VBtn
        color="error"
        variant="elevated"
        size="small"
        @click="refreshDashboard"
      >
        Retry
      </VBtn>
    </VAlert>

    <!-- Onboarding Banner -->
    <VAlert
      v-if="!isProfileComplete"
      type="warning"
      variant="tonal"
      class="mb-6"
      closable
      @click:close="dismissBanner"
    >
      <VAlertTitle class="mb-2">
        Complete Your Franchise Profile
      </VAlertTitle>
      <p class="mb-3">
        Welcome to your franchisor dashboard! To get started and unlock all features,
        please complete your franchise registration process.
      </p>
      <VBtn
        color="info"
        variant="elevated"
        size="small"
        @click="startOnboarding"
      >
        Complete Onboarding Process
      </VBtn>
    </VAlert>

    <!-- Page Header -->
    <VRow class="mb-6">
      <VCol cols="12">
        <div class="d-flex align-center justify-space-between">
          <div>
            <h2 class="text-h2 mb-1">
              Franchisor Dashboard
            </h2>
            <p class="text-body-1 text-medium-emphasis">
              Welcome back! Here's what's happening with your franchise.
            </p>
          </div>
          <div class="d-flex align-center gap-3">
            <VBtn
              v-if="!isLoading"
              color="secondary"
              variant="outlined"
              prepend-icon="tabler-refresh"
              @click="refreshDashboard"
            >
              Refresh
            </VBtn>
            <VBtn
              color="primary"
              prepend-icon="tabler-plus"
              to="/franchisor/add-lead"
            >
              Add New Lead
            </VBtn>
          </div>
        </div>
      </VCol>
    </VRow>

    <!-- Dashboard Stats -->
    <VRow class="mb-6">
      <template v-if="isLoading">
        <VCol
          v-for="i in 4"
          :key="`loading-${i}`"
          cols="12"
          md="3"
          sm="6"
        >
          <VCard>
            <VCardText
              class="d-flex align-center justify-center"
              style="min-height: 100px;"
            >
              <VProgressCircular
                indeterminate
                color="primary"
              />
            </VCardText>
          </VCard>
        </VCol>
      </template>
      <template v-else>
        <template
          v-for="(data, id) in dashboardStats"
          :key="id"
        >
          <VCol
            cols="12"
            md="3"
            sm="6"
          >
            <VCard>
              <VCardText class="d-flex align-center">
                <VAvatar
                  size="44"
                  rounded
                  :color="data.iconColor"
                  variant="tonal"
                >
                  <VIcon
                    :icon="data.icon"
                    size="26"
                  />
                </VAvatar>

                <div class="ms-4">
                  <div class="text-body-2 text-disabled">
                    {{ data.title }}
                  </div>
                  <div class="d-flex align-center flex-wrap">
                    <h4 class="text-h4 me-2">
                      {{ data.value }}
                    </h4>
                    <div
                      v-if="data.change !== 0"
                      class="text-body-2"
                      :class="data.change > 0 ? 'text-success' : 'text-error'"
                    >
                      {{ data.change > 0 ? '+' : '' }}{{ data.change }}%
                    </div>
                  </div>
                  <div class="text-body-2 text-disabled">
                    {{ data.desc }}
                  </div>
                </div>
              </VCardText>
            </VCard>
          </VCol>
        </template>
      </template>
    </VRow>

    <!-- Quick Actions & Recent Activity -->
    <VRow>
      <!-- Quick Actions -->
      <VCol
        cols="12"
        md="6"
      >
        <VCard>
          <VCardItem class="pb-4">
            <VCardTitle>Quick Actions</VCardTitle>
          </VCardItem>
          <VCardText>
            <VRow>
              <template
                v-for="(action, index) in quickActions"
                :key="index"
              >
                <VCol cols="6">
                  <VCard
                    variant="tonal"
                    :color="action.color"
                    class="cursor-pointer"
                    @click="$router.push(action.to)"
                  >
                    <VCardText class="text-center pa-4">
                      <VIcon
                        :icon="action.icon"
                        size="32"
                        class="mb-2"
                      />
                      <div class="text-body-2 font-weight-medium">
                        {{ action.title }}
                      </div>
                    </VCardText>
                  </VCard>
                </VCol>
              </template>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Recent Activity -->
      <VCol
        cols="12"
        md="6"
      >
        <VCard>
          <VCardItem class="pb-4">
            <VCardTitle>Recent Activity</VCardTitle>
          </VCardItem>
          <VCardText>
            <VList class="card-list">
              <template v-if="isLoading">
                <VListItem class="text-center py-8">
                  <VProgressCircular
                    indeterminate
                    color="primary"
                  />
                  <div class="mt-2 text-body-2 text-disabled">
                    Loading recent activities...
                  </div>
                </VListItem>
              </template>
              <template v-else-if="recentActivities.length > 0">
                <template
                  v-for="(activity, index) in recentActivities"
                  :key="index"
                >
                  <VListItem>
                    <template #prepend>
                      <VAvatar
                        size="32"
                        :color="activity.color"
                        variant="tonal"
                      >
                        <VIcon
                          :icon="activity.icon"
                          size="18"
                        />
                      </VAvatar>
                    </template>
                    <VListItemTitle class="text-body-2 font-weight-medium">
                      {{ activity.title }}
                    </VListItemTitle>
                    <VListItemSubtitle class="text-body-2">
                      {{ activity.time }}
                    </VListItemSubtitle>
                  </VListItem>
                  <VDivider v-if="index !== recentActivities.length - 1" />
                </template>
              </template>
              <template v-else>
                <VListItem>
                  <VListItemTitle class="text-center text-disabled">
                    No recent activities
                  </VListItemTitle>
                </VListItem>
              </template>
            </VList>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Dashboard Navigation Cards -->
    <VRow class="mt-6">
      <VCol
        cols="12"
        md="4"
      >
        <VCard
          class="cursor-pointer"
          @click="$router.push('/franchisor/dashboard/leads')"
        >
          <VCardText class="text-center pa-6">
            <VIcon
              icon="tabler-users"
              size="48"
              color="primary"
              class="mb-4"
            />
            <h5 class="text-h5 mb-2">
              Leads Dashboard
            </h5>
            <p class="text-body-2 text-disabled mb-0">
              Manage and track all your leads
            </p>
          </VCardText>
        </VCard>
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <VCard
          class="cursor-pointer"
          @click="$router.push('/franchisor/dashboard/operations')"
        >
          <VCardText class="text-center pa-6">
            <VIcon
              icon="tabler-settings"
              size="48"
              color="success"
              class="mb-4"
            />
            <h5 class="text-h5 mb-2">
              Operations Dashboard
            </h5>
            <p class="text-body-2 text-disabled mb-0">
              Monitor operational activities
            </p>
          </VCardText>
        </VCard>
      </VCol>

      <VCol
        cols="12"
        md="4"
      >
        <VCard
          class="cursor-pointer"
          @click="$router.push('/franchisor/sales-associates')"
        >
          <VCardText class="text-center pa-6">
            <VIcon
              icon="tabler-user-check"
              size="48"
              color="info"
              class="mb-4"
            />
            <h5 class="text-h5 mb-2">
              Sales Associates
            </h5>
            <p class="text-body-2 text-disabled mb-0">
              Manage your sales team
            </p>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </section>
</template>

<style scoped>
.cursor-pointer {
  cursor: pointer;
  transition: transform 0.2s ease;
}

.cursor-pointer:hover {
  transform: translateY(-2px);
}

.card-list .v-list-item {
  padding-inline: 0;
}
</style>
