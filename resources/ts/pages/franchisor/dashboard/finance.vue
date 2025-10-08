<script setup lang="ts">
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

// 👉 Finance Stats
const financeStats = ref([
  { 
    icon: 'tabler-currency-dollar', 
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
    icon: 'tabler-crown', 
    color: 'warning', 
    title: 'Total Royalties', 
    value: '$368,295', 
    change: 12.3, 
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

// 👉 Top Stores by Sales Chart
const topStoresSalesSeries = [
  {
    name: 'Monthly Sales',
    data: [485000, 425000, 398000, 365000, 342000],
  },
]

const topStoresSalesConfig = {
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
      return `$${(val / 1000).toFixed(0)}k`
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
        return `$${(val / 1000).toFixed(0)}k`
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
        return `$${val.toLocaleString()}`
      },
    },
  },
}

// 👉 Top Stores by Royalty Chart
const topStoresRoyaltySeries = [
  {
    name: 'Monthly Royalty',
    data: [72750, 63750, 59700, 54750, 51300],
  },
]

const topStoresRoyaltyConfig = {
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
      return `$${(val / 1000).toFixed(1)}k`
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
        return `$${(val / 1000).toFixed(0)}k`
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
        return `$${val.toLocaleString()}`
      },
    },
  },
}

// 👉 Summary Chart (Combined Sales, Expenses, Royalties, Profit)
const summarySeries = [
  {
    name: 'Sales',
    data: [420000, 385000, 445000, 398000, 465000, 425000, 485000, 452000, 478000, 495000, 512000, 548000],
  },
  {
    name: 'Expenses',
    data: [145000, 132000, 158000, 142000, 165000, 148000, 172000, 155000, 168000, 175000, 182000, 188000],
  },
  {
    name: 'Royalties',
    data: [63000, 57750, 66750, 59700, 69750, 63750, 72750, 67800, 71700, 74250, 76800, 82200],
  },
  {
    name: 'Profit',
    data: [212000, 195250, 220250, 196300, 230250, 213250, 240250, 229200, 238300, 245750, 253200, 277800],
  },
]

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
        return `$${(val / 1000).toFixed(0)}k`
      },
    },
  },
  tooltip: {
    y: {
      formatter(val: number) {
        return `$${val.toLocaleString()}`
      },
    },
  },
}

// 👉 Summary Table Data
const summaryTableData = ref([
  {
    month: 'January',
    sales: '$420,000',
    expenses: '$145,000',
    royalties: '$63,000',
    profit: '$212,000',
  },
  {
    month: 'February',
    sales: '$385,000',
    expenses: '$132,000',
    royalties: '$57,750',
    profit: '$195,250',
  },
  {
    month: 'March',
    sales: '$445,000',
    expenses: '$158,000',
    royalties: '$66,750',
    profit: '$220,250',
  },
  {
    month: 'April',
    sales: '$398,000',
    expenses: '$142,000',
    royalties: '$59,700',
    profit: '$196,300',
  },
  {
    month: 'May',
    sales: '$465,000',
    expenses: '$165,000',
    royalties: '$69,750',
    profit: '$230,250',
  },
  {
    month: 'June',
    sales: '$425,000',
    expenses: '$148,000',
    royalties: '$63,750',
    profit: '$213,250',
  },
])

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
    <!-- 👉 Finance Stats Cards -->
    <VRow class="mb-6">
      <VCol
        v-for="(data, index) in financeStats"
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
