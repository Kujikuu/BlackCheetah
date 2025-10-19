<script setup lang="ts">
import { SaudiRiyal } from 'lucide-vue-next'
import { computed, onMounted, ref } from 'vue'
import { type PaymentData, type RoyaltyRecord, type RoyaltyStatistics, royaltyApi } from '@/services/api/royalty'
import { $api } from '@/utils/api'

// Loading states
const isLoading = ref(false)
const isLoadingStats = ref(false)
const isCreating = ref(false)

// Reactive data
const selectedPeriod = ref<'monthly' | 'quarterly' | 'annual' | 'special'>('monthly')
const isExportDialogVisible = ref(false)
const isMarkCompletedModalVisible = ref(false)
const isViewRoyaltyDialogVisible = ref(false)
const isCreateRoyaltyModalVisible = ref(false)
const selectedRoyalty = ref<RoyaltyRecord | null>(null)
const viewedRoyalty = ref<RoyaltyRecord | null>(null)
const exportFormat = ref('csv')
const exportDataType = ref('all')

// Create royalty form data
const createRoyaltyData = ref({
  franchise_id: null as number | null,
  unit_id: null as number | null,
  franchisee_id: null as number | null,
  type: 'monthly' as 'monthly' | 'quarterly',
  period_year: new Date().getFullYear(),
  period_month: new Date().getMonth() + 1,
  period_quarter: null,
  base_revenue: 0,
  royalty_rate: 8.5,
  royalty_amount: 0,
  marketing_fee_rate: 2.0,
  marketing_fee_amount: 0,
  technology_fee_rate: 0,
  technology_fee_amount: 50,
  other_fees: 0,
  adjustments: 0,
  total_amount: 0,
  due_date: '',
  status: 'pending' as 'pending' | 'paid' | 'overdue',
  description: '',
  notes: '',
  is_auto_generated: false,
})

// Dropdown options
const franchises = ref([])
const units = ref([])
const franchisees = ref([])

// Payment form data
const paymentData = ref<PaymentData>({
  amount_paid: 0,
  payment_date: '',
  payment_type: '',
  attachment: null,
})

// Data from API
const royaltyRecords = ref<RoyaltyRecord[]>([])

const statistics = ref<RoyaltyStatistics>({
  royalty_collected_till_date: 0,
  upcoming_royalties: 0,
  total_royalties: 0,
  pending_amount: 0,
  paid_amount: 0,
  overdue_amount: 0,
  overdue_count: 0,
})

// Computed values for stat cards
const royaltyCollectedTillDate = computed(() => {
  const value = statistics.value?.royalty_collected_till_date
  return value ? Number(value) : 0
})

const upcomingRoyalties = computed(() => {
  const value = statistics.value?.upcoming_royalties
  return value ? Number(value) : 0
})

// Period options
const periodOptions = [
  { title: 'Monthly', value: 'monthly' },
  { title: 'Quarterly', value: 'quarterly' },
  { title: 'Annual', value: 'annual' },
  { title: 'Special', value: 'special' },
]

// Export options
const exportFormatOptions = [
  { title: 'CSV', value: 'csv' },
  { title: 'Excel', value: 'excel' },
]

const exportDataTypeOptions = [
  { title: 'All Royalties', value: 'all' },
  { title: 'Paid Only', value: 'paid' },
  { title: 'Pending Only', value: 'pending' },
  { title: 'Overdue Only', value: 'overdue' },
]

// Payment type options
const paymentTypeOptions = [
  { title: 'Bank Transfer', value: 'bank_transfer' },
  { title: 'Credit Card', value: 'credit_card' },
  { title: 'Mada', value: 'mada' },
  { title: 'STC Pay', value: 'stc_pay' },
  { title: 'SADAD', value: 'sadad' },
  { title: 'Check', value: 'check' },
  { title: 'Cash', value: 'cash' },
  { title: 'Online Payment', value: 'online_payment' },
]

// Table headers
const tableHeaders = [
  { title: 'Billing Period', key: 'billingPeriod', sortable: true },
  { title: 'Franchisee Name', key: 'franchiseeName', sortable: true },
  { title: 'Store Location', key: 'storeLocation', sortable: true },
  { title: 'Due Date', key: 'dueDate', sortable: true },
  { title: 'Gross Sales (SAR)', key: 'grossSales', sortable: true },
  { title: 'Royalty %', key: 'royaltyPercentage', sortable: true },
  { title: 'Amount (SAR)', key: 'amount', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false },
]

