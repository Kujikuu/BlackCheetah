<script setup lang="ts">
import { formatCurrency } from '@/@core/utils/formatters'
// API composable
const { data: financeData, execute: fetchFinanceData, isFetching: isLoading } = useApi('/v1/franchisor/dashboard/finance')

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

// 👉 Reactive data with proper types
interface FinanceStat {
  icon: string
  color: string
  title: string
  value: string
  change: number
  isHover: boolean
}

interface ChartSeries {
  name: string
  data: number[]
}

interface ApiResponse {
  success: boolean
  data: {
    stats: {
      total_sales: number
      sales_change: number
      total_expenses: number
      expenses_change: number
      net_profit: number
      profit_change: number
      profit_margin: number
      margin_change: number
    }
    top_stores_sales?: Array<{ name: string; sales: number }>
    top_stores_royalty?: Array<{ name: string; royalty: number }>
    sales_chart?: Array<{ month: string; amount: number }>
    expenses_chart?: Array<{ month: string; amount: number }>
    profit_chart?: Array<{ month: string; amount: number }>
    royalty_chart?: Array<{ month: string; amount: number }>
    monthly_breakdown?: Array<{ 
      month: string
      sales: number
      expenses: number
      royalties: number
      profit: number
    }>
  }
}

const financeStats = ref<FinanceStat[]>([])
const topStoresSalesSeries = ref<ChartSeries[]>([{ name: 'Sales', data: [] }])
const topStoresRoyaltySeries = ref<ChartSeries[]>([{ name: 'Royalty', data: [] }])
const salesChartSeries = ref<ChartSeries[]>([{ name: 'Sales', data: [] }])
const expensesChartSeries = ref<ChartSeries[]>([{ name: 'Expenses', data: [] }])
const profitChartSeries = ref<ChartSeries[]>([{ name: 'Profit', data: [] }])
const royaltyChartSeries = ref<ChartSeries[]>([{ name: 'Royalty', data: [] }])
const monthlyBreakdownData = ref<Array<{ 
  month: string
  sales: number
  expenses: number
  royalties: number
  profit: number
}>>([])

// 👉 Watch for API data changes
watch(financeData, (newData) => {
  const apiData = newData as ApiResponse
  if (apiData?.success && apiData?.data) {
    const data = apiData.data

    // Update finance stats
    financeStats.value = [
      { 
        icon: 'tabler-currency-dollar', 
        color: 'primary', 
        title: 'Total Sales', 
        value: formatCurrency(data.stats.total_sales), 
        change: data.stats.sales_change, 
        isHover: false,
      },
      { 
        icon: 'tabler-receipt', 
        color: 'error', 
        title: 'Total Expenses', 
        value: formatCurrency(data.stats.total_expenses), 
        change: data.stats.expenses_change, 
        isHover: false,
      },
      { 
        icon: 'tabler-chart-line', 
        color: 'success', 
        title: 'Net Profit', 
        value: formatCurrency(data.stats.net_profit), 
        change: data.stats.profit_change, 
        isHover: false,
      },
      { 
        icon: 'tabler-percentage', 
        color: 'warning', 
        title: 'Profit Margin', 
        value: `${data.stats.profit_margin}%`, 
        change: data.stats.margin_change, 
        isHover: false,
      },
    ]

    // Update chart series for top stores sales
    if (data.top_stores_sales) {
      topStoresSalesSeries.value = [{
        name: 'Sales',
        data: data.top_stores_sales.map((store: any) => store.sales)
      }]
    }

    // Update chart series for top stores royalty
    if (data.top_stores_royalty) {
      topStoresRoyaltySeries.value = [{
        name: 'Royalty',
        data: data.top_stores_royalty.map((store: any) => store.royalty)
      }]
    }

    // Update sales chart series
    if (data.sales_chart) {
      salesChartSeries.value = [{
        name: 'Sales',
        data: data.sales_chart.map((item: any) => item.amount)
      }]
    }

    // Update expenses chart series
    if (data.expenses_chart) {
      expensesChartSeries.value = [{
        name: 'Expenses',
        data: data.expenses_chart.map((item: any) => item.amount)
      }]
    }

    // Update profit chart series
    if (data.profit_chart) {
      profitChartSeries.value = [{
        name: 'Profit',
        data: data.profit_chart.map((item: any) => item.amount)
      }]
    }

    // Update royalty chart series
    if (data.royalty_chart) {
      royaltyChartSeries.value = [{
        name: 'Royalty',
        data: data.royalty_chart.map((item: any) => item.amount)
      }]
    }

    // Update monthly breakdown data
    if (data.monthly_breakdown) {
      monthlyBreakdownData.value = data.monthly_breakdown
    }
  }
}, { immediate: true })

