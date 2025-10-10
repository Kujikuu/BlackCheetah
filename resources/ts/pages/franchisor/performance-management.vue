<script setup lang="ts">
import { computed, ref } from 'vue'
import { useTheme } from 'vuetify'

const vuetifyTheme = useTheme()

// Type definitions
interface UnitData {
    sales: number[]
    expenses: number[]
    royalties: number[]
    profit: number[]
}

interface PeriodData {
    [key: string]: UnitData
}

interface PerformanceData {
    [key: string]: PeriodData
}

// Reactive data
const selectedPeriod = ref('monthly')
const selectedUnit = ref('all')

// Mock data for franchisee units
const franchiseeUnits = [
    { id: 'all', name: 'All Units', location: 'Overview' },
    { id: 'unit1', name: 'Downtown Branch', location: 'New York, NY' },
    { id: 'unit2', name: 'Mall Location', location: 'Los Angeles, CA' },
    { id: 'unit3', name: 'Airport Store', location: 'Chicago, IL' },
    { id: 'unit4', name: 'Suburban Center', location: 'Houston, TX' },
    { id: 'unit5', name: 'City Plaza', location: 'Phoenix, AZ' },
]

// Mock performance data
const performanceData: PerformanceData = {
    monthly: {
        all: {
            sales: [45000, 52000, 48000, 61000, 55000, 67000, 43000, 58000, 53000, 67000, 61000, 79000],
            expenses: [25000, 28000, 26000, 32000, 29000, 35000, 23000, 31000, 28000, 35000, 32000, 42000],
            royalties: [4500, 5200, 4800, 6100, 5500, 6700, 4300, 5800, 5300, 6700, 6100, 7900],
            profit: [15500, 18800, 17200, 22900, 20500, 25300, 15700, 21200, 19700, 25300, 22900, 29100],
        },
        unit1: {
            sales: [12000, 14000, 13000, 16000, 15000, 18000, 11000, 15500, 14200, 18000, 16500, 21000],
            expenses: [7000, 8000, 7500, 9000, 8500, 10000, 6500, 8800, 8000, 10000, 9200, 12000],
            royalties: [1200, 1400, 1300, 1600, 1500, 1800, 1100, 1550, 1420, 1800, 1650, 2100],
            profit: [3800, 4600, 4200, 5400, 5000, 6200, 3400, 5150, 4780, 6200, 5650, 6900],
        },
        unit2: {
            sales: [10500, 12200, 11800, 14500, 13200, 16000, 9800, 13800, 12600, 16200, 14800, 18500],
            expenses: [6200, 7100, 6800, 8300, 7600, 9200, 5600, 7900, 7200, 9300, 8500, 10600],
            royalties: [1050, 1220, 1180, 1450, 1320, 1600, 980, 1380, 1260, 1620, 1480, 1850],
            profit: [3250, 3880, 3820, 4750, 4280, 5200, 3220, 4520, 4140, 5280, 4820, 6050],
        },
        unit3: {
            sales: [11200, 13100, 12400, 15200, 14000, 16800, 10500, 14600, 13400, 17000, 15600, 19200],
            expenses: [6500, 7600, 7200, 8800, 8100, 9700, 6100, 8400, 7700, 9800, 9000, 11100],
            royalties: [1120, 1310, 1240, 1520, 1400, 1680, 1050, 1460, 1340, 1700, 1560, 1920],
            profit: [3580, 4190, 3960, 4880, 4500, 5420, 3350, 4740, 4360, 5500, 5040, 6180],
        },
        unit4: {
            sales: [8800, 10200, 9600, 11800, 10800, 13000, 8200, 11400, 10400, 13200, 12100, 14800],
            expenses: [5200, 6000, 5700, 6900, 6300, 7600, 4800, 6700, 6100, 7700, 7100, 8700],
            royalties: [880, 1020, 960, 1180, 1080, 1300, 820, 1140, 1040, 1320, 1210, 1480],
            profit: [2720, 3180, 2940, 3720, 3420, 4100, 2580, 3560, 3260, 4180, 3790, 4620],
        },
        unit5: {
            sales: [9500, 11000, 10300, 12600, 11500, 13800, 8900, 12200, 11200, 14200, 13000, 15900],
            expenses: [5600, 6500, 6100, 7400, 6800, 8100, 5200, 7200, 6600, 8300, 7600, 9300],
            royalties: [950, 1100, 1030, 1260, 1150, 1380, 890, 1220, 1120, 1420, 1300, 1590],
            profit: [2950, 3400, 3170, 3940, 3550, 4320, 2810, 3780, 3480, 4480, 4100, 5010],
        },
    },
    yearly: {
        all: {
            sales: [580000, 620000, 680000, 750000, 820000],
            expenses: [320000, 340000, 370000, 400000, 430000],
            royalties: [58000, 62000, 68000, 75000, 82000],
            profit: [202000, 218000, 242000, 275000, 308000],
        },
        unit1: {
            sales: [145000, 155000, 170000, 187500, 205000],
            expenses: [80000, 85000, 92500, 100000, 107500],
            royalties: [14500, 15500, 17000, 18750, 20500],
            profit: [50500, 54500, 60500, 68750, 77000],
        },
        unit2: {
            sales: [126000, 134000, 147000, 162000, 177000],
            expenses: [70000, 74000, 81000, 89000, 97000],
            royalties: [12600, 13400, 14700, 16200, 17700],
            profit: [43400, 46600, 51300, 56800, 62300],
        },
        unit3: {
            sales: [134400, 143200, 157200, 173200, 189600],
            expenses: [74200, 79000, 86700, 95500, 104400],
            royalties: [13440, 14320, 15720, 17320, 18960],
            profit: [46760, 49880, 54780, 60380, 66240],
        },
        unit4: {
            sales: [105600, 112400, 123200, 135800, 148400],
            expenses: [58400, 62200, 68200, 75100, 82100],
            royalties: [10560, 11240, 12320, 13580, 14840],
            profit: [36640, 38960, 42680, 47120, 51460],
        },
        unit5: {
            sales: [113400, 120800, 132600, 146000, 159600],
            expenses: [62600, 66700, 73200, 80600, 88100],
            royalties: [11340, 12080, 13260, 14600, 15960],
            profit: [39460, 42020, 46140, 50800, 55540],
        },
    },
    daily: {
        all: {
            sales: [1800, 2100, 1900, 2300, 2000, 2500, 1700, 2200, 2100, 2400, 2200, 2800, 1900, 2300],
            expenses: [1000, 1200, 1100, 1300, 1150, 1400, 950, 1250, 1200, 1350, 1250, 1550, 1100, 1300],
            royalties: [180, 210, 190, 230, 200, 250, 170, 220, 210, 240, 220, 280, 190, 230],
            profit: [620, 690, 610, 770, 650, 850, 580, 730, 690, 810, 730, 970, 610, 770],
        },
        unit1: {
            sales: [450, 525, 475, 575, 500, 625, 425, 550, 525, 600, 550, 700, 475, 575],
            expenses: [250, 300, 275, 325, 288, 350, 238, 313, 300, 338, 313, 388, 275, 325],
            royalties: [45, 53, 48, 58, 50, 63, 43, 55, 53, 60, 55, 70, 48, 58],
            profit: [155, 173, 153, 193, 163, 213, 145, 183, 173, 203, 183, 243, 153, 193],
        },
        unit2: {
            sales: [390, 456, 413, 500, 435, 543, 370, 478, 456, 522, 478, 609, 413, 500],
            expenses: [217, 254, 230, 278, 242, 302, 206, 266, 254, 290, 266, 339, 230, 278],
            royalties: [39, 46, 41, 50, 44, 54, 37, 48, 46, 52, 48, 61, 41, 50],
            profit: [134, 156, 142, 172, 149, 187, 127, 164, 156, 180, 164, 209, 142, 172],
        },
        unit3: {
            sales: [414, 483, 437, 529, 460, 575, 392, 507, 483, 553, 507, 645, 437, 529],
            expenses: [230, 269, 243, 294, 256, 320, 218, 282, 269, 308, 282, 359, 243, 294],
            royalties: [41, 48, 44, 53, 46, 58, 39, 51, 48, 55, 51, 65, 44, 53],
            profit: [143, 166, 150, 182, 158, 197, 135, 174, 166, 190, 174, 221, 150, 182],
        },
        unit4: {
            sales: [324, 378, 342, 414, 360, 450, 306, 396, 378, 432, 396, 504, 342, 414],
            expenses: [180, 210, 190, 230, 200, 250, 170, 220, 210, 240, 220, 280, 190, 230],
            royalties: [32, 38, 34, 41, 36, 45, 31, 40, 38, 43, 40, 50, 34, 41],
            profit: [112, 130, 118, 143, 124, 155, 105, 136, 130, 149, 136, 174, 118, 143],
        },
        unit5: {
            sales: [351, 409, 370, 448, 390, 487, 331, 428, 409, 468, 428, 545, 370, 448],
            expenses: [195, 227, 206, 249, 217, 271, 184, 238, 227, 260, 238, 303, 206, 249],
            royalties: [35, 41, 37, 45, 39, 49, 33, 43, 41, 47, 43, 55, 37, 45],
            profit: [121, 141, 127, 154, 134, 167, 114, 147, 141, 161, 147, 187, 127, 154],
        },
    },
}

