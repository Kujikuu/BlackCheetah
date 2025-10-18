<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useTheme } from 'vuetify'
import { getBarChartConfig, getLineChartSimpleConfig } from '@core/libs/apex-chart/apexCharConfig'
import type { LowStockChartData, ShiftCoverageData, StaffData, StoreData } from '@/services/api/franchisee-dashboard'
import { franchiseeDashboardApi } from '@/services/api/franchisee-dashboard'

// 👉 Store
const currentTab = ref('store')
const vuetifyTheme = useTheme()

// Loading and error states
const loading = ref(false)
const error = ref<string | null>(null)
const hasLoadedApiData = ref(false)

// Utility function for percentage display
const prefixWithPlus = (value: number) => value > 0 ? `+${value}` : value.toString()

// Store data - will be populated from API
const storeData = ref<StoreData>({
  totalItems: 0,
  totalStocks: 0,
  lowStockItems: 0,
  outOfStockItems: 0,
})

// Staff data - will be populated from API
const staffData = ref<StaffData>({
  totalStaffs: 0,
  newHires: 0,
  monthlyAbsenteeismRate: 0,
  topPerformers: [],
})

// Chart data for low stock items - initialized with safe default values
const lowStockChartData = ref<LowStockChartData[]>([
  {
    name: 'Intake',
    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
  },
  {
    name: 'Available',
    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
  },
])

// Chart data for employee shift coverage - initialized with safe default values
const shiftCoverageData = ref<ShiftCoverageData[]>([
  {
    name: 'Morning Shift',
    data: [0, 0, 0, 0, 0, 0, 0],
  },
  {
    name: 'Afternoon Shift',
    data: [0, 0, 0, 0, 0, 0, 0],
  },
  {
    name: 'Evening Shift',
    data: [0, 0, 0, 0, 0, 0, 0],
  },
  {
    name: 'Night Shift',
    data: [0, 0, 0, 0, 0, 0, 0],
  },
])

// Load dashboard data
const loadDashboardData = async () => {
  loading.value = true
  error.value = null

  try {
    // Load all operations data
    const operationsResponse = await franchiseeDashboardApi.getOperationsData()
    if (operationsResponse.success && operationsResponse.data) {
      const operations = operationsResponse.data

      storeData.value = operations.storeData
      staffData.value = operations.staffData
      lowStockChartData.value = operations.lowStockChart
      shiftCoverageData.value = operations.shiftCoverageChart
      hasLoadedApiData.value = true
    }
  }
  catch (err) {
    error.value = 'Failed to load operations data. Please try again.'
    console.error('Error loading operations data:', err)
  }
  finally {
    loading.value = false
  }
}

// Load data on component mount
onMounted(() => {
  loadDashboardData()
})

// Chart configurations
const lowStockChartConfig = computed(() => getLineChartSimpleConfig(vuetifyTheme.current.value))
const shiftCoverageChartConfig = computed(() => getBarChartConfig(vuetifyTheme.current.value))

// Computed properties to check if chart data is ready
const isLowStockChartDataReady = computed(() => {
  return !loading.value
         && hasLoadedApiData.value
         && lowStockChartData.value.length > 0
         && lowStockChartData.value.every(series =>
           series.data
           && Array.isArray(series.data)
           && series.data.length > 0
           && series.data.every((value: any) => typeof value === 'number' && !Number.isNaN(value)),
         )
})

const isShiftCoverageChartDataReady = computed(() => {
  return !loading.value
         && hasLoadedApiData.value
         && shiftCoverageData.value.length > 0
         && shiftCoverageData.value.every(series =>
           series.data
           && Array.isArray(series.data)
           && series.data.length > 0
           && series.data.every((value: any) => typeof value === 'number' && !Number.isNaN(value)),
         )
})

// Widget data for Store tab
const storeWidgetData = computed(() => [
  { title: 'Total Items', value: storeData.value.totalItems.toLocaleString(), change: 5, desc: 'Inventory items', icon: 'tabler-package', iconColor: 'primary' },
  { title: 'Total Stocks', value: storeData.value.totalStocks.toLocaleString(), change: 12, desc: 'Stock quantity', icon: 'tabler-stack', iconColor: 'success' },
  { title: 'Low Stock Items', value: storeData.value.lowStockItems.toString(), change: -8, desc: 'Items running low', icon: 'tabler-alert-triangle', iconColor: 'warning' },
  { title: 'Out of Stock', value: storeData.value.outOfStockItems.toString(), change: -15, desc: 'Items unavailable', icon: 'tabler-x-circle', iconColor: 'error' },
])

