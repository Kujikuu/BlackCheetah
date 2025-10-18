<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { type TechnicalRequest, technicalRequestApi } from '@/services/api/technical-request'

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

// Delete Confirmation Modal
const isDeleteConfirmDialogVisible = ref(false)
const requestToDelete = ref<any>(null)

// Bulk Delete Confirmation Modal
const isBulkDeleteConfirmDialogVisible = ref(false)

const submitRequestForm = ref({
  subject: '',
  category: '',
  priority: 'medium',
  description: '',
  attachments: [] as File[],
})

// Form validation
const isFormValid = ref(false)

const subjectRules = [
  (v: string) => !!v || 'Subject is required',
  (v: string) => (v && v.length >= 5) || 'Subject must be at least 5 characters',
]

const categoryRules = [
  (v: string) => !!v || 'Category is required',
]

const descriptionRules = [
  (v: string) => !!v || 'Description is required',
  (v: string) => (v && v.length >= 10) || 'Description must be at least 10 characters',
]

// Computed property to check if form can be submitted
const canSubmit = computed(() => {
  return submitRequestForm.value.subject.length >= 5
    && submitRequestForm.value.category !== ''
    && submitRequestForm.value.description.length >= 10
})

// Update data table options
const updateOptions = (options: any) => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
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

// Mock data for initial display (will be replaced by API)
const mockRequests = ref([
  {
    id: 1,
    requestId: 'TR-2024-001',
    userName: 'Name',
    userEmail: 'john.doe@example.com',
    userAvatar: '',
    subject: 'Login issues on mobile app',
    description: 'Unable to login using mobile device. Getting authentication error.',
    priority: 'high',
    status: 'open',
    date: '2024-01-15',
    category: 'Authentication',
    attachments: [
      { name: 'error-screenshot.png', size: '245 KB', url: '#' },
      { name: 'error-log.txt', size: '12 KB', url: '#' },
    ],
  },
  {
    id: 2,
    requestId: 'TR-2024-002',
    userName: 'Jane Smith',
    userEmail: 'jane.smith@example.com',
    userAvatar: '',
    subject: 'Payment processing error',
    description: 'Payment gateway not responding during checkout process.',
    priority: 'critical',
    status: 'in-progress',
    date: '2024-01-14',
    category: 'Payment',
    attachments: [
      { name: 'payment-error.pdf', size: '156 KB', url: '#' },
    ],
  },
  {
    id: 3,
    requestId: 'TR-2024-003',
    userName: 'Mike Johnson',
    userEmail: 'mike.j@example.com',
    userAvatar: '',
    subject: 'Dashboard data not loading',
    description: 'Dashboard statistics showing blank after recent update.',
    priority: 'medium',
    status: 'open',
    date: '2024-01-13',
    category: 'Dashboard',
    attachments: [],
  },
  {
    id: 4,
    requestId: 'TR-2024-004',
    userName: 'Sarah Williams',
    userEmail: 'sarah.w@example.com',
    userAvatar: '',
    subject: 'Email notifications not working',
    description: 'Not receiving email notifications for new orders.',
    priority: 'low',
    status: 'resolved',
    date: '2024-01-12',
    category: 'Notifications',
    attachments: [],
  },
  {
    id: 5,
    requestId: 'TR-2024-005',
    userName: 'David Brown',
    userEmail: 'david.b@example.com',
    userAvatar: '',
    subject: 'Report generation timeout',
    description: 'Monthly reports timing out when trying to generate.',
    priority: 'high',
    status: 'in-progress',
    date: '2024-01-11',
    category: 'Reports',
    attachments: [
      { name: 'report-timeout-log.txt', size: '8 KB', url: '#' },
    ],
  },
])

// Filtered data - API handles filtering
const filteredRequests = computed(() => technicalRequests.value)

// Status options (must match backend enum)
const statusOptions = [
  { title: 'Open', value: 'open' },
  { title: 'In Progress', value: 'in_progress' },
  { title: 'Resolved', value: 'resolved' },
  { title: 'Closed', value: 'closed' },
]

// Priority options (must match backend enum)
const priorityOptions = [
  { title: 'Low', value: 'low' },
  { title: 'Medium', value: 'medium' },
  { title: 'High', value: 'high' },
  { title: 'Urgent', value: 'urgent' },
]

