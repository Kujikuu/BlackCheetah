<script setup lang="ts">
import { formatCurrency } from '@/@core/utils/formatters'
import { computed, ref } from 'vue'
import { useTheme } from 'vuetify'

const vuetifyTheme = useTheme()

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

// Update data table options
const updateOptions = (options: any) => {
    sortBy.value = options.sortBy[0]?.key
    orderBy.value = options.sortBy[0]?.order
}

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

interface FinancialData {
    [key: string]: PeriodData
}

interface FranchiseeUnit {
    id: string
    name: string
    location: string
    sales: number
    expenses: number
    royalties: number
    netSales: number
    profit: number
    profitMargin: number
}

// Reactive data
const selectedPeriod = ref('monthly')
const selectedUnit = ref('all')

// Data table state
const activeTab = ref('sales')
const selectedSalesRows = ref([])
const selectedExpensesRows = ref([])
const selectedProfitRows = ref([])

// Computed current selected rows based on active tab
const currentSelectedRows = computed(() => {
    switch (activeTab.value) {
        case 'sales': return selectedSalesRows.value
        case 'expenses': return selectedExpensesRows.value
        case 'profit': return selectedProfitRows.value
        default: return []
    }
})

// Mock data for franchisee units
const franchiseeUnits = [
    { id: 'all', name: 'All Units', location: 'Overview' },
    { id: 'unit1', name: 'Downtown Branch', location: 'New York, NY' },
    { id: 'unit2', name: 'Mall Location', location: 'Los Angeles, CA' },
    { id: 'unit3', name: 'Airport Store', location: 'Chicago, IL' },
    { id: 'unit4', name: 'Suburban Center', location: 'Houston, TX' },
    { id: 'unit5', name: 'City Plaza', location: 'Phoenix, AZ' },
]

