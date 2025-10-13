<script setup lang="ts">
import type {
  AddFinancialDataPayload,
  FinancialOverviewData,
  UnitProduct
} from '@/services/api/franchisee-dashboard'
import { franchiseeDashboardApi } from '@/services/api/franchisee-dashboard'
import { computed, onMounted, ref } from 'vue'

interface AddDataForm {
  // Sales fields
  product: string
  date: string
  quantitySold: number

  // Expense fields
  expenseCategory: string
  amount: number
  description: string
}

// Reactive data
const activeTab = ref('sales')
const searchQuery = ref('')
const selectedItems = ref<string[]>([])
const isAddDataModalVisible = ref(false)
const isImportModalVisible = ref(false)
const selectedCategory = ref('sales')
const importCategory = ref('sales')
const isLoading = ref(false)
const isEditMode = ref(false)
const editItemId = ref<string>('')
const dateFilter = ref<'daily' | 'monthly' | 'yearly'>('monthly')

// Financial data
const financialData = ref<FinancialOverviewData | null>(null)

// Unit products
const unitProducts = ref<UnitProduct[]>([])

// Form data
const addDataForm = ref<AddDataForm>({
  product: '',
  date: '',
  quantitySold: 0,
  expenseCategory: '',
  amount: 0,
  description: '',
})

// Load financial data from API
const loadFinancialData = async () => {
  isLoading.value = true
  try {
    const response = await franchiseeDashboardApi.getFinancialOverview()
    if (response.success) {
      financialData.value = response.data
    }
  }
  catch (error) {
    console.error('Error loading financial data:', error)
  }
  finally {
    isLoading.value = false
  }
}

// Load unit products from API
const loadUnitProducts = async () => {
  try {
    const response = await franchiseeDashboardApi.getUnitProducts()
    if (response.success) {
      unitProducts.value = response.data
    }
  }
  catch (error) {
    console.error('Error loading unit products:', error)
  }
}

// Computed properties for data
const salesData = computed(() => financialData.value?.sales || [])
const expenseData = computed(() => financialData.value?.expenses || [])
const profitData = computed(() => financialData.value?.profit || [])

// Load data on mount
onMounted(() => {
  loadFinancialData()
  loadUnitProducts()
})

// Computed product options from unit stock
const productOptions = computed(() => {
  return unitProducts.value
    .filter(product => product.status === 'active' && product.stock > 0)
    .map(product => ({
      title: `${product.name} (${product.stock} in stock)`,
      value: product.name,
      stock: product.stock,
    }))
})

// Get selected product's stock
const selectedProductStock = computed(() => {
  if (!addDataForm.value.product)
    return 0
  const product = unitProducts.value.find(p => p.name === addDataForm.value.product)
  return product?.stock || 0
})

// Validate quantity against available stock
const isQuantityValid = computed(() => {
  if (selectedCategory.value !== 'sales')
    return true
  return addDataForm.value.quantitySold > 0
    && addDataForm.value.quantitySold <= selectedProductStock.value
})

// Error message for quantity validation
const quantityErrorMessage = computed(() => {
  if (!addDataForm.value.product)
    return ''
  if (addDataForm.value.quantitySold === 0)
    return 'Quantity must be greater than 0'
  if (addDataForm.value.quantitySold > selectedProductStock.value)
    return `Cannot exceed available stock (${selectedProductStock.value} units)`
  return ''
})

// Expense category options
const expenseCategoryOptions = [
  'Food Supplies',
  'Utilities',
  'Staff Wages',
  'Marketing',
  'Equipment Maintenance',
  'Rent',
  'Insurance',
  'Cleaning Supplies',
  'Office Supplies',
  'Transportation',
]

// Import category options
const importCategoryOptions = [
  { title: 'Sales', value: 'sales' },
  { title: 'Expense', value: 'expense' },
]

// Table headers
const salesHeaders = [
  { title: 'Product', key: 'product', sortable: true },
  { title: 'Date of Sale', key: 'dateOfSale', sortable: true },
  { title: 'Unit Price (SAR)', key: 'unitPrice', sortable: true },
  { title: 'Quantity Sold', key: 'quantitySold', sortable: true },
  { title: 'Sale (SAR)', key: 'sale', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false },
]

