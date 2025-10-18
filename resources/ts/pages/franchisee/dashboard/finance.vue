<script setup lang="ts">
import { SaudiRiyal } from 'lucide-vue-next'
import { computed, onMounted, ref } from 'vue'
import { useTheme } from 'vuetify'
import { franchiseeDashboardApi } from '@/services/api/franchisee-dashboard'
import type { FinanceWidgetData } from '@/services/api/franchisee-dashboard'

const chartColors = {
  primary: '#9155FD',
  warning: '#FFB400',
  success: '#56CA00',
  info: '#16B1FF',
  error: '#FF4C51',
}

const headingColor = 'rgba(var(--v-theme-on-background), var(--v-high-emphasis-opacity))'
const labelColor = 'rgba(var(--v-theme-on-background), var(--v-medium-emphasis-opacity))'
const borderColor = 'rgba(var(--v-border-color), var(--v-border-opacity))'

// Loading and error states
const loading = ref(false)
const error = ref<string | null>(null)
const hasLoadedApiData = ref(false)

// 👉 Finance Stats
const financeStats = ref<FinanceWidgetData[]>([
  {
    icon: SaudiRiyal,
    color: 'primary',
    title: 'Total Sales',
    value: '$2,458,650',
    change: 18.2,
    isHover: false,
  },
  {
    icon: 'tabler-receipt',
    color: 'error',
    title: 'Total Expenses',
    value: '$845,230',
    change: -8.7,
    isHover: false,
  },
  {
    icon: 'tabler-trending-up',
    color: 'success',
    title: 'Total Profit',
    value: '$1,245,125',
    change: 24.5,
    isHover: false,
  },
])

// 👉 Summary Chart (Combined Sales, Expenses, Profit)
const summarySeries = ref([
  {
    name: 'Sales',
    data: [420000, 385000, 445000, 398000, 465000, 425000, 485000, 452000, 478000, 495000, 512000, 548000],
  },
  {
    name: 'Expenses',
    data: [145000, 132000, 158000, 142000, 165000, 148000, 172000, 155000, 168000, 175000, 182000, 188000],
  },
  {
    name: 'Profit',
    data: [212000, 195250, 220250, 196300, 230250, 213250, 240250, 229200, 238300, 245750, 253200, 277800],
  },
])

// Format currency
const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'SAR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount)
}

// Load dashboard data
const loadDashboardData = async () => {
  loading.value = true
  error.value = null

  try {
    // Load finance statistics
    const financeStatsResponse = await franchiseeDashboardApi.getFinanceStatistics()
    if (financeStatsResponse.success && financeStatsResponse.data) {
      const stats = financeStatsResponse.data

      financeStats.value = [
        {
          icon: SaudiRiyal,
          color: 'primary',
          title: 'Total Sales',
          value: formatCurrency(stats.totalSales),
          change: stats.salesChange,
          isHover: false,
        },
        {
          icon: 'tabler-receipt',
          color: 'error',
          title: 'Total Expenses',
          value: formatCurrency(stats.totalExpenses),
          change: stats.expensesChange,
          isHover: false,
        },
        {
          icon: 'tabler-trending-up',
          color: 'success',
          title: 'Total Profit',
          value: formatCurrency(stats.totalProfit),
          change: stats.profitChange,
          isHover: false,
        },
      ]
    }

    // Load financial summary data
    const summaryResponse = await franchiseeDashboardApi.getFinancialSummary()
    if (summaryResponse.success && summaryResponse.data) {
      const summary = summaryResponse.data

      summarySeries.value = [
        {
          name: 'Sales',
          data: summary.sales,
        },
        {
          name: 'Expenses',
          data: summary.expenses,
        },
        {
          name: 'Profit',
          data: summary.profit,
        },
      ]
      hasLoadedApiData.value = true
    }
  }
  catch (err) {
    error.value = 'Failed to load finance data. Please try again.'
    console.error('Error loading finance data:', err)
  }
  finally {
    loading.value = false
  }
}

// Load data on component mount
onMounted(() => {
  loadDashboardData()
})

const themeColors = useTheme().computedThemes.value

// Computed property to check if chart data is ready
const isChartDataReady = computed(() => {
  return !loading.value
    && hasLoadedApiData.value
    && summarySeries.value.length > 0
    && summarySeries.value.every(series =>
      series.data
      && Array.isArray(series.data)
      && series.data.length > 0
      && series.data.every((value: any) => typeof value === 'number' && !Number.isNaN(value)),
    )
})

