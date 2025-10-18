<script setup lang="ts">
import { SaudiRiyal } from 'lucide-vue-next'
import { computed, onMounted, ref, watch } from 'vue'
import { useTheme } from 'vuetify'
import { type ChartData, type ExpenseData, type FinancialStatistics, type ProfitData, type SalesData, type UnitPerformance, financialApi } from '@/services/api/financial'
import { formatCurrency } from '@/@core/utils/formatters'

const vuetifyTheme = useTheme()

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

// Update data table options
const updateOptions = async (options: any) => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
  await loadData()
}

// Type definitions
// interface FranchiseeUnit {
//   id: string
//   name: string
//   location: string
//   sales: number
//   expenses: number
//   royalties: number
//   netSales: number
//   profit: number
//   profitMargin: number
// }

// Reactive data
const selectedPeriod = ref<'daily' | 'monthly' | 'yearly'>('monthly')
const selectedUnit = ref('all')

// Data table state
const activeTab = ref<'sales' | 'expense' | 'profit'>('sales')
const selectedSalesRows = ref([])
const selectedExpensesRows = ref([])
const selectedProfitRows = ref([])

// Computed property for current selected rows based on active tab
const selectedRows = computed(() => {
  if (activeTab.value === 'sales')
    return selectedSalesRows.value
  if (activeTab.value === 'expense')
    return selectedExpensesRows.value
  if (activeTab.value === 'profit')
    return selectedProfitRows.value

  return []
})

// Loading states
const isLoading = ref(false)
const isChartLoading = ref(false)
const isStatisticsLoading = ref(false)
const isTableLoading = ref(false)
const isUnitsLoading = ref(false)

// View modal states
const isViewSaleDialogVisible = ref(false)
const isViewExpenseDialogVisible = ref(false)
const viewedSale = ref<any>(null)
const viewedExpense = ref<any>(null)

// API data
const chartData = ref<ChartData>({ categories: [], series: [] })

const statistics = ref<FinancialStatistics>({
  totalSales: 0,
  totalExpenses: 0,
  totalProfit: 0,
  totalRoyalties: 0,
  salesGrowth: 0,
  profitMargin: 0,
})

const salesData = ref<SalesData[]>([])
const expensesData = ref<ExpenseData[]>([])
const profitData = ref<ProfitData[]>([])
const unitsTableData = ref<UnitPerformance[]>([])
const totalItems = ref(0)

// Search query
const searchQuery = ref('')

// Mock data for franchisee units (this would come from an API in a real implementation)
// const franchiseeUnits = [
//   { id: 'all', name: 'All Units', location: 'Overview' },
// ]

