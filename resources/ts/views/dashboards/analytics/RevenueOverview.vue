<script setup lang="ts">
import { useTheme } from 'vuetify'
import { hexToRgb } from '@layouts/utils'
import { formatCurrency } from '@/@core/utils/formatters'
import { SaudiRiyal } from 'lucide-vue-next'

interface Props {
    currentMonthRevenue?: number
    revenueChange?: number
    pendingRoyalties?: number
}

const props = withDefaults(defineProps<Props>(), {
    currentMonthRevenue: 0,
    revenueChange: 0,
    pendingRoyalties: 0,
})

const vuetifyTheme = useTheme()

// Generate weekly revenue data
const series = computed(() => [
    {
        data: [
            Math.round(props.currentMonthRevenue * 0.10),
            Math.round(props.currentMonthRevenue * 0.15),
            Math.round(props.currentMonthRevenue * 0.12),
            Math.round(props.currentMonthRevenue * 0.13),
            Math.round(props.currentMonthRevenue * 0.20),
            Math.round(props.currentMonthRevenue * 0.15),
            Math.round(props.currentMonthRevenue * 0.15),
        ],
    },
])

const chartOptions = computed(() => {
    const currentTheme = vuetifyTheme.current.value.colors
    const variableTheme = vuetifyTheme.current.value.variables

    return {
        chart: {
            parentHeightOffset: 0,
            type: 'bar',
            toolbar: {
                show: false,
            },
        },
        plotOptions: {
            bar: {
                barHeight: '60%',
                columnWidth: '38%',
                startingShape: 'rounded',
                endingShape: 'rounded',
                borderRadius: 4,
                distributed: true,
            },
        },
        grid: {
            show: false,
            padding: {
                top: -30,
                bottom: 0,
                left: -10,
                right: -10,
            },
        },
        colors: [
            `rgba(${hexToRgb(currentTheme.primary)},${variableTheme['dragged-opacity']})`,
            `rgba(${hexToRgb(currentTheme.primary)},${variableTheme['dragged-opacity']})`,
            `rgba(${hexToRgb(currentTheme.primary)},${variableTheme['dragged-opacity']})`,
            `rgba(${hexToRgb(currentTheme.primary)},${variableTheme['dragged-opacity']})`,
            `rgba(${hexToRgb(currentTheme.primary)}, 1)`,
            `rgba(${hexToRgb(currentTheme.primary)},${variableTheme['dragged-opacity']})`,
            `rgba(${hexToRgb(currentTheme.primary)},${variableTheme['dragged-opacity']})`,
        ],
        dataLabels: {
            enabled: false,
        },
        legend: {
            show: false,
        },
        xaxis: {
            categories: ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'],
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false,
            },
            labels: {
                style: {
                    colors: `rgba(${hexToRgb(currentTheme['on-surface'])},${variableTheme['disabled-opacity']})`,
                    fontSize: '13px',
                    fontFamily: 'Public Sans',
                },
            },
        },
        yaxis: {
            labels: {
                show: false,
            },
        },
        tooltip: {
            enabled: false,
        },
        responsive: [
            {
                breakpoint: 1025,
                options: {
                    chart: {
                        height: 199,
                    },
                },
            },
        ],
    }
})

const revenueReports = computed(() => [
    {
        color: 'primary',
        icon: SaudiRiyal,
        title: 'Revenue',
        amount: formatCurrency(props.currentMonthRevenue, 'SAR', false),
        progress: '100',
    },
    {
        color: 'warning',
        icon: 'tabler-receipt',
        title: 'Pending',
        amount: formatCurrency(props.pendingRoyalties, 'SAR', false),
        progress: props.currentMonthRevenue > 0 ? Math.round((props.pendingRoyalties / props.currentMonthRevenue) * 100).toString() : '0',
    },
    {
        color: props.revenueChange >= 0 ? 'success' : 'error',
        icon: props.revenueChange >= 0 ? 'tabler-trending-up' : 'tabler-trending-down',
        title: 'Growth',
        amount: `${props.revenueChange >= 0 ? '+' : ''}${props.revenueChange}%`,
        progress: Math.abs(props.revenueChange).toString(),
    },
])

const moreList = [
    { title: 'View More', value: 'View More' },
    { title: 'Export', value: 'Export' },
]
</script>

<template>
    <VCard>
        <VCardItem class="pb-sm-0">
            <VCardTitle>Revenue Reports</VCardTitle>
            <VCardSubtitle>Monthly Revenue Overview</VCardSubtitle>

            <!-- <template #append>
                <div class="mt-n4 me-n2">
                    <MoreBtn size="small" :menu-list="moreList" />
                </div>
            </template> -->
        </VCardItem>

        <VCardText>
            <VRow>
                <VCol cols="12" sm="5" lg="6" class="d-flex flex-column align-self-center">
                    <div class="d-flex align-center gap-2 mb-3 flex-wrap">
                        <h2 class="text-h2">
                            {{ formatCurrency(currentMonthRevenue, 'SAR', false) }}
                        </h2>
                        <VChip v-if="revenueChange !== 0" label size="small"
                            :color="revenueChange > 0 ? 'success' : 'error'">
                            {{ revenueChange > 0 ? '+' : '' }}{{ revenueChange }}%
                        </VChip>
                    </div>

                    <span class="text-sm text-medium-emphasis">
                        Current month revenue compared to last month
                    </span>
                </VCol>

                <VCol cols="12" sm="7" lg="6">
                    <VueApexCharts :options="chartOptions" :series="series" height="161" />
                </VCol>
            </VRow>

            <div class="border rounded mt-5 pa-5">
                <VRow>
                    <VCol v-for="report in revenueReports" :key="report.title" cols="12" sm="4">
                        <div class="d-flex align-center">
                            <VAvatar rounded size="26" :color="report.color" variant="tonal" class="me-2">
                                <VIcon size="18" :icon="report.icon" />
                            </VAvatar>

                            <h6 class="text-base font-weight-regular">
                                {{ report.title }}
                            </h6>
                        </div>
                        <h6 class="text-h4 my-2">
                            {{ report.amount }}
                        </h6>
                        <VProgressLinear :model-value="report.progress" :color="report.color" height="4" rounded
                            rounded-bar />
                    </VCol>
                </VRow>
            </div>
        </VCardText>
    </VCard>
</template>
