<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useTheme } from 'vuetify'
import type { PerformanceManagementData } from '@/services/api/franchisee-dashboard'
import { franchiseeDashboardApi } from '@/services/api/franchisee-dashboard'

const vuetifyTheme = useTheme()

// Data
const performanceData = ref<PerformanceManagementData | null>(null)
const isLoading = ref(false)

const loadPerformanceData = async () => {
  try {
    isLoading.value = true

    const response = await franchiseeDashboardApi.getPerformanceManagement()
    if (response.success)
      performanceData.value = response.data
  }
  catch (error) {
    console.error('Error loading performance data:', error)
  }
  finally {
    isLoading.value = false
  }
}

// Load data on mount
onMounted(async () => {
  await loadPerformanceData()
})

// Data for Top and Low Performing Products
const topPerformingProductData = computed(() => performanceData.value?.productPerformance.topPerformingProductData || Array(12).fill(0))
const lowPerformingProductData = computed(() => performanceData.value?.productPerformance.lowPerformingProductData || Array(12).fill(0))

const labelColor = 'rgba(var(--v-theme-on-background), var(--v-medium-emphasis-opacity))'

// Product Performance Chart Options
const productChartOptions = computed(() => {
  return {
    chart: {
      type: 'bar',
      height: 350,
      toolbar: { show: false },
      parentHeightOffset: 0,
    },
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: '55%',
        endingShape: 'rounded',
      },
    },
    dataLabels: {
      enabled: false,
    },
    stroke: {
      show: true,
      width: 2,
      colors: ['transparent'],
    },
    xaxis: {
      categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      labels: {
        style: {
          colors: labelColor,
          fontSize: '13px',
        },
      },
      axisBorder: {
        show: false,
      },
      axisTicks: {
        show: false,
      },
    },
    yaxis: {
      labels: {
        style: {
          colors: labelColor,
          fontSize: '13px',
        },
      },
    },
    fill: {
      opacity: 1,
    },
    legend: {
      show: true,
      position: 'top',
      horizontalAlign: 'left',
      labels: {
        colors: labelColor,
      },
    },
    colors: ['#FFA726', '#7B1FA2'],
    grid: {
      borderColor,
      strokeDashArray: 5,
    },
    tooltip: {
      theme: vuetifyTheme.current.value.dark ? 'dark' : 'light',
      y: {
        formatter: (val: number) => val.toString(),
      },
    },
  }
})

// Product Performance Chart Data
const productChartData = computed(() => [
  {
    name: 'Top Performing Product',
    data: topPerformingProductData.value,
  },
  {
    name: 'Low Performing Product',
    data: lowPerformingProductData.value,
  },
])

// Royalty data
const royaltyAmount = computed(() => `${performanceData.value?.royalty.amount || 0} SAR`)
const royaltyPhaseData = computed(() => performanceData.value?.royalty.phaseData || [0, 0, 0, 0])

// Royalty Chart Options
const royaltyChartOptions = computed(() => {
  return {
    chart: {
      type: 'line',
      height: 120,
      toolbar: { show: false },
      sparkline: { enabled: true },
    },
    stroke: {
      curve: 'smooth',
      width: 3,
    },
    colors: ['#2196F3'],
    xaxis: {
      categories: ['Phase 1', 'Phase 2', 'Phase 3', 'Phase 4'],
    },
    tooltip: {
      theme: vuetifyTheme.current.value.dark ? 'dark' : 'light',
      y: {
        formatter: (val: number) => `${val} SAR`,
      },
    },
    markers: {
      size: 6,
      colors: ['#2196F3'],
      strokeColors: '#fff',
      strokeWidth: 2,
    },
  }
})

// Royalty Chart Data
const royaltyChartData = computed(() => [
  {
    name: 'Royalty',
    data: royaltyPhaseData.value,
  },
])

// Tasks Overview Data
const tasksData = computed(() => ({
  completed: performanceData.value?.tasksOverview.completed || 0,
  inProgress: performanceData.value?.tasksOverview.inProgress || 0,
  due: performanceData.value?.tasksOverview.due || 0,
  total: performanceData.value?.tasksOverview.total || 0,
}))