// Chart options
const chartOptions = computed(() => {
    const currentTheme = vuetifyTheme.current.value.colors

    return {
        chart: {
            type: 'line',
            height: 400,
            toolbar: { show: false },
            zoom: { enabled: false },
            parentHeightOffset: 0,
        },
        stroke: {
            curve: 'smooth',
            width: 3,
        },
        xaxis: {
            categories: selectedPeriod.value === 'monthly'
                ? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
                : selectedPeriod.value === 'yearly'
                    ? ['2019', '2020', '2021', '2022', '2023']
                    : ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7', 'Day 8', 'Day 9', 'Day 10', 'Day 11', 'Day 12', 'Day 13', 'Day 14'],
            labels: {
                style: {
                    colors: `rgba(${currentTheme['on-surface']}, 0.6)`,
                    fontSize: '13px',
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
                    colors: `rgba(${currentTheme['on-surface']}, 0.6)`,
                    fontSize: '13px',
                },
                formatter: (value: number) => `${(value / 1000).toFixed(0)}k SAR`,
            },
        },
        legend: {
            show: true,
            position: 'top',
            horizontalAlign: 'left',
            labels: {
                colors: `rgba(${currentTheme['on-surface']}, 0.8)`,
            },
        },
        colors: [
            'rgba(var(--v-theme-success), 1)',
            'rgba(var(--v-theme-error), 1)',
            'rgba(var(--v-theme-warning), 1)',
            'rgba(var(--v-theme-primary), 1)',
        ],
        grid: {
            borderColor: `rgba(${currentTheme['on-surface']}, 0.12)`,
            strokeDashArray: 5,
            padding: {
                top: -20,
                bottom: -10,
                left: 0,
                right: 0,
            },
        },
        tooltip: {
            theme: vuetifyTheme.current.value.dark ? 'dark' : 'light',
            y: {
                formatter: (value: number) => `${value.toLocaleString()} SAR`,
            },
        },
    }
})