// Mock financial data
const financialData: FinancialData = {
    monthly: {
        all: {
            sales: [450000, 520000, 480000, 610000, 550000, 670000, 430000, 580000, 530000, 670000, 610000, 790000],
            expenses: [250000, 280000, 260000, 320000, 290000, 350000, 230000, 310000, 280000, 350000, 320000, 420000],
            royalties: [45000, 52000, 48000, 61000, 55000, 67000, 43000, 58000, 53000, 67000, 61000, 79000],
            profit: [155000, 188000, 172000, 229000, 205000, 253000, 157000, 212000, 197000, 253000, 229000, 291000],
        },
        unit1: {
            sales: [120000, 140000, 130000, 160000, 150000, 180000, 110000, 155000, 142000, 180000, 165000, 210000],
            expenses: [70000, 80000, 75000, 90000, 85000, 100000, 65000, 88000, 80000, 100000, 92000, 120000],
            royalties: [12000, 14000, 13000, 16000, 15000, 18000, 11000, 15500, 14200, 18000, 16500, 21000],
            profit: [38000, 46000, 42000, 54000, 50000, 62000, 34000, 51500, 47800, 62000, 56500, 69000],
        },
        unit2: {
            sales: [105000, 122000, 118000, 145000, 132000, 160000, 98000, 138000, 126000, 162000, 148000, 185000],
            expenses: [62000, 71000, 68000, 83000, 76000, 92000, 56000, 79000, 72000, 93000, 85000, 106000],
            royalties: [10500, 12200, 11800, 14500, 13200, 16000, 9800, 13800, 12600, 16200, 14800, 18500],
            profit: [32500, 38800, 38200, 47500, 42800, 52000, 32200, 45200, 41400, 52800, 48200, 60500],
        },
        unit3: {
            sales: [112000, 131000, 124000, 152000, 140000, 168000, 105000, 146000, 134000, 170000, 156000, 192000],
            expenses: [65000, 76000, 72000, 88000, 81000, 97000, 61000, 84000, 77000, 98000, 90000, 111000],
            royalties: [11200, 13100, 12400, 15200, 14000, 16800, 10500, 14600, 13400, 17000, 15600, 19200],
            profit: [35800, 41900, 39600, 48800, 45000, 54200, 33500, 47400, 43600, 55000, 50400, 61800],
        },
        unit4: {
            sales: [88000, 102000, 96000, 118000, 108000, 130000, 82000, 114000, 104000, 132000, 121000, 148000],
            expenses: [52000, 60000, 57000, 69000, 63000, 76000, 48000, 67000, 61000, 77000, 71000, 87000],
            royalties: [8800, 10200, 9600, 11800, 10800, 13000, 8200, 11400, 10400, 13200, 12100, 14800],
            profit: [27200, 31800, 29400, 37200, 34200, 41000, 25800, 35600, 32600, 41800, 37900, 46200],
        },
        unit5: {
            sales: [95000, 110000, 103000, 126000, 115000, 138000, 89000, 122000, 112000, 142000, 130000, 159000],
            expenses: [56000, 65000, 61000, 74000, 68000, 81000, 52000, 72000, 66000, 83000, 76000, 93000],
            royalties: [9500, 11000, 10300, 12600, 11500, 13800, 8900, 12200, 11200, 14200, 13000, 15900],
            profit: [29500, 34000, 31700, 39400, 35500, 43200, 28100, 37800, 34800, 44800, 41000, 50100],
        },
    },
    yearly: {
        all: {
            sales: [5800000, 6200000, 6800000, 7500000, 8200000],
            expenses: [3200000, 3400000, 3700000, 4000000, 4300000],
            royalties: [580000, 620000, 680000, 750000, 820000],
            profit: [2020000, 2180000, 2420000, 2750000, 3080000],
        },
        unit1: {
            sales: [1450000, 1550000, 1700000, 1875000, 2050000],
            expenses: [800000, 850000, 925000, 1000000, 1075000],
            royalties: [145000, 155000, 170000, 187500, 205000],
            profit: [505000, 545000, 605000, 687500, 770000],
        },
        unit2: {
            sales: [1260000, 1340000, 1470000, 1620000, 1770000],
            expenses: [700000, 740000, 810000, 890000, 970000],
            royalties: [126000, 134000, 147000, 162000, 177000],
            profit: [434000, 466000, 513000, 568000, 623000],
        },
        unit3: {
            sales: [1344000, 1432000, 1572000, 1732000, 1896000],
            expenses: [742000, 790000, 867000, 955000, 1044000],
            royalties: [134400, 143200, 157200, 173200, 189600],
            profit: [467600, 498800, 547800, 603800, 662400],
        },
        unit4: {
            sales: [1056000, 1124000, 1232000, 1358000, 1484000],
            expenses: [584000, 622000, 682000, 751000, 821000],
            royalties: [105600, 112400, 123200, 135800, 148400],
            profit: [366400, 389600, 426800, 471200, 514600],
        },
        unit5: {
            sales: [1134000, 1208000, 1326000, 1460000, 1596000],
            expenses: [626000, 667000, 732000, 806000, 881000],
            royalties: [113400, 120800, 132600, 146000, 159600],
            profit: [394600, 420200, 461400, 508000, 555400],
        },
    },
    daily: {
        all: {
            sales: [18000, 21000, 19000, 23000, 20000, 25000, 17000, 22000, 21000, 24000, 22000, 28000, 19000, 23000],
            expenses: [10000, 12000, 11000, 13000, 11500, 14000, 9500, 12500, 12000, 13500, 12500, 15500, 11000, 13000],
            royalties: [1800, 2100, 1900, 2300, 2000, 2500, 1700, 2200, 2100, 2400, 2200, 2800, 1900, 2300],
            profit: [6200, 6900, 6100, 7700, 6500, 8500, 5800, 7300, 6900, 8100, 7300, 9700, 6100, 7700],
        },
        unit1: {
            sales: [4500, 5250, 4750, 5750, 5000, 6250, 4250, 5500, 5250, 6000, 5500, 7000, 4750, 5750],
            expenses: [2500, 3000, 2750, 3250, 2880, 3500, 2380, 3130, 3000, 3380, 3130, 3880, 2750, 3250],
            royalties: [450, 525, 475, 575, 500, 625, 425, 550, 525, 600, 550, 700, 475, 575],
            profit: [1550, 1725, 1525, 1925, 1620, 2125, 1445, 1820, 1725, 2020, 1820, 2420, 1525, 1925],
        },
        unit2: {
            sales: [3900, 4560, 4130, 5000, 4350, 5430, 3700, 4780, 4560, 5220, 4780, 6090, 4130, 5000],
            expenses: [2170, 2540, 2300, 2780, 2420, 3020, 2060, 2660, 2540, 2900, 2660, 3390, 2300, 2780],
            royalties: [390, 456, 413, 500, 435, 543, 370, 478, 456, 522, 478, 609, 413, 500],
            profit: [1340, 1564, 1417, 1720, 1495, 1867, 1270, 1642, 1564, 1798, 1642, 2091, 1417, 1720],
        },
        unit3: {
            sales: [4140, 4830, 4370, 5290, 4600, 5750, 3920, 5070, 4830, 5530, 5070, 6450, 4370, 5290],
            expenses: [2300, 2690, 2430, 2940, 2560, 3200, 2180, 2820, 2690, 3080, 2820, 3590, 2430, 2940],
            royalties: [414, 483, 437, 529, 460, 575, 392, 507, 483, 553, 507, 645, 437, 529],
            profit: [1426, 1657, 1503, 1821, 1580, 1975, 1348, 1743, 1657, 1897, 1743, 2215, 1503, 1821],
        },
        unit4: {
            sales: [3240, 3780, 3420, 4140, 3600, 4500, 3060, 3960, 3780, 4320, 3960, 5040, 3420, 4140],
            expenses: [1800, 2100, 1900, 2300, 2000, 2500, 1700, 2200, 2100, 2400, 2200, 2800, 1900, 2300],
            royalties: [324, 378, 342, 414, 360, 450, 306, 396, 378, 432, 396, 504, 342, 414],
            profit: [1116, 1302, 1178, 1426, 1240, 1550, 1054, 1364, 1302, 1488, 1364, 1736, 1178, 1426],
        },
        unit5: {
            sales: [3510, 4090, 3700, 4480, 3900, 4870, 3310, 4280, 4090, 4680, 4280, 5450, 3700, 4480],
            expenses: [1950, 2270, 2060, 2490, 2170, 2710, 1840, 2380, 2270, 2600, 2380, 3030, 2060, 2490],
            royalties: [351, 409, 370, 448, 390, 487, 331, 428, 409, 468, 428, 545, 370, 448],
            profit: [1209, 1411, 1270, 1542, 1340, 1673, 1139, 1472, 1411, 1612, 1472, 1875, 1270, 1542],
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
    const periodData = financialData[selectedPeriod.value] as PeriodData
    const data = periodData?.[selectedUnit.value] || periodData?.all

    return [
        { name: 'Sales', data: data?.sales || [] },
        { name: 'Expenses', data: data?.expenses || [] },
        { name: 'Royalties', data: data?.royalties || [] },
        { name: 'Profit', data: data?.profit || [] },
    ]
})

// Computed stat cards data
const totalSales = computed(() => {
    const periodData = financialData[selectedPeriod.value] as PeriodData
    const data = periodData?.all
    if (!data?.sales) return 0
    return data.sales.reduce((sum, value) => sum + value, 0)
})

const totalExpenses = computed(() => {
    const periodData = financialData[selectedPeriod.value] as PeriodData
    const data = periodData?.all
    if (!data?.expenses) return 0
    return data.expenses.reduce((sum, value) => sum + value, 0)
})

const totalProfit = computed(() => {
    const periodData = financialData[selectedPeriod.value] as PeriodData
    const data = periodData?.all
    if (!data?.profit) return 0
    return data.profit.reduce((sum, value) => sum + value, 0)
})

// Computed units table data
const unitsTableData = computed((): FranchiseeUnit[] => {
    const periodData = financialData[selectedPeriod.value] as PeriodData

    return franchiseeUnits.slice(1).map(unit => {
        const data = periodData?.[unit.id]
        if (!data) {
            return {
                id: unit.id,
                name: unit.name,
                location: unit.location,
                sales: 0,
                expenses: 0,
                royalties: 0,
                netSales: 0,
                profit: 0,
                profitMargin: 0,
            }
        }

        const sales = data.sales.reduce((sum, value) => sum + value, 0)
        const expenses = data.expenses.reduce((sum, value) => sum + value, 0)
        const royalties = data.royalties.reduce((sum, value) => sum + value, 0)
        const profit = data.profit.reduce((sum, value) => sum + value, 0)
        const netSales = sales - royalties
        const profitMargin = sales > 0 ? (profit / sales) * 100 : 0

        return {
            id: unit.id,
            name: unit.name,
            location: unit.location,
            sales,
            expenses,
            royalties,
            netSales,
            profit,
            profitMargin,
        }
    })
})

const periodOptions = [
    { title: 'Daily', value: 'daily' },
    { title: 'Monthly', value: 'monthly' },
    { title: 'Yearly', value: 'yearly' },
]

// Table headers
const tableHeaders = [
    { title: 'Franchisee Name', key: 'name', sortable: true },
    { title: 'Location', key: 'location', sortable: true },
    { title: 'Sales (SAR)', key: 'sales', sortable: true },
    { title: 'Expenses (SAR)', key: 'expenses', sortable: true },
    { title: 'Royalties (SAR)', key: 'royalties', sortable: true },
    { title: 'Net Sales (SAR)', key: 'netSales', sortable: true },
    { title: 'Profit (SAR)', key: 'profit', sortable: true },
    { title: 'Profit Margin (%)', key: 'profitMargin', sortable: true },
]

// Mock data for different tabs
const salesData = ref([
    { id: 1, product: 'Product A', unitPrice: 150, quantity: 100, sale: 15000, date: '2024-01-15' },
    { id: 2, product: 'Product B', unitPrice: 200, quantity: 75, sale: 15000, date: '2024-01-16' },
])

const expensesData = ref([
    { id: 1, expenseCategory: 'Rent', amount: 5000, description: 'Monthly rent', date: '2024-01-01' },
    { id: 2, expenseCategory: 'Utilities', amount: 1200, description: 'Electricity and water', date: '2024-01-02' },
])

const profitData = ref([
    { id: 1, date: '2024-01-01', totalSales: 50000, totalExpenses: 30000, profit: 20000 },
    { id: 2, date: '2024-01-02', totalSales: 55000, totalExpenses: 32000, profit: 23000 },
])

// Headers for different tabs
const salesHeaders = [
    { title: 'Product', key: 'product', sortable: true },
    { title: 'Unit Price', key: 'unitPrice', sortable: true },
    { title: 'Quantity', key: 'quantity', sortable: true },
    { title: 'Sale Amount', key: 'sale', sortable: true },
    { title: 'Date', key: 'date', sortable: true },
    { title: 'Actions', key: 'actions', sortable: false },
]

const expensesHeaders = [
    { title: 'Category', key: 'expenseCategory', sortable: true },
    { title: 'Amount', key: 'amount', sortable: true },
    { title: 'Description', key: 'description', sortable: true },
    { title: 'Date', key: 'date', sortable: true },
    { title: 'Actions', key: 'actions', sortable: false },
]

const profitHeaders = [
    { title: 'Date', key: 'date', sortable: true },
    { title: 'Total Sales', key: 'totalSales', sortable: true },
    { title: 'Total Expenses', key: 'totalExpenses', sortable: true },
    { title: 'Profit', key: 'profit', sortable: true },
    { title: 'Actions', key: 'actions', sortable: false },
]

// Computed properties for current tab
const currentData = computed(() => {
    switch (activeTab.value) {
        case 'sales': return salesData.value
        case 'expenses': return expensesData.value
        case 'profit': return profitData.value
        default: return []
    }
})

const currentHeaders = computed(() => {
    switch (activeTab.value) {
        case 'sales': return salesHeaders
        case 'expenses': return expensesHeaders
        case 'profit': return profitHeaders
        default: return []
    }
})

const currentTotal = computed(() => currentData.value.length)

// Search query
const searchQuery = ref('')

// Modal states
const isAddDataModalVisible = ref(false)
const isImportModalVisible = ref(false)
const addDataCategory = ref('sales')
const importCategory = ref('sales')
const importFile = ref(null)
const addDataForm = ref({
    product: '',
    dateOfSale: '',
    unitPrice: 0,
    quantitySold: 0,
    expenseCategory: '',
    dateOfExpense: '',
    amount: 0,
    description: ''
})

// Functions
const exportData = () => {
    console.log('Exporting data for tab:', activeTab.value)
    // Implementation for export functionality
}

const deleteSelected = () => {
    console.log('Deleting selected items for tab:', activeTab.value)
    // Implementation for delete functionality
}

const openImportModal = () => {
    isImportModalVisible.value = true
}

const openAddDataModal = () => {
    isAddDataModalVisible.value = true
}

const submitAddData = () => {
    console.log('Adding data:', addDataForm.value)
    isAddDataModalVisible.value = false
}

const submitImport = () => {
    console.log('Importing file:', importFile.value)
    isImportModalVisible.value = false
}

const deleteItem = (id: string | number) => {
    console.log('Deleting item:', id)
}

const editItem = (id: string | number) => {
    console.log('Editing item:', id)
}

const resolveProfitVariant = (profit: number) => {
    return profit > 0 ? 'success' : 'error'
}
</script>

<template>
    <section>
        <!-- Page Header -->
        <div class="d-flex justify-space-between align-center mb-6">
            <div>
                <h4 class="text-h4 mb-1">
                    Financial Overview
                </h4>
                <p class="text-body-1 mb-0">
                    Manage your financial data across sales, expenses, and profit
                </p>
            </div>
            <div class="d-flex gap-3">
                <VBtn variant="outlined" prepend-icon="tabler-upload" @click="openImportModal">
                    Import
                </VBtn>
                <VBtn prepend-icon="tabler-plus" @click="openAddDataModal">
                    Add Data
                </VBtn>
            </div>
        </div>

        <!-- Tabs -->
        <VTabs v-model="activeTab" class="mb-6">
            <VTab value="sales">
                Sales
            </VTab>
            <VTab value="expenses">
                Expenses
            </VTab>
            <VTab value="profit">
                Profit
            </VTab>
        </VTabs>

        <!-- Main Data Table Card -->
        <VCard class="mb-6">
            <VCardText class="d-flex flex-wrap gap-4">
                <div class="me-3 d-flex gap-3">
                    <AppSelect :model-value="itemsPerPage" :items="[
                        { value: 10, title: '10' },
                        { value: 25, title: '25' },
                        { value: 50, title: '50' },
                        { value: 100, title: '100' },
                        { value: -1, title: 'All' },
                    ]" style="inline-size: 6.25rem;" @update:model-value="itemsPerPage = parseInt($event, 10)" />
                </div>
                <VSpacer />

                <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
                    <!-- Search -->
                    <div style="inline-size: 15.625rem;">
                        <AppTextField v-model="searchQuery" :placeholder="`Search ${activeTab}...`" />
                    </div>

                    <!-- Export button -->
                    <VBtn variant="tonal" color="secondary" prepend-icon="tabler-upload"
                        :disabled="!currentSelectedRows.length" @click="exportData">
                        Export
                    </VBtn>

                    <!-- Delete button -->
                    <VBtn variant="tonal" color="error" prepend-icon="tabler-trash"
                        :disabled="!currentSelectedRows.length" @click="deleteSelected">
                        Delete Selected
                    </VBtn>
                </div>
            </VCardText>

            <VDivider />

            <!-- Data Table -->
            <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:page="page"
                :model-value="currentSelectedRows" :items="currentData" item-value="id" :items-length="currentTotal"
                :headers="currentHeaders" class="text-no-wrap" show-select @update:options="updateOptions"
                @update:model-value="(value) => {
                    switch (activeTab) {
                        case 'sales': selectedSalesRows = value; break;
                        case 'expenses': selectedExpensesRows = value; break;
                        case 'profit': selectedProfitRows = value; break;
                    }
                }">
                <!-- Sales specific templates -->
                <template v-if="activeTab === 'sales'" #item.product="{ item }">
                    <div class="d-flex align-center gap-x-2">
                        <VIcon icon="tabler-package" size="22" color="primary" />
                        <div class="text-body-1 text-high-emphasis">
                            {{ item.product }}
                        </div>
                    </div>
                </template>

                <template v-if="activeTab === 'sales'" #item.unitPrice="{ item }">
                    <div class="text-body-1 text-high-emphasis">
                        {{ formatCurrency(item.unitPrice) }}
                    </div>
                </template>

                <template v-if="activeTab === 'sales'" #item.sale="{ item }">
                    <VChip color="success" size="small" label>
                        {{ formatCurrency(item.sale) }}
                    </VChip>
                </template>

                <!-- Expenses specific templates -->
                <template v-if="activeTab === 'expenses'" #item.expenseCategory="{ item }">
                    <div class="d-flex align-center gap-x-2">
                        <VIcon icon="tabler-receipt" size="22" color="warning" />
                        <div class="text-body-1 text-high-emphasis text-capitalize">
                            {{ item.expenseCategory }}
                        </div>
                    </div>
                </template>

                <template v-if="activeTab === 'expenses'" #item.amount="{ item }">
                    <VChip color="error" size="small" label>
                        {{ formatCurrency(item.amount) }}
                    </VChip>
                </template>

                <template v-if="activeTab === 'expenses'" #item.description="{ item }">
                    <div class="text-body-2" style="max-width: 200px;">
                        {{ item.description }}
                    </div>
                </template>

                <!-- Profit specific templates -->
                <template v-if="activeTab === 'profit'" #item.date="{ item }">
                    <div class="d-flex align-center gap-x-2">
                        <VIcon icon="tabler-calendar" size="22" color="info" />
                        <div class="text-body-1 text-high-emphasis">
                            {{ item.date }}
                        </div>
                    </div>
                </template>

                <template v-if="activeTab === 'profit'" #item.totalSales="{ item }">
                    <div class="text-body-1 text-high-emphasis">
                        {{ formatCurrency(item.totalSales) }}
                    </div>
                </template>

                <template v-if="activeTab === 'profit'" #item.totalExpenses="{ item }">
                    <div class="text-body-1 text-high-emphasis">
                        {{ formatCurrency(item.totalExpenses) }}
                    </div>
                </template>

                <template v-if="activeTab === 'profit'" #item.profit="{ item }">
                    <VChip :color="resolveProfitVariant(item.profit)" size="small" label>
                        {{ formatCurrency(item.profit) }}
                    </VChip>
                </template>

                <!-- Actions -->
                <template #item.actions="{ item }">
                    <IconBtn @click="deleteItem(item.id)">
                        <VIcon icon="tabler-trash" />
                    </IconBtn>

                    <IconBtn @click="editItem(item.id)">
                        <VIcon icon="tabler-edit" />
                    </IconBtn>

                    <VBtn icon variant="text" color="medium-emphasis">
                        <VIcon icon="tabler-dots-vertical" />
                        <VMenu activator="parent">
                            <VList>
                                <VListItem @click="editItem(item.id)">
                                    <template #prepend>
                                        <VIcon icon="tabler-edit" />
                                    </template>
                                    <VListItemTitle>Edit</VListItemTitle>
                                </VListItem>

                                <VListItem @click="deleteItem(item.id)">
                                    <template #prepend>
                                        <VIcon icon="tabler-trash" />
                                    </template>
                                    <VListItemTitle>Delete</VListItemTitle>
                                </VListItem>
                            </VList>
                        </VMenu>
                    </VBtn>
                </template>

                <!-- Pagination -->
                <template #bottom>
                    <TablePagination v-model:page="page" :items-per-page="itemsPerPage" :total-items="currentTotal" />
                </template>
            </VDataTableServer>
        </VCard>

        <!-- Period Selector -->
        <VRow class="mb-6">
            <VCol cols="12">
                <div class="d-flex justify-space-between align-center flex-wrap gap-4">
                    <div>
                        <h4 class="text-h4 mb-1">Financial Overview</h4>
                        <p class="text-body-1 text-medium-emphasis">
                            Comprehensive financial analysis and performance tracking across all franchise units
                        </p>
                    </div>

                    <!-- Header Actions -->
                    <div class="d-flex gap-3 align-center flex-wrap">
                        <!-- Period Selector -->
                        <VSelect v-model="selectedPeriod" :items="periodOptions" item-title="title" item-value="value"
                            density="compact" style="min-width: 120px;" variant="outlined" />
                    </div>
                </div>
            </VCol>
        </VRow>

        <!-- Stat Cards -->
        <VRow class="mb-6">
            <!-- Total Sales -->
            <VCol cols="12" md="4">
                <VCard>
                    <VCardText>
                        <div class="d-flex align-center justify-space-between">
                            <div>
                                <h6 class="text-h6 mb-1">Total Sales</h6>
                                <div class="text-body-2 text-medium-emphasis mb-3">
                                    {{ selectedPeriod === 'yearly' ? 'Annual' : selectedPeriod === 'monthly' ? 'Monthly'
                                        :
                                        'Daily' }} Revenue
                                </div>
                                <h4 class="text-h4 text-success">
                                    {{ totalSales.toLocaleString() }} SAR
                                </h4>
                            </div>
                            <VAvatar color="success" variant="tonal" size="56">
                                <VIcon icon="tabler-trending-up" size="28" />
                            </VAvatar>
                        </div>
                    </VCardText>
                </VCard>
            </VCol>

            <!-- Total Expenses -->
            <VCol cols="12" md="4">
                <VCard>
                    <VCardText>
                        <div class="d-flex align-center justify-space-between">
                            <div>
                                <h6 class="text-h6 mb-1">Total Expenses</h6>
                                <div class="text-body-2 text-medium-emphasis mb-3">
                                    {{ selectedPeriod === 'yearly' ? 'Annual' : selectedPeriod === 'monthly' ? 'Monthly'
                                        :
                                        'Daily' }} Costs
                                </div>
                                <h4 class="text-h4 text-error">
                                    {{ totalExpenses.toLocaleString() }} SAR
                                </h4>
                            </div>
                            <VAvatar color="error" variant="tonal" size="56">
                                <VIcon icon="tabler-trending-down" size="28" />
                            </VAvatar>
                        </div>
                    </VCardText>
                </VCard>
            </VCol>

            <!-- Total Profit -->
            <VCol cols="12" md="4">
                <VCard>
                    <VCardText>
                        <div class="d-flex align-center justify-space-between">
                            <div>
                                <h6 class="text-h6 mb-1">Total Profit</h6>
                                <div class="text-body-2 text-medium-emphasis mb-3">
                                    {{ selectedPeriod === 'yearly' ? 'Annual' : selectedPeriod === 'monthly' ? 'Monthly'
                                        :
                                        'Daily' }} Earnings
                                </div>
                                <h4 class="text-h4 text-primary">
                                    {{ totalProfit.toLocaleString() }} SAR
                                </h4>
                            </div>
                            <VAvatar color="primary" variant="tonal" size="56">
                                <VIcon icon="tabler-currency-dollar" size="28" />
                            </VAvatar>
                        </div>
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>



        <!-- Add Data Modal -->
        <VDialog v-model="isAddDataModalVisible" max-width="600">
            <VCard>
                <VCardTitle>Add New Data</VCardTitle>
                <VCardText>
                    <VSelect v-model="addDataCategory" :items="[
                        { title: 'Sales', value: 'sales' },
                        { title: 'Expense', value: 'expense' }
                    ]" label="Category" class="mb-4" />

                    <!-- Sales Fields -->
                    <div v-if="addDataCategory === 'sales'">
                        <VTextField v-model="addDataForm.product" label="Product" class="mb-4" />
                        <VTextField v-model="addDataForm.dateOfSale" label="Date of Sale" type="date" class="mb-4" />
                        <VTextField v-model="addDataForm.unitPrice" label="Unit Price" type="number" prefix="$"
                            class="mb-4" />
                        <VTextField v-model="addDataForm.quantitySold" label="Quantity Sold" type="number"
                            class="mb-4" />
                    </div>

                    <!-- Expense Fields -->
                    <div v-if="addDataCategory === 'expense'">
                        <VTextField v-model="addDataForm.expenseCategory" label="Expense Category" class="mb-4" />
                        <VTextField v-model="addDataForm.dateOfExpense" label="Date of Expense" type="date"
                            class="mb-4" />
                        <VTextField v-model="addDataForm.amount" label="Amount" type="number" prefix="$" class="mb-4" />
                        <VTextField v-model="addDataForm.description" label="Description" class="mb-4" />
                    </div>
                </VCardText>
                <VCardActions>
                    <VSpacer />
                    <VBtn variant="outlined" @click="isAddDataModalVisible = false">
                        Cancel
                    </VBtn>
                    <VBtn color="primary" @click="submitAddData">
                        Add Data
                    </VBtn>
                </VCardActions>
            </VCard>
        </VDialog>

        <!-- Import Modal -->
        <VDialog v-model="isImportModalVisible" max-width="500">
            <VCard>
                <VCardTitle>Import Data</VCardTitle>
                <VCardText>
                    <VSelect v-model="importCategory" :items="[
                        { title: 'Sales', value: 'sales' },
                        { title: 'Expense', value: 'expense' }
                    ]" label="Category" class="mb-4" />
                    <VFileInput v-model="importFile" label="Choose file" accept=".csv,.xlsx"
                        prepend-icon="tabler-upload" />
                </VCardText>
                <VCardActions>
                    <VSpacer />
                    <VBtn variant="outlined" @click="isImportModalVisible = false">
                        Cancel
                    </VBtn>
                    <VBtn color="primary" @click="submitImport">
                        Import
                    </VBtn>
                </VCardActions>
            </VCard>
        </VDialog>


    </section>
</template>
