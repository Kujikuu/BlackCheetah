<script setup lang="ts">
import { SaudiRiyal } from 'lucide-vue-next'
import { computed, onMounted, ref } from 'vue'
import { useTheme } from 'vuetify'
import type { ProductSalesItem, SalesWidgetData } from '@/services/api/franchisee-dashboard'
import { franchiseeDashboardApi } from '@/services/api/franchisee-dashboard'
import { getAreaChartSplineConfig } from '@core/libs/apex-chart/apexCharConfig'

// Sales dashboard data
const vuetifyTheme = useTheme()

// Loading state
const loading = ref(false)
const error = ref<string | null>(null)
const hasLoadedApiData = ref(false)

// Utility function to prefix positive numbers with +
const prefixWithPlus = (value: number) => {
  return value > 0 ? `+${value}` : value.toString()
}

// Reactive data with default values
const widgetData = ref<SalesWidgetData[]>([])
const mostSellingItemsData = ref<ProductSalesItem[]>([])
const lowSellingItemsData = ref<ProductSalesItem[]>([])

const monthlyPerformanceData = ref<any[]>([
  {
    name: 'Top Performing Items',
    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
  },
  {
    name: 'Low Performing Items',
    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
  },
  {
    name: 'Average Performance',
    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
  },
])

// Chart configurations
const monthlyPerformanceChartConfig = computed(() => getAreaChartSplineConfig(vuetifyTheme.current.value))

// Computed property to check if chart data is ready
const isChartDataReady = computed(() => {
  return !loading.value
    && hasLoadedApiData.value
    && monthlyPerformanceData.value.length > 0
    && monthlyPerformanceData.value.every(series =>
      series.data
      && Array.isArray(series.data)
      && series.data.length > 0
      && series.data.every((value: any) => typeof value === 'number' && !Number.isNaN(value)),
    )
})

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
    // Load sales statistics
    const salesStatsResponse = await franchiseeDashboardApi.getSalesStatistics()
    if (salesStatsResponse.success && salesStatsResponse.data) {
      const stats = salesStatsResponse.data

      widgetData.value = [
        {
          title: 'Total Sales',
          value: formatCurrency(stats.totalSales),
          change: stats.salesChange,
          desc: 'This month sales',
          icon: SaudiRiyal,
          iconColor: 'primary',
        },
        {
          title: 'Total Profit',
          value: formatCurrency(stats.totalProfit),
          change: stats.profitChange,
          desc: 'This month profit',
          icon: 'tabler-trending-up',
          iconColor: 'success',
        },
      ]
    }

    // Load product sales data
    const productSalesResponse = await franchiseeDashboardApi.getProductSales()
    if (productSalesResponse.success && productSalesResponse.data) {
      mostSellingItemsData.value = productSalesResponse.data.mostSelling
      lowSellingItemsData.value = productSalesResponse.data.lowSelling
    }

    // Load monthly performance data
    const monthlyPerformanceResponse = await franchiseeDashboardApi.getMonthlyPerformance()
    if (monthlyPerformanceResponse.success && monthlyPerformanceResponse.data) {
      const performance = monthlyPerformanceResponse.data

      monthlyPerformanceData.value = [
        {
          name: 'Top Performing Items',
          data: performance.topPerforming,
        },
        {
          name: 'Low Performing Items',
          data: performance.lowPerforming,
        },
        {
          name: 'Average Performance',
          data: performance.averagePerformance,
        },
      ]
      hasLoadedApiData.value = true
    }
  }
  catch (err) {
    error.value = 'Failed to load dashboard data. Please try again.'
    console.error('Error loading dashboard data:', err)
  }
  finally {
    loading.value = false
  }
}