// Fallback data in case API fails
const fallbackFinanceStats: FinanceStat[] = [
  { 
    icon: 'tabler-currency-dollar', 
    color: 'primary', 
    title: 'Total Sales', 
    value: '$0', 
    change: 0, 
    isHover: false,
  },
  { 
    icon: 'tabler-receipt', 
    color: 'error', 
    title: 'Total Expenses', 
    value: '$0', 
    change: 0, 
    isHover: false,
  },
  { 
    icon: 'tabler-chart-line', 
    color: 'success', 
    title: 'Net Profit', 
    value: '$0', 
    change: 0, 
    isHover: false,
  },
  { 
    icon: 'tabler-percentage', 
    color: 'warning', 
    title: 'Profit Margin', 
    value: '0%', 
    change: 0, 
    isHover: false,
  },
]

// Use fallback data if API data is not available
const displayStats = computed(() => {
  return financeStats.value.length > 0 ? financeStats.value : fallbackFinanceStats
})

// 👉 Fetch data on component mount
onMounted(() => {
  fetchFinanceData()
})

// 👉 Top Stores by Sales Chart Configuration
const topStoresSalesConfig = computed(() => ({
  chart: {
    type: 'bar',
    toolbar: {
      show: false,
    },
    parentHeightOffset: 0,
  },
  plotOptions: {
    bar: {
      horizontal: true,
      borderRadius: 8,
      barHeight: '60%',
    },
  },
  dataLabels: {
    enabled: true,
    formatter(val: number) {
      return `${(val / 1000).toFixed(0)}k SAR`
    },
    style: {
      colors: ['#fff'],
      fontSize: '13px',
      fontWeight: 500,
    },
  },
  colors: [chartColors.primary],
  xaxis: {
    categories: ['Downtown Store', 'Mall Location', 'City Center', 'Westside Branch', 'Airport Plaza'],
    labels: {
      style: {
        colors: labelColor,
        fontSize: '13px',
      },
      formatter(val: number) {
        return `${(val / 1000).toFixed(0)}k SAR`
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
  grid: {
    strokeDashArray: 8,
    borderColor,
    xaxis: {
      lines: {
        show: true,
      },
    },
    yaxis: {
      lines: {
        show: false,
      },
    },
    padding: {
      top: -18,
      left: 21,
      right: 25,
      bottom: 10,
    },
  },
  tooltip: {
    y: {
      formatter(val: number) {
        return `${val.toLocaleString()} SAR`
      },
    },
  },
}))

// 👉 Top Stores by Royalty Chart Configuration
const topStoresRoyaltyConfig = computed(() => ({
  chart: {
    type: 'bar',
    toolbar: {
      show: false,
    },
    parentHeightOffset: 0,
  },
  plotOptions: {
    bar: {
      horizontal: true,
      borderRadius: 8,
      barHeight: '60%',
    },
  },
  dataLabels: {
    enabled: true,
    formatter(val: number) {
      return `${(val / 1000).toFixed(1)}k SAR`
    },
    style: {
      colors: ['#fff'],
      fontSize: '13px',
      fontWeight: 500,
    },
  },
  colors: [chartColors.warning],
  xaxis: {
    categories: ['Downtown Store', 'Mall Location', 'City Center', 'Westside Branch', 'Airport Plaza'],
    labels: {
      style: {
        colors: labelColor,
        fontSize: '13px',
      },
      formatter(val: number) {
        return `${(val / 1000).toFixed(0)}k SAR`
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
  grid: {
    strokeDashArray: 8,
    borderColor,
    xaxis: {
      lines: {
        show: true,
      },
    },
    yaxis: {
      lines: {
        show: false,
      },
    },
    padding: {
      top: -18,
      left: 21,
      right: 25,
      bottom: 10,
    },
  },
  tooltip: {
    y: {
      formatter(val: number) {
        return `${val.toLocaleString()} SAR`
      },
    },
  },
}))

// 👉 Summary Chart (Combined Sales, Expenses, Royalties, Profit)
const summarySeries = computed(() => [
  {
    name: 'Sales',
    data: salesChartSeries.value.length > 0 ? salesChartSeries.value[0].data : [],
  },
  {
    name: 'Expenses',
    data: expensesChartSeries.value.length > 0 ? expensesChartSeries.value[0].data : [],
  },
  {
    name: 'Royalties',
    data: royaltyChartSeries.value.length > 0 ? royaltyChartSeries.value[0].data : [],
  },
  {
    name: 'Profit',
    data: profitChartSeries.value.length > 0 ? profitChartSeries.value[0].data : [],
  },
])

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
    strokeColors: [chartColors.primary, chartColors.error, chartColors.warning, chartColors.success],
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
  colors: [chartColors.primary, chartColors.error, chartColors.warning, chartColors.success],
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
        return `${(val / 1000).toFixed(0)}k SAR`
      },
    },
  },
  tooltip: {
    y: {
      formatter(val: number) {
        return `${val.toLocaleString()} SAR`
      },
    },
  },
}

// 👉 Summary Table Data
const summaryTableData = computed(() => {
  // Use API data only - no fallback
  return monthlyBreakdownData.value.map(item => ({
    month: item.month,
    sales: formatCurrency(item.sales),
    expenses: formatCurrency(item.expenses),
    royalties: formatCurrency(item.royalties),
    profit: formatCurrency(item.profit),
  }))
})

const summaryHeaders = [
  { title: 'Month', key: 'month' },
  { title: 'Sales', key: 'sales', align: 'end' as const },
  { title: 'Expenses', key: 'expenses', align: 'end' as const },
  { title: 'Royalties', key: 'royalties', align: 'end' as const },
  { title: 'Profit', key: 'profit', align: 'end' as const },
]
</script>

<template>
  <section>
    <!-- 👉 Finance Statistics Cards -->
    <VRow class="mb-6">
      <VCol
        v-for="(data, index) in displayStats"
        :key="index"
        cols="12"
        md="3"
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

    <!-- 👉 Top Stores Charts Row -->
    <VRow class="mb-6">
      <!-- Top 5 Stores by Sales -->
      <VCol
        cols="12"
        md="6"
      >
        <VCard>
          <VCardItem
            title="Top 5 Stores by Monthly Sales"
            subtitle="Performance comparison"
          >
            <template #append>
              <VBtn
                variant="tonal"
                size="small"
                append-icon="tabler-chevron-down"
              >
                This Month
              </VBtn>
            </template>
          </VCardItem>

          <VCardText>
            <VueApexCharts
              type="bar"
              height="320"
              :options="topStoresSalesConfig"
              :series="topStoresSalesSeries"
            />
          </VCardText>
        </VCard>
      </VCol>

      <!-- Top 5 Stores by Royalty -->
      <VCol
        cols="12"
        md="6"
      >
        <VCard>
          <VCardItem
            title="Top 5 Stores by Monthly Royalty"
            subtitle="Royalty contributions"
          >
            <template #append>
              <VBtn
                variant="tonal"
                size="small"
                append-icon="tabler-chevron-down"
              >
                This Month
              </VBtn>
            </template>
          </VCardItem>

          <VCardText>
            <VueApexCharts
              type="bar"
              height="320"
              :options="topStoresRoyaltyConfig"
              :series="topStoresRoyaltySeries"
            />
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
            subtitle="Yearly overview of sales, expenses, royalties and profit"
          >
            <template #append>
              <VBtn
                variant="tonal"
                size="small"
                append-icon="tabler-chevron-down"
              >
                2024
              </VBtn>
            </template>
          </VCardItem>

          <VCardText>
            <VueApexCharts
              type="line"
              height="400"
              :options="summaryConfig"
              :series="summarySeries"
            />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- 👉 Summary Table -->
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardItem class="pb-4">
            <VCardTitle>Monthly Financial Breakdown</VCardTitle>
            <VCardSubtitle>Detailed monthly statistics</VCardSubtitle>
          </VCardItem>

          <VDivider />

          <VDataTable
            :headers="summaryHeaders"
            :items="summaryTableData"
            hide-default-footer
            class="text-no-wrap"
          >
            <template #item.month="{ item }">
              <div class="text-body-1 font-weight-medium">
                {{ item.month }}
              </div>
            </template>

            <template #item.sales="{ item }">
              <div class="text-body-1 text-success font-weight-medium">
                {{ item.sales }}
              </div>
            </template>

            <template #item.expenses="{ item }">
              <div class="text-body-1 text-error font-weight-medium">
                {{ item.expenses }}
              </div>
            </template>

            <template #item.royalties="{ item }">
              <div class="text-body-1 text-warning font-weight-medium">
                {{ item.royalties }}
              </div>
            </template>

            <template #item.profit="{ item }">
              <div class="text-body-1 text-primary font-weight-medium">
                {{ item.profit }}
              </div>
            </template>
          </VDataTable>
        </VCard>
      </VCol>
    </VRow>
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