// API Functions
const fetchRoyalties = async () => {
  try {
    isLoading.value = true

    const response = await royaltyApi.getRoyalties({
      period: selectedPeriod.value,
    })

    royaltyRecords.value = response.data.data
  }
  catch (error) {

  }
  finally {
    isLoading.value = false
  }
}

const fetchStatistics = async () => {
  try {
    isLoadingStats.value = true

    const response = await royaltyApi.getStatistics({
      period: selectedPeriod.value,
    })

    statistics.value = response.data
  }
  catch (error) {

  }
  finally {
    isLoadingStats.value = false
  }
}

// Functions
const openExportDialog = () => {
  isExportDialogVisible.value = true
}

const performExport = async () => {
  try {
    const blob = await royaltyApi.exportRoyalties({
      format: exportFormat.value as 'csv' | 'excel',
      data_type: exportDataType.value as 'all' | 'paid' | 'pending' | 'overdue',
      period: selectedPeriod.value as 'daily' | 'monthly' | 'yearly',
    })

    // Create download link
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')

    link.href = url
    link.download = `royalty-data-${selectedPeriod.value}-${new Date().toISOString().split('T')[0]}.${exportFormat.value === 'csv' ? 'csv' : 'xlsx'}`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)

    isExportDialogVisible.value = false
  }
  catch (error) {

  }
}

const viewRoyalty = (royalty: RoyaltyRecord) => {
  viewedRoyalty.value = royalty
  isViewRoyaltyDialogVisible.value = true
}

const markAsCompleted = (royalty: RoyaltyRecord) => {
  selectedRoyalty.value = royalty
  paymentData.value = {
    amount_paid: royalty.amount,
    payment_date: new Date().toISOString().split('T')[0],
    payment_type: '',
    attachment: null,
  }
  isMarkCompletedModalVisible.value = true
}

const submitPayment = async () => {
  if (!selectedRoyalty.value)
    return

  try {
    await royaltyApi.markAsPaid(selectedRoyalty.value.id, paymentData.value)

    // Refresh data
    await fetchRoyalties()
    await fetchStatistics()

    // Reset form and close modal
    isMarkCompletedModalVisible.value = false
    selectedRoyalty.value = null
    paymentData.value = {
      amount_paid: 0,
      payment_date: '',
      payment_type: '',
      attachment: null,
    }
  }
  catch (error) {

  }
}

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0])
    paymentData.value.attachment = target.files[0]
}