const resolveStatusVariant = (status: string) => {
  const statusLowerCase = status.toLowerCase().replace('_', '-')
  if (statusLowerCase === 'open')
    return 'info'
  if (statusLowerCase === 'in-progress')
    return 'warning'
  if (statusLowerCase === 'resolved')
    return 'success'
  if (statusLowerCase === 'closed')
    return 'secondary'

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

const isViewRequestDialogVisible = ref(false)
const selectedRequest = ref<any>(null)

// View request
const viewRequest = (request: any) => {
  selectedRequest.value = request
  isViewRequestDialogVisible.value = true
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

// Get file icon based on extension
const getFileIcon = (fileName: string) => {
  const ext = fileName.split('.').pop()?.toLowerCase()

  if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(ext || ''))
    return 'tabler-photo'
  if (['pdf'].includes(ext || ''))
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

// Category options (matching database enum)
const categoryOptions = [
  { title: 'Hardware', value: 'hardware' },
  { title: 'Software', value: 'software' },
  { title: 'Network', value: 'network' },
  { title: 'POS System', value: 'pos_system' },
  { title: 'Website', value: 'website' },
  { title: 'Mobile App', value: 'mobile_app' },
  { title: 'Training', value: 'training' },
  { title: 'Other', value: 'other' },
]

// Submit request function
const submitRequest = async () => {
  // Validate using canSubmit computed property
  if (!canSubmit.value)
    return

  try {
    isLoading.value = true

    // Get current user ID from cookie
    const userData = useCookie<any>('userData')
    const currentUserId = userData.value?.id

    if (!currentUserId) {
      isLoading.value = false

      return
    }

    // Create request via API
    await technicalRequestApi.createTechnicalRequest({
      title: submitRequestForm.value.subject,
      description: submitRequestForm.value.description,
      category: submitRequestForm.value.category as any,
      priority: submitRequestForm.value.priority as any,
      status: 'open',
      requester_id: currentUserId,
    })

    // Refresh the list
    await fetchRequests()

    // Reset form and close modal
    resetSubmitForm()
    isSubmitRequestDialogVisible.value = false
  }
  catch (error) {
    console.error('Error submitting technical request:', error)
  }
  finally {
    isLoading.value = false
  }
}

// Reset submit form
const resetSubmitForm = () => {
  submitRequestForm.value = {
    subject: '',
    category: '',
    priority: 'medium',
    description: '',
    attachments: [],
  }
  isFormValid.value = false
}

// Handle file upload
const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files)
    submitRequestForm.value.attachments = Array.from(target.files)
}

// Remove attachment
const removeAttachment = (index: number) => {
  submitRequestForm.value.attachments.splice(index, 1)
}

