<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { type TechnicalRequest, technicalRequestApi } from '@/services/api'
import SubmitRequestForm from '@/views/technical-requests/SubmitRequestForm.vue'
import ViewRequestDialog from '@/views/technical-requests/ViewRequestDialog.vue'
import EditRequestDialog from '@/views/technical-requests/EditRequestDialog.vue'
import DeleteConfirmDialog from '@/views/technical-requests/DeleteConfirmDialog.vue'
import BulkDeleteConfirmDialog from '@/views/technical-requests/BulkDeleteConfirmDialog.vue'
import TechnicalRequestsTable from '@/views/technical-requests/TechnicalRequestsTable.vue'

// Local display interface
interface DisplayRequest {
  id: number | string
  requestId: string
  userName: string
  userEmail: string
  userAvatar: string
  subject: string
  description: string
  priority: string
  status: string
  date: string
  category: string
  attachments: any[]
}

// Loading states
const isLoading = ref(false)

// Snackbar for notifications
const snackbar = ref({
  show: false,
  message: '',
  color: 'success',
})

const showSnackbar = (message: string, color: string = 'success') => {
  snackbar.value = {
    show: true,
    message,
    color,
  }
}

// Store
const searchQuery = ref('')
const selectedStatus = ref()
const selectedPriority = ref()

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref<number[]>([])

// Submit Request Modal
const isSubmitRequestDialogVisible = ref(false)

// View Request Modal
const isViewRequestDialogVisible = ref(false)
const selectedRequest = ref<any>(null)

// Edit Request Modal
const isEditRequestDialogVisible = ref(false)
const requestToEdit = ref<any>(null)

// Delete Confirmation Modal
const isDeleteConfirmDialogVisible = ref(false)
const requestToDelete = ref<any>(null)

// Bulk Delete Confirmation Modal
const isBulkDeleteConfirmDialogVisible = ref(false)

// User role detection
const userRole = ref('')
const isAdmin = computed(() => userRole.value === 'admin')


// Update data table options
const updateOptions = (options: any) => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

const getFileIcon = (fileName: string) => {
  const ext = fileName.split('.').pop()?.toLowerCase()

  if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(ext || ''))
    return 'tabler-photo'
  else if (['pdf'].includes(ext || ''))
    return 'tabler-file-type-pdf'
  if (['doc', 'docx'].includes(ext || ''))
    return 'tabler-file-type-doc'
  if (['xls', 'xlsx'].includes(ext || ''))
    return 'tabler-file-type-xls'
  if (['txt', 'log'].includes(ext || ''))
    return 'tabler-file-text'
  if (['zip', 'rar', '7z'].includes(ext || ''))
    return 'tabler-file-zip'

  return 'tabler-file'
}

// Status and priority variant resolvers (these should be passed as props or imported from parent)
const resolveStatusVariant = (status: string) => {
  const statusLowerCase = status.toLowerCase().replace('_', '-')
  if (statusLowerCase === 'open')
    return 'info'
  if (statusLowerCase === 'in-progress')
    return 'warning'
  if (statusLowerCase === 'pending-info')
    return 'warning'
  if (statusLowerCase === 'resolved')
    return 'success'
  if (statusLowerCase === 'closed')
    return 'secondary'
  if (statusLowerCase === 'cancelled')
    return 'error'

  return 'primary'
}

const resolvePriorityVariant = (priority: string) => {
  const priorityLowerCase = priority.toLowerCase()
  if (priorityLowerCase === 'low')
    return { color: 'info', icon: 'tabler-arrow-down' }
  if (priorityLowerCase === 'medium')
    return { color: 'warning', icon: 'tabler-minus' }
  if (priorityLowerCase === 'high')
    return { color: 'error', icon: 'tabler-arrow-up' }
  if (priorityLowerCase === 'urgent')
    return { color: 'error', icon: 'tabler-alert-triangle' }

  return { color: 'primary', icon: 'tabler-minus' }
}

const formatStatus = (status: string) => {
  // Convert snake_case to Title Case
  if (!status) return ''
  return status
    .split('_')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join(' ')
}

// Headers
const headers = [
  { title: 'Request ID', key: 'requestId' },
  { title: 'Subject', key: 'subject' },
  { title: 'Priority', key: 'priority' },
  { title: 'Status', key: 'status' },
  { title: 'Date', key: 'date' },
  { title: 'Actions', key: 'actions', sortable: false },
]

// Real data from API
const technicalRequests = ref<DisplayRequest[]>([])
const totalRequests = ref(0)