const expenseHeaders = [
  { title: 'Expense Category', key: 'expenseCategory', sortable: true },
  { title: 'Date of Expense', key: 'dateOfExpense', sortable: true },
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

// Computed filtered data
const filteredSalesData = computed(() => {
  if (!searchQuery.value)
    return salesData.value

  return salesData.value.filter(item =>
    item.product.toLowerCase().includes(searchQuery.value.toLowerCase())
    || item.dateOfSale.includes(searchQuery.value),
  )
})

const filteredExpenseData = computed(() => {
  if (!searchQuery.value)
    return expenseData.value

  return expenseData.value.filter(item =>
    item.expenseCategory.toLowerCase().includes(searchQuery.value.toLowerCase())
    || item.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    || item.dateOfExpense.includes(searchQuery.value),
  )
})

const filteredProfitData = computed(() => {
  let data = filteredProfitByPeriod.value

  if (searchQuery.value) {
    data = data.filter(item =>
      item.date.includes(searchQuery.value),
    )
  }

  return data
})

// Filter options
const dateFilterOptions = [
  { title: 'Daily', value: 'daily' },
  { title: 'Monthly', value: 'monthly' },
  { title: 'Yearly', value: 'yearly' },
]

// Helper function to check if date is within filter period
const isDateInFilterPeriod = (dateString: string): boolean => {
  const itemDate = new Date(dateString)
  const now = new Date()

  if (dateFilter.value === 'daily') {
    return itemDate.toDateString() === now.toDateString()
  } else if (dateFilter.value === 'monthly') {
    return itemDate.getMonth() === now.getMonth() && itemDate.getFullYear() === now.getFullYear()
  } else if (dateFilter.value === 'yearly') {
    return itemDate.getFullYear() === now.getFullYear()
  }
  return true
}

// Filtered data by date period
const filteredSalesByPeriod = computed(() => {
  return salesData.value.filter(item => isDateInFilterPeriod(item.dateOfSale))
})

const filteredExpensesByPeriod = computed(() => {
  return expenseData.value.filter(item => isDateInFilterPeriod(item.dateOfExpense))
})

const filteredProfitByPeriod = computed(() => {
  return profitData.value.filter(item => isDateInFilterPeriod(item.date))
})

// Computed stat cards data (filtered by period)
const totalSales = computed(() => {
  return filteredSalesByPeriod.value.reduce((sum, item) => sum + item.sale, 0)
})

const totalExpenses = computed(() => {
  return filteredExpensesByPeriod.value.reduce((sum, item) => sum + item.amount, 0)
})

const totalProfit = computed(() => {
  return filteredProfitByPeriod.value.reduce((sum, item) => sum + item.profit, 0)
})

// Methods
const openAddDataModal = (category: string) => {
  selectedCategory.value = category
  isEditMode.value = false
  editItemId.value = ''
  resetForm()
  isAddDataModalVisible.value = true
}

const openEditModal = (item: any, category: string) => {
  selectedCategory.value = category
  isEditMode.value = true
  editItemId.value = item.id

  if (category === 'sales') {
    addDataForm.value = {
      product: item.product,
      date: item.dateOfSale,
      quantitySold: item.quantitySold,
      expenseCategory: '',
      amount: 0,
      description: '',
    }
  } else {
    addDataForm.value = {
      product: '',
      date: item.dateOfExpense,
      quantitySold: 0,
      expenseCategory: item.expenseCategory,
      amount: item.amount,
      description: item.description || '',
    }
  }

  isAddDataModalVisible.value = true
}

const resetForm = () => {
  addDataForm.value = {
    product: '',
    date: '',
    quantitySold: 0,
    expenseCategory: '',
    amount: 0,
    description: '',
  }
  isEditMode.value = false
  editItemId.value = ''
}

const saveData = async () => {
  // Validate quantity for sales
  if (selectedCategory.value === 'sales' && !isQuantityValid.value) {
    return
  }

  isLoading.value = true
  try {
    const payload: AddFinancialDataPayload = {
      category: selectedCategory.value as 'sales' | 'expense',
      date: addDataForm.value.date,
    }

    if (selectedCategory.value === 'sales') {
      payload.product = addDataForm.value.product
      payload.quantitySold = addDataForm.value.quantitySold
    }
    else {
      payload.expenseCategory = addDataForm.value.expenseCategory
      payload.amount = addDataForm.value.amount
      payload.description = addDataForm.value.description
    }

    let response
    if (isEditMode.value) {
      response = await franchiseeDashboardApi.updateFinancialData(editItemId.value, payload)
    } else {
      response = await franchiseeDashboardApi.addFinancialData(payload)
    }

    if (response.success) {
      // Reload data to get updated list and fresh stock quantities
      await loadFinancialData()
      await loadUnitProducts()
      isAddDataModalVisible.value = false
      resetForm()
      isEditMode.value = false
      editItemId.value = ''
    }
  }
  catch (error: any) {
    console.error('Error saving data:', error)
    // Handle specific error messages from backend
    if (error.response?.data?.message) {
      alert(error.response.data.message)
    }
  }
  finally {
    isLoading.value = false
  }
}

const exportData = () => {
  let dataToExport: any[] = []
  let filename = ''

  if (activeTab.value === 'sales') {
    dataToExport = selectedItems.value.length > 0
      ? salesData.value.filter(item => selectedItems.value.includes(item.id))
      : salesData.value
    filename = 'sales_data.csv'
  }
  else if (activeTab.value === 'expense') {
    dataToExport = selectedItems.value.length > 0
      ? expenseData.value.filter(item => selectedItems.value.includes(item.id))
      : expenseData.value
    filename = 'expense_data.csv'
  }
  else if (activeTab.value === 'profit') {
    dataToExport = selectedItems.value.length > 0
      ? profitData.value.filter(item => selectedItems.value.includes(item.id))
      : profitData.value
    filename = 'profit_data.csv'
  }

  // Simple CSV export logic (in real app, use a proper CSV library)
  console.log('Exporting data:', dataToExport, 'as', filename)
}

const deleteSelected = async () => {
  if (selectedItems.value.length === 0)
    return

  isLoading.value = true
  try {
    const response = await franchiseeDashboardApi.deleteFinancialData({
      category: activeTab.value as 'sales' | 'expense' | 'profit',
      ids: selectedItems.value,
    })

    if (response.success) {
      // Reload data to get updated list
      await loadFinancialData()
      selectedItems.value = []
    }
  }
  catch (error) {
    console.error('Error deleting data:', error)
  }
  finally {
    isLoading.value = false
  }
}

const handleImport = () => {
  // Import logic would go here
  console.log('Importing data for category:', importCategory.value)
  isImportModalVisible.value = false
}
</script>

<template>
  <section>
    <!-- Page Header -->
    <VRow class="mb-6">
      <VCol cols="12">
        <div class="d-flex justify-space-between align-center flex-wrap gap-4">
          <div>
            <h4 class="text-h4 mb-1">
              Financial Overview
            </h4>
            <p class="text-body-1 text-medium-emphasis">
              Track your franchise financial performance and metrics
            </p>
          </div>

          <!-- Header Actions -->
          <div class="d-flex gap-3 align-center flex-wrap">
            <VBtn color="primary" variant="outlined" prepend-icon="tabler-upload" @click="isImportModalVisible = true">
              Import
            </VBtn>
            <VBtn color="primary" prepend-icon="tabler-plus" @click="openAddDataModal(activeTab)">
              Add Data
            </VBtn>
            <VSelect v-model="dateFilter" :items="dateFilterOptions" item-title="title" item-value="value"
              density='comfortable' style="min-width: 120px;" variant="outlined" />
          </div>
        </div>
      </VCol>
    </VRow>

    <!-- Stat Cards -->
    <VRow class="mb-6">
      <!-- Total Sales -->
      <VCol cols="12" md="4">
        <VCard>
          <VCardText class="d-flex align-center">
            <VAvatar size="44" rounded color="primary" variant="tonal" class="me-4">
              <VIcon size="24" icon="tabler-currency-riyal" />
            </VAvatar>
            <div>
              <span class="text-sm text-medium-emphasis">Total Sales</span>
              <div class="text-h6 font-weight-medium">
                {{ totalSales.toLocaleString() }} SAR
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Total Expenses -->
      <VCol cols="12" md="4">
        <VCard>
          <VCardText class="d-flex align-center">
            <VAvatar size="44" rounded color="error" variant="tonal" class="me-4">
              <VIcon size="24" icon="tabler-trending-down" />
            </VAvatar>
            <div>
              <span class="text-sm text-medium-emphasis">Total Expenses</span>
              <div class="text-h6 font-weight-medium">
                {{ totalExpenses.toLocaleString() }} SAR
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Total Profit -->
      <VCol cols="12" md="4">
        <VCard>
          <VCardText class="d-flex align-center">
            <VAvatar size="44" rounded color="success" variant="tonal" class="me-4">
              <VIcon size="24" icon="tabler-trending-up" />
            </VAvatar>
            <div>
              <span class="text-sm text-medium-emphasis">Total Profit</span>
              <div class="text-h6 font-weight-medium">
                {{ totalProfit.toLocaleString() }} SAR
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Financial Data Tabs -->
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardText>
            <VTabs v-model="activeTab" color="primary" class="mb-4">
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

            <!-- Search and Actions Bar -->
            <div class="d-flex justify-space-between align-center mb-4">
              <VTextField v-model="searchQuery" placeholder="Search..." prepend-inner-icon="tabler-search"
                variant="outlined" density="compact" style="max-width: 300px;" clearable />
              <div class="d-flex gap-2">
                <VBtn v-if="selectedItems.length > 0" color="primary" variant="outlined" prepend-icon="tabler-download"
                  @click="exportData">
                  Export Selected ({{ selectedItems.length }})
                </VBtn>
                <VBtn v-if="selectedItems.length > 0" color="error" variant="outlined" prepend-icon="tabler-trash"
                  @click="deleteSelected">
                  Delete Selected ({{ selectedItems.length }})
                </VBtn>
                <VBtn v-if="selectedItems.length === 0" color="primary" variant="outlined"
                  prepend-icon="tabler-download" @click="exportData">
                  Export All
                </VBtn>
              </div>
            </div>

            <!-- Tab Content -->
            <VTabsWindow v-model="activeTab">
              <!-- Sales Tab -->
              <VTabsWindowItem value="sales">
                <VDataTable v-model="selectedItems" :headers="salesHeaders" :items="filteredSalesData"
                  :items-per-page="10" show-select class="text-no-wrap" item-value="id">
                  <template #item.unitPrice="{ item }">
                    <span class="font-weight-medium">
                      {{ Number(item.unitPrice).toFixed(2) }} SAR
                    </span>
                  </template>
                  <template #item.sale="{ item }">
                    <span class="text-success font-weight-medium">
                      {{ Number(item.sale).toFixed(2) }} SAR
                    </span>
                  </template>
                  <template #item.actions="{ item }">
                    <VBtn icon="tabler-edit" variant="text" color="primary" size="small"
                      @click="openEditModal(item, 'sales')" />
                  </template>
                </VDataTable>
              </VTabsWindowItem>

              <!-- Expense Tab -->
              <VTabsWindowItem value="expense">
                <VDataTable v-model="selectedItems" :headers="expenseHeaders" :items="filteredExpenseData"
                  :items-per-page="10" show-select class="text-no-wrap" item-value="id">
                  <template #item.amount="{ item }">
                    <span class="text-error font-weight-medium">
                      {{ Number(item.amount).toFixed(2) }} SAR
                    </span>
                  </template>
                  <template #item.expenseCategory="{ item }">
                    <VChip size="small" variant="tonal" color="primary">
                      {{ item.expenseCategory }}
                    </VChip>
                  </template>
                  <template #item.actions="{ item }">
                    <VBtn icon="tabler-edit" variant="text" color="primary" size="small"
                      @click="openEditModal(item, 'expense')" />
                  </template>
                </VDataTable>
              </VTabsWindowItem>

              <!-- Profit Tab -->
              <VTabsWindowItem value="profit">
                <VDataTable v-model="selectedItems" :headers="profitHeaders" :items="filteredProfitData"
                  :items-per-page="10" show-select class="text-no-wrap" item-value="id">
                  <template #item.totalSales="{ item }">
                    <span class="font-weight-medium">
                      {{ Number(item.totalSales).toFixed(2) }} SAR
                    </span>
                  </template>
                  <template #item.totalExpenses="{ item }">
                    <span class="text-error">
                      {{ Number(item.totalExpenses).toFixed(2) }} SAR
                    </span>
                  </template>
                  <template #item.profit="{ item }">
                    <span class="text-success font-weight-medium">
                      {{ Number(item.profit).toFixed(2) }} SAR
                    </span>
                  </template>
                </VDataTable>
              </VTabsWindowItem>
            </VTabsWindow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </section>

  <!-- Add Data Modal -->
  <VDialog v-model="isAddDataModalVisible" max-width="500">
    <VCard>
      <VCardTitle class="text-h6 font-weight-medium">
        {{ isEditMode ? 'Edit' : 'Add' }} {{ selectedCategory === 'sales' ? 'Sales' : 'Expense' }} Data
      </VCardTitle>
      <VCardText>
        <VForm @submit.prevent="saveData">
          <VRow>
            <!-- Sales Fields -->
            <template v-if="selectedCategory === 'sales'">
              <VCol cols="12">
                <VSelect v-model="addDataForm.product" :items="productOptions" label="Product" variant="outlined"
                  item-title="title" item-value="value" :loading="unitProducts.length === 0"
                  :disabled="unitProducts.length === 0"
                  :hint="unitProducts.length === 0 ? 'No products available in stock' : ''" persistent-hint required />
              </VCol>
              <VCol cols="12">
                <VTextField v-model="addDataForm.date" label="Date" type="date" variant="outlined" required />
              </VCol>
              <VCol cols="12">
                <VTextField v-model.number="addDataForm.quantitySold" label="Quantity Sold" type="number"
                  variant="outlined" required :min="1" :max="selectedProductStock" :error="!!quantityErrorMessage"
                  :error-messages="quantityErrorMessage"
                  :hint="selectedProductStock > 0 ? `Available stock: ${selectedProductStock} units` : ''"
                  persistent-hint :disabled="!addDataForm.product" />
              </VCol>
            </template>

            <!-- Expense Fields -->
            <template v-else>
              <VCol cols="12">
                <VSelect v-model="addDataForm.expenseCategory" :items="expenseCategoryOptions" label="Expense Category"
                  variant="outlined" required />
              </VCol>
              <VCol cols="12">
                <VTextField v-model="addDataForm.date" label="Date" type="date" variant="outlined" required />
              </VCol>
              <VCol cols="12">
                <VTextField v-model.number="addDataForm.amount" label="Amount (SAR)" type="number" step="0.01"
                  variant="outlined" required />
              </VCol>
              <VCol cols="12">
                <VTextarea v-model="addDataForm.description" label="Note/Description" variant="outlined" rows="3" />
              </VCol>
            </template>
          </VRow>
        </VForm>
      </VCardText>
      <VCardActions class="d-flex justify-end gap-2 pa-4">
        <VBtn variant="outlined" @click="isAddDataModalVisible = false">
          Cancel
        </VBtn>
        <VBtn color="primary" @click="saveData" :disabled="selectedCategory === 'sales' && !isQuantityValid"
          :loading="isLoading">
          Save
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- Import Modal -->
  <VDialog v-model="isImportModalVisible" max-width="400">
    <VCard>
      <VCardTitle class="text-h6 font-weight-medium">
        Import Data
      </VCardTitle>
      <VCardText>
        <VSelect v-model="importCategory" :items="importCategoryOptions" label="Select Category" variant="outlined"
          class="mb-4" />
        <VFileInput label="Choose CSV file" variant="outlined" accept=".csv" prepend-icon="tabler-file-upload" />
      </VCardText>
      <VCardActions class="d-flex justify-end gap-2 pa-4">
        <VBtn variant="outlined" @click="isImportModalVisible = false">
          Cancel
        </VBtn>
        <VBtn color="primary" @click="handleImport">
          Import
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