// Load data on component mount
onMounted(() => {
  loadDashboardData()
})
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
              Loading dashboard data...
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
      <!-- 👉 Widgets -->
      <div class="d-flex mb-6">
        <VRow>
          <template
            v-for="(data, id) in widgetData"
            :key="id"
          >
            <VCol
              cols="12"
              md="6"
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

      <!-- 👉 Charts Section -->
      <VRow class="mb-6">
        <!-- Most Selling Items List -->
        <VCol
          cols="12"
          md="6"
        >
          <VCard>
            <VCardItem>
              <VCardTitle>Most Selling Items</VCardTitle>
              <VCardSubtitle>Top performing products</VCardSubtitle>
            </VCardItem>
            <VCardText>
              <VList class="py-0">
                <VListItem
                  v-for="(item, index) in mostSellingItemsData"
                  :key="index"
                  class="px-0"
                >
                  <template #prepend>
                    <VAvatar
                      :color="index === 0 ? 'success' : index === 1 ? 'primary' : index === 2 ? 'warning' : 'secondary'"
                      size="40"
                      class="me-3"
                    >
                      <span class="text-sm font-weight-medium">{{ index + 1 }}</span>
                    </VAvatar>
                  </template>

                  <VListItemTitle class="font-weight-medium text-high-emphasis">
                    {{ item.name }}
                  </VListItemTitle>

                  <VListItemSubtitle class="d-flex align-center gap-2 mt-1">
                    <VChip
                      size="small"
                      color="success"
                      variant="tonal"
                    >
                      {{ item.quantity }} sold
                    </VChip>
                    <span class="text-body-2 text-medium-emphasis">•</span>
                    <span class="text-body-2 font-weight-medium text-high-emphasis">
                      {{ item.avgPrice ? formatCurrency(item.avgPrice) : (item.price || 'N/A') }}
                    </span>
                  </VListItemSubtitle>
                </VListItem>
              </VList>
            </VCardText>
          </VCard>
        </VCol>

        <!-- Low Selling Items List -->
        <VCol
          cols="12"
          md="6"
        >
          <VCard>
            <VCardItem>
              <VCardTitle>Low Selling Items</VCardTitle>
              <VCardSubtitle>Underperforming products</VCardSubtitle>
            </VCardItem>
            <VCardText>
              <VList class="py-0">
                <VListItem
                  v-for="(item, index) in lowSellingItemsData"
                  :key="index"
                  class="px-0"
                >
                  <template #prepend>
                    <VAvatar
                      :color="index === 0 ? 'error' : index === 1 ? 'warning' : index === 2 ? 'info' : 'secondary'"
                      size="40"
                      class="me-3"
                    >
                      <span class="text-sm font-weight-medium">{{ index + 1 }}</span>
                    </VAvatar>
                  </template>

                  <VListItemTitle class="font-weight-medium text-high-emphasis">
                    {{ item.name }}
                  </VListItemTitle>

                  <VListItemSubtitle class="d-flex align-center gap-2 mt-1">
                    <VChip
                      size="small"
                      color="error"
                      variant="tonal"
                    >
                      {{ item.quantity }} sold
                    </VChip>
                    <span class="text-body-2 text-medium-emphasis">•</span>
                    <span class="text-body-2 font-weight-medium text-high-emphasis">
                      {{ item.avgPrice ? formatCurrency(item.avgPrice) : (item.price || 'N/A') }}
                    </span>
                  </VListItemSubtitle>
                </VListItem>
              </VList>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

      <!-- Month wise Top and Low Performing Items Bar Chart -->
      <VCard class="mb-6">
        <VCardItem>
          <VCardTitle>Month wise Top and Low Performing Items</VCardTitle>
          <VCardSubtitle>Monthly performance comparison</VCardSubtitle>
        </VCardItem>
        <VCardText>
          <VueApexCharts
            v-if="isChartDataReady"
            type="area"
            height="350"
            :options="{
              ...monthlyPerformanceChartConfig,
              xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
              },
              tooltip: {
                theme: vuetifyTheme.current.value.dark ? 'dark' : 'light',
                y: {
                  formatter: (value: number) => `${value.toLocaleString()} SAR`,
                },
              },
            }"
            :series="monthlyPerformanceData"
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
    </div>
  </section>
</template>