// Tasks Donut Chart Options
const tasksChartOptions = computed(() => {
  return {
    chart: {
      type: 'donut',
      height: 200,
    },
    labels: ['Completed', 'In Progress', 'Due'],
    colors: ['#4CAF50', '#2196F3', '#F44336'],
    legend: {
      show: false,
    },
    dataLabels: {
      enabled: false,
    },
    plotOptions: {
      pie: {
        donut: {
          size: '70%',
        },
      },
    },
    tooltip: {
      theme: vuetifyTheme.current.value.dark ? 'dark' : 'light',
      y: {
        formatter: (val: number) => `${val} tasks`,
      },
    },
  }
})

// Tasks Chart Data
const tasksChartData = computed(() => [tasksData.value.completed, tasksData.value.inProgress, tasksData.value.due])

// Customer Satisfaction Data
const customerSatisfactionData = computed(() => ({
  score: performanceData.value?.customerSatisfaction.score || 0,
  users: performanceData.value?.customerSatisfaction.users || 0,
  positive: performanceData.value?.customerSatisfaction.positive || 0,
  neutral: performanceData.value?.customerSatisfaction.neutral || 0,
  negative: performanceData.value?.customerSatisfaction.negative || 0,
}))

// Customer Satisfaction Gauge Chart Options
const satisfactionGaugeOptions = computed(() => {
  return {
    chart: {
      type: 'radialBar',
      height: 200,
    },
    plotOptions: {
      radialBar: {
        startAngle: -90,
        endAngle: 90,
        hollow: {
          size: '60%',
        },
        dataLabels: {
          name: {
            show: false,
          },
          value: {
            fontSize: '24px',
            fontWeight: 'bold',
            color: '#4CAF50',
            formatter: (val: number) => `${val}%`,
          },
        },
      },
    },
    fill: {
      colors: ['#4CAF50'],
    },
    stroke: {
      lineCap: 'round',
    },
  }
})

// Customer Satisfaction Gauge Data
const satisfactionGaugeData = computed(() => [customerSatisfactionData.value.score])

// Franchisee selector
</script>

