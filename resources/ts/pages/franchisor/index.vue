<script setup lang="ts">
import { SaudiRiyal } from 'lucide-vue-next'
import { useFranchisorDashboard } from '@/composables/useFranchisorDashboard'
import { useFranchiseCheck } from '@/composables/useFranchiseCheck'
import { franchiseApi } from '@/services/api'
import { formatCurrency } from '@/@core/utils/formatters'
import FranchiseOverview from '@/views/dashboards/analytics/FranchiseOverview.vue'
import FranchiseeStats from '@/views/dashboards/analytics/FranchiseeStats.vue'
import UnitStats from '@/views/dashboards/analytics/UnitStats.vue'
import RevenueOverview from '@/views/dashboards/analytics/RevenueOverview.vue'
import TaskTracker from '@/views/dashboards/analytics/TaskTracker.vue'

// 👉 Router
const router = useRouter()

// 👉 Franchise check
const { checkAndRedirect } = useFranchiseCheck()

// 👉 Franchise name
const franchiseName = ref<string>('Franchisor')

// 👉 Dashboard composable
const {
  isLoading,
  error,
  dashboardStats: apiStats,
  brokers,
  recentActivities,
  profileCompletionStatus,
  isProfileComplete,
  franchiseExists,
  dismissBanner,
  initializeDashboard,
  refreshDashboard,
} = useFranchisorDashboard()

// 👉 Navigate to onboarding
const startOnboarding = () => {
  router.push('/franchisor/franchise-registration')
}

// 👉 Fetch franchise name
const fetchFranchiseName = async () => {
  try {
    const response = await franchiseApi.getFranchiseData()
    if (response.success && response.data) {
      franchiseName.value = response.data.franchiseDetails?.franchiseName || 'Franchisor'
    }
  }
  catch (err: any) {
    console.error('Error fetching franchise name:', err)
  }
}

// 👉 Computed stats for components
const franchiseStats = computed(() => ({
  totalFranchisees: apiStats.value?.totalFranchisees || 0,
  totalUnits: apiStats.value?.totalUnits || 0,
  activeUnits: apiStats.value?.activeUnits || 0,
  inactiveUnits: apiStats.value?.inactiveUnits || 0,
  totalLeads: apiStats.value?.totalLeads || 0,
  activeTasks: apiStats.value?.activeTasks || 0,
  completedTasks: apiStats.value?.completedTasks || 0,
  pendingTasks: apiStats.value?.pendingTasks || 0,
  totalTasks: apiStats.value?.totalTasks || 0,
  currentMonthRevenue: apiStats.value?.currentMonthRevenue || 0,
  currentMonthSales: apiStats.value?.currentMonthSales || 0,
  currentMonthExpenses: apiStats.value?.currentMonthExpenses || 0,
  currentMonthProfit: apiStats.value?.currentMonthProfit || 0,
  revenueChange: apiStats.value?.revenueChange || 0,
  pendingRoyalties: apiStats.value?.pendingRoyalties || 0,
}))

// 👉 Unit stats from backend
const unitStats = computed(() => ({
  total: franchiseStats.value.totalUnits,
  active: franchiseStats.value.activeUnits,
  inactive: franchiseStats.value.inactiveUnits,
}))

// 👉 Task stats from backend
const taskStats = computed(() => ({
  active: franchiseStats.value.activeTasks,
  completed: franchiseStats.value.completedTasks,
  pending: franchiseStats.value.pendingTasks,
  total: franchiseStats.value.totalTasks,
}))

// 👉 Initialize dashboard data on mount
onMounted(async () => {
  // Check if franchisor needs to complete franchise registration first
  const needsRedirect = await checkAndRedirect()
  if (needsRedirect) {
    return // Stop execution if redirected
  }

  // Initialize dashboard to check franchise status (this will set franchiseExists flag)
  await initializeDashboard()

  // Only fetch franchise data if franchise exists to avoid API errors
  if (franchiseExists.value) {
    await fetchFranchiseName()
  }
})
</script>

<template>
  <section>
    <!-- Error Alert -->
    <VAlert v-if="error" type="error" variant="tonal" class="mb-6" closable @click:close="error = null">
      <VAlertTitle class="mb-2">
        Error Loading Dashboard
      </VAlertTitle>
      <p class="mb-3">
        {{ error }}
      </p>
      <VBtn color="error" variant="elevated" size="small" @click="refreshDashboard">
        Retry
      </VBtn>
    </VAlert>

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
    <VRow>
      <VCol cols="12">
        <div class="d-flex align-center justify-space-between">
          <div>
            <h2 class="text-h2 mb-1">
              {{ franchiseName }}
            </h2>
            <p class="text-body-1 text-medium-emphasis">
              Welcome back! Here's what's happening with your franchise.
            </p>
          </div>
          <!-- <div class="d-flex align-center gap-3">
            <VBtn v-if="!isLoading" color="secondary" variant="outlined" prepend-icon="tabler-refresh"
              @click="refreshDashboard">
              Refresh
            </VBtn>
            <VBtn color="primary" prepend-icon="tabler-plus" to="/franchisor/add-lead">
              Add New Lead
            </VBtn>
          </div> -->
        </div>
      </VCol>
    </VRow>
    <VRow class="match-height">
      <!-- 👉 Franchise Overview -->
      <VCol cols="12" md="6">
        <FranchiseOverview :total-franchisees="franchiseStats.totalFranchisees"
          :total-units="franchiseStats.totalUnits" />
      </VCol>

      <!-- 👉 Total Franchisees -->
      <VCol cols="12" md="3" sm="6">
        <FranchiseeStats :total-franchisees="franchiseStats.totalFranchisees" :change="0" />
      </VCol>

      <!-- 👉 Unit Statistics -->
      <VCol cols="12" md="3" sm="6">
        <UnitStats :total-units="unitStats.total" :active-units="unitStats.active"
          :inactive-units="unitStats.inactive" />
      </VCol>

      <!-- 👉 Revenue Overview -->
      <VCol cols="12" md="6">
        <RevenueOverview :current-month-revenue="franchiseStats.currentMonthRevenue"
          :revenue-change="franchiseStats.revenueChange" :pending-royalties="franchiseStats.pendingRoyalties"
          :current-month-sales="franchiseStats.currentMonthSales" :current-month-expenses="franchiseStats.currentMonthExpenses"
          :current-month-profit="franchiseStats.currentMonthProfit" />
      </VCol>

      <!-- 👉 Task Tracker -->
      <VCol cols="12" md="6">
        <TaskTracker :active-tasks="taskStats.active" :completed-tasks="taskStats.completed"
          :pending-tasks="taskStats.pending" :total-tasks="taskStats.total" />
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
