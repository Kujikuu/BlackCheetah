<script setup lang="ts">
import { useTheme } from 'vuetify'

interface Props {
  totalFranchisees?: number
  change?: number
}

const props = withDefaults(defineProps<Props>(), {
  totalFranchisees: 0,
  change: 0,
})

const vuetifyTheme = useTheme()

const currentTheme = vuetifyTheme.current.value.colors

// Generate chart data based on total franchisees
const series = computed(() => [
  {
    data: [
      Math.round(props.totalFranchisees * 0.7),
      Math.round(props.totalFranchisees * 0.5),
      props.totalFranchisees,
      Math.round(props.totalFranchisees * 0.8),
    ],
  },
])

const chartOptions = {
  chart: {
    type: 'area',
    toolbar: {
      show: false,
    },
    sparkline: {
      enabled: true,
    },
  },
  tooltip: {
    theme: vuetifyTheme.current.value.dark ? 'dark' : 'light',
  },
  markers: {
    colors: 'transparent',
    strokeColors: 'transparent',
  },
  grid: {
    show: false,
  },
  colors: [currentTheme.success],
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 0.8,
      opacityFrom: 0.6,
      opacityTo: 0.1,
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
  responsive: [
    {
      breakpoint: 1387,
      options: {
        chart: {
          height: 80,
        },
      },
    },
    {
      breakpoint: 1200,
      options: {
        chart: {
          height: 120,
        },
      },
    },
  ],
}
</script>

<template>
  <VCard>
    <VCardText>
      <h5 class="text-h5 mb-3">
        Total Franchisees
      </h5>
      <p class="mb-0">
        Active Franchise Partners
      </p>
      <h4 class="text-h4">
        {{ totalFranchisees }}
      </h4>
      <div v-if="change !== 0" class="text-sm mt-1" :class="change > 0 ? 'text-success' : 'text-error'">
        {{ change > 0 ? '+' : '' }}{{ change }}% from last month
      </div>
    </VCardText>

    <VueApexCharts
      :options="chartOptions"
      :series="series"
      :height="80"
    />
  </VCard>
</template>