// Widget data for Staff tab
const staffWidgetData = computed(() => [
  { title: 'Total Staffs', value: staffData.value.totalStaffs.toString(), change: 8, desc: 'Active employees', icon: 'tabler-users', iconColor: 'primary' },
  { title: 'New Hires', value: staffData.value.newHires.toString(), change: 25, desc: 'This month', icon: 'tabler-user-plus', iconColor: 'success' },
  { title: 'Absenteeism Rate', value: `${staffData.value.monthlyAbsenteeismRate.toFixed(1)}%`, change: -12, desc: 'Monthly average', icon: 'tabler-user-x', iconColor: 'warning' },
])

// Check if we have top performers data
const hasPerformersData = computed(() => {
  return staffData.value.topPerformers && staffData.value.topPerformers.length > 0
})

const tabs = [
  { title: 'Store', value: 'store', icon: 'tabler-building-store' },
  { title: 'Staff', value: 'staff', icon: 'tabler-user' },
]
</script>

<template>
  <section>
    <!-- Loading State -->
    <VRow
      v-if="loading"
      class="mb-6"
    >
      <VCol cols="12">
        <VCard>
          <VCardText class="text-center py-8">
            <VProgressCircular
              indeterminate
              color="primary"
              size="64"
            />
            <div class="mt-4 text-body-1">
              Loading operations data...
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Error State -->
    <VRow
      v-else-if="error"
      class="mb-6"
    >
      <VCol cols="12">
        <VAlert
          type="error"
          variant="tonal"
          class="mb-0"
        >
          <template #prepend>
            <VIcon icon="tabler-alert-circle" />
          </template>
          {{ error }}
          <template #append>
            <VBtn
              color="error"
              variant="text"
              size="small"
              @click="loadDashboardData"
            >
              Retry
            </VBtn>
          </template>
        </VAlert>
      </VCol>
    </VRow>

    <!-- Dashboard Content -->
    <div v-else>
      <!-- 👉 Tabs -->
      <VTabs
        v-model="currentTab"
        class="mb-6"
      >
        <VTab
          v-for="tab in tabs"
          :key="tab.value"
          :value="tab.value"
        >
          <VIcon
            :icon="tab.icon"
            start
          />
          {{ tab.title }}
        </VTab>
      </VTabs>

      <VWindow
        v-model="currentTab"
        class="disable-tab-transition"
      >
        <!-- Store Tab -->
        <VWindowItem value="store">
          <!-- 👉 Store Stat Cards -->
          <div class="d-flex mb-6">
            <VRow>
              <template
                v-for="(data, id) in storeWidgetData"
                :key="id"
              >
                <VCol
                  cols="12"
                  md="3"
                  sm="6"
                >
                  <VCard>
                    <VCardText>
                      <div class="d-flex justify-space-between">
                        <div class="d-flex flex-column gap-y-1">
                          <div class="text-body-1 text-high-emphasis">
                            {{ data.title }}
                          </div>
                          <div class="d-flex gap-x-2 align-center">
                            <h4 class="text-h4">
                              {{ data.value }}
                            </h4>
                            <div
                              class="text-base"
                              :class="data.change > 0 ? 'text-success' : 'text-error'"
                            >
                              ({{ prefixWithPlus(data.change) }}%)
                            </div>
                          </div>
                          <div class="text-sm">
                            {{ data.desc }}
                          </div>
                        </div>
                        <VAvatar
                          :color="data.iconColor"
                          variant="tonal"
                          rounded
                          size="42"
                        >
                          <VIcon
                            :icon="data.icon"
                            size="26"
                          />
                        </VAvatar>
                      </div>
                    </VCardText>
                  </VCard>
                </VCol>
              </template>
            </VRow>
          </div>

          <!-- 👉 Items on Low Stock Chart -->
          <VCard class="mb-6">
            <VCardItem>
              <VCardTitle>Items on Low Stock</VCardTitle>
            </VCardItem>
            <VCardText>
              <VueApexCharts
                v-if="isLowStockChartDataReady"
                type="line"
                height="400"
                :options="lowStockChartConfig"
                :series="lowStockChartData"
              />
              <div
                v-else
                class="text-center py-8"
              >
                <VProgressCircular
                  indeterminate
                  color="primary"
                  size="32"
                />
                <div class="mt-4 text-body-2 text-medium-emphasis">
                  Loading chart data...
                </div>
              </div>
            </VCardText>
          </VCard>
        </VWindowItem>

        <!-- Staff Tab -->
        <VWindowItem value="staff">
          <!-- 👉 Staff Stat Cards -->
          <div class="d-flex mb-6">
            <VRow>
              <template
                v-for="(data, id) in staffWidgetData"
                :key="id"
              >
                <VCol
                  cols="12"
                  md="4"
                  sm="6"
                >
                  <VCard>
                    <VCardText>
                      <div class="d-flex justify-space-between">
                        <div class="d-flex flex-column gap-y-1">
                          <div class="text-body-1 text-high-emphasis">
                            {{ data.title }}
                          </div>
                          <div class="d-flex gap-x-2 align-center">
                            <h4 class="text-h4">
                              {{ data.value }}
                            </h4>
                            <div
                              class="text-base"
                              :class="data.change > 0 ? 'text-success' : 'text-error'"
                            >
                              ({{ prefixWithPlus(data.change) }}%)
                            </div>
                          </div>
                          <div class="text-sm">
                            {{ data.desc }}
                          </div>
                        </div>
                        <VAvatar
                          :color="data.iconColor"
                          variant="tonal"
                          rounded
                          size="42"
                        >
                          <VIcon
                            :icon="data.icon"
                            size="26"
                          />
                        </VAvatar>
                      </div>
                    </VCardText>
                  </VCard>
                </VCol>
              </template>
            </VRow>
          </div>

          <VRow>
            <!-- 👉 Top 5 Performer -->
            <VCol
              cols="12"
              md="6"
            >
              <VCard>
                <VCardItem>
                  <VCardTitle>Top 5 Performer</VCardTitle>
                </VCardItem>
                <VCardText>
                  <VList v-if="hasPerformersData">
                    <VListItem
                      v-for="(performer, index) in staffData.topPerformers"
                      :key="performer.id || index"
                      class="px-0"
                    >
                      <template #prepend>
                        <VAvatar
                          size="40"
                          :color="index < 3 ? 'primary' : 'secondary'"
                          variant="tonal"
                        >
                          <span class="text-sm font-weight-medium">{{ index + 1 }}</span>
                        </VAvatar>
                      </template>

                      <VListItemTitle class="font-weight-medium">
                        {{ performer.name }}
                      </VListItemTitle>
                      <VListItemSubtitle>
                        {{ performer.department }}
                      </VListItemSubtitle>

                      <template #append>
                        <div class="text-end">
                          <div class="text-body-1 font-weight-medium">
                            {{ performer.performance || performer.score }}%
                          </div>
                          <div class="text-caption text-disabled">
                            Performance
                          </div>
                        </div>
                      </template>
                    </VListItem>
                  </VList>
                  <div
                    v-else
                    class="text-center py-8"
                  >
                    <VProgressCircular
                      v-if="loading"
                      indeterminate
                      color="primary"
                      size="32"
                    />
                    <div v-else>
                      <VIcon
                        icon="tabler-users"
                        size="48"
                        class="text-disabled mb-4"
                      />
                      <div class="text-body-2 text-medium-emphasis">
                        No performance data available
                      </div>
                    </div>
                  </div>
                </VCardText>
              </VCard>
            </VCol>

            <!-- 👉 Employee Shift Coverage Chart -->
            <VCol
              cols="12"
              md="6"
            >
              <VCard>
                <VCardItem>
                  <VCardTitle>Employee Shift Coverage</VCardTitle>
                </VCardItem>
                <VCardText>
                  <VueApexCharts
                    v-if="isShiftCoverageChartDataReady"
                    type="radar"
                    height="300"
                    :options="shiftCoverageChartConfig"
                    :series="shiftCoverageData"
                  />
                  <div
                    v-else
                    class="text-center py-8"
                  >
                    <VProgressCircular
                      indeterminate
                      color="primary"
                      size="32"
                    />
                    <div class="mt-4 text-body-2 text-medium-emphasis">
                      Loading chart data...
                    </div>
                  </div>
                </VCardText>
              </VCard>
            </VCol>
          </VRow>
        </VWindowItem>
      </VWindow>
    </div>
  </section>
</template>