// Computed chart data
const chartData = computed(() => {
    const periodData = performanceData[selectedPeriod.value] as PeriodData
    const data = periodData?.[selectedUnit.value] || periodData?.all

    return [
        { name: 'Sales', data: data?.sales || [] },
        { name: 'Expenses', data: data?.expenses || [] },
        { name: 'Royalties', data: data?.royalties || [] },
        { name: 'Profit', data: data?.profit || [] },
    ]
})

// Mock stat data
const topPerformingLocations = [
    { name: 'Downtown Branch', location: 'New York, NY', revenue: '285,000 SAR', growth: '+15.2%' },
    { name: 'Airport Store', location: 'Chicago, IL', revenue: '267,000 SAR', growth: '+12.8%' },
    { name: 'Mall Location', location: 'Los Angeles, CA', revenue: '245,000 SAR', growth: '+9.5%' },
]

const customerSatisfactionScore = {
    score: 4.8,
    maxScore: 5.0,
    totalReviews: 2847,
    trend: '+0.3',
}

const topRatedFranchise = {
    name: 'Downtown Branch',
    location: 'New York, NY',
    rating: 4.9,
    reviews: 1247,
    manager: 'Sarah Johnson',
}

const lowestRatedFranchise = {
    name: 'Suburban Center',
    location: 'Houston, TX',
    rating: 3.2,
    reviews: 89,
    manager: 'Mike Wilson',
}

