<script setup lang="ts">
import { SaudiRiyal } from 'lucide-vue-next'
import { computed, onMounted, ref } from 'vue'
import { type PaymentData, type RoyaltyRecord, type RoyaltyStatistics, royaltyApi } from '@/services/api/royalty'
import MarkCompletedRoyaltyDialog from '@/components/dialogs/royalty/MarkCompletedRoyaltyDialog.vue'
import ViewRoyaltyDetailsDialog from '@/components/dialogs/royalty/ViewRoyaltyDetailsDialog.vue'

// Loading states
const isLoading = ref(false)
const isLoadingStats = ref(false)

// Reactive data
const selectedPeriod = ref('monthly')
const isExportDialogVisible = ref(false)
const isMarkCompletedModalVisible = ref(false)
const isViewRoyaltyDialogVisible = ref(false)
const selectedRoyalty = ref<RoyaltyRecord | null>(null)
const viewedRoyalty = ref<RoyaltyRecord | null>(null)
const exportFormat = ref('csv')
const exportDataType = ref('all')

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
const royaltyCollectedTillDate = computed(() => statistics.value.royalty_collected_till_date)
const upcomingRoyalties = computed(() => statistics.value.upcoming_royalties)

// Period options
const periodOptions = [
  { title: 'Daily', value: 'daily' },
  { title: 'Monthly', value: 'monthly' },
  { title: 'Yearly', value: 'yearly' },
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
  { title: 'Billing Period', key: 'billing_period', sortable: true },
  { title: 'Due Date', key: 'due_date', sortable: true },
  { title: 'Gross Sales (SAR)', key: 'gross_sales', sortable: true },
  { title: 'Royalty %', key: 'royalty_percentage', sortable: true },
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
    console.error('Error fetching royalties:', error)
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
    console.error('Error fetching statistics:', error)
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
    console.error('Error exporting royalties:', error)
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
    console.error('Error marking royalty as paid:', error)
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

// Lifecycle hooks
onMounted(() => {
  fetchRoyalties()
  fetchStatistics()
})
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
            <VSelect v-model="selectedPeriod" :items="periodOptions" item-title="title" item-value="value"
              density="comfortable" style="min-width: 120px;" variant="outlined" />

            <!-- Export Button -->
            <VBtn color="primary" variant="elevated" @click="openExportDialog">
              <VIcon icon="tabler-download" class="me-2" />
              Export
            </VBtn>
          </div>
        </div>
      </VCol>
    </VRow>

    <!-- Stat Cards -->
    <VRow class="mb-6">
      <!-- Royalty Collected Till Date -->
      <VCol cols="12" md="6">
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
                  {{ (royaltyCollectedTillDate || 0).toLocaleString() }} SAR
                </h4>
              </div>
              <VAvatar color="success" variant="tonal" size="56">
                <SaudiRiyal size="28" />
              </VAvatar>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Upcoming Royalties -->
      <VCol cols="12" md="6">
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
                  {{ (upcomingRoyalties || 0).toLocaleString() }} SAR
                </h4>
              </div>
              <VAvatar color="warning" variant="tonal" size="56">
                <VIcon icon="tabler-clock" size="28" />
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

          <VDataTable :headers="tableHeaders" :items="royaltyRecords" :items-per-page="10" class="text-no-wrap">
            <!-- Billing Period Column -->
            <template #item.billing_period="{ item }">
              <div class="font-weight-medium">
                {{ item.billing_period }}
              </div>
            </template>

            <!-- Due Date Column -->
            <template #item.due_date="{ item }">
              <div class="text-body-2">
                {{ formatDate(item.due_date) }}
              </div>
            </template>

            <!-- Gross Sales Column -->
            <template #item.gross_sales="{ item }">
              <div class="font-weight-medium text-info">
                {{ (item.gross_sales || 0).toLocaleString() }}
              </div>
            </template>

            <!-- Royalty Percentage Column -->
            <template #item.royalty_percentage="{ item }">
              <VChip size="small" variant="tonal" color="primary">
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
              <VChip :color="getStatusColor(item.status)" size="small" variant="tonal" class="text-capitalize">
                {{ item.status }}
              </VChip>
            </template>

            <!-- Actions Column -->
            <template #item.actions="{ item }">
              <div class="d-flex gap-2">
                <VBtn icon size="small" color="info" variant="text" @click="viewRoyalty(item)">
                  <VIcon icon="tabler-eye" size="20" />
                  <VTooltip activator="parent" location="top">
                    View Details
                  </VTooltip>
                </VBtn>

                <VBtn v-if="item.status !== 'paid'" icon size="small" color="success" variant="text"
                  @click="markAsCompleted(item)">
                  <VIcon icon="tabler-check" size="20" />
                  <VTooltip activator="parent" location="top">
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
    <VDialog v-model="isExportDialogVisible" max-width="600">
      <DialogCloseBtn @click="isExportDialogVisible = false" />
      <VCard title="Export Royalty Data">
        <VCardText>
          <VRow>
            <VCol cols="12">
              <VSelect v-model="exportFormat" :items="exportFormatOptions" item-title="title" item-value="value"
                label="Export Format" variant="outlined" density="compact" />
            </VCol>
          </VRow>

          <VAlert type="info" variant="tonal" class="mt-4">
            <div class="text-body-2">
              <strong>Current Selection:</strong><br>
              Period: {{ selectedPeriod.charAt(0).toUpperCase() + selectedPeriod.slice(1) }}<br>
              Data Type: {{exportDataTypeOptions.find(opt => opt.value === exportDataType)?.title}}<br>
              Format: {{ exportFormat.toUpperCase() }}
            </div>
          </VAlert>
        </VCardText>

        <VCardText class="d-flex align-center justify-center gap-4">
          <VBtn color="error" variant="outlined" @click="isExportDialogVisible = false">
            Cancel
          </VBtn>
          <VBtn color="primary" variant="elevated" @click="performExport">
            Export Data
          </VBtn>
        </VCardText>
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
