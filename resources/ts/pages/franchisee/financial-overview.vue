<script setup lang="ts">
import { computed, ref } from 'vue'

// Type definitions
interface SalesRecord {
  id: string
  product: string
  dateOfSale: string
  unitPrice: number
  quantitySold: number
  sale: number
}

interface ExpenseRecord {
  id: string
  expenseCategory: string
  dateOfExpense: string
  amount: number
  description: string
}

interface ProfitRecord {
  id: string
  date: string
  totalSales: number
  totalExpenses: number
  profit: number
}

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

// Form data
const addDataForm = ref<AddDataForm>({
  product: '',
  date: '',
  quantitySold: 0,
  expenseCategory: '',
  amount: 0,
  description: '',
})

// Mock data
const salesData = ref<SalesRecord[]>([
  {
    id: '1',
    product: 'Burger Deluxe',
    dateOfSale: '2024-01-15',
    unitPrice: 25.50,
    quantitySold: 45,
    sale: 1147.50,
  },
  {
    id: '2',
    product: 'Chicken Wings',
    dateOfSale: '2024-01-15',
    unitPrice: 18.00,
    quantitySold: 32,
    sale: 576.00,
  },
  {
    id: '3',
    product: 'Caesar Salad',
    dateOfSale: '2024-01-14',
    unitPrice: 15.75,
    quantitySold: 28,
    sale: 441.00,
  },
  {
    id: '4',
    product: 'Fish & Chips',
    dateOfSale: '2024-01-14',
    unitPrice: 22.00,
    quantitySold: 19,
    sale: 418.00,
  },
  {
    id: '5',
    product: 'Pasta Carbonara',
    dateOfSale: '2024-01-13',
    unitPrice: 20.25,
    quantitySold: 35,
    sale: 708.75,
  },
])

const expenseData = ref<ExpenseRecord[]>([
  {
    id: '1',
    expenseCategory: 'Food Supplies',
    dateOfExpense: '2024-01-15',
    amount: 1250.00,
    description: 'Weekly food inventory purchase',
  },
  {
    id: '2',
    expenseCategory: 'Utilities',
    dateOfExpense: '2024-01-14',
    amount: 450.75,
    description: 'Electricity and water bills',
  },
  {
    id: '3',
    expenseCategory: 'Staff Wages',
    dateOfExpense: '2024-01-13',
    amount: 2800.00,
    description: 'Weekly staff payment',
  },
  {
    id: '4',
    expenseCategory: 'Marketing',
    dateOfExpense: '2024-01-12',
    amount: 350.00,
    description: 'Social media advertising campaign',
  },
  {
    id: '5',
    expenseCategory: 'Equipment Maintenance',
    dateOfExpense: '2024-01-11',
    amount: 180.50,
    description: 'Kitchen equipment servicing',
  },
])

const profitData = ref<ProfitRecord[]>([
  {
    id: '1',
    date: '2024-01-15',
    totalSales: 3291.25,
    totalExpenses: 1700.75,
    profit: 1590.50,
  },
  {
    id: '2',
    date: '2024-01-14',
    totalSales: 2859.00,
    totalExpenses: 1450.75,
    profit: 1408.25,
  },
  {
    id: '3',
    date: '2024-01-13',
    totalSales: 3508.75,
    totalExpenses: 2980.00,
    profit: 528.75,
  },
  {
    id: '4',
    date: '2024-01-12',
    totalSales: 2750.50,
    totalExpenses: 1350.00,
    profit: 1400.50,
  },
  {
    id: '5',
    date: '2024-01-11',
    totalSales: 2980.25,
    totalExpenses: 1680.50,
    profit: 1299.75,
  },
])

// Product options for sales form
const productOptions = [
  'Burger Deluxe',
  'Chicken Wings',
  'Caesar Salad',
  'Fish & Chips',
  'Pasta Carbonara',
  'Grilled Salmon',
  'Beef Steak',
  'Vegetarian Pizza',
  'Chicken Sandwich',
  'Greek Salad',
]

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
]

const expenseHeaders = [
  { title: 'Expense Category', key: 'expenseCategory', sortable: true },
  { title: 'Date of Expense', key: 'dateOfExpense', sortable: true },
  { title: 'Amount (SAR)', key: 'amount', sortable: true },
  { title: 'Description', key: 'description', sortable: true },
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
  if (!searchQuery.value)
    return profitData.value

  return profitData.value.filter(item =>
    item.date.includes(searchQuery.value),
  )
})

// Computed stat cards data
const totalSales = computed(() => {
  return salesData.value.reduce((sum, item) => sum + item.sale, 0)
})

const totalExpenses = computed(() => {
  return expenseData.value.reduce((sum, item) => sum + item.amount, 0)
})

const totalProfit = computed(() => {
  return profitData.value.reduce((sum, item) => sum + item.profit, 0)
})