<template>
  <section>
    <!-- Page Header -->
    <VRow class="mb-6">
      <VCol cols="12">
        <div class="d-flex justify-space-between align-center flex-wrap gap-4">
          <div>
            <h4 class="text-h4 mb-1">
              Performance Management
            </h4>
            <p class="text-body-1 text-medium-emphasis">
              Monitor and analyze franchise performance across all locations
            </p>
          </div>
        </div>
      </VCol>
    </VRow>

    <!-- Top Row: Product Performance Chart and Royalty Card -->
    <VRow class="mb-6">
      <!-- Top and Low Performing Product Chart -->
      <VCol
        cols="12"
        lg="8"
      >
        <VCard>
          <VCardText>
            <h5 class="text-h5 font-weight-semibold mb-4">
              Top and Low Performing Product
            </h5>
            <VueApexCharts
              type="bar"
              height="350"
              :options="productChartOptions"
              :series="productChartData"
            />
          </VCardText>
        </VCard>
      </VCol>

      <!-- Royalty Card -->
      <VCol
        cols="12"
        lg="4"
      >
        <VCard class="h-100">
          <VCardText>
            <h5 class="text-h5 font-weight-semibold mb-4">
              Royalty
            </h5>
            <div class="text-center mb-4">
              <h2 class="text-h2 font-weight-bold text-primary">
                {{ royaltyAmount }}
              </h2>
            </div>
            <VueApexCharts
              type="line"
              height="120"
              :options="royaltyChartOptions"
              :series="royaltyChartData"
            />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Bottom Row: Tasks Overview and Customer Satisfaction -->
    <VRow>
      <!-- Tasks Overview -->
      <VCol
        cols="12"
        md="6"
      >
        <VCard>
          <VCardText>
            <div class="d-flex justify-space-between align-center mb-4">
              <h5 class="text-h5 font-weight-semibold">
                Tasks Overview
              </h5>
            </div>

            <div class="d-flex align-center">
              <!-- Donut Chart -->
              <div class="flex-shrink-0">
                <VueApexCharts
                  type="donut"
                  height="200"
                  :options="tasksChartOptions"
                  :series="tasksChartData"
                />
              </div>

              <!-- Tasks Stats -->
              <div class="flex-grow-1 ms-6">
                <div class="text-center mb-4">
                  <h6 class="text-h6 font-weight-semibold text-medium-emphasis">
                    Total Tasks
                  </h6>
                  <h2 class="text-h2 font-weight-bold">
                    {{ tasksData.total }}
                  </h2>
                </div>

                <div class="d-flex flex-column gap-3">
                  <div class="d-flex align-center">
                    <div class="d-flex align-center me-3">
                      <div
                        class="rounded-circle me-2"
                        style="width: 12px; height: 12px; background-color: #4CAF50;"
                      />
                      <span class="text-sm">{{ tasksData.completed }}</span>
                    </div>
                    <span class="text-sm text-medium-emphasis">Completed</span>
                  </div>

                  <div class="d-flex align-center">
                    <div class="d-flex align-center me-3">
                      <div
                        class="rounded-circle me-2"
                        style="width: 12px; height: 12px; background-color: #2196F3;"
                      />
                      <span class="text-sm">{{ tasksData.inProgress }}</span>
                    </div>
                    <span class="text-sm text-medium-emphasis">In Progress</span>
                  </div>

                  <div class="d-flex align-center">
                    <div class="d-flex align-center me-3">
                      <div
                        class="rounded-circle me-2"
                        style="width: 12px; height: 12px; background-color: #F44336;"
                      />
                      <span class="text-sm">{{ tasksData.due }}</span>
                    </div>
                    <span class="text-sm text-medium-emphasis">Due</span>
                  </div>
                </div>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Customer Satisfaction Score -->
      <VCol
        cols="12"
        md="6"
      >
        <VCard>
          <VCardText>
            <h5 class="text-h5 font-weight-semibold mb-4">
              Customer Satisfaction Score
            </h5>

            <div class="d-flex align-center">
              <!-- Gauge Chart -->
              <div class="flex-shrink-0">
                <VueApexCharts
                  type="radialBar"
                  height="200"
                  :options="satisfactionGaugeOptions"
                  :series="satisfactionGaugeData"
                />
              </div>

              <!-- Satisfaction Stats -->
              <div class="flex-grow-1 ms-6">
                <div class="text-center mb-4">
                  <h2 class="text-h2 font-weight-bold">
                    {{ customerSatisfactionData.users }}
                  </h2>
                  <span class="text-sm text-medium-emphasis">USERS</span>
                </div>

                <div class="d-flex flex-column gap-3">
                  <div class="d-flex align-center justify-space-between">
                    <div class="d-flex align-center">
                      <div
                        class="rounded-circle me-2"
                        style="width: 12px; height: 12px; background-color: #4CAF50;"
                      />
                      <span class="text-sm">Positive</span>
                    </div>
                    <span class="text-sm font-weight-semibold text-success">{{
                      customerSatisfactionData.positive }}%</span>
                  </div>

                  <div class="d-flex align-center justify-space-between">
                    <div class="d-flex align-center">
                      <div
                        class="rounded-circle me-2"
                        style="width: 12px; height: 12px; background-color: #FFA726;"
                      />
                      <span class="text-sm">Neutral</span>
                    </div>
                    <span class="text-sm font-weight-semibold text-warning">{{
                      customerSatisfactionData.neutral }}%</span>
                  </div>

                  <div class="d-flex align-center justify-space-between">
                    <div class="d-flex align-center">
                      <div
                        class="rounded-circle me-2"
                        style="width: 12px; height: 12px; background-color: #F44336;"
                      />
                      <span class="text-sm">Negative</span>
                    </div>
                    <span class="text-sm font-weight-semibold text-error">{{
                      customerSatisfactionData.negative }}%</span>
                  </div>
                </div>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </section>
</template>

<style scoped>
.v-list-item {
  min-height: 48px;
}

.v-card {
  height: 100%;
}
</style>