// Computed current selected rows based on active tab
const currentSelectedRows = computed(() => {
  switch (activeTab.value) {
    case 'sales': return selectedSalesRows.value
    case 'expense': return selectedExpensesRows.value
    case 'profit': return selectedProfitRows.value
    default: return []
  }
})

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
      categories: chartData.value.categories,
      labels: {
        style: {
          colors: labelColor,
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
          colors: labelColor,
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
        colors: labelColor,
      },
    },
    colors: [
      chartColors.success,
      chartColors.error,
      chartColors.warning,
      chartColors.primary,
    ],
    grid: {
      borderColor,
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

// Computed stat cards data
const totalSales = computed(() => statistics.value.totalSales)
const totalExpenses = computed(() => statistics.value.totalExpenses)
const totalProfit = computed(() => statistics.value.totalProfit)

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

// Headers for different tabs
const salesHeaders = [
  { title: 'Product', key: 'product', sortable: true },
  { title: 'Date of Sale', key: 'date', sortable: true },
  { title: 'Unit Price (SAR)', key: 'unitPrice', sortable: true },
  { title: 'Quantity Sold', key: 'quantity', sortable: true },
  { title: 'Sale (SAR)', key: 'sale', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false },
]

const expensesHeaders = [
  { title: 'Expense Category', key: 'expenseCategory', sortable: true },
  { title: 'Date of Expense', key: 'date', sortable: true },
  { title: 'Amount (SAR)', key: 'amount', sortable: true },
  { title: 'Description', key: 'description', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false },
]

const profitHeaders = [
  { title: 'Date', key: 'date', sortable: true },
  { title: 'Total Sales (SAR)', key: 'totalSales', sortable: true },
  { title: 'Total Expenses (SAR)', key: 'totalExpenses', sortable: true },
  { title: 'Profit (SAR)', key: 'profit', sortable: true },
]

// Computed properties for current tab
const currentData = computed(() => {
  switch (activeTab.value) {
    case 'sales': return salesData.value
    case 'expense': return expensesData.value
    case 'profit': return profitData.value
    default: return []
  }
})

const currentHeaders = computed(() => {
  switch (activeTab.value) {
    case 'sales': return salesHeaders
    case 'expense': return expensesHeaders
    case 'profit': return profitHeaders
    default: return []
  }
})

const currentTotal = computed(() => totalItems.value)

// Modal states
const isAddDataModalVisible = ref(false)
const isImportModalVisible = ref(false)
const addDataCategory = ref<'sales' | 'expense'>('sales')
const importCategory = ref<'sales' | 'expenses'>('sales')
const importFile = ref<File | null>(null)

const addDataForm = ref({
  product: '',
  dateOfSale: '',
  unitPrice: 0,
  quantitySold: 0,
  expenseCategory: '',
  dateOfExpense: '',
  amount: 0,
  description: '',
})

// API functions
const loadChartData = async () => {
  isChartLoading.value = true
  try {
    const filters = {
      period: selectedPeriod.value,
      unit: selectedUnit.value === 'all' ? undefined : selectedUnit.value,
    }

    const chartResponse = await financialApi.getCharts(filters)

    chartData.value = chartResponse.data || { categories: [], series: [] }
  }
  catch (error) {
    console.error('Error loading chart data:', error)
  }
  finally {
    isChartLoading.value = false
  }
}

const loadStatistics = async () => {
  isStatisticsLoading.value = true
  try {
    const filters = {
      period: selectedPeriod.value,
      unit: selectedUnit.value === 'all' ? undefined : selectedUnit.value,
    }

    const statsResponse = await financialApi.getStatistics(filters)

    statistics.value = statsResponse.data || {
      totalSales: 0,
      totalExpenses: 0,
      totalProfit: 0,
      totalRoyalties: 0,
      salesGrowth: 0,
      profitMargin: 0,
    }
  }
  catch (error) {
    console.error('Error loading statistics:', error)
  }
  finally {
    isStatisticsLoading.value = false
  }
}

const loadTableData = async () => {
  isTableLoading.value = true
  try {
    const filters = {
      search: searchQuery.value || undefined,
      page: page.value,
      per_page: itemsPerPage.value,
      sortBy: sortBy.value,
      sortOrder: orderBy.value,
      period: selectedPeriod.value,
      unit_id: selectedUnit.value === 'all' ? undefined : selectedUnit.value,
    }

    switch (activeTab.value) {
      case 'sales': {
        const salesResponse = await financialApi.getSales(filters)

        salesData.value = salesResponse.data?.data || []
        totalItems.value = salesResponse.data?.total || 0
        break
      }
      case 'expense': {
        const expensesResponse = await financialApi.getExpenses(filters)

        expensesData.value = expensesResponse.data?.data || []
        totalItems.value = expensesResponse.data?.total || 0
        break
      }
      case 'profit': {
        const profitResponse = await financialApi.getProfit(filters)

        profitData.value = profitResponse.data?.data || []
        totalItems.value = profitResponse.data?.total || 0
        break
      }
    }
  }
  catch (error) {
    console.error('Error loading table data:', error)
  }
  finally {
    isTableLoading.value = false
  }
}

const loadUnitsData = async () => {
  isUnitsLoading.value = true
  try {
    const filters = {
      period: selectedPeriod.value,
      unit: selectedUnit.value === 'all' ? undefined : selectedUnit.value,
    }

    const unitsResponse = await financialApi.getUnitPerformance(filters)

    // Transform the data to match the expected format
    const unitsData = unitsResponse.data?.data || []
    if (Array.isArray(unitsData)) {
      unitsTableData.value = unitsData.map((unit: any) => ({
        id: unit.id,
        name: unit.name,
        location: unit.location,
        sales: unit.sales || 0,
        expenses: unit.expenses || 0,
        royalties: unit.royalties || 0,
        netSales: (unit.sales || 0) - (unit.royalties || 0),
        profit: unit.profit || 0,
        profitMargin: unit.profitMargin || 0,
      }))
    }
    else {
      unitsTableData.value = []
    }
  }
  catch (error) {
    console.error('Error loading units data:', error)
  }
  finally {
    isUnitsLoading.value = false
  }
}

const loadData = async () => {
  isLoading.value = true
  try {
    await Promise.all([
      loadChartData(),
      loadStatistics(),
      loadTableData(),
      loadUnitsData(),
    ])
  }
  finally {
    isLoading.value = false
  }
}

// Bulk delete function
const bulkDelete = () => {
  if (selectedRows.value.length === 0)
    return

  // TODO: Implement bulk delete functionality
  console.log('Bulk delete:', selectedRows.value)

  // Clear selection after delete
  if (activeTab.value === 'sales')
    selectedSalesRows.value = []
  else if (activeTab.value === 'expenses')
    selectedExpensesRows.value = []
  else if (activeTab.value === 'profit')
    selectedProfitRows.value = []
}

// Functions
const exportData = async () => {
  try {
    const filters = {
      period: selectedPeriod.value,
      unit: selectedUnit.value === 'all' ? undefined : selectedUnit.value,
    }

    const blob = await financialApi.exportData(activeTab.value, filters)

    // Create download link
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')

    link.href = url
    link.download = `${activeTab.value}-data-${selectedPeriod.value}.csv`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  }
  catch (error) {
    console.error('Error exporting data:', error)
  }
}

const deleteSelected = async () => {
  try {
    const selectedIds = currentSelectedRows.value.map((item: any) => item.id)

    for (const id of selectedIds) {
      const numericId = typeof id === 'string' ? Number.parseInt(id, 10) : id

      switch (activeTab.value) {
        case 'sales':
          await financialApi.deleteSale(numericId)
          break
        case 'expense':
          await financialApi.deleteExpense(numericId)
          break
        case 'profit':
        // Profit data is calculated, so we don't delete it directly
        // We would need to delete the underlying sales/expense records
          break
      }
    }

    // Reload data
    await loadTableData()

    // Clear selection
    switch (activeTab.value) {
      case 'sales':
        selectedSalesRows.value = []
        break
      case 'expense':
        selectedExpensesRows.value = []
        break
      case 'profit':
        selectedProfitRows.value = []
        break
    }
  }
  catch (error) {
    console.error('Error deleting selected items:', error)
  }
}

const openImportModal = () => {
  isImportModalVisible.value = true
}

const openAddDataModal = () => {
  isAddDataModalVisible.value = true
}

const submitAddData = async () => {
  try {
    if (addDataCategory.value === 'sales') {
      await financialApi.createSale({
        product: addDataForm.value.product,
        unit_price: addDataForm.value.unitPrice,
        quantity: addDataForm.value.quantitySold,
        date: addDataForm.value.dateOfSale,
        customer_name: 'Walk-in Customer', // Default value for now
        customer_email: 'customer@example.com', // Default value for now
        franchise_id: 1, // TODO: Get from user context
        unit_id: 1, // TODO: Get from user context or form
      })
    }
    else {
      await financialApi.createExpense({
        expense_category: addDataForm.value.expenseCategory,
        amount: addDataForm.value.amount,
        description: addDataForm.value.description,
        date: addDataForm.value.dateOfExpense,
      })
    }

    isAddDataModalVisible.value = false
    await loadTableData()

    // Reset form
    addDataForm.value = {
      product: '',
      dateOfSale: '',
      unitPrice: 0,
      quantitySold: 0,
      expenseCategory: '',
      dateOfExpense: '',
      amount: 0,
      description: '',
    }
  }
  catch (error) {
    console.error('Error adding data:', error)
  }
}

const submitImport = async () => {
  if (!importFile.value)
    return

  try {
    await financialApi.importData(importFile.value, importCategory.value)
    isImportModalVisible.value = false
    await loadTableData()
    importFile.value = null
  }
  catch (error) {
    console.error('Error importing data:', error)
  }
}

const deleteItem = async (id: string | number) => {
  try {
    const numericId = typeof id === 'string' ? Number.parseInt(id, 10) : id

    switch (activeTab.value) {
      case 'sales':
        await financialApi.deleteSale(numericId)
        break
      case 'expense':
        await financialApi.deleteExpense(numericId)
        break
      case 'profit':
      // Profit data is calculated, so we don't delete it directly
        break
    }

    await loadTableData()
  }
  catch (error) {
    console.error('Error deleting item:', error)
  }
}

const viewItem = (id: string | number) => {
  const numericId = typeof id === 'string' ? Number.parseInt(id, 10) : id

  // Find the item in the current data
  let item
  switch (activeTab.value) {
    case 'sales':
      item = salesData.value.find(s => s.id === numericId)
      break
    case 'expense':
      item = expensesData.value.find(e => e.id === numericId)
      break
    case 'profit':
      item = profitData.value.find(p => p.id === numericId)
      break
  }

  if (item) {
    // Open appropriate modal based on tab
    switch (activeTab.value) {
      case 'sales':
        viewedSale.value = item
        isViewSaleDialogVisible.value = true
        break
      case 'expense':
        viewedExpense.value = item
        isViewExpenseDialogVisible.value = true
        break
      case 'profit':
      // Profit items might not need a detail view, or create one if needed
        console.log('Viewing profit item:', item)
        break
    }
  }
}

const resolveProfitVariant = (profit: number) => {
  return profit > 0 ? 'success' : 'error'
}

// Watch for changes
watch([selectedPeriod, selectedUnit], () => {
  loadData()
})

watch(activeTab, () => {
  loadTableData()
})

watch(searchQuery, () => {
  page.value = 1
  loadTableData()
})

// Load data on mount
onMounted(() => {
  loadData()
})
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
    </div>

    <!-- Tabs -->
    <VTabs
      v-model="activeTab"
      class="mb-6"
    >
      <VTab value="sales">
        Sales
      </VTab>
      <VTab value="expense">
        Expense
      </VTab>
      <VTab value="profit">
        Profit
      </VTab>
    </VTabs>

    <!-- Main Data Table Card -->
    <VCard class="mb-6">
      <VCardText class="d-flex flex-wrap gap-4">
        <div class="me-3 d-flex gap-3 align-center">
          <span class="text-body-2 text-disabled">Show:</span>
          <AppSelect
            :model-value="itemsPerPage"
            :items="[
              { value: 10, title: '10' },
              { value: 25, title: '25' },
              { value: 50, title: '50' },
              { value: 100, title: '100' },
            ]"
            style="inline-size: 6.25rem;"
            @update:model-value="itemsPerPage = parseInt($event, 10)"
          />
        </div>
        <VSpacer />

        <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
          <!-- Search -->
          <div style="inline-size: 15.625rem;">
            <AppTextField
              v-model="searchQuery"
              :placeholder="`Search ${activeTab}...`"
              prepend-inner-icon="tabler-search"
            />
          </div>

          <!-- Export button -->
          <VBtn
            variant="tonal"
            color="secondary"
            prepend-icon="tabler-download"
            :disabled="!currentSelectedRows.length"
            @click="exportData"
          >
            Export Selected
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- Data Table -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :model-value="currentSelectedRows"
        :items="currentData"
        item-value="id"
        :items-length="currentTotal"
        :headers="currentHeaders"
        class="text-no-wrap"
        show-select
        :loading="isTableLoading"
        @update:options="updateOptions"
        @update:model-value="(value) => {
          switch (activeTab) {
          case 'sales': selectedSalesRows = value; break;
          case 'expense': selectedExpensesRows = value; break;
          case 'profit': selectedProfitRows = value; break;
          }
        }"
      >
        <!-- Sales specific templates -->
        <template
          v-if="activeTab === 'sales'"
          #item.unitPrice="{ item }"
        >
          <span class="font-weight-medium">
            {{ Number((item as SalesData).unitPrice).toFixed(2) }} SAR
          </span>
        </template>

        <template
          v-if="activeTab === 'sales'"
          #item.sale="{ item }"
        >
          <span class="text-success font-weight-medium">
            {{ Number((item as SalesData).sale).toFixed(2) }} SAR
          </span>
        </template>

        <!-- Expenses specific templates -->
        <template
          v-if="activeTab === 'expense'"
          #item.amount="{ item }"
        >
          <span class="text-error font-weight-medium">
            {{ Number((item as ExpenseData).amount).toFixed(2) }} SAR
          </span>
        </template>

        <template
          v-if="activeTab === 'expense'"
          #item.expenseCategory="{ item }"
        >
          <VChip
            size="small"
            variant="tonal"
            color="primary"
          >
            {{ (item as ExpenseData).expenseCategory }}
          </VChip>
        </template>

        <!-- Profit specific templates -->
        <template
          v-if="activeTab === 'profit'"
          #item.totalSales="{ item }"
        >
          <span class="font-weight-medium">
            {{ Number((item as ProfitData).totalSales).toFixed(2) }} SAR
          </span>
        </template>

        <template
          v-if="activeTab === 'profit'"
          #item.totalExpenses="{ item }"
        >
          <span class="text-error">
            {{ Number((item as ProfitData).totalExpenses).toFixed(2) }} SAR
          </span>
        </template>

        <template
          v-if="activeTab === 'profit'"
          #item.profit="{ item }"
        >
          <span class="text-success font-weight-medium">
            {{ Number((item as ProfitData).profit).toFixed(2) }} SAR
          </span>
        </template>

        <!-- Actions for Sales Tab -->
        <template
          v-if="activeTab === 'sales'"
          #item.actions="{ item }"
        >
          <VBtn
            icon
            variant="text"
            color="primary"
            size="small"
            @click="viewItem(item.id)"
          >
            <VIcon icon="tabler-eye" />
            <VTooltip
              activator="parent"
              location="top"
            >
              View Sale Details
            </VTooltip>
          </VBtn>
        </template>

        <!-- Actions for Expenses Tab -->
        <template
          v-if="activeTab === 'expense'"
          #item.actions="{ item }"
        >
          <VBtn
            icon
            variant="text"
            color="primary"
            size="small"
            @click="viewItem(item.id)"
          >
            <VIcon icon="tabler-eye" />
            <VTooltip
              activator="parent"
              location="top"
            >
              View Expense Details
            </VTooltip>
          </VBtn>
        </template>

        <!-- Actions for Profit Tab -->
        <template
          v-if="activeTab === 'profit'"
          #item.actions="{ item }"
        >
          <VBtn
            icon
            variant="text"
            color="primary"
            size="small"
            @click="viewItem(item.id)"
          >
            <VIcon icon="tabler-eye" />
            <VTooltip
              activator="parent"
              location="top"
            >
              View Profit Details
            </VTooltip>
          </VBtn>
        </template>

        <!-- Pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="currentTotal"
          />
        </template>
      </VDataTableServer>
    </VCard>

    <!-- Period Selector -->
    <VRow class="mb-6">
      <VCol cols="12">
        <div class="d-flex justify-space-between align-center flex-wrap gap-4">
          <div>
            <h4 class="text-h4 mb-1">
              Financial Overview
            </h4>
            <p class="text-body-1 text-medium-emphasis">
              Comprehensive financial analysis and performance tracking across all franchise units
            </p>
          </div>

          <!-- Header Actions -->
          <div class="d-flex gap-3 align-center flex-wrap">
            <!-- Period Selector -->
            <VSelect
              v-model="selectedPeriod"
              :items="periodOptions"
              item-title="title"
              item-value="value"
              density="compact"
              style="min-width: 120px;"
              variant="outlined"
            />
          </div>
        </div>
      </VCol>
    </VRow>

    <!-- Stat Cards -->
    <VRow class="mb-6">
      <!-- Total Sales -->
      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardText>
            <div class="d-flex align-center justify-space-between">
              <div>
                <h6 class="text-h6 mb-1">
                  Total Sales
                </h6>
                <div class="text-body-2 text-medium-emphasis mb-3">
                  {{ selectedPeriod === 'yearly' ? 'Annual' : selectedPeriod === 'monthly' ? 'Monthly'
                    : 'Daily' }} Revenue
                </div>
                <h4 class="text-h4 text-success">
                  {{ (totalSales || 0).toLocaleString() }} SAR
                </h4>
              </div>
              <VAvatar
                color="success"
                variant="tonal"
                size="56"
              >
                <VIcon
                  icon="tabler-trending-up"
                  size="28"
                />
              </VAvatar>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Total Expenses -->
      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardText>
            <div class="d-flex align-center justify-space-between">
              <div>
                <h6 class="text-h6 mb-1">
                  Total Expenses
                </h6>
                <div class="text-body-2 text-medium-emphasis mb-3">
                  {{ selectedPeriod === 'yearly' ? 'Annual' : selectedPeriod === 'monthly' ? 'Monthly'
                    : 'Daily' }} Costs
                </div>
                <h4 class="text-h4 text-error">
                  {{ (totalExpenses || 0).toLocaleString() }} SAR
                </h4>
              </div>
              <VAvatar
                color="error"
                variant="tonal"
                size="56"
              >
                <VIcon
                  icon="tabler-trending-down"
                  size="28"
                />
              </VAvatar>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Total Profit -->
      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardText>
            <div class="d-flex align-center justify-space-between">
              <div>
                <h6 class="text-h6 mb-1">
                  Total Profit
                </h6>
                <div class="text-body-2 text-medium-emphasis mb-3">
                  {{ selectedPeriod === 'yearly' ? 'Annual' : selectedPeriod === 'monthly' ? 'Monthly'
                    : 'Daily' }} Earnings
                </div>
                <h4 class="text-h4 text-primary">
                  {{ (totalProfit || 0).toLocaleString() }} SAR
                </h4>
              </div>
              <VAvatar
                color="primary"
                variant="tonal"
                size="56"
              >
                <SaudiRiyal size="28" />
              </VAvatar>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Chart -->
    <VCard class="mb-6">
      <VCardText>
        <h6 class="text-h6 mb-4">
          Financial Performance
        </h6>
        <VueApexCharts
          type="line"
          height="400"
          :options="chartOptions"
          :series="chartData?.series || []"
          :loading="isChartLoading"
        />
      </VCardText>
    </VCard>

    <!-- Units Performance Table -->
    <VCard>
      <VCardText>
        <h6 class="text-h6 mb-4">
          Units Performance
        </h6>
        <VDataTable
          :headers="tableHeaders"
          :items="unitsTableData"
          :loading="isUnitsLoading"
          class="text-no-wrap"
        >
          <template #item.sales="{ item }">
            <div class="text-body-1 text-high-emphasis">
              {{ formatCurrency(item.sales || 0) }}
            </div>
          </template>

          <template #item.expenses="{ item }">
            <div class="text-body-1 text-high-emphasis">
              {{ formatCurrency(item.expenses || 0) }}
            </div>
          </template>

          <template #item.royalties="{ item }">
            <div class="text-body-1 text-high-emphasis">
              {{ formatCurrency(item.royalties || 0) }}
            </div>
          </template>

          <template #item.netSales="{ item }">
            <div class="text-body-1 text-high-emphasis">
              {{ formatCurrency(item.netSales || 0) }}
            </div>
          </template>

          <template #item.profit="{ item }">
            <div class="text-body-1 text-high-emphasis">
              {{ formatCurrency(item.profit || 0) }}
            </div>
          </template>

          <template #item.profitMargin="{ item }">
            <VChip
              :color="(item.profitMargin || 0) > 0 ? 'success' : 'error'"
              size="small"
              label
            >
              {{ (item.profitMargin || 0).toFixed(1) }}%
            </VChip>
          </template>
        </VDataTable>
      </VCardText>
    </VCard>

    <!-- Add Data Modal -->
    <VDialog
      v-model="isAddDataModalVisible"
      max-width="600"
    >
      <VCard>
        <VCardTitle>Add New Data</VCardTitle>
        <VCardText>
          <VSelect
            v-model="addDataCategory"
            :items="[
              { title: 'Sales', value: 'sales' },
              { title: 'Expense', value: 'expense' },
            ]"
            label="Category"
            class="mb-4"
          />

          <!-- Sales Fields -->
          <div v-if="addDataCategory === 'sales'">
            <VTextField
              v-model="addDataForm.product"
              label="Product"
              class="mb-4"
            />
            <VTextField
              v-model="addDataForm.dateOfSale"
              label="Date of Sale"
              type="date"
              class="mb-4"
            />
            <VTextField
              v-model="addDataForm.unitPrice"
              label="Unit Price"
              type="number"
              prefix="SAR"
              class="mb-4"
            />
            <VTextField
              v-model="addDataForm.quantitySold"
              label="Quantity Sold"
              type="number"
              class="mb-4"
            />
          </div>

          <!-- Expense Fields -->
          <div v-if="addDataCategory === 'expense'">
            <VTextField
              v-model="addDataForm.expenseCategory"
              label="Expense Category"
              class="mb-4"
            />
            <VTextField
              v-model="addDataForm.dateOfExpense"
              label="Date of Expense"
              type="date"
              class="mb-4"
            />
            <VTextField
              v-model="addDataForm.amount"
              label="Amount"
              type="number"
              prefix="SAR"
              class="mb-4"
            />
            <VTextField
              v-model="addDataForm.description"
              label="Description"
              class="mb-4"
            />
          </div>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="outlined"
            @click="isAddDataModalVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            :loading="isLoading"
            @click="submitAddData"
          >
            Add Data
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Import Modal -->
    <VDialog
      v-model="isImportModalVisible"
      max-width="500"
    >
      <VCard>
        <VCardTitle>Import Data</VCardTitle>
        <VCardText>
          <VSelect
            v-model="importCategory"
            :items="[
              { title: 'Sales', value: 'sales' },
              { title: 'Expense', value: 'expenses' },
            ]"
            label="Category"
            class="mb-4"
          />
          <VFileInput
            v-model="importFile"
            label="Choose file"
            accept=".csv,.xlsx"
            prepend-icon="tabler-upload"
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="outlined"
            @click="isImportModalVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            :loading="isLoading"
            :disabled="!importFile"
            @click="submitImport"
          >
            Import
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- View Sale Details Dialog -->
    <VDialog
      v-model="isViewSaleDialogVisible"
      max-width="700"
    >
      <VCard v-if="viewedSale">
        <VCardItem>
          <VCardTitle>Sale Details</VCardTitle>
          <template #append>
            <VChip
              color="success"
              size="small"
              variant="tonal"
            >
              {{ viewedSale.referenceNumber || `REV-${String(viewedSale.id).padStart(6, '0')}` }}
            </VChip>
          </template>
        </VCardItem>

        <VDivider class="mb-4" />

        <VCardText>
          <VRow>
            <!-- Product Information -->
            <VCol cols="12">
              <h6 class="text-h6 mb-4 text-primary">
                <VIcon
                  icon="tabler-shopping-cart"
                  class="me-2"
                />
                Product Information
              </h6>
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <div class="mb-4">
                <div class="text-body-2 text-medium-emphasis mb-1">
                  Product Name
                </div>
                <div class="font-weight-medium text-h6">
                  {{ viewedSale.product }}
                </div>
              </div>
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <div class="mb-4">
                <div class="text-body-2 text-medium-emphasis mb-1">
                  Sale Date
                </div>
                <div class="font-weight-medium">
                  {{ new Date(viewedSale.date).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                  }) }}
                </div>
              </div>
            </VCol>

            <!--
              <VCol cols="12" md="6">
              <div class="mb-4">
              <div class="text-body-2 text-medium-emphasis mb-1">
              Store Location
              </div>
              <div class="font-weight-medium">
              {{ viewedSale.unitName || 'N/A' }}
              </div>
              </div>
              </VCol>

              <VCol cols="12" md="6">
              <div class="mb-4">
              <div class="text-body-2 text-medium-emphasis mb-1">
              Customer
              </div>
              <div class="font-weight-medium">
              {{ viewedSale.customerName || 'N/A' }}
              </div>
              </div>
              </VCol>
            -->

            <!-- Financial Details -->
            <VCol cols="12">
              <VDivider class="my-4" />
              <h6 class="text-h6 mb-4 text-primary">
                <VIcon
                  icon="tabler-report-money"
                  class="me-2"
                />
                Financial Details
              </h6>
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <VCard
                variant="tonal"
                color="info"
                class="pa-4"
              >
                <div class="text-center">
                  <VIcon
                    icon="tabler-tag"
                    size="32"
                    class="mb-2"
                  />
                  <div class="text-body-2 text-medium-emphasis mb-1">
                    Unit Price
                  </div>
                  <div class="text-h6 font-weight-bold">
                    {{ Number(viewedSale.unitPrice).toFixed(2) }} SAR
                  </div>
                </div>
              </VCard>
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <VCard
                variant="tonal"
                color="warning"
                class="pa-4"
              >
                <div class="text-center">
                  <VIcon
                    icon="tabler-stack"
                    size="32"
                    class="mb-2"
                  />
                  <div class="text-body-2 text-medium-emphasis mb-1">
                    Quantity
                  </div>
                  <div class="text-h6 font-weight-bold">
                    {{ viewedSale.quantity }}
                  </div>
                </div>
              </VCard>
            </VCol>

            <VCol
              cols="12"
              md="4"
            >
              <VCard
                variant="tonal"
                color="success"
                class="pa-4"
              >
                <div class="text-center">
                  <VIcon
                    icon="tabler-coins"
                    size="32"
                    class="mb-2"
                  />
                  <div class="text-body-2 text-medium-emphasis mb-1">
                    Total Sale
                  </div>
                  <div class="text-h6 font-weight-bold">
                    {{ Number(viewedSale.sale).toFixed(2) }} SAR
                  </div>
                </div>
              </VCard>
            </VCol>

            <!-- Calculation Breakdown -->
            <VCol cols="12">
              <VDivider class="my-4" />
              <h6 class="text-h6 mb-4 text-primary">
                <VIcon
                  icon="tabler-calculator"
                  class="me-2"
                />
                Calculation
              </h6>
            </VCol>

            <VCol cols="12">
              <VTable density="compact">
                <tbody>
                  <tr>
                    <td class="font-weight-medium">
                      Unit Price:
                    </td>
                    <td class="text-end">
                      {{ Number(viewedSale.unitPrice).toFixed(2) }} SAR
                    </td>
                  </tr>
                  <tr>
                    <td class="font-weight-medium">
                      Quantity:
                    </td>
                    <td class="text-end">
                      × {{ viewedSale.quantity }}
                    </td>
                  </tr>
                  <tr class="bg-success-lighten-5">
                    <td class="font-weight-bold text-success">
                      Total Amount:
                    </td>
                    <td class="text-end font-weight-bold text-success">
                      {{ Number(viewedSale.sale).toFixed(2) }} SAR
                    </td>
                  </tr>
                </tbody>
              </VTable>
            </VCol>
          </VRow>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isViewSaleDialogVisible = false"
          >
            Close
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- View Expense Details Dialog -->
    <VDialog
      v-model="isViewExpenseDialogVisible"
      max-width="700"
    >
      <VCard v-if="viewedExpense">
        <VCardItem>
          <VCardTitle>Expense Details</VCardTitle>
          <template #append>
            <VChip
              color="error"
              size="small"
              variant="tonal"
            >
              {{ viewedExpense.referenceNumber || `EXP-${String(viewedExpense.id).padStart(6, '0')}` }}
            </VChip>
          </template>
        </VCardItem>

        <VDivider class="mb-4" />

        <VCardText>
          <VRow>
            <!-- Expense Information -->
            <VCol cols="12">
              <h6 class="text-h6 mb-4 text-primary">
                <VIcon
                  icon="tabler-receipt"
                  class="me-2"
                />
                Expense Information
              </h6>
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <div class="mb-4">
                <div class="text-body-2 text-medium-emphasis mb-1">
                  Category
                </div>
                <VChip
                  color="error"
                  size="small"
                  variant="tonal"
                  class="text-capitalize"
                >
                  {{ viewedExpense.expenseCategory }}
                </VChip>
              </div>
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <div class="mb-4">
                <div class="text-body-2 text-medium-emphasis mb-1">
                  Expense Date
                </div>
                <div class="font-weight-medium">
                  {{ new Date(viewedExpense.date).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                  }) }}
                </div>
              </div>
            </VCol>

            <!--
              <VCol cols="12" md="6">
              <div class="mb-4">
              <div class="text-body-2 text-medium-emphasis mb-1">
              Store Location
              </div>
              <div class="font-weight-medium">
              {{ viewedExpense.unitName || 'N/A' }}
              </div>
              </div>
              </VCol>

              <VCol cols="12" md="6">
              <div class="mb-4">
              <div class="text-body-2 text-medium-emphasis mb-1">
              Payment Method
              </div>
              <div class="font-weight-medium text-capitalize">
              {{ viewedExpense.paymentMethod ? viewedExpense.paymentMethod.replace('_', ' ') : 'N/A' }}
              </div>
              </div>
              </VCol>
            -->

            <VCol
              v-if="viewedExpense.vendorName"
              cols="12"
            >
              <div class="mb-4">
                <div class="text-body-2 text-medium-emphasis mb-1">
                  Vendor
                </div>
                <div class="font-weight-medium">
                  {{ viewedExpense.vendorName }}
                </div>
              </div>
            </VCol>

            <VCol cols="12">
              <div class="mb-4">
                <div class="text-body-2 text-medium-emphasis mb-1">
                  Description
                </div>
                <div class="font-weight-medium">
                  {{ viewedExpense.description || 'No description provided' }}
                </div>
              </div>
            </VCol>

            <!-- Financial Details -->
            <VCol cols="12">
              <VDivider class="my-4" />
              <h6 class="text-h6 mb-4 text-primary">
                <VIcon
                  icon="tabler-report-money"
                  class="me-2"
                />
                Amount
              </h6>
            </VCol>

            <VCol cols="12">
              <VCard
                variant="tonal"
                color="error"
                class="pa-6"
              >
                <div class="text-center">
                  <VIcon
                    icon="tabler-currency-dollar"
                    size="48"
                    class="mb-3"
                  />
                  <div class="text-body-1 text-medium-emphasis mb-2">
                    Expense Amount
                  </div>
                  <div class="text-h4 font-weight-bold">
                    {{ Number(viewedExpense.amount).toFixed(2) }} SAR
                  </div>
                </div>
              </VCard>
            </VCol>
          </VRow>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isViewExpenseDialogVisible = false"
          >
            Close
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </section>
</template>