// Export functionality
const isExportDialogVisible = ref(false)
const exportFormat = ref('csv')
const exportDataType = ref('performance')

const exportFormatOptions = [
    { title: 'CSV Format', value: 'csv' },
    { title: 'Excel Format', value: 'xlsx' },
]

const exportDataTypeOptions = [
    { title: 'Performance Data', value: 'performance' },
    { title: 'Statistics Summary', value: 'stats' },
    { title: 'All Data', value: 'all' },
]

// Helper function to convert data to CSV
const convertToCSV = (data: any[], headers: string[]) => {
    const csvContent = [
        headers.join(','),
        ...data.map(row => headers.map(header => {
            const value = row[header] || ''
            return typeof value === 'string' && value.includes(',') ? `"${value}"` : value
        }).join(','))
    ].join('\n')
    return csvContent
}

// Helper function to download file
const downloadFile = (content: string, filename: string, mimeType: string) => {
    const blob = new Blob([content], { type: mimeType })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
}

// Main export function
const exportData = () => {
    isExportDialogVisible.value = true
}

const performExport = () => {
    const timestamp = new Date().toISOString().split('T')[0]
    const unitName = franchiseeUnits.find(u => u.id === selectedUnit.value)?.name || 'All Units'

    if (exportDataType.value === 'performance' || exportDataType.value === 'all') {
        exportPerformanceData(timestamp, unitName)
    }

    if (exportDataType.value === 'stats' || exportDataType.value === 'all') {
        exportStatsData(timestamp)
    }

    isExportDialogVisible.value = false
}