// Map API response to display format
const mapToDisplayRequest = (apiRequest: TechnicalRequest): DisplayRequest => {
  // Handle attachments - could be array, string, or null
  let attachments: any[] = []
  if (apiRequest.attachments) {
    if (Array.isArray(apiRequest.attachments)) {
      attachments = apiRequest.attachments.map((url: string, index: number) => ({
        name: url.split('/').pop() || `attachment-${index}`,
        size: 'Unknown',
        url,
      }))
    }
  }

  return {
    id: apiRequest.id,
    requestId: apiRequest.ticket_number,
    userName: apiRequest.requester?.name || 'Unknown',
    userEmail: apiRequest.requester?.email || '',
    userAvatar: '',
    subject: apiRequest.title,
    description: apiRequest.description,
    priority: apiRequest.priority,
    status: apiRequest.status, // Keep backend format, we'll handle display in variant resolver
    date: new Date(apiRequest.created_at).toISOString().split('T')[0],
    category: apiRequest.category,
    attachments,
  }
}

// Fetch technical requests
const fetchRequests = async () => {
  try {
    isLoading.value = true

    const response = await technicalRequestApi.getTechnicalRequests({
      status: selectedStatus.value, // Already in correct format from statusOptions
      priority: selectedPriority.value,
      search: searchQuery.value,
      per_page: itemsPerPage.value,
      page: page.value,
    })

    technicalRequests.value = response.data.data.map(mapToDisplayRequest)
    totalRequests.value = response.data.total
  }
  catch (error) {
    console.error('Error fetching technical requests:', error)
  }
  finally {
    isLoading.value = false
  }
}

// Filtered data - API handles filtering
const filteredRequests = computed(() => technicalRequests.value)

// Status options (must match backend enum)
const statusOptions = [
  { title: 'Open', value: 'open' },
  { title: 'In Progress', value: 'in_progress' },
  { title: 'Pending Info', value: 'pending_info' },
  { title: 'Resolved', value: 'resolved' },
  { title: 'Closed', value: 'closed' },
  { title: 'Cancelled', value: 'cancelled' },
]

// Priority options (must match backend enum)
const priorityOptions = [
  { title: 'Low', value: 'low' },
  { title: 'Medium', value: 'medium' },
  { title: 'High', value: 'high' },
  { title: 'Urgent', value: 'urgent' },
]

// View request
const viewRequest = (request: any) => {
  selectedRequest.value = request
  isViewRequestDialogVisible.value = true
}

// Edit request
const editRequest = (request: any) => {
  requestToEdit.value = request
  isEditRequestDialogVisible.value = true
  isViewRequestDialogVisible.value = false // Close view dialog
}

// Handle edit from view dialog
const handleEditFromView = (request: any) => {
  editRequest(request)
}

// Delete request - show confirmation
const deleteRequest = (request: any) => {
  requestToDelete.value = request
  isDeleteConfirmDialogVisible.value = true
}

// Confirm delete request
const confirmDelete = async () => {
  if (!requestToDelete.value)
    return

  try {
    isLoading.value = true

    // Delete via API
    await technicalRequestApi.deleteTechnicalRequest(requestToDelete.value.id)

    // Refresh the list after deletion
    await fetchRequests()

    // Remove from selectedRows if exists
    const selectedIndex = selectedRows.value.findIndex(row => row === requestToDelete.value.id)
    if (selectedIndex !== -1)
      selectedRows.value.splice(selectedIndex, 1)

    // Reset and close dialog
    requestToDelete.value = null
    isDeleteConfirmDialogVisible.value = false
  }
  catch (error) {
    console.error('Error deleting technical request:', error)
  }
  finally {
    isLoading.value = false
  }
}

// Show bulk delete confirmation
const showBulkDeleteConfirmation = () => {
  if (selectedRows.value.length === 0)
    return

  isBulkDeleteConfirmDialogVisible.value = true
}

// Confirm bulk delete
const confirmBulkDelete = async () => {
  try {
    isLoading.value = true
    await technicalRequestApi.bulkDelete(selectedRows.value)

    // Refresh the list after deletion
    await fetchRequests()

    // Clear selection
    selectedRows.value = []

    // Close dialog
    isBulkDeleteConfirmDialogVisible.value = false
  }
  catch (error) {
    console.error('Error bulk deleting requests:', error)
  }
  finally {
    isLoading.value = false
  }
}

// Legacy function for backward compatibility
const bulkDelete = showBulkDeleteConfirmation

// Download attachment
const downloadAttachment = (attachment: any) => {
  if (attachment.url && attachment.url !== '#')
    window.open(attachment.url, '_blank')
}

// Handle successful submission from SubmitRequestForm
const handleSubmitSuccess = async () => {
  showSnackbar('Technical request created successfully!', 'success')
  await fetchRequests()
}

// Handle successful edit from EditRequestDialog
const handleEditSuccess = async () => {
  showSnackbar('Technical request updated successfully!', 'success')
  await fetchRequests()
}