// Methods
const openAddDataModal = (category: string) => {
  selectedCategory.value = category
  resetForm()
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
}

const saveData = () => {
  if (selectedCategory.value === 'sales') {
    const unitPrice = getProductPrice(addDataForm.value.product)

    const newSale: SalesRecord = {
      id: Date.now().toString(),
      product: addDataForm.value.product,
      dateOfSale: addDataForm.value.date,
      unitPrice,
      quantitySold: addDataForm.value.quantitySold,
      sale: unitPrice * addDataForm.value.quantitySold,
    }

    salesData.value.unshift(newSale)
  }
  else if (selectedCategory.value === 'expense') {
    const newExpense: ExpenseRecord = {
      id: Date.now().toString(),
      expenseCategory: addDataForm.value.expenseCategory,
      dateOfExpense: addDataForm.value.date,
      amount: addDataForm.value.amount,
      description: addDataForm.value.description,
    }

    expenseData.value.unshift(newExpense)
  }

  isAddDataModalVisible.value = false
  resetForm()
}

const getProductPrice = (product: string): number => {
  const prices: { [key: string]: number } = {
    'Burger Deluxe': 25.50,
    'Chicken Wings': 18.00,
    'Caesar Salad': 15.75,
    'Fish & Chips': 22.00,
    'Pasta Carbonara': 20.25,
    'Grilled Salmon': 28.00,
    'Beef Steak': 35.00,
    'Vegetarian Pizza': 19.50,
    'Chicken Sandwich': 16.75,
    'Greek Salad': 14.25,
  }

  return prices[product] || 0
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

const deleteSelected = () => {
  if (activeTab.value === 'sales')
    salesData.value = salesData.value.filter(item => !selectedItems.value.includes(item.id))
  else if (activeTab.value === 'expense')
    expenseData.value = expenseData.value.filter(item => !selectedItems.value.includes(item.id))
  else if (activeTab.value === 'profit')
    profitData.value = profitData.value.filter(item => !selectedItems.value.includes(item.id))

  selectedItems.value = []
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
            <VBtn
              color="primary"
              variant="outlined"
              prepend-icon="tabler-upload"
              @click="isImportModalVisible = true"
            >
              Import
            </VBtn>
            <VBtn
              color="primary"
              prepend-icon="tabler-plus"
              @click="openAddDataModal(activeTab)"
            >
              Add Data
            </VBtn>
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
          <VCardText class="d-flex align-center">
            <VAvatar
              size="44"
              rounded
              color="primary"
              variant="tonal"
              class="me-4"
            >
              <VIcon
                size="24"
                icon="tabler-currency-dollar"
              />
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
      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardText class="d-flex align-center">
            <VAvatar
              size="44"
              rounded
              color="error"
              variant="tonal"
              class="me-4"
            >
              <VIcon
                size="24"
                icon="tabler-trending-down"
              />
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
      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardText class="d-flex align-center">
            <VAvatar
              size="44"
              rounded
              color="success"
              variant="tonal"
              class="me-4"
            >
              <VIcon
                size="24"
                icon="tabler-trending-up"
              />
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
            <VTabs
              v-model="activeTab"
              color="primary"
              class="mb-4"
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

            <!-- Search and Actions Bar -->
            <div class="d-flex justify-space-between align-center mb-4">
              <VTextField
                v-model="searchQuery"
                placeholder="Search..."
                prepend-inner-icon="tabler-search"
                variant="outlined"
                density="compact"
                style="max-width: 300px;"
                clearable
              />
              <div class="d-flex gap-2">
                <VBtn
                  v-if="selectedItems.length > 0"
                  color="primary"
                  variant="outlined"
                  prepend-icon="tabler-download"
                  @click="exportData"
                >
                  Export Selected ({{ selectedItems.length }})
                </VBtn>
                <VBtn
                  v-if="selectedItems.length > 0"
                  color="error"
                  variant="outlined"
                  prepend-icon="tabler-trash"
                  @click="deleteSelected"
                >
                  Delete Selected ({{ selectedItems.length }})
                </VBtn>
                <VBtn
                  v-if="selectedItems.length === 0"
                  color="primary"
                  variant="outlined"
                  prepend-icon="tabler-download"
                  @click="exportData"
                >
                  Export All
                </VBtn>
              </div>
            </div>

            <!-- Tab Content -->
            <VTabsWindow v-model="activeTab">
              <!-- Sales Tab -->
              <VTabsWindowItem value="sales">
                <VDataTable
                  v-model="selectedItems"
                  :headers="salesHeaders"
                  :items="filteredSalesData"
                  :items-per-page="10"
                  show-select
                  class="text-no-wrap"
                  item-value="id"
                >
                  <template #item.unitPrice="{ item }">
                    <span class="font-weight-medium">
                      {{ item.unitPrice.toFixed(2) }} SAR
                    </span>
                  </template>
                  <template #item.sale="{ item }">
                    <span class="text-success font-weight-medium">
                      {{ item.sale.toFixed(2) }} SAR
                    </span>
                  </template>
                </VDataTable>
              </VTabsWindowItem>

              <!-- Expense Tab -->
              <VTabsWindowItem value="expense">
                <VDataTable
                  v-model="selectedItems"
                  :headers="expenseHeaders"
                  :items="filteredExpenseData"
                  :items-per-page="10"
                  show-select
                  class="text-no-wrap"
                  item-value="id"
                >
                  <template #item.amount="{ item }">
                    <span class="text-error font-weight-medium">
                      {{ item.amount.toFixed(2) }} SAR
                    </span>
                  </template>
                  <template #item.expenseCategory="{ item }">
                    <VChip
                      size="small"
                      variant="tonal"
                      color="primary"
                    >
                      {{ item.expenseCategory }}
                    </VChip>
                  </template>
                </VDataTable>
              </VTabsWindowItem>

              <!-- Profit Tab -->
              <VTabsWindowItem value="profit">
                <VDataTable
                  v-model="selectedItems"
                  :headers="profitHeaders"
                  :items="filteredProfitData"
                  :items-per-page="10"
                  show-select
                  class="text-no-wrap"
                  item-value="id"
                >
                  <template #item.totalSales="{ item }">
                    <span class="font-weight-medium">
                      {{ item.totalSales.toFixed(2) }} SAR
                    </span>
                  </template>
                  <template #item.totalExpenses="{ item }">
                    <span class="text-error">
                      {{ item.totalExpenses.toFixed(2) }} SAR
                    </span>
                  </template>
                  <template #item.profit="{ item }">
                    <span class="text-success font-weight-medium">
                      {{ item.profit.toFixed(2) }} SAR
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
  <VDialog
    v-model="isAddDataModalVisible"
    max-width="500"
  >
    <VCard>
      <VCardTitle class="text-h6 font-weight-medium">
        Add {{ selectedCategory === 'sales' ? 'Sales' : 'Expense' }} Data
      </VCardTitle>
      <VCardText>
        <VForm @submit.prevent="saveData">
          <VRow>
            <!-- Sales Fields -->
            <template v-if="selectedCategory === 'sales'">
              <VCol cols="12">
                <VSelect
                  v-model="addDataForm.product"
                  :items="productOptions"
                  label="Product"
                  variant="outlined"
                  required
                />
              </VCol>
              <VCol cols="12">
                <VTextField
                  v-model="addDataForm.date"
                  label="Date"
                  type="date"
                  variant="outlined"
                  required
                />
              </VCol>
              <VCol cols="12">
                <VTextField
                  v-model.number="addDataForm.quantitySold"
                  label="Quantity Sold"
                  type="number"
                  variant="outlined"
                  required
                />
              </VCol>
            </template>

            <!-- Expense Fields -->
            <template v-else>
              <VCol cols="12">
                <VSelect
                  v-model="addDataForm.expenseCategory"
                  :items="expenseCategoryOptions"
                  label="Expense Category"
                  variant="outlined"
                  required
                />
              </VCol>
              <VCol cols="12">
                <VTextField
                  v-model="addDataForm.date"
                  label="Date"
                  type="date"
                  variant="outlined"
                  required
                />
              </VCol>
              <VCol cols="12">
                <VTextField
                  v-model.number="addDataForm.amount"
                  label="Amount (SAR)"
                  type="number"
                  step="0.01"
                  variant="outlined"
                  required
                />
              </VCol>
              <VCol cols="12">
                <VTextarea
                  v-model="addDataForm.description"
                  label="Note/Description"
                  variant="outlined"
                  rows="3"
                />
              </VCol>
            </template>
          </VRow>
        </VForm>
      </VCardText>
      <VCardActions class="d-flex justify-end gap-2 pa-4">
        <VBtn
          variant="outlined"
          @click="isAddDataModalVisible = false"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          @click="saveData"
        >
          Save
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- Import Modal -->
  <VDialog
    v-model="isImportModalVisible"
    max-width="400"
  >
    <VCard>
      <VCardTitle class="text-h6 font-weight-medium">
        Import Data
      </VCardTitle>
      <VCardText>
        <VSelect
          v-model="importCategory"
          :items="importCategoryOptions"
          label="Select Category"
          variant="outlined"
          class="mb-4"
        />
        <VFileInput
          label="Choose CSV file"
          variant="outlined"
          accept=".csv"
          prepend-icon="tabler-file-upload"
        />
      </VCardText>
      <VCardActions class="d-flex justify-end gap-2 pa-4">
        <VBtn
          variant="outlined"
          @click="isImportModalVisible = false"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          @click="handleImport"
        >
          Import
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
