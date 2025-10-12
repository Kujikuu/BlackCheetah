<script setup lang="ts">
import { useTheme } from 'vuetify'
import { getAreaChartSplineConfig } from '@core/libs/apex-chart/apexCharConfig'

// Sales dashboard data
const vuetifyTheme = useTheme()

// Utility function to prefix positive numbers with +
const prefixWithPlus = (value: number) => {
  return value > 0 ? `+${value}` : value.toString()
}

const widgetData = ref([
  { title: 'Total Sales', value: '$125,450', change: 15, desc: 'This month sales', icon: 'tabler-currency-dollar', iconColor: 'primary' },
  { title: 'Total Profit', value: '$45,230', change: 22, desc: 'This month profit', icon: 'tabler-trending-up', iconColor: 'success' },
])

// Chart configurations
const monthlyPerformanceChartConfig = computed(() => getAreaChartSplineConfig(vuetifyTheme.current.value))

// Sales data with detailed information
const mostSellingItemsData = [
  { name: 'Smartphones', quantity: 245, price: '$899.99' },
  { name: 'Laptops', quantity: 189, price: '$1,299.99' },
  { name: 'Tablets', quantity: 156, price: '$599.99' },
  { name: 'Accessories', quantity: 98, price: '$49.99' },
]

const lowSellingItemsData = [
  { name: 'Cameras', quantity: 23, price: '$799.99' },
  { name: 'Printers', quantity: 18, price: '$299.99' },
  { name: 'Monitors', quantity: 15, price: '$449.99' },
  { name: 'Others', quantity: 12, price: '$199.99' },
]

// Monthly performance data for area chart
const monthlyPerformanceData = [
  {
    name: 'Top Performing Items',
    data: [120, 140, 110, 180, 95, 160, 85, 200, 145, 125, 190, 165],
  },
  {
    name: 'Low Performing Items',
    data: [45, 65, 55, 35, 48, 75, 90, 42, 58, 70, 52, 68],
  },
  {
    name: 'Average Performance',
    data: [82, 102, 82, 107, 71, 117, 87, 121, 101, 97, 121, 116],
  },
]
</script>

<template>
  <section>
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
                  <span class="text-body-2 font-weight-medium text-high-emphasis">{{ item.price }}</span>
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
                  <span class="text-body-2 font-weight-medium text-high-emphasis">{{ item.price }}</span>
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
          type="area"
          height="350"
          :options="{
            ...monthlyPerformanceChartConfig,
            xaxis: {
              categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            },
          }"
          :series="monthlyPerformanceData"
        />
      </VCardText>
    </VCard>
  </section>
</template>
