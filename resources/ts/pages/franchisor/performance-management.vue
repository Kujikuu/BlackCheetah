<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useTheme } from 'vuetify'
import { $api } from '@/utils/api'

const vuetifyTheme = useTheme()

// Type definitions
interface ApiResponse<T> {
  success: boolean
  data: T
  message: string
}

interface Unit {
  id: string | number
  name: string
  location: string
  code?: string
}

interface ChartDataset {
  revenue: number[]
  expenses: number[]
  royalties: number[]
  profit: number[]
}

interface ChartData {
  periods: string[]
  datasets: Record<string, ChartDataset>
}

interface TopPerformer {
  id: number
  name: string
  location: string
  revenue: string
  growth: string
}

interface CustomerSatisfaction {
  score: number
  max_score: number
  total_reviews: number
  trend: string
}

interface RatedUnit {
  id: number
  name: string
  location: string
  rating: number
  reviews: number
  manager: string
}

interface RatingsData {
  top_rated: RatedUnit | null
  lowest_rated: RatedUnit | null
}

// Reactive data
const selectedPeriod = ref('monthly')
const selectedUnit = ref('all')
const loading = ref(false)
const error = ref<string | null>(null)

// API data
const franchiseeUnits = ref<Unit[]>([])
const chartData = ref<ChartData>({ periods: [], datasets: {} })
const topPerformingLocations = ref<TopPerformer[]>([])
const customerSatisfactionScore = ref<CustomerSatisfaction>({ score: 0, max_score: 5.0, total_reviews: 0, trend: '0' })
const ratingsData = ref<RatingsData>({ top_rated: null, lowest_rated: null })

// API endpoints
const API_BASE = '/api/v1/franchisor/performance'

// Computed properties
const topRatedFranchise = computed(() => ratingsData.value.top_rated)
const lowestRatedFranchise = computed(() => ratingsData.value.lowest_rated)

// Chart options
const chartOptions = computed(() => {
  const currentTheme = vuetifyTheme.current.value.colors

  return {
    chart: {
      type: 'line',
      height: 400,
      toolbar: { show: false },
      zoom: { enabled: false },
      parentHeightOffset: 0,
    },
    stroke: {
      curve: 'smooth',
      width: 3,
    },
    xaxis: {
      categories: chartData.value.periods,
      labels: {
        style: {
          colors: `rgba(${currentTheme['on-surface']}, 0.6)`,
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
          colors: `rgba(${currentTheme['on-surface']}, 0.6)`,
          fontSize: '13px',
        },
        formatter: (value: number) => `${(value / 1000).toFixed(0)}k SAR`,
      },
    },
    legend: {
      show: true,
      position: 'top',
      horizontalAlign: 'left',
      labels: {
        colors: `rgba(${currentTheme['on-surface']}, 0.8)`,
      },
    },
    colors: [
      'rgba(var(--v-theme-success), 1)',
      'rgba(var(--v-theme-error), 1)',
      'rgba(var(--v-theme-warning), 1)',
      'rgba(var(--v-theme-primary), 1)',
    ],
    grid: {
      borderColor: `rgba(${currentTheme['on-surface']}, 0.12)`,
      strokeDashArray: 5,
      padding: {
        top: -20,
        bottom: -10,
        left: 0,
        right: 0,
      },
    },
    tooltip: {
      theme: vuetifyTheme.current.value.dark ? 'dark' : 'light',
      y: {
        formatter: (value: number) => `${value.toLocaleString()} SAR`,
      },
    },
  }
})

// Computed chart data
const chartSeries = computed(() => {
  const datasetKey = selectedUnit.value === 'all' ? 'all' : selectedUnit.value.toString()
  const dataset = chartData.value.datasets[datasetKey]

  if (!dataset)
    return []

  return [
    { name: 'Revenue', data: dataset.revenue },
    { name: 'Expenses', data: dataset.expenses },
    { name: 'Royalties', data: dataset.royalties },
    { name: 'Profit', data: dataset.profit },
  ]
})

