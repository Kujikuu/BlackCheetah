<script setup lang="ts">
import { useTheme } from 'vuetify'

const vuetifyTheme = useTheme()
const currentTheme = vuetifyTheme.current.value.colors

// Reactive data
const isLoading = ref(true)
const error = ref('')
const totalUsers = ref(0)
const userGrowth = ref(0)

const series = ref([
  {
    name: 'Users',
    data: [0, 0, 0, 0, 0, 0, 0],
  },
])

// Fetch chart data from API
const fetchChartData = async () => {
  try {
    isLoading.value = true

    const response = await $api('/v1/admin/dashboard/chart-data')

    if (response.success && response.data.users) {
      // Get last 7 months of user data
      const userData = response.data.users.slice(-7)
      const userCounts = userData.map((item: any) => item.users)

      series.value = [
        {
          name: 'Users',
          data: userCounts,
        },
      ]

      // Calculate total users and growth
      const currentMonth = userCounts[userCounts.length - 1] || 0
      const previousMonth = userCounts[userCounts.length - 2] || 0

      totalUsers.value = userData.reduce((sum: number, item: any) => sum + item.users, 0)

      if (previousMonth > 0)
        userGrowth.value = ((currentMonth - previousMonth) / previousMonth) * 100
      else
        userGrowth.value = currentMonth > 0 ? 100 : 0
    }
  }
  catch (err) {
    console.error('Error fetching chart data:', err)
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

const chartOptions = {
  chart: {
    type: 'area',
    parentHeightOffset: 0,
    toolbar: {
      show: false,
    },
    sparkline: {
      enabled: true,
    },
  },
  markers: {
    colors: 'transparent',
    strokeColors: 'transparent',
  },
  grid: {
    show: false,
  },
  colors: [currentTheme.primary],
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 0.9,
      opacityFrom: 0.5,
      opacityTo: 0.07,
      stops: [0, 80, 100],
    },
  },
  dataLabels: {
    enabled: false,
  },
  stroke: {
    width: 2,
    curve: 'smooth',
  },
  xaxis: {
    show: true,
    lines: {
      show: false,
    },
    labels: {
      show: false,
    },
    stroke: {
      width: 0,
    },
    axisBorder: {
      show: false,
    },
  },
  yaxis: {
    stroke: {
      width: 0,
    },
    show: false,
  },
  tooltip: {
    enabled: false,
  },
}
</script>

<template>
  <VCard>
    <VCardItem class="pb-3">
      <VCardTitle>
        Total Users
      </VCardTitle>
      <VCardSubtitle>
        Last 7 Days
      </VCardSubtitle>
    </VCardItem>

    <VueApexCharts
      :options="chartOptions"
      :series="series"
      :height="68"
    />

    <VCardText class="pt-1">
      <div
        v-if="isLoading"
        class="d-flex align-center justify-space-between gap-x-2"
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
        class="text-center text-error text-sm"
      >
        {{ error }}
      </div>
      <div
        v-else
        class="d-flex align-center justify-space-between gap-x-2"
      >
        <h4 class="text-h4 text-center">
          {{ totalUsers.toLocaleString() }}
        </h4>
        <span
          class="text-sm"
          :class="userGrowth >= 0 ? 'text-success' : 'text-error'"
        >
          {{ userGrowth >= 0 ? '+' : '' }}{{ userGrowth.toFixed(1) }}%
        </span>
      </div>
    </VCardText>
  </VCard>
</template>
