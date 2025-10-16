<script setup lang="ts">
import { useTheme } from 'vuetify'

const vuetifyTheme = useTheme()

// Reactive data
const isLoading = ref(true)
const error = ref('')
const totalRevenue = ref(0)
const revenueGrowth = ref(0)

const series = ref([
  {
    data: [0, 0, 0, 0, 0, 0, 0],
  },
])

// Fetch chart data from API
const fetchChartData = async () => {
  try {
    isLoading.value = true

    const response = await $api('/v1/admin/dashboard/chart-data')

    if (response.success && response.data.revenue) {
      // Get last 7 months of revenue data
      const revenueData = response.data.revenue.slice(-7)
      const revenueCounts = revenueData.map((item: any) => item.revenue)

      series.value = [
        {
          data: revenueCounts,
        },
      ]

      // Calculate total revenue and growth
      const currentMonth = revenueCounts[revenueCounts.length - 1] || 0
      const previousMonth = revenueCounts[revenueCounts.length - 2] || 0

      totalRevenue.value = revenueData.reduce((sum: number, item: any) => sum + item.revenue, 0)

      if (previousMonth > 0)
        revenueGrowth.value = ((currentMonth - previousMonth) / previousMonth) * 100
      else
        revenueGrowth.value = currentMonth > 0 ? 100 : 0
    }
  }
  catch (err) {
    console.error('Error fetching revenue chart data:', err)
    error.value = 'Failed to load chart data'
  }
  finally {
    isLoading.value = false
  }
}

// Fetch data on component mount
onMounted(() => {
  fetchChartData()
})

const chartOptions = computed(() => {
  const currentTheme = vuetifyTheme.current.value.colors
  const variableTheme = vuetifyTheme.current.value.variables

  const headingColor = 'rgba(var(--v-theme-on-background), var(--v-high-emphasis-opacity))'
  const labelColor = 'rgba(var(--v-theme-on-background), var(--v-medium-emphasis-opacity))'
  const borderColor = 'rgba(var(--v-border-color), var(--v-border-opacity))'

  const chartColors = {
    primary: '#9155FD',
    warning: '#FFB400',
    success: '#56CA00',
    info: '#16B1FF',
    error: '#FF4C51',
  }

  return {
    chart: {
      height: 162,
      type: 'bar',
      parentHeightOffset: 0,
      toolbar: {
        show: false,
      },
    },
    plotOptions: {
      bar: {
        barHeight: '80%',
        columnWidth: '30%',
        startingShape: 'rounded',
        endingShape: 'rounded',
        borderRadius: 6,
        distributed: true,
      },
    },
    tooltip: {
      enabled: false,
    },
    grid: {
      show: false,
      padding: {
        top: -20,
        bottom: -12,
        left: -10,
        right: 0,
      },
    },
    colors: [
      chartColors.success,
      chartColors.success,
      chartColors.success,
      chartColors.success,
      chartColors.success,
      chartColors.success,
      chartColors.success,
    ],
    dataLabels: {
      enabled: false,
    },
    legend: {
      show: false,
    },
    xaxis: {
      categories: ['M', 'T', 'W', 'T', 'F', 'S', 'S'],
      axisBorder: {
        show: false,
      },
      axisTicks: {
        show: false,
      },
      labels: {
        style: {
          colors: labelColor,
          fontSize: '13px',
        },
      },
    },
    yaxis: {
      labels: {
        show: false,
      },
    },
    states: {
      hover: {
        filter: {
          type: 'none',
        },
      },
    },
    responsive: [
      {
        breakpoint: 1640,
        options: {
          plotOptions: {
            bar: {
              columnWidth: '40%',
              borderRadius: 6,
            },
          },
        },
      },
    ],
  }
})
</script>

<template>
  <VCard>
    <VCardText class="d-flex justify-space-between">
      <div class="d-flex flex-column">
        <div class="mb-auto">
          <h5 class="text-h5 text-no-wrap mb-2">
            Revenue Growth
          </h5>
          <div class="text-body-1">
            Last 7 Months
          </div>
        </div>

        <div v-if="isLoading">
          <VSkeletonLoader type="text" width="100px" height="32px" class="mb-2" />
          <VSkeletonLoader type="chip" width="60px" height="24px" />
        </div>
        <div v-else-if="error">
          <h5 class="text-h3 mb-2 text-error">
            Error
          </h5>
          <VChip label color="error" size="small">
            Failed to load
          </VChip>
        </div>
        <div v-else>
          <h5 class="text-h3 mb-2">
            SAR {{ totalRevenue.toLocaleString() }}
          </h5>
          <VChip label :color="revenueGrowth >= 0 ? 'success' : 'error'" size="small">
            {{ revenueGrowth >= 0 ? '+' : '' }}{{ revenueGrowth.toFixed(1) }}%
          </VChip>
        </div>
      </div>
      <div>
        <VueApexCharts v-if="!isLoading && !error" :options="chartOptions" :series="series" :height="162" />
        <div v-else-if="isLoading" class="d-flex align-center justify-center" style="height: 162px; width: 162px;">
          <VProgressCircular indeterminate color="primary" size="40" />
        </div>
        <div v-else class="d-flex align-center justify-center" style="height: 162px; width: 162px;">
          <VIcon icon="tabler-alert-circle" color="error" size="40" />
        </div>
      </div>
    </VCardText>
  </VCard>
</template>