// API Methods
const fetchUnits = async () => {
  try {
    const response = await $api<ApiResponse<Unit[]>>(`${API_BASE}/units`)
    if (response.success)
      franchiseeUnits.value = response.data
  }
  catch (err) {
    console.error('Error fetching units:', err)
    error.value = 'Failed to fetch units'
  }
}

const fetchChartData = async () => {
  try {
    loading.value = true

    const unitId = selectedUnit.value === 'all' ? null : selectedUnit.value

    const response = await $api<ApiResponse<ChartData>>(`${API_BASE}/chart-data`, {
      query: {
        period_type: selectedPeriod.value,
        unit_id: unitId,
      },
    })

    if (response.success)
      chartData.value = response.data
  }
  catch (err) {
    console.error('Error fetching chart data:', err)
    error.value = 'Failed to fetch chart data'
  }
  finally {
    loading.value = false
  }
}

const fetchTopPerformers = async () => {
  try {
    const response = await $api<ApiResponse<TopPerformer[]>>(`${API_BASE}/top-performers`, {
      query: {
        period_type: selectedPeriod.value,
        limit: 3,
      },
    })

    if (response.success)
      topPerformingLocations.value = response.data
  }
  catch (err) {
    console.error('Error fetching top performers:', err)
  }
}

const fetchCustomerSatisfaction = async () => {
  try {
    const response = await $api<ApiResponse<CustomerSatisfaction>>(`${API_BASE}/customer-satisfaction`, {
      query: {
        period_type: selectedPeriod.value,
      },
    })

    if (response.success)
      customerSatisfactionScore.value = response.data
  }
  catch (err) {
    console.error('Error fetching customer satisfaction:', err)
  }
}

const fetchRatings = async () => {
  try {
    const response = await $api<ApiResponse<RatingsData>>(`${API_BASE}/ratings`, {
      query: {
        period_type: selectedPeriod.value,
      },
    })

    if (response.success)
      ratingsData.value = response.data
  }
  catch (err) {
    console.error('Error fetching ratings:', err)
  }
}

const fetchAllData = async () => {
  await Promise.all([
    fetchUnits(),
    fetchChartData(),
    fetchTopPerformers(),
    fetchCustomerSatisfaction(),
    fetchRatings(),
  ])
}

// Watch for changes in period and unit selection
watch([selectedPeriod, selectedUnit], () => {
  fetchChartData()
  fetchTopPerformers()
  fetchCustomerSatisfaction()
  fetchRatings()
})

// Load data on component mount
onMounted(() => {
  fetchAllData()
})

// Export functionality
const isExportDialogVisible = ref(false)
const exportFormat = ref('csv')
const exportDataType = ref('performance')

const exportFormatOptions = [
  { title: 'CSV Format', value: 'csv' },
  { title: 'Excel Format', value: 'xlsx' },
]

const exportDataTypeOptions = [
  { title: 'Performance Data', value: 'performance' },
  { title: 'Statistics Summary', value: 'stats' },
  { title: 'All Data', value: 'all' },
]

// Helper function to convert data to CSV
const convertToCSV = (data: any[], headers: string[]) => {
  return [
    headers.join(','),
    ...data.map(row => headers.map(header => {
      const value = row[header] || ''

      return typeof value === 'string' && value.includes(',') ? `"${value}"` : value
    }).join(',')),
  ].join('\n')
}

// Helper function to download file
const downloadFile = (content: string, filename: string, mimeType: string) => {
  const blob = new Blob([content], { type: mimeType })
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')

  link.href = url
  link.download = filename
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  window.URL.revokeObjectURL(url)
}

// Main export function
const exportData = () => {
  isExportDialogVisible.value = true
}

