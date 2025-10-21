<script setup lang="ts">
import { adminApi, type AdminChartData } from '@/services/api'

// Reactive data
const isLoading = ref(true)
const error = ref('')
const totalRequests = ref(0)
const requestsGrowth = ref(0)

const series = ref([
  {
    name: 'Requests',
    data: [0, 0, 0, 0, 0, 0, 0],
  },
])

// Fetch chart data from API
const fetchChartData = async () => {
  try {
    isLoading.value = true

    const response = await adminApi.getDashboardChartData()

    if (response.success && response.data.requests) {
      // Get last 7 months of requests data
      const requestsData = response.data.requests.slice(-7)
      const requestsCounts = requestsData.map((item: any) => item.requests)

      series.value = [
        {
          name: 'Requests',
          data: requestsCounts,
        },
      ]

      // Calculate total requests and growth
      const currentMonth = requestsCounts[requestsCounts.length - 1] || 0
      const previousMonth = requestsCounts[requestsCounts.length - 2] || 0

      totalRequests.value = requestsData.reduce((sum: number, item: any) => sum + item.requests, 0)

      if (previousMonth > 0)
        requestsGrowth.value = ((currentMonth - previousMonth) / previousMonth) * 100
      else
        requestsGrowth.value = currentMonth > 0 ? 100 : 0
    }
  }
  catch (err) {
    console.error('Error fetching requests chart data:', err)
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

const chartColors = {
  primary: '#9155FD',
  warning: '#FFB400',
  success: '#56CA00',
  info: '#16B1FF',
  error: '#FF4C51',
}

const chartOptions = computed(() => {
  return {
    chart: {
      height: 90,
      parentHeightOffset: 0,
      type: 'bar',
      toolbar: {
        show: false,
      },
    },
    tooltip: {
      enabled: false,
    },
    plotOptions: {
      bar: {
        barHeight: '100%',
        columnWidth: '30%',
        startingShape: 'rounded',
        endingShape: 'rounded',
        borderRadius: 4,
        colors: {
          backgroundBarColors: [
            chartColors.warning,
            chartColors.warning,
            chartColors.warning,
            chartColors.warning,
            chartColors.warning,
            chartColors.warning,
            chartColors.warning,
          ],
          backgroundBarRadius: 4,
        },
      },
    },
    colors: [chartColors.warning],
    grid: {
      show: false,
      padding: {
        top: -30,
        left: -16,
        bottom: 0,
        right: -6,
      },
    },
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
        show: false,
      },
    },
    yaxis: {
      labels: {
        show: false,
      },
    },
    responsive: [
      {
        breakpoint: 1264,
        options: {
          plotOptions: {
            bar: {
              borderRadius: 6,
              columnWidth: '30%',
              colors: {
                backgroundBarRadius: 6,
              },
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
    <VCardItem class="pb-3">
      <VCardTitle>Technical Requests</VCardTitle>
      <VCardSubtitle>Last Week</VCardSubtitle>
    </VCardItem>

    <VCardText>
      <VueApexCharts
        :options="chartOptions"
        :series="series"
        :height="62"
      />

      <div
        v-if="isLoading"
        class="d-flex align-center justify-space-between gap-x-2 mt-3"
      >
        <VSkeletonLoader
          type="text"
          width="60px"
          height="32px"
        />
        <VSkeletonLoader
          type="text"
          width="40px"
        />
      </div>
      <div
        v-else-if="error"
        class="text-center text-error text-sm mt-3"
      >
        {{ error }}
      </div>
      <div
        v-else
        class="d-flex align-center justify-space-between gap-x-2 mt-3"
      >
        <h4 class="text-h4 text-center">
          {{ totalRequests.toLocaleString() }}
        </h4>
        <div
          class="text-sm"
          :class="requestsGrowth >= 0 ? 'text-success' : 'text-error'"
        >
          {{ requestsGrowth >= 0 ? '+' : '' }}{{ requestsGrowth.toFixed(1) }}%
        </div>
      </div>
    </VCardText>
  </VCard>
</template>