const summaryConfig = {
  chart: {
    type: 'line',
    stacked: false,
    parentHeightOffset: 0,
    toolbar: {
      show: false,
    },
    zoom: {
      enabled: false,
    },
  },
  markers: {
    size: 4,
    colors: '#fff',
    strokeColors: [chartColors.primary, chartColors.error, chartColors.success],
    hover: {
      size: 5,
    },
    borderRadius: 4,
  },
  stroke: {
    curve: 'smooth',
    width: 3,
    lineCap: 'round',
  },
  legend: {
    show: true,
    position: 'top',
    horizontalAlign: 'left',
    markers: {
      width: 10,
      height: 10,
      offsetX: -3,
    },
    fontSize: '14px',
    fontFamily: 'Open Sans',
    fontWeight: 400,
    labels: {
      colors: headingColor,
    },
    itemMargin: {
      horizontal: 12,
      vertical: 8,
    },
  },
  grid: {
    strokeDashArray: 8,
    borderColor,
  },
  colors: [chartColors.primary, chartColors.error, chartColors.success],
  dataLabels: {
    enabled: false,
  },
  xaxis: {
    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    labels: {
      style: {
        colors: labelColor,
        fontSize: '13px',
        fontWeight: 400,
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
        fontWeight: 400,
      },
      formatter(val: number) {
        return `${new Intl.NumberFormat('en-US', {
          style: 'currency',
          currency: 'SAR',
          minimumFractionDigits: 0,
          maximumFractionDigits: 0,
        }).format(val / 1000)}k`
      },
    },
  },
  tooltip: {
    theme: themeColors.dark ? 'dark' : 'light',
    y: {
      formatter(val: number) {
        return new Intl.NumberFormat('en-US', {
          style: 'currency',
          currency: 'SAR',
          minimumFractionDigits: 0,
          maximumFractionDigits: 0,
        }).format(val)
      },
    },
  },
}
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
              Loading finance data...
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
      <!-- 👉 Finance Stats Cards -->
      <VRow class="mb-6">
        <VCol
          v-for="(data, index) in financeStats"
          :key="index"
          cols="12"
          md="4"
          sm="6"
        >
          <VCard
            class="finance-card-statistics cursor-pointer"
            :style="data.isHover ? `border-block-end-color: rgb(var(--v-theme-${data.color}))` : `border-block-end-color: rgba(var(--v-theme-${data.color}),0.38)`"
            @mouseenter="data.isHover = true"
            @mouseleave="data.isHover = false"
          >
            <VCardText>
              <div class="d-flex align-center gap-x-4 mb-1">
                <VAvatar
                  variant="tonal"
                  :color="data.color"
                  rounded
                >
                  <VIcon
                    :icon="data.icon"
                    size="28"
                  />
                </VAvatar>
                <h4 class="text-h4">
                  {{ data.value }}
                </h4>
              </div>
              <div class="text-body-1 mb-1">
                {{ data.title }}
              </div>
              <div class="d-flex gap-x-2 align-center">
                <h6 class="text-h6">
                  {{ (data.change > 0) ? '+' : '' }} {{ data.change }}%
                </h6>
                <div class="text-sm text-disabled">
                  than last month
                </div>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

      <!-- 👉 Summary Chart -->
      <VRow class="mb-6">
        <VCol cols="12">
          <VCard>
            <VCardItem
              title="Financial Summary"
              subtitle="Yearly overview of sales, expenses and profit"
            >
              <template #append>
                <VBtn
                  variant="tonal"
                  size="small"
                  append-icon="tabler-chevron-down"
                >
                  2025
                </VBtn>
              </template>
            </VCardItem>

            <VCardText>
              <VueApexCharts
                v-if="isChartDataReady"
                type="line"
                height="400"
                :options="summaryConfig"
                :series="summarySeries"
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
    </div>
  </section>
</template>

<style lang="scss" scoped>
@use "@core-scss/base/mixins" as mixins;

.finance-card-statistics {
  border-block-end-style: solid;
  border-block-end-width: 2px;

  &:hover {
    border-block-end-width: 3px;
    margin-block-end: -1px;

    @include mixins.elevation(8);

    transition: all 0.1s ease-out;
  }
}

.skin--bordered {
  .finance-card-statistics {
    border-block-end-width: 2px;

    &:hover {
      border-block-end-width: 3px;
      margin-block-end: -2px;
      transition: all 0.1s ease-out;
    }
  }
}
</style>