// Lifecycle hooks
onMounted(() => {
  // Initialize user role from cookie
  const userData = useCookie<any>('userData')
  userRole.value = userData.value?.role || ''

  fetchRequests()
})

// Watch for filter changes
watch([selectedStatus, selectedPriority, searchQuery], () => {
  fetchRequests()
})
</script>

<template>
  <section>
    <!-- Page Header -->
    <VRow class="mb-6">
      <VCol cols="12">
        <div class="d-flex align-center justify-space-between">
          <div>
            <h2 class="text-h2 mb-1">
              Technical Requests
            </h2>
            <p class="text-body-1 text-medium-emphasis">
              Manage and track all technical support requests
            </p>
          </div>
          <div>
            <VBtn color="primary" @click="isSubmitRequestDialogVisible = true"> <!-- v-if="!isAdmin" -->
              <VIcon icon="tabler-plus" class="me-2" />
              Submit Request
            </VBtn>
          </div>
        </div>
      </VCol>
    </VRow>

    <!-- Filters and Table -->
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Filters</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <!-- Select Status -->
          <VCol cols="12" sm="4">
            <AppSelect v-model="selectedStatus" placeholder="Select Status" :items="statusOptions" clearable
              clear-icon="tabler-x" />
          </VCol>

          <!-- Select Priority -->
          <VCol cols="12" sm="4">
            <AppSelect v-model="selectedPriority" placeholder="Select Priority" :items="priorityOptions" clearable
              clear-icon="tabler-x" />
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <VCardText class="d-flex flex-wrap gap-4">
        <div class="me-3 d-flex gap-3">
          <AppSelect :model-value="itemsPerPage" :items="[
            { value: 10, title: '10' },
            { value: 25, title: '25' },
            { value: 50, title: '50' },
            { value: 100, title: '100' },
            { value: -1, title: 'All' },
          ]" style="inline-size: 6.25rem;" @update:model-value="itemsPerPage = parseInt($event, 10)" />

          <!-- Bulk Actions -->
          <VBtn v-if="selectedRows.length > 0" variant="tonal" color="error" @click="bulkDelete">
            <VIcon icon="tabler-trash" class="me-2" />
            Delete Selected ({{ selectedRows.length }})
          </VBtn>
        </div>
        <VSpacer />

        <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
          <!-- Search -->
          <div style="inline-size: 15.625rem;">
            <AppTextField v-model="searchQuery" placeholder="Search Requests" />
          </div>
        </div>
      </VCardText>

      <VDivider />

      <!-- Data Table -->
      <TechnicalRequestsTable :items="filteredRequests" :items-length="totalRequests" :loading="isLoading"
        v-model:selectedRows="selectedRows" :headers="headers" :is-admin="isAdmin" v-model:itemsPerPage="itemsPerPage"
        v-model:page="page" :resolve-priority-variant="resolvePriorityVariant"
        :resolve-status-variant="resolveStatusVariant" :format-status="formatStatus" :get-file-icon="getFileIcon" @view-request="viewRequest"
        @edit-request="editRequest" @delete-request="deleteRequest" @update:options="updateOptions" />
    </VCard>

    <!-- View Request Dialog -->
    <ViewRequestDialog v-model:visible="isViewRequestDialogVisible" :request="selectedRequest"
      :show-edit-button="isAdmin" :is-admin="isAdmin" :resolve-priority-variant="resolvePriorityVariant"
      :resolve-status-variant="resolveStatusVariant" :format-status="formatStatus" :get-file-icon="getFileIcon" @edit="handleEditFromView" />

    <!-- Edit Request Dialog -->
    <EditRequestDialog v-model:visible="isEditRequestDialogVisible" :request="requestToEdit"
      :is-admin="isAdmin" @success="handleEditSuccess" />

    <!-- Submit Request Dialog -->
    <SubmitRequestForm v-model:visible="isSubmitRequestDialogVisible" @success="handleSubmitSuccess" />

    <!-- Delete Confirmation Dialog -->
    <DeleteConfirmDialog v-model:visible="isDeleteConfirmDialogVisible" :request="requestToDelete" :loading="isLoading"
      @confirm="confirmDelete" />

    <!-- Bulk Delete Confirmation Dialog -->
    <BulkDeleteConfirmDialog v-model:visible="isBulkDeleteConfirmDialogVisible" :selected-count="selectedRows.length"
      :loading="isLoading" @confirm="confirmBulkDelete" />

    <!-- Snackbar for notifications -->
    <VSnackbar v-model="snackbar.show" timeout="3000">
      {{ snackbar.message }}
      <template #actions>
        <VBtn :color="snackbar.color" @click="snackbar.show = false">
          Close
        </VBtn>
      </template>
    </VSnackbar>
  </section>
</template>