const getStatusColor = (status: string) => {
  switch (status) {
    case 'paid': return 'success'
    case 'pending': return 'warning'
    case 'overdue': return 'error'
    default: return 'default'
  }
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

// Fetch dropdown data
const fetchFranchises = async () => {
  try {
    const response = await $api('/api/v1/franchisor/franchise')

    if (response.success && response.data) {
      // myFranchise returns a single franchise object, so wrap it in an array
      franchises.value = [response.data]
    }
  }
  catch (error) {

  }
}

const fetchUnits = async () => {
  try {
    const response = await $api('/api/v1/franchisor/units')

    if (response.success && response.data) {
      // myUnits returns paginated data, extract the actual units from data.data
      units.value = response.data.data || []
    }
  }
  catch (error) {

  }
}

const fetchFranchisees = async () => {
  try {
    const response = await $api('/api/v1/franchisor/franchisees')

    if (response.success && response.data) {
      // myFranchisees returns paginated data, extract the actual franchisees from data.data
      franchisees.value = response.data.data || []
    }
  }
  catch (error) {

  }
}

// Calculate amounts based on base revenue
const calculateAmounts = () => {
  const baseRevenue = createRoyaltyData.value.base_revenue

  // Calculate royalty amount
  createRoyaltyData.value.royalty_amount = (baseRevenue * createRoyaltyData.value.royalty_rate) / 100

  // Calculate marketing fee amount
  createRoyaltyData.value.marketing_fee_amount = (baseRevenue * createRoyaltyData.value.marketing_fee_rate) / 100

  // Calculate technology fee amount (if rate-based)
  if (createRoyaltyData.value.technology_fee_rate > 0)
    createRoyaltyData.value.technology_fee_amount = (baseRevenue * createRoyaltyData.value.technology_fee_rate) / 100

  // Calculate total amount
  createRoyaltyData.value.total_amount
    = createRoyaltyData.value.royalty_amount
    + createRoyaltyData.value.marketing_fee_amount
    + createRoyaltyData.value.technology_fee_amount
    + createRoyaltyData.value.other_fees
    + createRoyaltyData.value.adjustments
}

// Create royalty function
const createRoyalty = async () => {
  try {
    isCreating.value = true

    // Clear previous validation errors
    clearValidationErrors()

    // Validate form before submission
    const isValid = await validateForm()
    if (!isValid)

      return

    // Calculate amounts before submitting
    calculateAmounts()

    // Prepare data for API - convert null to undefined for optional fields
    const apiData = {
      ...createRoyaltyData.value,
      franchise_id: createRoyaltyData.value.franchise_id || undefined,
      unit_id: createRoyaltyData.value.unit_id || undefined,
      franchisee_id: createRoyaltyData.value.franchisee_id || undefined,
    }

    const response = await royaltyApi.createRoyalty(apiData)

    if (response.success) {
      // Reset form
      resetCreateForm()

      // Close modal
      isCreateRoyaltyModalVisible.value = false

      // Refresh data
      await fetchRoyalties()
      await fetchStatistics()

      // Show success message (you might want to add a toast notification here)
    }
  }
  catch (error) {

    // Handle error (you might want to add error notification here)
  }
  finally {
    isCreating.value = false
  }
}

// Reset create form
const resetCreateForm = () => {
  createRoyaltyData.value = {
    franchise_id: null,
    unit_id: null,
    franchisee_id: null,
    type: 'monthly' as 'monthly' | 'quarterly',
    period_year: new Date().getFullYear(),
    period_month: new Date().getMonth() + 1,
    period_quarter: null,
    base_revenue: 0,
    royalty_rate: 8.5,
    royalty_amount: 0,
    marketing_fee_rate: 2.0,
    marketing_fee_amount: 0,
    technology_fee_rate: 0,
    technology_fee_amount: 50,
    other_fees: 0,
    adjustments: 0,
    total_amount: 0,
    due_date: '',
    status: 'pending' as 'pending' | 'paid' | 'overdue',
    description: '',
    notes: '',
    is_auto_generated: false,
  }
}

// Open create royalty modal
const openCreateRoyaltyModal = () => {
  resetCreateForm()
  isCreateRoyaltyModalVisible.value = true
}

// Lifecycle hooks
onMounted(() => {
  fetchRoyalties()
  fetchStatistics()
  fetchFranchises()
  fetchUnits()
  fetchFranchisees()
})

// Validation errors
const validationErrors = ref<Record<string, string[]>>({})

// Validation rules
const validationRules = {
  franchise_id: [
    (v: any) => !!v || 'Franchise selection is required',
  ],
  franchisee_id: [
    (v: any) => !!v || 'Franchisee selection is required',
  ],
  type: [
    (v: any) => !!v || 'Royalty type is required',
    (v: any) => ['monthly', 'quarterly'].includes(v) || 'Invalid royalty type',
  ],
  period_year: [
    (v: any) => !!v || 'Period year is required',
    (v: any) => v >= 2020 || 'Period year must be at least 2020',
    (v: any) => v <= new Date().getFullYear() + 1 || 'Period year cannot be more than next year',
  ],
  period_month: [
    (v: any) => {
      if (createRoyaltyData.value.type === 'monthly')
        return !!v || 'Period month is required for monthly royalties'

      return true
    },
    (v: any) => {
      if (v && (v < 1 || v > 12))
        return 'Period month must be between 1 and 12'

      return true
    },
  ],
  period_quarter: [
    (v: any) => {
      if (createRoyaltyData.value.type === 'quarterly')
        return !!v || 'Period quarter is required for quarterly royalties'

      return true
    },
    (v: any) => {
      if (v && (v < 1 || v > 4))
        return 'Period quarter must be between 1 and 4'

      return true
    },
  ],
  base_revenue: [
    (v: any) => v !== null && v !== undefined && v !== '' || 'Base revenue is required',
    (v: any) => v >= 0 || 'Base revenue cannot be negative',
    (v: any) => v <= 999999999.99 || 'Base revenue is too large',
  ],
  royalty_rate: [
    (v: any) => v !== null && v !== undefined && v !== '' || 'Royalty rate is required',
    (v: any) => v >= 0 || 'Royalty rate cannot be negative',
    (v: any) => v <= 100 || 'Royalty rate cannot exceed 100%',
  ],
  marketing_fee_rate: [
    (v: any) => v !== null && v !== undefined && v !== '' || 'Marketing fee rate is required',
    (v: any) => v >= 0 || 'Marketing fee rate cannot be negative',
    (v: any) => v <= 100 || 'Marketing fee rate cannot exceed 100%',
  ],
  due_date: [
    (v: any) => !!v || 'Due date is required',
    (v: any) => {
      if (v) {
        const today = new Date().toISOString().split('T')[0]

        return v >= today || 'Due date cannot be in the past'
      }

      return true
    },
  ],
  technology_fee_amount: [
    (v: any) => v === null || v === undefined || v === '' || v >= 0 || 'Technology fee cannot be negative',
    (v: any) => v === null || v === undefined || v === '' || v <= 999999999.99 || 'Technology fee is too large',
  ],
  other_fees: [
    (v: any) => v === null || v === undefined || v === '' || v >= 0 || 'Other fees cannot be negative',
    (v: any) => v === null || v === undefined || v === '' || v <= 999999999.99 || 'Other fees is too large',
  ],
  adjustments: [
    (v: any) => v === null || v === undefined || v === '' || v >= -999999999.99 || 'Adjustments value is too low',
    (v: any) => v === null || v === undefined || v === '' || v <= 999999999.99 || 'Adjustments value is too high',
  ],
  description: [
    (v: any) => !v || v.length <= 1000 || 'Description cannot exceed 1000 characters',
  ],
  notes: [
    (v: any) => !v || v.length <= 2000 || 'Notes cannot exceed 2000 characters',
  ],
}

// Form validation
const isFormValid = ref(false)
const formRef = ref()

// Validate form function
const validateForm = async () => {
  if (formRef.value) {
    const { valid } = await formRef.value.validate()

    isFormValid.value = valid

    return valid
  }

  return false
}

// Clear validation errors
const clearValidationErrors = () => {
  validationErrors.value = {}
  if (formRef.value)
    formRef.value.resetValidation()
}

// Handle API validation errors
const handleValidationErrors = (errors: Record<string, string[]>) => {
  validationErrors.value = errors

  // Show first error in a snackbar or alert
  const firstError = Object.values(errors)[0]?.[0]
  if (firstError) {
    // You can replace this with your preferred notification method

  }
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
              Royalty Management
            </h4>
            <p class="text-body-1 text-medium-emphasis">
              Track and manage royalty payments from all franchise units
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

            <!-- Create Royalty Button -->
            <VBtn
              color="success"
              variant="elevated"
              @click="openCreateRoyaltyModal"
            >
              <VIcon
                icon="tabler-plus"
                class="me-2"
              />
              Create Royalty
            </VBtn>

            <!-- Export Button -->
            <VBtn
              color="primary"
              variant="elevated"
              @click="openExportDialog"
            >
              <VIcon
                icon="tabler-download"
                class="me-2"
              />
              Export
            </VBtn>
          </div>
        </div>
      </VCol>
    </VRow>

    <!-- Stat Cards -->
    <VRow class="mb-6">
      <!-- Royalty Collected Till Date -->
      <VCol
        cols="12"
        md="6"
      >
        <VCard>
          <VCardText>
            <div class="d-flex align-center justify-space-between">
              <div>
                <h6 class="text-h6 mb-1">
                  Royalty Collected Till Date
                </h6>
                <div class="text-body-2 text-medium-emphasis mb-3">
                  Total payments received
                </div>
                <h4 class="text-h4 text-success">
                  {{ formatCurrency(royaltyCollectedTillDate, 'SAR', true) }}
                </h4>
              </div>
              <VAvatar
                color="success"
                variant="tonal"
                size="56"
              >
                <SaudiRiyal size="28" />
              </VAvatar>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Upcoming Royalties -->
      <VCol
        cols="12"
        md="6"
      >
        <VCard>
          <VCardText>
            <div class="d-flex align-center justify-space-between">
              <div>
                <h6 class="text-h6 mb-1">
                  Upcoming Royalties
                </h6>
                <div class="text-body-2 text-medium-emphasis mb-3">
                  Pending payments due
                </div>
                <h4 class="text-h4 text-warning">
                  {{ formatCurrency(upcomingRoyalties, 'SAR', true) }}
                </h4>
              </div>
              <VAvatar
                color="warning"
                variant="tonal"
                size="56"
              >
                <VIcon
                  icon="tabler-clock"
                  size="28"
                />
              </VAvatar>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Royalties Table -->
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardItem class="pb-4">
            <VCardTitle class="text-h6">
              Royalty Records
            </VCardTitle>
            <VCardSubtitle class="text-body-2">
              Manage royalty payments and track collection status
            </VCardSubtitle>
          </VCardItem>

          <VDivider />

          <VDataTable
            :headers="tableHeaders"
            :items="royaltyRecords"
            :items-per-page="10"
            class="text-no-wrap"
          >
            <!-- Billing Period Column -->
            <template #item.billingPeriod="{ item }">
              <div class="font-weight-medium">
                {{ item.billing_period }}
              </div>
            </template>

            <!-- Franchisee Name Column -->
            <template #item.franchiseeName="{ item }">
              <div class="font-weight-medium text-primary">
                {{ item.franchisee_name }}
              </div>
            </template>

            <!-- Store Location Column -->
            <template #item.storeLocation="{ item }">
              <div class="text-body-2 text-medium-emphasis">
                {{ item.store_location }}
              </div>
            </template>

            <!-- Due Date Column -->
            <template #item.dueDate="{ item }">
              <div class="text-body-2">
                {{ formatDate(item.due_date) }}
              </div>
            </template>

            <!-- Gross Sales Column -->
            <template #item.grossSales="{ item }">
              <div class="font-weight-medium text-info">
                {{ formatCurrency(item.gross_sales || 0, 'SAR', false) }}
              </div>
            </template>

            <!-- Royalty Percentage Column -->
            <template #item.royaltyPercentage="{ item }">
              <VChip
                size="small"
                variant="tonal"
                color="primary"
              >
                {{ item.royalty_percentage }}%
              </VChip>
            </template>

            <!-- Amount Column -->
            <template #item.amount="{ item }">
              <div class="font-weight-medium text-success">
                {{ (item.amount || 0).toLocaleString() }}
              </div>
            </template>

            <!-- Status Column -->
            <template #item.status="{ item }">
              <VChip
                :color="getStatusColor(item.status)"
                size="small"
                variant="tonal"
                class="text-capitalize"
              >
                {{ item.status }}
              </VChip>
            </template>

            <!-- Actions Column -->
            <template #item.actions="{ item }">
              <div class="d-flex gap-2">
                <VBtn
                  icon
                  size="small"
                  color="info"
                  variant="text"
                  @click="viewRoyalty(item)"
                >
                  <VIcon
                    icon="tabler-eye"
                    size="20"
                  />
                  <VTooltip
                    activator="parent"
                    location="top"
                  >
                    View Details
                  </VTooltip>
                </VBtn>

                <VBtn
                  v-if="item.status !== 'paid'"
                  icon
                  size="small"
                  color="success"
                  variant="text"
                  @click="markAsCompleted(item)"
                >
                  <VIcon
                    icon="tabler-check"
                    size="20"
                  />
                  <VTooltip
                    activator="parent"
                    location="top"
                  >
                    Mark as Completed
                  </VTooltip>
                </VBtn>
              </div>
            </template>
          </VDataTable>
        </VCard>
      </VCol>
    </VRow>

    <!-- Export Dialog -->
    <VDialog
      v-model="isExportDialogVisible"
      max-width="500"
    >
      <VCard>
        <VCardItem>
          <VCardTitle>Export Royalty Data</VCardTitle>
        </VCardItem>

        <VCardText>
          <VRow>
            <VCol cols="12">
              <VSelect
                v-model="exportDataType"
                :items="exportDataTypeOptions"
                item-title="title"
                item-value="value"
                label="Data Type"
                variant="outlined"
                density="compact"
              />
            </VCol>
            <VCol cols="12">
              <VSelect
                v-model="exportFormat"
                :items="exportFormatOptions"
                item-title="title"
                item-value="value"
                label="Export Format"
                variant="outlined"
                density="compact"
              />
            </VCol>
          </VRow>

          <VAlert
            type="info"
            variant="tonal"
            class="mt-4"
          >
            <div class="text-body-2">
              <strong>Current Selection:</strong><br>
              Period: {{ selectedPeriod.charAt(0).toUpperCase() + selectedPeriod.slice(1) }}<br>
              Data Type: {{ exportDataTypeOptions.find(opt => opt.value === exportDataType)?.title }}<br>
              Format: {{ exportFormat.toUpperCase() }}
            </div>
          </VAlert>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isExportDialogVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            @click="performExport"
          >
            Export Data
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Create Royalty Modal -->
    <VDialog
      v-model="isCreateRoyaltyModalVisible"
      max-width="800"
      persistent
    >
      <VCard>
        <VCardItem>
          <VCardTitle>Create New Royalty Record</VCardTitle>
        </VCardItem>

        <VCardText>
          <VForm
            ref="formRef"
            v-model="isFormValid"
            @submit.prevent="createRoyalty"
          >
            <VRow>
              <!-- Franchise Selection -->
              <VCol
                cols="12"
                md="6"
              >
                <VSelect
                  v-model="createRoyaltyData.franchise_id"
                  :items="franchises"
                  item-title="business_name"
                  item-value="id"
                  label="Franchise *"
                  variant="outlined"
                  density="compact"
                  required
                  clearable
                  :rules="validationRules.franchise_id"
                  :error-messages="validationErrors.franchise_id"
                />
              </VCol>

              <!-- Unit Selection -->
              <VCol
                cols="12"
                md="6"
              >
                <VSelect
                  v-model="createRoyaltyData.unit_id"
                  :items="units"
                  item-title="unit_name"
                  item-value="id"
                  label="Unit"
                  variant="outlined"
                  density="compact"
                  clearable
                />
              </VCol>

              <!-- Franchisee Selection -->
              <VCol
                cols="12"
                md="6"
              >
                <VSelect
                  v-model="createRoyaltyData.franchisee_id"
                  :items="franchisees"
                  item-title="name"
                  item-value="id"
                  label="Franchisee *"
                  variant="outlined"
                  density="compact"
                  required
                  clearable
                  :rules="validationRules.franchisee_id"
                  :error-messages="validationErrors.franchisee_id"
                />
              </VCol>

              <!-- Royalty Type -->
              <VCol
                cols="12"
                md="6"
              >
                <VSelect
                  v-model="createRoyaltyData.type"
                  :items="periodOptions"
                  item-title="title"
                  item-value="value"
                  label="Royalty Type *"
                  variant="outlined"
                  density="compact"
                  required
                  :rules="validationRules.type"
                  :error-messages="validationErrors.type"
                />
              </VCol>

              <!-- Period Year -->
              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model.number="createRoyaltyData.period_year"
                  label="Period Year *"
                  type="number"
                  variant="outlined"
                  density="compact"
                  required
                  :rules="validationRules.period_year"
                  :error-messages="validationErrors.period_year"
                />
              </VCol>

              <!-- Period Month (for monthly type) -->
              <VCol
                v-if="createRoyaltyData.type === 'monthly'"
                cols="12"
                md="4"
              >
                <VTextField
                  v-model.number="createRoyaltyData.period_month"
                  label="Period Month *"
                  type="number"
                  min="1"
                  max="12"
                  variant="outlined"
                  density="compact"
                  required
                  :rules="validationRules.period_month"
                  :error-messages="validationErrors.period_month"
                />
              </VCol>

              <!-- Due Date -->
              <VCol
                cols="12"
                md="4"
              >
                <VTextField
                  v-model="createRoyaltyData.due_date"
                  label="Due Date *"
                  type="date"
                  variant="outlined"
                  density="compact"
                  required
                  :rules="validationRules.due_date"
                  :error-messages="validationErrors.due_date"
                />
              </VCol>

              <!-- Base Revenue -->
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model.number="createRoyaltyData.base_revenue"
                  label="Base Revenue (SAR) *"
                  type="number"
                  step="0.01"
                  variant="outlined"
                  density="compact"
                  required
                  :rules="validationRules.base_revenue"
                  :error-messages="validationErrors.base_revenue"
                  @input="calculateAmounts"
                />
              </VCol>

              <!-- Royalty Rate -->
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model.number="createRoyaltyData.royalty_rate"
                  label="Royalty Rate (%)"
                  type="number"
                  step="0.1"
                  variant="outlined"
                  density="compact"
                  :rules="validationRules.royalty_rate"
                  :error-messages="validationErrors.royalty_rate"
                  @input="calculateAmounts"
                />
              </VCol>

              <!-- Marketing Fee Rate -->
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model.number="createRoyaltyData.marketing_fee_rate"
                  label="Marketing Fee Rate (%)"
                  type="number"
                  step="0.1"
                  variant="outlined"
                  density="compact"
                  :rules="validationRules.marketing_fee_rate"
                  :error-messages="validationErrors.marketing_fee_rate"
                  @input="calculateAmounts"
                />
              </VCol>

              <!-- Technology Fee Amount -->
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model.number="createRoyaltyData.technology_fee_amount"
                  label="Technology Fee (SAR)"
                  type="number"
                  step="0.01"
                  variant="outlined"
                  density="compact"
                  :rules="validationRules.technology_fee_amount"
                  :error-messages="validationErrors.technology_fee_amount"
                  @input="calculateAmounts"
                />
              </VCol>

              <!-- Other Fees -->
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model.number="createRoyaltyData.other_fees"
                  label="Other Fees (SAR)"
                  type="number"
                  step="0.01"
                  variant="outlined"
                  density="compact"
                  :rules="validationRules.other_fees"
                  :error-messages="validationErrors.other_fees"
                  @input="calculateAmounts"
                />
              </VCol>

              <!-- Adjustments -->
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model.number="createRoyaltyData.adjustments"
                  label="Adjustments (SAR)"
                  type="number"
                  step="0.01"
                  variant="outlined"
                  density="compact"
                  :rules="validationRules.adjustments"
                  :error-messages="validationErrors.adjustments"
                  @input="calculateAmounts"
                />
              </VCol>

              <!-- Description -->
              <VCol cols="12">
                <VTextarea
                  v-model="createRoyaltyData.description"
                  label="Description"
                  variant="outlined"
                  density="compact"
                  rows="2"
                />
              </VCol>

              <!-- Notes -->
              <VCol cols="12">
                <VTextarea
                  v-model="createRoyaltyData.notes"
                  label="Notes"
                  variant="outlined"
                  density="compact"
                  rows="2"
                />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="secondary"
            variant="tonal"
            :disabled="isCreating"
            @click="isCreateRoyaltyModalVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            :loading="isCreating"
            @click="createRoyalty"
          >
            Create Royalty
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Mark as Completed Modal -->
    <VDialog
      v-model="isMarkCompletedModalVisible"
      max-width="600"
    >
      <VCard>
        <VCardItem>
          <VCardTitle>Mark Royalty as Completed</VCardTitle>
        </VCardItem>

        <VCardText>
          <VForm @submit.prevent="submitPayment">
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model.number="paymentData.amount_paid"
                  label="Amount Paid (SAR)"
                  type="number"
                  variant="outlined"
                  density="compact"
                  :rules="[v => !!v || 'Amount is required']"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="paymentData.payment_date"
                  label="Payment Date"
                  type="date"
                  variant="outlined"
                  density="compact"
                  :rules="[v => !!v || 'Payment date is required']"
                />
              </VCol>
              <VCol cols="12">
                <VSelect
                  v-model="paymentData.payment_type"
                  :items="paymentTypeOptions"
                  item-title="title"
                  item-value="value"
                  label="Payment Type"
                  variant="outlined"
                  density="compact"
                  :rules="[v => !!v || 'Payment type is required']"
                />
              </VCol>
              <VCol cols="12">
                <VFileInput
                  label="Attachment (Optional)"
                  variant="outlined"
                  density="compact"
                  accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                  @change="handleFileUpload"
                />
                <div class="text-caption text-medium-emphasis mt-1">
                  Supported formats: PDF, JPG, PNG, DOC, DOCX
                </div>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isMarkCompletedModalVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            @click="submitPayment"
          >
            Mark as Completed
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- View Royalty Details Dialog -->
    <VDialog
      v-model="isViewRoyaltyDialogVisible"
      max-width="700"
    >
      <VCard v-if="viewedRoyalty">
        <VCardItem>
          <VCardTitle>Royalty Details</VCardTitle>
        </VCardItem>

        <VDivider class="mb-4" />

        <VCardText>
          <VRow>
            <!-- Basic Information -->
            <VCol cols="12">
              <h6 class="text-h6 mb-4 text-primary">
                Basic Information
              </h6>
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <div class="mb-4">
                <div class="text-body-2 text-medium-emphasis mb-1">
                  Billing Period
                </div>
                <div class="font-weight-medium">
                  {{ viewedRoyalty.billing_period }}
                </div>
              </div>
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <div class="mb-4">
                <div class="text-body-2 text-medium-emphasis mb-1">
                  Due Date
                </div>
                <div class="font-weight-medium">
                  {{ formatDate(viewedRoyalty.due_date) }}
                </div>
              </div>
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <div class="mb-4">
                <div class="text-body-2 text-medium-emphasis mb-1">
                  Franchisee Name
                </div>
                <div class="font-weight-medium text-primary">
                  {{ viewedRoyalty.franchisee_name }}
                </div>
              </div>
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <div class="mb-4">
                <div class="text-body-2 text-medium-emphasis mb-1">
                  Store Location
                </div>
                <div class="font-weight-medium">
                  {{ viewedRoyalty.store_location }}
                </div>
              </div>
            </VCol>

            <!-- Financial Information -->
            <VCol cols="12">
              <VDivider class="my-4" />
              <h6 class="text-h6 mb-4 text-primary">
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
                    icon="tabler-chart-line"
                    size="32"
                    class="mb-2"
                  />
                  <div class="text-body-2 text-medium-emphasis mb-1">
                    Gross Sales
                  </div>
                  <div class="text-h6 font-weight-bold">
                    {{ (viewedRoyalty.gross_sales || 0).toLocaleString() }} SAR
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
                color="primary"
                class="pa-4"
              >
                <div class="text-center">
                  <VIcon
                    icon="tabler-percentage"
                    size="32"
                    class="mb-2"
                  />
                  <div class="text-body-2 text-medium-emphasis mb-1">
                    Royalty Rate
                  </div>
                  <div class="text-h6 font-weight-bold">
                    {{ viewedRoyalty.royalty_percentage }}%
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
                    Royalty Amount
                  </div>
                  <div class="text-h6 font-weight-bold">
                    {{ (viewedRoyalty.amount || 0).toLocaleString() }} SAR
                  </div>
                </div>
              </VCard>
            </VCol>

            <!-- Status Information -->
            <VCol cols="12">
              <VDivider class="my-4" />
              <h6 class="text-h6 mb-4 text-primary">
                Status Information
              </h6>
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <div class="mb-4">
                <div class="text-body-2 text-medium-emphasis mb-2">
                  Payment Status
                </div>
                <VChip
                  :color="getStatusColor(viewedRoyalty.status)"
                  size="large"
                  variant="tonal"
                  class="text-capitalize"
                >
                  <VIcon
                    :icon="viewedRoyalty.status === 'paid' ? 'tabler-check'
                      : viewedRoyalty.status === 'pending' ? 'tabler-clock' : 'tabler-alert-triangle'"
                    class="me-2"
                  />
                  {{ viewedRoyalty.status }}
                </VChip>
              </div>
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <div class="mb-4">
                <div class="text-body-2 text-medium-emphasis mb-2">
                  Days Until Due
                </div>
                <div class="font-weight-medium">
                  {{ Math.ceil((new Date(viewedRoyalty.due_date).getTime() - new Date().getTime())
                    / (1000 * 60
                      * 60 * 24)) }} days
                </div>
              </div>
            </VCol>

            <!-- Calculation Breakdown -->
            <VCol cols="12">
              <VDivider class="my-4" />
              <h6 class="text-h6 mb-4 text-primary">
                Calculation Breakdown
              </h6>
            </VCol>

            <VCol cols="12">
              <VTable density="compact">
                <tbody>
                  <tr>
                    <td class="font-weight-medium">
                      Gross Sales:
                    </td>
                    <td class="text-end">
                      {{ (viewedRoyalty.gross_sales || 0).toLocaleString() }} SAR
                    </td>
                  </tr>
                  <tr>
                    <td class="font-weight-medium">
                      Royalty Rate:
                    </td>
                    <td class="text-end">
                      {{ viewedRoyalty.royalty_percentage }}%
                    </td>
                  </tr>
                  <tr>
                    <td class="font-weight-medium">
                      Calculation:
                    </td>
                    <td class="text-end">
                      {{ (viewedRoyalty.gross_sales || 0).toLocaleString() }} × {{
                        viewedRoyalty.royalty_percentage }}%
                    </td>
                  </tr>
                  <tr class="bg-primary-lighten-5">
                    <td class="font-weight-bold text-primary">
                      Total Royalty Amount:
                    </td>
                    <td class="text-end font-weight-bold text-primary">
                      {{
                        (viewedRoyalty.amount || 0).toLocaleString() }} SAR
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
            @click="isViewRoyaltyDialogVisible = false"
          >
            Close
          </VBtn>
          <VBtn
            v-if="viewedRoyalty.status !== 'paid'"
            color="primary"
            @click="markAsCompleted(viewedRoyalty); isViewRoyaltyDialogVisible = false"
          >
            <VIcon
              icon="tabler-check"
              class="me-2"
            />
            Mark as Completed
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </section>
</template>