const performExport = async () => {
  try {
    const unitId = selectedUnit.value === 'all' ? null : selectedUnit.value

    const response = await $api<ApiResponse<any>>(`${API_BASE}/export`, {
      query: {
        period_type: selectedPeriod.value,
        unit_id: unitId,
        export_type: exportDataType.value,
      },
    })

    if (response.success) {
      const timestamp = new Date().toISOString().split('T')[0]
      const unitName = franchiseeUnits.value.find(u => u.id === selectedUnit.value)?.name || 'All Units'

      if (exportDataType.value === 'performance' || exportDataType.value === 'all')
        exportPerformanceData(response.data.performance, timestamp, unitName)

      if (exportDataType.value === 'stats' || exportDataType.value === 'all')
        exportStatsData(response.data.stats, timestamp)
    }
  }
  catch (err) {
    console.error('Error exporting data:', err)
    error.value = 'Failed to export data'
  }

  isExportDialogVisible.value = false
}

const exportPerformanceData = (performanceData: any[], timestamp: string, unitName: string) => {
  if (!performanceData || performanceData.length === 0)
    return

  const headers = ['Period Date', 'Unit Name', 'Revenue', 'Expenses', 'Royalties', 'Profit', 'Profit Margin', 'Customer Rating', 'Reviews', 'Growth Rate']
  const filename = `performance-data-${unitName.replace(/\s+/g, '-').toLowerCase()}-${selectedPeriod.value}-${timestamp}.${exportFormat.value}`

  if (exportFormat.value === 'csv') {
    const csvContent = convertToCSV(performanceData, headers)

    downloadFile(csvContent, filename, 'text/csv')
  }
  else {
    const csvContent = convertToCSV(performanceData, headers)

    downloadFile(csvContent, filename, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
  }
}

const exportStatsData = (statsData: any, timestamp: string) => {
  if (!statsData)
    return

  const statsRows = [
    // Top Performing Locations
    ...topPerformingLocations.value.map((location, index) => ({
      'Category': 'Top Performing Locations',
      'Rank': index + 1,
      'Name': location.name,
      'Location': location.location,
      'Revenue (SAR)': location.revenue,
      'Growth (%)': location.growth,
      'Rating': '',
      'Reviews': '',
      'Manager': '',
    })),

    // Customer Satisfaction
    {
      'Category': 'Customer Satisfaction',
      'Rank': '',
      'Name': 'Overall Score',
      'Location': '',
      'Revenue (SAR)': '',
      'Growth (%)': '',
      'Rating': `${customerSatisfactionScore.value.score}/5.0`,
      'Reviews': customerSatisfactionScore.value.total_reviews.toString(),
      'Manager': '',
    },

    // Top Rated Franchise
    ...(topRatedFranchise.value
      ? [{
        "Category": 'Top Rated Franchise',
        "Rank": 1,
        "Name": topRatedFranchise.value.name,
        "Location": topRatedFranchise.value.location,
        'Revenue (SAR)': '',
        'Growth (%)': '',
        "Rating": `${topRatedFranchise.value.rating}/5.0`,
        "Reviews": topRatedFranchise.value.reviews.toString(),
        "Manager": topRatedFranchise.value.manager,
      }]
      : []),

    // Lowest Rated Franchise
    ...(lowestRatedFranchise.value
      ? [{
        "Category": 'Needs Attention',
        "Rank": '',
        "Name": lowestRatedFranchise.value.name,
        "Location": lowestRatedFranchise.value.location,
        'Revenue (SAR)': '',
        'Growth (%)': '',
        "Rating": `${lowestRatedFranchise.value.rating}/5.0`,
        "Reviews": lowestRatedFranchise.value.reviews.toString(),
        "Manager": lowestRatedFranchise.value.manager,
      }]
      : []),
  ]

  const headers = ['Category', 'Rank', 'Name', 'Location', 'Revenue (SAR)', 'Growth (%)', 'Rating', 'Reviews', 'Manager']
  const filename = `stats-summary-${timestamp}.${exportFormat.value}`

  if (exportFormat.value === 'csv') {
    const csvContent = convertToCSV(statsRows, headers)

    downloadFile(csvContent, filename, 'text/csv')
  }
  else {
    const csvContent = convertToCSV(statsRows, headers)

    downloadFile(csvContent, filename, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
  }
}

const periodOptions = [
  { title: 'Daily', value: 'daily' },
  { title: 'Monthly', value: 'monthly' },
  { title: 'Yearly', value: 'yearly' },
]
</script>

<template>
  <section>
    <!-- Loading State -->
    <VOverlay
      v-model="loading"
      class="align-center justify-center"
    >
      <VProgressCircular
        indeterminate
        color="primary"
        size="64"
      />
      <div class="text-center mt-4">
        <h4 class="text-h4">
          Loading Performance Data...
        </h4>
        <p class="text-body-1 text-medium-emphasis">
          Please wait while we fetch the latest metrics
        </p>
      </div>
    </VOverlay>

    <!-- Error State -->
    <VAlert
      v-if="error"
      type="error"
      class="mb-4"
      dismissible
      @click:dismiss="error = null"
    >
      {{ error }}
    </VAlert>

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

          <!-- Header Actions -->
          <div class="d-flex gap-3 align-center flex-wrap">
            <!-- Period Selector -->
            <VSelect
              v-model="selectedPeriod"
              :items="periodOptions"
              item-title="title"
              item-value="value"
              density="compact"
              style="min-width: 120px;"
              variant="outlined"
            />

            <!-- Export Button -->
            <VBtn
              color="primary"
              prepend-icon="tabler-download"
              :disabled="loading"
              @click="exportData"
            >
              Export
            </VBtn>
          </div>
        </div>
      </VCol>
    </VRow>

    <!-- Performance Chart -->
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardItem class="pb-4">
            <VCardTitle class="text-h6">
              Franchise Performance Overview
            </VCardTitle>
            <template #append>
              <!-- Unit Selector Tabs -->
              <VTabs
                v-model="selectedUnit"
                density="compact"
                color="primary"
              >
                <VTab
                  v-for="unit in franchiseeUnits"
                  :key="unit.id"
                  :value="unit.id"
                  size="small"
                >
                  {{ unit.name }}
                </VTab>
              </VTabs>
            </template>
          </VCardItem>

          <VDivider />

          <VCardText>
            <!-- Chart Container -->
            <div style="height: 400px; position: relative;">
              <VueApexCharts
                v-if="chartSeries.length > 0"
                type="line"
                height="400"
                :options="chartOptions"
                :series="chartSeries"
              />
              <div
                v-else
                class="d-flex align-center justify-center h-100"
              >
                <div class="text-center">
                  <VIcon
                    icon="tabler-chart-line"
                    size="64"
                    class="text-medium-emphasis mb-4"
                  />
                  <p class="text-body-1 text-medium-emphasis">
                    No data available for the selected period and unit
                  </p>
                </div>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Stat Cards -->
    <VRow class="mt-6">
      <!-- Top 3 Performing Locations -->
      <VCol
        cols="12"
        md="3"
      >
        <VCard class="h-100">
          <VCardText>
            <div class="d-flex align-center justify-space-between mb-4">
              <div>
                <h6 class="text-h6 mb-1">
                  Top 3 Performing Location
                </h6>
                <div class="text-body-2 text-medium-emphasis">
                  This {{ selectedPeriod === 'monthly' ? 'month' : selectedPeriod === 'yearly' ? 'year' : 'period' }}
                </div>
              </div>
              <VAvatar
                color="primary"
                variant="tonal"
                size="40"
              >
                <VIcon icon="tabler-trophy" />
              </VAvatar>
            </div>

            <div class="mt-4">
              <div v-if="topPerformingLocations.length > 0">
                <div
                  v-for="(location, index) in topPerformingLocations"
                  :key="index"
                  class="d-flex align-center justify-space-between py-3"
                  :class="{ 'border-b': index < topPerformingLocations.length - 1 }"
                >
                  <div class="d-flex align-center">
                    <VAvatar
                      :color="index === 0 ? 'warning' : index === 1 ? 'info' : 'success'"
                      size="24"
                      class="me-3"
                    >
                      <span class="text-caption font-weight-bold">{{ index + 1 }}</span>
                    </VAvatar>
                    <div>
                      <div class="text-body-2 font-weight-medium">
                        {{ location.name }}
                      </div>
                      <div class="text-caption text-medium-emphasis">
                        {{ location.location }}
                      </div>
                    </div>
                  </div>
                  <div class="text-end">
                    <div class="text-body-2 font-weight-medium">
                      {{ location.revenue }}
                    </div>
                    <div class="text-caption text-success">
                      {{ location.growth }}
                    </div>
                  </div>
                </div>
              </div>
              <div
                v-else
                class="text-center py-4"
              >
                <VIcon
                  icon="tabler-chart-bar"
                  size="48"
                  class="text-medium-emphasis mb-2"
                />
                <p class="text-body-2 text-medium-emphasis">
                  No performance data available
                </p>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Customer Satisfaction Score -->
      <VCol
        cols="12"
        md="3"
      >
        <VCard class="h-100">
          <VCardText>
            <div class="d-flex align-center justify-space-between mb-4">
              <div>
                <h6 class="text-h6 mb-1">
                  Customer Satisfaction Score
                </h6>
                <div class="text-body-2 text-medium-emphasis">
                  Average rating
                </div>
              </div>
              <VAvatar
                color="success"
                variant="tonal"
                size="40"
              >
                <VIcon icon="tabler-star" />
              </VAvatar>
            </div>

            <div class="mt-4 text-center">
              <div class="d-flex align-center justify-center mb-3">
                <h3 class="text-h3 me-2 text-success">
                  {{ customerSatisfactionScore.score }}
                </h3>
                <div class="text-body-2 text-medium-emphasis">
                  / {{ customerSatisfactionScore.max_score }}
                </div>
              </div>
              <VRating
                :model-value="customerSatisfactionScore.score"
                readonly
                size="small"
                color="warning"
                class="mb-3"
              />
              <div class="text-body-2 text-medium-emphasis mb-2">
                Based on {{ customerSatisfactionScore.total_reviews.toLocaleString() }} reviews
              </div>
              <VChip
                color="success"
                size="small"
                variant="tonal"
              >
                <VIcon
                  start
                  icon="tabler-trending-up"
                  size="16"
                />
                {{ customerSatisfactionScore.trend }} this month
              </VChip>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Top-Rated Franchise -->
      <VCol
        cols="12"
        md="3"
      >
        <VCard class="h-100">
          <VCardText>
            <div class="d-flex align-center justify-space-between mb-4">
              <div>
                <h6 class="text-h6 mb-1">
                  Top-Rated Franchise
                </h6>
                <div class="text-body-2 text-medium-emphasis">
                  Highest customer rating
                </div>
              </div>
              <VAvatar
                color="warning"
                variant="tonal"
                size="40"
              >
                <VIcon icon="tabler-award" />
              </VAvatar>
            </div>

            <div
              v-if="topRatedFranchise"
              class="mt-4 text-center"
            >
              <VAvatar
                color="warning"
                size="60"
                class="mb-3"
              >
                <VIcon
                  icon="tabler-building-store"
                  size="30"
                />
              </VAvatar>
              <div class="text-h6 font-weight-medium mb-1">
                {{ topRatedFranchise.name }}
              </div>
              <div class="text-body-2 text-medium-emphasis mb-3">
                {{ topRatedFranchise.location }}
              </div>
              <div class="d-flex align-center justify-center mb-2">
                <h4 class="text-h4 me-2 text-warning">
                  {{ topRatedFranchise.rating }}
                </h4>
                <VRating
                  :model-value="topRatedFranchise.rating"
                  readonly
                  size="small"
                  color="warning"
                />
              </div>
              <div class="text-caption text-medium-emphasis">
                {{ topRatedFranchise.reviews }} reviews
              </div>
            </div>
            <div
              v-else
              class="mt-4 text-center"
            >
              <VIcon
                icon="tabler-star-off"
                size="48"
                class="text-medium-emphasis mb-2"
              />
              <p class="text-body-2 text-medium-emphasis">
                No rating data available
              </p>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Lowest-Rated Franchise (Needs Attention) -->
      <VCol
        cols="12"
        md="3"
      >
        <VCard class="h-100">
          <VCardText>
            <div class="d-flex align-center justify-space-between mb-4">
              <div>
                <h6 class="text-h6 mb-1">
                  Lowest-Rated Franchise
                </h6>
                <div class="text-body-2 text-medium-emphasis">
                  Needs attention
                </div>
              </div>
              <VAvatar
                color="error"
                variant="tonal"
                size="40"
              >
                <VIcon icon="tabler-alert-triangle" />
              </VAvatar>
            </div>

            <div
              v-if="lowestRatedFranchise"
              class="mt-4 text-center"
            >
              <VAvatar
                color="error"
                size="60"
                class="mb-3"
              >
                <VIcon
                  icon="tabler-building-store"
                  size="30"
                />
              </VAvatar>
              <div class="text-h6 font-weight-medium mb-1">
                {{ lowestRatedFranchise.name }}
              </div>
              <div class="text-body-2 text-medium-emphasis mb-3">
                {{ lowestRatedFranchise.location }}
              </div>
              <div class="d-flex align-center justify-center mb-2">
                <h4 class="text-h4 me-2 text-error">
                  {{ lowestRatedFranchise.rating }}
                </h4>
                <VRating
                  :model-value="lowestRatedFranchise.rating"
                  readonly
                  size="small"
                  color="warning"
                />
              </div>
              <div class="text-caption text-medium-emphasis mb-3">
                {{ lowestRatedFranchise.reviews }} reviews
              </div>
              <VBtn
                color="error"
                variant="tonal"
                size="small"
                block
              >
                <VIcon
                  start
                  icon="tabler-message"
                  size="16"
                />
                Contact Unit
              </VBtn>
            </div>
            <div
              v-else
              class="mt-4 text-center"
            >
              <VIcon
                icon="tabler-star"
                size="48"
                class="text-medium-emphasis mb-2"
              />
              <p class="text-body-2 text-medium-emphasis">
                No rating data available
              </p>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Export Options Dialog -->
    <VDialog
      v-model="isExportDialogVisible"
      max-width="500"
    >
      <VCard class="text-center px-6 py-8">
        <VCardItem class="pb-4">
          <VCardTitle class="text-h5 mb-2">
            <VIcon
              icon="tabler-download"
              class="me-2"
            />
            Export Data
          </VCardTitle>
          <VCardSubtitle class="text-body-1">
            Choose export format and data type
          </VCardSubtitle>
        </VCardItem>

        <VDivider class="mb-6" />

        <VCardText class="text-start">
          <VRow>
            <VCol cols="12">
              <VSelect
                v-model="exportDataType"
                :items="exportDataTypeOptions"
                item-title="title"
                item-value="value"
                label="Data Type"
                variant="outlined"
                density="comfortable"
                prepend-inner-icon="tabler-database"
              />
            </VCol>
            <VCol cols="12">
              <VSelect
                v-model="exportFormat"
                :items="exportFormatOptions"
                item-title="title"
                item-value="value"
                label="Export Format"
                variant="outlined"
                density="comfortable"
                prepend-inner-icon="tabler-file-type-csv"
              />
            </VCol>
          </VRow>

          <!-- Export Info -->
          <VAlert
            type="info"
            variant="tonal"
            class="mt-4"
            density="compact"
          >
            <template #prepend>
              <VIcon icon="tabler-info-circle" />
            </template>
            <div class="text-body-2">
              <strong>Current Selection:</strong><br>
              Period: {{ periodOptions.find(p => p.value === selectedPeriod)?.title }}<br>
              Unit: {{ franchiseeUnits.find(u => u.id === selectedUnit)?.name }}
            </div>
          </VAlert>
        </VCardText>

        <VCardActions class="d-flex align-center justify-center gap-3 pt-4">
          <VBtn
            variant="outlined"
            color="secondary"
            @click="isExportDialogVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            prepend-icon="tabler-download"
            :disabled="loading"
            @click="performExport"
          >
            Export Data
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
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