// Lifecycle hooks
onMounted(() => {
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
            <VBtn
              color="primary"
              @click="isSubmitRequestDialogVisible = true"
            >
              <VIcon
                icon="tabler-plus"
                class="me-2"
              />
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
          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="selectedStatus"
              placeholder="Select Status"
              :items="statusOptions"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>

          <!-- Select Priority -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="selectedPriority"
              placeholder="Select Priority"
              :items="priorityOptions"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <VCardText class="d-flex flex-wrap gap-4">
        <div class="me-3 d-flex gap-3">
          <AppSelect
            :model-value="itemsPerPage"
            :items="[
              { value: 10, title: '10' },
              { value: 25, title: '25' },
              { value: 50, title: '50' },
              { value: 100, title: '100' },
              { value: -1, title: 'All' },
            ]"
            style="inline-size: 6.25rem;"
            @update:model-value="itemsPerPage = parseInt($event, 10)"
          />

          <!-- Bulk Actions -->
          <VBtn
            v-if="selectedRows.length > 0"
            variant="tonal"
            color="error"
            @click="bulkDelete"
          >
            <VIcon
              icon="tabler-trash"
              class="me-2"
            />
            Delete Selected ({{ selectedRows.length }})
          </VBtn>
        </div>
        <VSpacer />

        <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
          <!-- Search -->
          <div style="inline-size: 15.625rem;">
            <AppTextField
              v-model="searchQuery"
              placeholder="Search Requests"
            />
          </div>
        </div>
      </VCardText>

      <VDivider />

      <!-- Data Table -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:model-value="selectedRows"
        v-model:page="page"
        :items="filteredRequests"
        item-value="id"
        :items-length="totalRequests"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Empty State -->
        <template #no-data>
          <div class="text-center pa-8">
            <VIcon
              icon="tabler-inbox-off"
              size="64"
              class="mb-4 text-disabled"
            />
            <h3 class="text-h5 mb-2">
              No Technical Requests Found
            </h3>
            <p class="text-body-1 text-medium-emphasis mb-4">
              No requests match your search criteria. Try adjusting your filters.
            </p>
          </div>
        </template>

        <!-- Request ID -->
        <template #item.requestId="{ item }">
          <div class="text-body-1 font-weight-medium text-primary">
            {{ item.requestId }}
          </div>
        </template>

        <!-- Subject -->
        <template #item.subject="{ item }">
          <div class="text-body-1">
            {{ item.subject }}
          </div>
          <div class="text-sm text-medium-emphasis">
            {{ item.category }}
          </div>
        </template>

        <!-- Priority -->
        <template #item.priority="{ item }">
          <VChip
            :color="resolvePriorityVariant(item.priority).color"
            size="small"
            label
            class="text-capitalize"
          >
            <VIcon
              :icon="resolvePriorityVariant(item.priority).icon"
              size="16"
              class="me-1"
            />
            {{ item.priority }}
          </VChip>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <VChip
            :color="resolveStatusVariant(item.status)"
            size="small"
            label
            class="text-capitalize"
          >
            {{ item.status }}
          </VChip>
        </template>

        <!-- Date -->
        <template #item.date="{ item }">
          <div class="text-body-1">
            {{ item.date }}
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <div class="d-flex gap-1">
            <VBtn
              icon
              variant="text"
              color="medium-emphasis"
              size="small"
            >
              <VIcon
                icon="tabler-dots-vertical"
                size="22"
              />
              <VMenu activator="parent">
                <VList>
                  <VListItem @click="viewRequest(item)">
                    <template #prepend>
                      <VIcon icon="tabler-eye" />
                    </template>
                    <VListItemTitle>View Details</VListItemTitle>
                  </VListItem>

                  <VListItem @click="deleteRequest(item)">
                    <template #prepend>
                      <VIcon
                        icon="tabler-trash"
                        color="error"
                      />
                    </template>
                    <VListItemTitle class="text-error">
                      Delete
                    </VListItemTitle>
                  </VListItem>
                </VList>
              </VMenu>
            </VBtn>
          </div>
        </template>

        <!-- Pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalRequests"
          />
        </template>
      </VDataTableServer>
    </VCard>

    <!-- View Request Dialog -->
    <VDialog
      v-model="isViewRequestDialogVisible"
      max-width="600"
    >
      <VCard v-if="selectedRequest">
        <VCardItem>
          <VCardTitle>Request Details</VCardTitle>

          <template #append>
            <IconBtn @click="isViewRequestDialogVisible = false">
              <VIcon icon="tabler-x" />
            </IconBtn>
          </template>
        </VCardItem>

        <VDivider />

        <VCardText>
          <VRow>
            <VCol cols="6">
              <div class="text-body-2 text-medium-emphasis mb-1">
                Request ID
              </div>
              <div class="text-body-1 font-weight-medium">
                {{ selectedRequest.requestId }}
              </div>
            </VCol>

            <VCol cols="6">
              <div class="text-body-2 text-medium-emphasis mb-1">
                Date
              </div>
              <div class="text-body-1">
                {{ selectedRequest.date }}
              </div>
            </VCol>

            <VCol cols="6">
              <div class="text-body-2 text-medium-emphasis mb-1">
                Priority
              </div>
              <VChip
                :color="resolvePriorityVariant(selectedRequest.priority).color"
                size="small"
                label
                class="text-capitalize"
              >
                <VIcon
                  :icon="resolvePriorityVariant(selectedRequest.priority).icon"
                  size="16"
                  class="me-1"
                />
                {{ selectedRequest.priority }}
              </VChip>
            </VCol>

            <VCol cols="6">
              <div class="text-body-2 text-medium-emphasis mb-1">
                Status
              </div>
              <VChip
                :color="resolveStatusVariant(selectedRequest.status)"
                size="small"
                label
                class="text-capitalize"
              >
                {{ selectedRequest.status }}
              </VChip>
            </VCol>

            <VCol cols="12">
              <div class="text-body-2 text-medium-emphasis mb-1">
                Category
              </div>
              <div class="text-body-1">
                {{ selectedRequest.category }}
              </div>
            </VCol>

            <VCol cols="12">
              <div class="text-body-2 text-medium-emphasis mb-1">
                Subject
              </div>
              <div class="text-body-1 font-weight-medium">
                {{ selectedRequest.subject }}
              </div>
            </VCol>

            <VCol cols="12">
              <div class="text-body-2 text-medium-emphasis mb-1">
                Description
              </div>
              <div class="text-body-1">
                {{ selectedRequest.description }}
              </div>
            </VCol>

            <!-- Attachments -->
            <VCol
              v-if="selectedRequest.attachments && selectedRequest.attachments.length > 0"
              cols="12"
            >
              <div class="text-body-2 text-medium-emphasis mb-2">
                Attachments ({{ selectedRequest.attachments.length }})
              </div>
              <VList
                lines="two"
                density="compact"
                class="pa-0"
              >
                <VListItem
                  v-for="(attachment, index) in selectedRequest.attachments"
                  :key="index"
                  class="px-0"
                >
                  <template #prepend>
                    <VAvatar
                      color="primary"
                      variant="tonal"
                      size="40"
                    >
                      <VIcon :icon="getFileIcon(attachment.name)" />
                    </VAvatar>
                  </template>

                  <VListItemTitle class="font-weight-medium">
                    {{ attachment.name }}
                  </VListItemTitle>
                  <VListItemSubtitle>
                    {{ attachment.size }}
                  </VListItemSubtitle>

                  <template #append>
                    <VBtn
                      icon
                      variant="text"
                      size="small"
                      color="primary"
                      @click="downloadAttachment(attachment)"
                    >
                      <VIcon icon="tabler-download" />
                      <VTooltip
                        activator="parent"
                        location="top"
                      >
                        Download
                      </VTooltip>
                    </VBtn>
                  </template>
                </VListItem>
              </VList>
            </VCol>
          </VRow>
        </VCardText>

        <VDivider />

        <VCardActions>
          <VSpacer />
          <VBtn
            variant="outlined"
            @click="isViewRequestDialogVisible = false"
          >
            Close
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Submit Request Dialog -->
    <VDialog
      v-model="isSubmitRequestDialogVisible"
      max-width="600"
      persistent
    >
      <VCard>
        <VCardItem>
          <VCardTitle>Submit Technical Request</VCardTitle>
          <template #append>
            <IconBtn @click="isSubmitRequestDialogVisible = false">
              <VIcon icon="tabler-x" />
            </IconBtn>
          </template>
        </VCardItem>

        <VDivider />

        <VForm
          v-model="isFormValid"
          @submit.prevent="submitRequest"
        >
          <VCardText>
            <VRow>
              <!-- Subject -->
              <VCol cols="12">
                <AppTextField
                  v-model="submitRequestForm.subject"
                  label="Subject"
                  placeholder="Enter request subject"
                  :rules="subjectRules"
                  required
                />
              </VCol>

              <!-- Category -->
              <VCol
                cols="12"
                sm="6"
              >
                <AppSelect
                  v-model="submitRequestForm.category"
                  label="Category"
                  placeholder="Select category"
                  :items="categoryOptions"
                  :rules="categoryRules"
                  required
                />
              </VCol>

              <!-- Priority -->
              <VCol
                cols="12"
                sm="6"
              >
                <AppSelect
                  v-model="submitRequestForm.priority"
                  label="Priority"
                  :items="priorityOptions"
                  required
                />
              </VCol>

              <!-- Description -->
              <VCol cols="12">
                <AppTextarea
                  v-model="submitRequestForm.description"
                  label="Description"
                  placeholder="Describe your technical issue in detail..."
                  rows="4"
                  :rules="descriptionRules"
                  required
                />
              </VCol>

              <!-- File Upload -->
              <VCol cols="12">
                <div class="text-body-2 text-medium-emphasis mb-2">
                  Attachments (Optional)
                </div>
                <VFileInput
                  label="Choose files"
                  multiple
                  chips
                  show-size
                  accept="image/*,.pdf,.doc,.docx,.txt,.log,.zip,.rar"
                  @change="handleFileUpload"
                />

                <!-- Display selected files -->
                <div
                  v-if="submitRequestForm.attachments.length > 0"
                  class="mt-3"
                >
                  <VChip
                    v-for="(file, index) in submitRequestForm.attachments"
                    :key="index"
                    closable
                    class="me-2 mb-2"
                    @click:close="removeAttachment(index)"
                  >
                    <VIcon
                      :icon="getFileIcon(file.name)"
                      class="me-1"
                    />
                    {{ file.name }}
                  </VChip>
                </div>
              </VCol>
            </VRow>
          </VCardText>

          <VDivider />

          <VCardActions class="pa-4">
            <VSpacer />
            <VBtn
              variant="outlined"
              @click="isSubmitRequestDialogVisible = false"
            >
              Cancel
            </VBtn>
            <VBtn
              type="submit"
              color="primary"
              :disabled="!canSubmit || isLoading"
              :loading="isLoading"
            >
              Submit Request
            </VBtn>
          </VCardActions>
        </VForm>
      </VCard>
    </VDialog>

    <!-- Delete Confirmation Dialog -->
    <VDialog
      v-model="isDeleteConfirmDialogVisible"
      max-width="500"
    >
      <VCard class="text-center px-10 py-6">
        <VCardText>
          <VIcon
            icon="tabler-alert-triangle"
            size="64"
            color="warning"
            class="mb-4"
          />
          <h3 class="text-h5 mb-2">
            Confirm Delete
          </h3>
          <p class="text-body-1 text-medium-emphasis mb-4">
            Are you sure you want to delete this technical request?
          </p>
          <div
            v-if="requestToDelete"
            class="text-start pa-4 bg-surface rounded"
          >
            <div class="text-body-2 text-medium-emphasis mb-1">
              Request ID
            </div>
            <div class="text-body-1 font-weight-medium mb-2">
              {{ requestToDelete.requestId }}
            </div>
            <div class="text-body-2 text-medium-emphasis mb-1">
              Subject
            </div>
            <div class="text-body-1">
              {{ requestToDelete.subject }}
            </div>
          </div>
          <p class="text-body-2 text-error mt-4 mb-0">
            This action cannot be undone.
          </p>
        </VCardText>
        <VCardText class="d-flex align-center justify-center gap-2">
          <VBtn
            variant="outlined"
            @click="isDeleteConfirmDialogVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="error"
            @click="confirmDelete"
          >
            Delete Request
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <!-- Bulk Delete Confirmation Dialog -->
    <VDialog
      v-model="isBulkDeleteConfirmDialogVisible"
      max-width="500"
    >
      <VCard class="text-center px-10 py-6">
        <VCardText>
          <VIcon
            icon="tabler-alert-triangle"
            size="64"
            color="warning"
            class="mb-4"
          />
          <h3 class="text-h5 mb-2">
            Confirm Bulk Delete
          </h3>
          <p class="text-body-1 text-medium-emphasis mb-4">
            Are you sure you want to delete {{ selectedRows.length }} technical request(s)?
          </p>
          <div class="text-start pa-4 bg-surface rounded">
            <div class="text-body-2 text-medium-emphasis mb-1">
              Selected Requests
            </div>
            <div class="text-body-1 font-weight-medium">
              {{ selectedRows.length }} request(s) will be permanently deleted
            </div>
          </div>
          <p class="text-body-2 text-error mt-4 mb-0">
            This action cannot be undone.
          </p>
        </VCardText>
        <VCardText class="d-flex align-center justify-center gap-2">
          <VBtn
            variant="outlined"
            @click="isBulkDeleteConfirmDialogVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="error"
            :loading="isLoading"
            @click="confirmBulkDelete"
          >
            Delete {{ selectedRows.length }} Request(s)
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>
