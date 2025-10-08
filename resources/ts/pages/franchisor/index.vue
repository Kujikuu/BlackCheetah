<script setup lang="ts">
// 👉 Router
const router = useRouter()

// 👉 Mock user profile completion status
const isProfileComplete = ref(false) // Set to false to show onboarding banner

// 👉 Dashboard stats
const dashboardStats = ref([
  { title: 'Total Leads', value: '156', change: 12.5, desc: 'Active leads in pipeline', icon: 'tabler-users', iconColor: 'primary' },
  { title: 'Active Operations', value: '23', change: 3.2, desc: 'Ongoing operations', icon: 'tabler-settings', iconColor: 'success' },
  { title: 'Sales Associates', value: '8', change: -2.1, desc: 'Active team members', icon: 'tabler-user-check', iconColor: 'info' },
  { title: 'Monthly Revenue', value: '$45,280', change: 8.7, desc: 'This month\'s revenue', icon: 'tabler-currency-dollar', iconColor: 'warning' },
])

// 👉 Quick actions
const quickActions = [
  { title: 'Add New Lead', icon: 'tabler-user-plus', color: 'primary', to: '/franchisor/add-lead' },
  { title: 'Manage Leads', icon: 'tabler-users', color: 'success', to: '/franchisor/lead-management' },
  { title: 'View Operations', icon: 'tabler-settings', color: 'info', to: '/franchisor/dashboard/operations' },
  { title: 'Sales Team', icon: 'tabler-user-check', color: 'warning', to: '/franchisor/sales-associates' },
]

// 👉 Recent activities
const recentActivities = ref([
  { type: 'lead', title: 'New lead: John Smith', time: '2 hours ago', icon: 'tabler-user-plus', color: 'primary' },
  { type: 'operation', title: 'Operation completed: Site Setup', time: '4 hours ago', icon: 'tabler-check', color: 'success' },
  { type: 'team', title: 'Sarah Johnson joined the team', time: '1 day ago', icon: 'tabler-user-check', color: 'info' },
  { type: 'revenue', title: 'Payment received: $2,500', time: '2 days ago', icon: 'tabler-currency-dollar', color: 'warning' },
])

// 👉 Navigate to onboarding
const startOnboarding = () => {
  router.push('/franchisor/franchise-registration-wizard')
}

// 👉 Dismiss banner (for demo purposes)
const dismissBanner = () => {
  isProfileComplete.value = true
}
</script>

<template>
  <section>
    <!-- Onboarding Banner -->
    <VAlert v-if="!isProfileComplete" type="warning" variant="tonal" class="mb-6" closable @click:close="dismissBanner">
      <VAlertTitle class="mb-2">
        Complete Your Franchise Profile
      </VAlertTitle>
      <p class="mb-3">
        Welcome to your franchisor dashboard! To get started and unlock all features,
        please complete your franchise registration process.
      </p>
      <VBtn color="info" variant="elevated" size="small" @click="startOnboarding">
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
          <VBtn color="primary" prepend-icon="tabler-plus" to="/franchisor/add-lead">
            Add New Lead
          </VBtn>
        </div>
      </VCol>
    </VRow>

    <!-- Dashboard Stats -->
    <VRow class="mb-6">
      <template v-for="(data, id) in dashboardStats" :key="id">
        <VCol cols="12" md="3" sm="6">
          <VCard>
            <VCardText class="d-flex align-center">
              <VAvatar size="44" rounded :color="data.iconColor" variant="tonal">
                <VIcon :icon="data.icon" size="26" />
              </VAvatar>

              <div class="ms-4">
                <div class="text-body-2 text-disabled">
                  {{ data.title }}
                </div>
                <div class="d-flex align-center flex-wrap">
                  <h4 class="text-h4 me-2">
                    {{ data.value }}
                  </h4>
                  <div class="text-success text-body-2" :class="data.change > 0 ? 'text-success' : 'text-error'">
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
    </VRow>

    <!-- Quick Actions & Recent Activity -->
    <VRow>
      <!-- Quick Actions -->
      <VCol cols="12" md="6">
        <VCard>
          <VCardItem class="pb-4">
            <VCardTitle>Quick Actions</VCardTitle>
          </VCardItem>
          <VCardText>
            <VRow>
              <template v-for="(action, index) in quickActions" :key="index">
                <VCol cols="6">
                  <VCard variant="tonal" :color="action.color" class="cursor-pointer" @click="$router.push(action.to)">
                    <VCardText class="text-center pa-4">
                      <VIcon :icon="action.icon" size="32" class="mb-2" />
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
      <VCol cols="12" md="6">
        <VCard>
          <VCardItem class="pb-4">
            <VCardTitle>Recent Activity</VCardTitle>
          </VCardItem>
          <VCardText>
            <VList class="card-list">
              <template v-for="(activity, index) in recentActivities" :key="index">
                <VListItem>
                  <template #prepend>
                    <VAvatar size="32" :color="activity.color" variant="tonal">
                      <VIcon :icon="activity.icon" size="18" />
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
            </VList>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Dashboard Navigation Cards -->
    <VRow class="mt-6">
      <VCol cols="12" md="4">
        <VCard class="cursor-pointer" @click="$router.push('/franchisor/dashboard/leads')">
          <VCardText class="text-center pa-6">
            <VIcon icon="tabler-users" size="48" color="primary" class="mb-4" />
            <h5 class="text-h5 mb-2">
              Leads Dashboard
            </h5>
            <p class="text-body-2 text-disabled mb-0">
              Manage and track all your leads
            </p>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" md="4">
        <VCard class="cursor-pointer" @click="$router.push('/franchisor/dashboard/operations')">
          <VCardText class="text-center pa-6">
            <VIcon icon="tabler-settings" size="48" color="success" class="mb-4" />
            <h5 class="text-h5 mb-2">
              Operations Dashboard
            </h5>
            <p class="text-body-2 text-disabled mb-0">
              Monitor operational activities
            </p>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" md="4">
        <VCard class="cursor-pointer" @click="$router.push('/franchisor/sales-associates')">
          <VCardText class="text-center pa-6">
            <VIcon icon="tabler-user-check" size="48" color="info" class="mb-4" />
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
