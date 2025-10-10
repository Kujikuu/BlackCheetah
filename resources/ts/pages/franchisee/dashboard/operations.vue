<script setup lang="ts">

import { getBarChartConfig, getLineChartSimpleConfig } from '@core/libs/apex-chart/apexCharConfig'
import { useTheme } from 'vuetify'

// 👉 Store
const currentTab = ref('store')
const vuetifyTheme = useTheme()
// Utility function for percentage display
const prefixWithPlus = (value: number) => value > 0 ? `+${value}` : value.toString()

// Store data for new structure
const storeData = ref({
  totalItems: 1250,
  totalStocks: 8500,
  lowStockItems: 45,
  outOfStockItems: 12,
})

const staffData = ref({
  totalStaffs: 24,
  newHires: 3,
  monthlyAbsenteeismRate: 8.5,
  topPerformers: [
    { name: 'Sarah Johnson', performance: 95, department: 'Sales' },
    { name: 'Michael Brown', performance: 92, department: 'Customer Service' },
    { name: 'Emily Davis', performance: 90, department: 'Inventory' },
    { name: 'John Smith', performance: 88, department: 'Operations' },
    { name: 'Lisa Wilson', performance: 85, department: 'Marketing' },
  ],
})

// Chart data for low stock items
const lowStockChartData = ref([
  {
    name: 'Intake',
    data: [120, 132, 101, 134, 90, 230, 210, 150, 180, 200, 220, 240],
  },
  {
    name: 'Available',
    data: [80, 95, 70, 110, 60, 180, 160, 120, 140, 160, 180, 200],
  },
])

// Chart data for employee shift coverage
const shiftCoverageData = ref([
  {
    name: 'Morning Shift',
    data: [8, 7, 8, 6, 7, 8, 7],
  },
  {
    name: 'Afternoon Shift',
    data: [6, 8, 7, 8, 6, 7, 8],
  },
  {
    name: 'Evening Shift',
    data: [4, 5, 6, 5, 4, 5, 6],
  },
  {
    name: 'Night Shift',
    data: [2, 3, 2, 3, 2, 2, 3],
  },
])

// Chart configurations
const lowStockChartConfig = computed(() => getLineChartSimpleConfig(vuetifyTheme.current.value))
const shiftCoverageChartConfig = computed(() => getBarChartConfig(vuetifyTheme.current.value))

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
  { title: 'Absenteeism Rate', value: `${staffData.value.monthlyAbsenteeismRate}%`, change: -12, desc: 'Monthly average', icon: 'tabler-user-x', iconColor: 'warning' },
])

// Current widget data based on selected tab
const currentWidgetData = computed(() => {
  return currentTab.value === 'store' ? storeWidgetData.value : staffWidgetData.value
})

const tabs = [
  { title: 'Store', value: 'store', icon: 'tabler-building-store' },
  { title: 'Staff', value: 'staff', icon: 'tabler-user' },
]
</script>

<template>
  <section>
    <!-- 👉 Tabs -->
    <VTabs v-model="currentTab" class="mb-6">
      <VTab v-for="tab in tabs" :key="tab.value" :value="tab.value">
        <VIcon :icon="tab.icon" start />
        {{ tab.title }}
      </VTab>
    </VTabs>

    <VWindow v-model="currentTab" class="disable-tab-transition">
      <!-- Store Tab -->
      <VWindowItem value="store">
        <!-- 👉 Store Stat Cards -->
        <div class="d-flex mb-6">
          <VRow>
            <template v-for="(data, id) in storeWidgetData" :key="id">
              <VCol cols="12" md="3" sm="6">
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
                          <div class="text-base" :class="data.change > 0 ? 'text-success' : 'text-error'">
                            ({{ prefixWithPlus(data.change) }}%)
                          </div>
                        </div>
                        <div class="text-sm">
                          {{ data.desc }}
                        </div>
                      </div>
                      <VAvatar :color="data.iconColor" variant="tonal" rounded size="42">
                        <VIcon :icon="data.icon" size="26" />
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
            <VueApexCharts type="line" height="400" :options="lowStockChartConfig" :series="lowStockChartData" />
          </VCardText>
        </VCard>
      </VWindowItem>

      <!-- Staff Tab -->
      <VWindowItem value="staff">
        <!-- 👉 Staff Stat Cards -->
        <div class="d-flex mb-6">
          <VRow>
            <template v-for="(data, id) in staffWidgetData" :key="id">
              <VCol cols="12" md="4" sm="6">
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
                          <div class="text-base" :class="data.change > 0 ? 'text-success' : 'text-error'">
                            ({{ prefixWithPlus(data.change) }}%)
                          </div>
                        </div>
                        <div class="text-sm">
                          {{ data.desc }}
                        </div>
                      </div>
                      <VAvatar :color="data.iconColor" variant="tonal" rounded size="42">
                        <VIcon :icon="data.icon" size="26" />
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
          <VCol cols="12" md="6">
            <VCard>
              <VCardItem>
                <VCardTitle>Top 5 Performer</VCardTitle>
              </VCardItem>
              <VCardText>
                <VList>
                  <VListItem v-for="(performer, index) in staffData.topPerformers" :key="performer.id" class="px-0">
                    <template #prepend>
                      <VAvatar size="40" :color="index < 3 ? 'primary' : 'secondary'" variant="tonal">
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
                          {{ performer.score }}%
                        </div>
                        <div class="text-caption text-disabled">
                          Performance
                        </div>
                      </div>
                    </template>
                  </VListItem>
                </VList>
              </VCardText>
            </VCard>
          </VCol>

          <!-- 👉 Employee Shift Coverage Chart -->
          <VCol cols="12" md="6">
            <VCard>
              <VCardItem>
                <VCardTitle>Employee Shift Coverage</VCardTitle>
              </VCardItem>
              <VCardText>
                <VueApexCharts type='radar' height="300" :options="shiftCoverageChartConfig"
                  :series="shiftCoverageData" />
              </VCardText>
            </VCard>
          </VCol>
        </VRow>
      </VWindowItem>
    </VWindow>
  </section>
</template>