const exportPerformanceData = (timestamp: string, unitName: string) => {
    const periodData = performanceData[selectedPeriod.value] as PeriodData
    const data = periodData?.[selectedUnit.value] || periodData?.all

    if (!data) return

    // Prepare performance data for export
    const categories = selectedPeriod.value === 'monthly'
        ? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
        : selectedPeriod.value === 'yearly'
            ? ['2019', '2020', '2021', '2022', '2023']
            : ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7', 'Day 8', 'Day 9', 'Day 10', 'Day 11', 'Day 12', 'Day 13', 'Day 14']

    const exportRows = categories.map((category, index) => ({
        Period: category,
        'Sales (SAR)': data.sales[index]?.toLocaleString() || '0',
        'Expenses (SAR)': data.expenses[index]?.toLocaleString() || '0',
        'Royalties (SAR)': data.royalties[index]?.toLocaleString() || '0',
        'Profit (SAR)': data.profit[index]?.toLocaleString() || '0',
        'Profit Margin (%)': data.sales[index] ? ((data.profit[index] / data.sales[index]) * 100).toFixed(2) : '0',
    }))

    const headers = ['Period', 'Sales (SAR)', 'Expenses (SAR)', 'Royalties (SAR)', 'Profit (SAR)', 'Profit Margin (%)']
    const filename = `performance-data-${unitName.replace(/\s+/g, '-').toLowerCase()}-${selectedPeriod.value}-${timestamp}.${exportFormat.value}`

    if (exportFormat.value === 'csv') {
        const csvContent = convertToCSV(exportRows, headers)
        downloadFile(csvContent, filename, 'text/csv')
    } else {
        // For Excel format, we'll create a simple CSV with .xlsx extension
        // In a real application, you'd use a library like xlsx or exceljs
        const csvContent = convertToCSV(exportRows, headers)
        downloadFile(csvContent, filename, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
    }
}

const exportStatsData = (timestamp: string) => {
    const statsRows = [
        // Top Performing Locations
        ...topPerformingLocations.map((location, index) => ({
            Category: 'Top Performing Locations',
            Rank: index + 1,
            Name: location.name,
            Location: location.location,
            'Revenue (SAR)': location.revenue,
            'Growth (%)': location.growth,
            Rating: '',
            Reviews: '',
            Manager: '',
        })),
        // Customer Satisfaction
        {
            Category: 'Customer Satisfaction',
            Rank: '',
            Name: 'Overall Score',
            Location: '',
            'Revenue (SAR)': '',
            'Growth (%)': '',
            Rating: `${customerSatisfactionScore.score}/5.0`,
            Reviews: customerSatisfactionScore.totalReviews.toString(),
            Manager: '',
        },
        // Top Rated Franchise
        {
            Category: 'Top Rated Franchise',
            Rank: 1,
            Name: topRatedFranchise.name,
            Location: topRatedFranchise.location,
            'Revenue (SAR)': '',
            'Growth (%)': '',
            Rating: `${topRatedFranchise.rating}/5.0`,
            Reviews: topRatedFranchise.reviews.toString(),
            Manager: topRatedFranchise.manager,
        },
        // Lowest Rated Franchise
        {
            Category: 'Needs Attention',
            Rank: '',
            Name: lowestRatedFranchise.name,
            Location: lowestRatedFranchise.location,
            'Revenue (SAR)': '',
            'Growth (%)': '',
            Rating: `${lowestRatedFranchise.rating}/5.0`,
            Reviews: lowestRatedFranchise.reviews.toString(),
            Manager: lowestRatedFranchise.manager,
        },
    ]

    const headers = ['Category', 'Rank', 'Name', 'Location', 'Revenue (SAR)', 'Growth (%)', 'Rating', 'Reviews', 'Manager']
    const filename = `stats-summary-${timestamp}.${exportFormat.value}`

    if (exportFormat.value === 'csv') {
        const csvContent = convertToCSV(statsRows, headers)
        downloadFile(csvContent, filename, 'text/csv')
    } else {
        const csvContent = convertToCSV(statsRows, headers)
        downloadFile(csvContent, filename, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
    }
}

const periodOptions = [
    { title: 'Daily', value: 'daily' },
    { title: 'Monthly', value: 'monthly' },
    { title: 'Yearly', value: 'yearly' },
]
</script>

<template>
    <section>
        <!-- Page Header -->
        <VRow class="mb-6">
            <VCol cols="12">
                <div class="d-flex justify-space-between align-center flex-wrap gap-4">
                    <div>
                        <h4 class="text-h4 mb-1">Performance Management</h4>
                        <p class="text-body-1 text-medium-emphasis">
                            Monitor and analyze franchise performance across all locations
                        </p>
                    </div>

                    <!-- Header Actions -->
                    <div class="d-flex gap-3 align-center flex-wrap">
                        <!-- Period Selector -->
                        <VSelect v-model="selectedPeriod" :items="periodOptions" item-title="title" item-value="value"
                            density="compact" style="min-width: 120px;" variant="outlined" />

                        <!-- Export Button -->
                        <VBtn color="primary" prepend-icon="tabler-download" @click="exportData">
                            Export
                        </VBtn>
                    </div>
                </div>
            </VCol>
        </VRow>

        <!-- Performance Chart -->
        <VRow>
            <VCol cols="12">
                <VCard>
                    <VCardItem class="pb-4">
                        <VCardTitle class="text-h6">Franchise Performance Overview</VCardTitle>
                        <template #append>
                            <!-- Unit Selector Tabs -->
                            <VTabs v-model="selectedUnit" density="compact" color="primary">
                                <VTab v-for="unit in franchiseeUnits" :key="unit.id" :value="unit.id" size="small">
                                    {{ unit.name }}
                                </VTab>
                            </VTabs>
                        </template>
                    </VCardItem>

                    <VDivider />

                    <VCardText>
                        <!-- Chart Container -->
                        <div style="height: 400px; position: relative;">
                            <VueApexCharts type="line" height="400" :options="chartOptions" :series="chartData" />
                        </div>
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>

        <!-- Stat Cards -->
        <VRow class="mt-6">
            <!-- Top 3 Performing Locations -->
            <VCol cols="12" md="3">
                <VCard class="h-100">
                    <VCardText>
                        <div class="d-flex align-center justify-space-between mb-4">
                            <div>
                                <h6 class="text-h6 mb-1">
                                    Top 3 Performing Location
                                </h6>
                                <div class="text-body-2 text-medium-emphasis">
                                    This month
                                </div>
                            </div>
                            <VAvatar color="primary" variant="tonal" size="40">
                                <VIcon icon="tabler-trophy" />
                            </VAvatar>
                        </div>

                        <div class="mt-4">
                            <div v-for="(location, index) in topPerformingLocations" :key="index"
                                class="d-flex align-center justify-space-between py-3"
                                :class="{ 'border-b': index < topPerformingLocations.length - 1 }">
                                <div class="d-flex align-center">
                                    <VAvatar :color="index === 0 ? 'warning' : index === 1 ? 'info' : 'success'"
                                        size="24" class="me-3">
                                        <span class="text-caption font-weight-bold">{{ index + 1 }}</span>
                                    </VAvatar>
                                    <div>
                                        <div class="text-body-2 font-weight-medium">
                                            {{ location.name }}
                                        </div>
                                        <div class="text-caption text-medium-emphasis">
                                            {{ location.location }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="text-body-2 font-weight-medium">
                                        {{ location.revenue }}
                                    </div>
                                    <div class="text-caption text-success">
                                        {{ location.growth }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </VCardText>
                </VCard>
            </VCol>

            <!-- Customer Satisfaction Score -->
            <VCol cols="12" md="3">
                <VCard class="h-100">
                    <VCardText>
                        <div class="d-flex align-center justify-space-between mb-4">
                            <div>
                                <h6 class="text-h6 mb-1">
                                    Customer Satisfaction Score
                                </h6>
                                <div class="text-body-2 text-medium-emphasis">
                                    Average rating
                                </div>
                            </div>
                            <VAvatar color="success" variant="tonal" size="40">
                                <VIcon icon="tabler-star" />
                            </VAvatar>
                        </div>

                        <div class="mt-4 text-center">
                            <div class="d-flex align-center justify-center mb-3">
                                <h3 class="text-h3 me-2 text-success">
                                    {{ customerSatisfactionScore.score }}
                                </h3>
                                <div class="text-body-2 text-medium-emphasis">
                                    / 5.0
                                </div>
                            </div>
                            <VRating :model-value="customerSatisfactionScore.score" readonly size="small"
                                color="warning" class="mb-3" />
                            <div class="text-body-2 text-medium-emphasis mb-2">
                                Based on {{ customerSatisfactionScore.totalReviews.toLocaleString() }} reviews
                            </div>
                            <VChip color="success" size="small" variant="tonal">
                                <VIcon start icon="tabler-trending-up" size="16" />
                                {{ customerSatisfactionScore.trend }} this month
                            </VChip>
                        </div>
                    </VCardText>
                </VCard>
            </VCol>

            <!-- Top-Rated Franchise -->
            <VCol cols="12" md="3">
                <VCard class="h-100">
                    <VCardText>
                        <div class="d-flex align-center justify-space-between mb-4">
                            <div>
                                <h6 class="text-h6 mb-1">
                                    Top-Rated Franchise
                                </h6>
                                <div class="text-body-2 text-medium-emphasis">
                                    Highest customer rating
                                </div>
                            </div>
                            <VAvatar color="warning" variant="tonal" size="40">
                                <VIcon icon="tabler-award" />
                            </VAvatar>
                        </div>

                        <div class="mt-4 text-center">
                            <VAvatar color="warning" size="60" class="mb-3">
                                <VIcon icon="tabler-building-store" size="30" />
                            </VAvatar>
                            <div class="text-h6 font-weight-medium mb-1">
                                {{ topRatedFranchise.name }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis mb-3">
                                {{ topRatedFranchise.location }}
                            </div>
                            <div class="d-flex align-center justify-center mb-2">
                                <h4 class="text-h4 me-2 text-warning">
                                    {{ topRatedFranchise.rating }}
                                </h4>
                                <VRating :model-value="topRatedFranchise.rating" readonly size="small"
                                    color="warning" />
                            </div>
                            <div class="text-caption text-medium-emphasis">
                                {{ topRatedFranchise.reviews }} reviews
                            </div>
                        </div>
                    </VCardText>
                </VCard>
            </VCol>

            <!-- Lowest-Rated Franchise (Needs Attention) -->
            <VCol cols="12" md="3">
                <VCard class="h-100">
                    <VCardText>
                        <div class="d-flex align-center justify-space-between mb-4">
                            <div>
                                <h6 class="text-h6 mb-1">
                                    Lowest-Rated Franchise
                                </h6>
                                <div class="text-body-2 text-medium-emphasis">
                                    Needs attention
                                </div>
                            </div>
                            <VAvatar color="error" variant="tonal" size="40">
                                <VIcon icon="tabler-alert-triangle" />
                            </VAvatar>
                        </div>

                        <div class="mt-4 text-center">
                            <VAvatar color="error" size="60" class="mb-3">
                                <VIcon icon="tabler-building-store" size="30" />
                            </VAvatar>
                            <div class="text-h6 font-weight-medium mb-1">
                                {{ lowestRatedFranchise.name }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis mb-3">
                                {{ lowestRatedFranchise.location }}
                            </div>
                            <div class="d-flex align-center justify-center mb-2">
                                <h4 class="text-h4 me-2 text-error">
                                    {{ lowestRatedFranchise.rating }}
                                </h4>
                                <VRating :model-value="lowestRatedFranchise.rating" readonly size="small"
                                    color="warning" />
                            </div>
                            <div class="text-caption text-medium-emphasis mb-3">
                                {{ lowestRatedFranchise.reviews }} reviews
                            </div>
                            <VBtn color="error" variant="tonal" size="small" block>
                                <VIcon start icon="tabler-message" size="16" />
                                Contact Unit
                            </VBtn>
                        </div>
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>

        <!-- Export Options Dialog -->
        <VDialog v-model="isExportDialogVisible" max-width="500">
            <VCard class="text-center px-6 py-8">
                <VCardItem class="pb-4">
                    <VCardTitle class="text-h5 mb-2">
                        <VIcon icon="tabler-download" class="me-2" />
                        Export Data
                    </VCardTitle>
                    <VCardSubtitle class="text-body-1">
                        Choose export format and data type
                    </VCardSubtitle>
                </VCardItem>

                <VDivider class="mb-6" />

                <VCardText class="text-start">
                    <VRow>
                        <VCol cols="12">
                            <VSelect v-model="exportDataType" :items="exportDataTypeOptions" item-title="title"
                                item-value="value" label="Data Type" variant="outlined" density="comfortable"
                                prepend-inner-icon="tabler-database" />
                        </VCol>
                        <VCol cols="12">
                            <VSelect v-model="exportFormat" :items="exportFormatOptions" item-title="title"
                                item-value="value" label="Export Format" variant="outlined" density="comfortable"
                                prepend-inner-icon="tabler-file-type-csv" />
                        </VCol>
                    </VRow>

                    <!-- Export Info -->
                    <VAlert type="info" variant="tonal" class="mt-4" density="compact">
                        <template #prepend>
                            <VIcon icon="tabler-info-circle" />
                        </template>
                        <div class="text-body-2">
                            <strong>Current Selection:</strong><br>
                            Period: {{periodOptions.find(p => p.value === selectedPeriod)?.title}}<br>
                            Unit: {{franchiseeUnits.find(u => u.id === selectedUnit)?.name}}
                        </div>
                    </VAlert>
                </VCardText>

                <VCardActions class="d-flex align-center justify-center gap-3 pt-4">
                    <VBtn variant="outlined" color="secondary" @click="isExportDialogVisible = false">
                        Cancel
                    </VBtn>
                    <VBtn color="primary" prepend-icon="tabler-download" @click="performExport">
                        Export Data
                    </VBtn>
                </VCardActions>
            </VCard>
        </VDialog>
    </section>
</template>

<style scoped>
.v-list-item {
    min-height: 48px;
}

.v-card {
    height: 100%;
}
</style>
