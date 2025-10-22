<script setup lang="ts">
import { SaudiRiyal } from 'lucide-vue-next'
import { computed, onMounted, ref } from 'vue'
import type { PeriodFilter } from '@/types/api'
import { type PaymentData, type RoyaltyRecord, type RoyaltyStatistics, royaltyApi, franchiseApi } from '@/services/api'
import ExportRoyaltyDialog from '@/components/dialogs/royalty/ExportRoyaltyDialog.vue'
import MarkCompletedRoyaltyDialog from '@/components/dialogs/royalty/MarkCompletedRoyaltyDialog.vue'
import ViewRoyaltyDetailsDialog from '@/components/dialogs/royalty/ViewRoyaltyDetailsDialog.vue'
import { formatCurrency } from '@/@core/utils/formatters'

// Loading states
const isLoading = ref(false)
const isLoadingStats = ref(false)
const isCreating = ref(false)

// Reactive data
const selectedPeriod = ref<PeriodFilter>('all')
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
const franchises = ref<any[]>([])
const units = ref<any[]>([])
const franchisees = ref<any[]>([])

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
  { title: 'All Time', value: 'all' },
  { title: 'This Year', value: 'yearly' },
  { title: 'This Month', value: 'monthly' },
  { title: 'Today', value: 'daily' },
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

const onExportCompleted = () => {
  // Optionally refresh data or show success message
  console.log('Export completed successfully')
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

// Event handler for dialog components
const onPaymentSubmitted = async () => {
  // Refresh data
  await fetchRoyalties()
  await fetchStatistics()
  
  // Reset form and close modal
  isMarkCompletedModalVisible.value = false
  selectedRoyalty.value = null
}

const onMarkAsCompletedFromView = (royalty: RoyaltyRecord) => {
  markAsCompleted(royalty)
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
    const response = await franchiseApi.getFranchiseData()

    if (response.success && response.data) {
      // API returns nested structure with franchise data
      // Handle both direct data and nested franchise property
      const franchiseData = (response.data as any).franchise || response.data
      
      franchises.value = [{
        id: franchiseData.id,
        business_name: franchiseData.legalDetails?.legalEntityName 
          || franchiseData.franchiseDetails?.franchiseName 
          || 'Unknown',
      }]
      
      // Auto-select the franchise if there's only one
      if (franchises.value.length === 1) {
        createRoyaltyData.value.franchise_id = franchises.value[0].id
      }
    }
  }
  catch (error) {
    console.error('Error fetching franchises:', error)
  }
}

const fetchUnits = async () => {
  try {
    const response = await franchiseApi.getUnits()

    if (response.success && response.data) {
      // myUnits returns paginated data, extract the actual units from data.data
      const unitData = response.data.data || []
      units.value = unitData.map((unit: any) => ({
        id: unit.id,
        unit_name: unit.unit_name || unit.name || `Unit ${unit.id}`,
      }))
    }
  }
  catch (error) {
    console.error('Error fetching units:', error)
  }
}

const fetchFranchisees = async () => {
  try {
    const response = await franchiseApi.getFranchisees()

    if (response.success && response.data) {
      // myFranchisees returns paginated data, extract the actual franchisees from data.data
      const franchiseeData = response.data.data || []
      franchisees.value = franchiseeData.map((franchisee: any) => ({
        id: franchisee.id,
        name: franchisee.name || franchisee.fullName || `Franchisee ${franchisee.id}`,
      }))
    }
  }
  catch (error) {
    console.error('Error fetching franchisees:', error)
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

// Watch for period changes
watch(selectedPeriod, () => {
  fetchRoyalties()
  fetchStatistics()
})

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

// Royalty type options for create form
const royaltyTypeOptions = [
  { title: 'Monthly', value: 'monthly' },
  { title: 'Quarterly', value: 'quarterly' },
]

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
                <SaudiRiyal :size="28" />
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
                {{ formatCurrency(item.amount || 0, 'SAR', false) }}
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

    <!-- Export Royalty Dialog -->
    <ExportRoyaltyDialog
      v-model:is-dialog-visible="isExportDialogVisible"
      :selected-period="selectedPeriod"
      @export-completed="onExportCompleted"
    />

    <!-- Create Royalty Modal -->
    <VDialog
      v-model="isCreateRoyaltyModalVisible"
      max-width="600"
      persistent
    >
      <DialogCloseBtn @click="isCreateRoyaltyModalVisible = false" />
      <VCard title="Create New Royalty Record">
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
                  :return-object="false"
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
                  :return-object="false"
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
                  :return-object="false"
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
                  :items="royaltyTypeOptions"
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
    <MarkCompletedRoyaltyDialog
      v-model:is-dialog-visible="isMarkCompletedModalVisible"
      :selected-royalty="selectedRoyalty"
      :payment-type-options="paymentTypeOptions"
      @payment-submitted="onPaymentSubmitted"
    />

    <!-- View Royalty Details Dialog -->
    <ViewRoyaltyDetailsDialog
      v-model:is-dialog-visible="isViewRoyaltyDialogVisible"
      :viewed-royalty="viewedRoyalty"
      @mark-as-completed="onMarkAsCompletedFromView"
    />
  </section>
</template>
