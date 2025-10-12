<script setup lang="ts">
import EditTechnicalRequestDrawer from '@/views/admin/modals/EditTechnicalRequestDrawer.vue'

// Define interface for technical request
interface TechnicalRequest {
  id: number
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

// Loading states
const isLoading = ref(false)
const isDeleting = ref(false)

// Update data table options
const updateOptions = (options: any) => {
  if (options.sortBy && options.sortBy.length > 0)
    sortBy.value = [options.sortBy[0]]
  else
    sortBy.value = []
}

// Headers
const headers = [
  { title: 'Request ID', key: 'requestId' },
  { title: 'User', key: 'user' },
  { title: 'Subject', key: 'subject' },
  { title: 'Priority', key: 'priority' },
  { title: 'Status', key: 'status' },
  { title: 'Date', key: 'date' },
  { title: 'Actions', key: 'actions', sortable: false },
]

// API data with proper typing
const technicalRequests = ref<TechnicalRequest[]>([])
const totalRequests = ref(0)

// Fetch technical requests from API
const fetchTechnicalRequests = async () => {
  try {
    isLoading.value = true

    const params = new URLSearchParams({
      page: page.value.toString(),
      per_page: itemsPerPage.value.toString(),
    })

    if (searchQuery.value)
      params.append('search', searchQuery.value)

    if (selectedStatus.value)
      params.append('status', selectedStatus.value)

    if (selectedPriority.value)
      params.append('priority', selectedPriority.value)

    if (sortBy.value && sortBy.value.length > 0) {
      params.append('sortBy', sortBy.value[0].key)
      params.append('sortOrder', sortBy.value[0].order || 'desc')
    }

    const response = await $api(`/v1/admin/technical-requests?${params.toString()}`)

    if (response.success) {
      // Map API response to component format
      technicalRequests.value = response.data.data.map((request: any) => ({
        id: request.id,
        requestId: request.ticket_number || `TR-${request.id}`,
        userName: request.user?.name || 'Unknown User',
        userEmail: request.user?.email || '',
        userAvatar: request.user?.avatar || '',
        subject: request.title || request.subject,
        description: request.description,
        priority: request.priority,
        status: request.status,
        date: new Date(request.created_at).toLocaleDateString(),
        category: request.category,
        attachments: request.attachments || [],
      }))

      totalRequests.value = response.data.total
    }
  }
  catch (error) {
    console.error('Error fetching technical requests:', error)

    // Show error notification
  }
  finally {
    isLoading.value = false
  }
}

// Since we're using server-side filtering, we don't need client-side filtering
const filteredRequests = computed(() => technicalRequests.value)

// Helper function for avatar text
const avatarText = (name: string | null | undefined) => {
  if (!name || typeof name !== 'string') {
    return 'U'
  }
  return name.split(' ').map(word => word.charAt(0)).join('').toUpperCase()
}

// CRUD Operations
const deleteRequest = async (id: number) => {
  try {
    isDeleting.value = true
    await $api(`/api/v1/admin/technical-requests/${id}`, {
      method: 'DELETE',
    })
    await fetchTechnicalRequests()
  }
  catch (error) {
    console.error('Error deleting request:', error)
  }
  finally {
    isDeleting.value = false
  }
}

const updateRequestStatus = async (id: number, status: string) => {
  try {
    await $api(`/api/v1/admin/technical-requests/${id}/status`, {
      method: 'PATCH',
      body: { status },
    })
    await fetchTechnicalRequests()
  }
  catch (error) {
    console.error('Error updating status:', error)
  }
}

const bulkDelete = async () => {
  if (selectedRows.value.length === 0)
    return

  try {
    isDeleting.value = true
    await $api('/api/v1/admin/technical-requests/bulk-delete', {
      method: 'DELETE',
      body: { ids: selectedRows.value },
    })
    selectedRows.value = []
    await fetchTechnicalRequests()
  }
  catch (error) {
    console.error('Error bulk deleting:', error)
  }
  finally {
    isDeleting.value = false
  }
}

// Status options
const statusOptions = [
  { title: 'Open', value: 'open' },
  { title: 'In Progress', value: 'in-progress' },
  { title: 'Resolved', value: 'resolved' },
  { title: 'Closed', value: 'closed' },
]

// Priority options
const priorityOptions = [
  { title: 'Low', value: 'low' },
  { title: 'Medium', value: 'medium' },
  { title: 'High', value: 'high' },
  { title: 'Critical', value: 'critical' },
]

const resolveStatusVariant = (status: string | null | undefined) => {
  if (!status || typeof status !== 'string') {
    return 'primary'
  }
  
  const statusLowerCase = status.toLowerCase()
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

const resolvePriorityVariant = (priority: string | null | undefined) => {
  if (!priority || typeof priority !== 'string') {
    return { color: 'primary', icon: 'tabler-minus' }
  }
  
  const priorityLowerCase = priority.toLowerCase()
  if (priorityLowerCase === 'low')
    return { color: 'info', icon: 'tabler-arrow-down' }
  if (priorityLowerCase === 'medium')
    return { color: 'warning', icon: 'tabler-minus' }
  if (priorityLowerCase === 'high')
    return { color: 'error', icon: 'tabler-arrow-up' }
  if (priorityLowerCase === 'critical')
    return { color: 'error', icon: 'tabler-alert-triangle' }

  return { color: 'primary', icon: 'tabler-minus' }
}

const isViewRequestDialogVisible = ref(false)
const isEditRequestDrawerVisible = ref(false)
const selectedRequest = ref<any>(null)

// View request
const viewRequest = (request: any) => {
  selectedRequest.value = {
    ...request,
    attachments: Array.isArray(request.attachments) ? request.attachments : []
  }
  isViewRequestDialogVisible.value = true
}

// Edit request
const editRequest = (request: any) => {
  selectedRequest.value = { ...request }
  isEditRequestDrawerVisible.value = true
}

// Update request
const updateRequest = (requestData: any) => {
  const index = technicalRequests.value.findIndex(r => r.id === requestData.id)
  if (index !== -1)
    technicalRequests.value[index] = { ...technicalRequests.value[index], ...requestData }

  selectedRequest.value = null
}

// Change status (using API)
const changeStatus = async (id: number, newStatus: string) => {
  await updateRequestStatus(id, newStatus)
}

// Export functions
const exportToCSV = () => {
  const dataToExport = selectedRows.value.length > 0
    ? technicalRequests.value.filter(request => selectedRows.value.includes(request.id))
    : technicalRequests.value

  console.log('Exporting to CSV:', dataToExport)

  // Implement CSV export logic
}

const exportToPDF = () => {
  const dataToExport = selectedRows.value.length > 0
    ? technicalRequests.value.filter(request => selectedRows.value.includes(request.id))
    : technicalRequests.value

  console.log('Exporting to PDF:', dataToExport)

  // Implement PDF export logic
}

// Bulk delete is now handled by the API version above

// Download attachment
const downloadAttachment = (attachment: any) => {
  console.log('Downloading:', attachment.name)

  // In production, this would trigger actual file download
  // window.open(attachment.url, '_blank')
}

// Get file icon based on extension
const getFileIcon = (fileName: string | null | undefined) => {
  if (!fileName || typeof fileName !== 'string') {
    return 'tabler-file'
  }

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

// Fetch data on component mount
onMounted(() => {
  fetchTechnicalRequests()
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

          <!-- Export Menu -->
          <VBtn
            variant="tonal"
            color="secondary"
          >
            <VIcon
              icon="tabler-upload"
              class="me-2"
            />
            Export
            <VMenu activator="parent">
              <VList>
                <VListItem @click="exportToCSV">
                  <template #prepend>
                    <VIcon icon="tabler-file-type-csv" />
                  </template>
                  <VListItemTitle>Export to CSV</VListItemTitle>
                </VListItem>
                <VListItem @click="exportToPDF">
                  <template #prepend>
                    <VIcon icon="tabler-file-type-pdf" />
                  </template>
                  <VListItemTitle>Export to PDF</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </VBtn>
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

        <!-- User -->
        <template #item.user="{ item }">
          <div class="d-flex align-center gap-x-4">
            <VAvatar
              size="34"
              :variant="!item.userAvatar ? 'tonal' : undefined"
              color="primary"
            >
              <VImg
                v-if="item.userAvatar"
                :src="item.userAvatar"
              />
              <span v-else>{{ avatarText(item.userName) }}</span>
            </VAvatar>
            <div class="d-flex flex-column">
              <h6 class="text-base font-weight-medium">
                {{ item.userName }}
              </h6>
              <div class="text-sm text-medium-emphasis">
                {{ item.userEmail }}
              </div>
            </div>
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
            <IconBtn
              size="small"
              @click="viewRequest(item)"
            >
              <VIcon icon="tabler-eye" />
              <VTooltip
                activator="parent"
                location="top"
              >
                View
              </VTooltip>
            </IconBtn>

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

                  <VListItem @click="editRequest(item)">
                    <template #prepend>
                      <VIcon icon="tabler-pencil" />
                    </template>
                    <VListItemTitle>Edit</VListItemTitle>
                  </VListItem>

                  <VDivider class="my-2" />

                  <VListSubheader>Change Status</VListSubheader>

                  <VListItem @click="changeStatus(item.id, 'open')">
                    <template #prepend>
                      <VIcon
                        icon="tabler-circle"
                        color="info"
                      />
                    </template>
                    <VListItemTitle>Open</VListItemTitle>
                  </VListItem>

                  <VListItem @click="changeStatus(item.id, 'in-progress')">
                    <template #prepend>
                      <VIcon
                        icon="tabler-circle"
                        color="warning"
                      />
                    </template>
                    <VListItemTitle>In Progress</VListItemTitle>
                  </VListItem>

                  <VListItem @click="changeStatus(item.id, 'resolved')">
                    <template #prepend>
                      <VIcon
                        icon="tabler-circle"
                        color="success"
                      />
                    </template>
                    <VListItemTitle>Resolved</VListItemTitle>
                  </VListItem>

                  <VListItem @click="changeStatus(item.id, 'closed')">
                    <template #prepend>
                      <VIcon
                        icon="tabler-circle"
                        color="secondary"
                      />
                    </template>
                    <VListItemTitle>Closed</VListItemTitle>
                  </VListItem>

                  <VDivider class="my-2" />

                  <VListItem @click="deleteRequest(item.id)">
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
            <VCol cols="12">
              <div class="d-flex align-center gap-x-4 mb-4">
                <VAvatar
                  size="48"
                  :variant="!selectedRequest.userAvatar ? 'tonal' : undefined"
                  color="primary"
                >
                  <VImg
                    v-if="selectedRequest.userAvatar"
                    :src="selectedRequest.userAvatar"
                  />
                  <span v-else>{{ avatarText(selectedRequest.userName) }}</span>
                </VAvatar>
                <div>
                  <h6 class="text-h6">
                    {{ selectedRequest.userName }}
                  </h6>
                  <div class="text-body-2 text-medium-emphasis">
                    {{ selectedRequest.userEmail }}
                  </div>
                </div>
              </div>
            </VCol>

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
              v-if="selectedRequest.attachments && Array.isArray(selectedRequest.attachments) && selectedRequest.attachments.length > 0"
              cols="12"
            >
              <div class="text-body-2 text-medium-emphasis mb-2">
                Attachments ({{ Array.isArray(selectedRequest.attachments) ? selectedRequest.attachments.filter(att => att && (att.name || att.filename)).length : 0 }})
              </div>
              <VList
                lines="two"
                density="compact"
                class="pa-0"
              >
                <VListItem
                  v-for="(attachment, index) in (Array.isArray(selectedRequest.attachments) ? selectedRequest.attachments.filter(att => att && (att.name || att.filename)) : [])"
                  :key="index"
                  class="px-0"
                >
                  <template #prepend>
                    <VAvatar
                      color="primary"
                      variant="tonal"
                      size="40"
                    >
                      <VIcon :icon="getFileIcon(attachment.name || attachment.filename)" />
                    </VAvatar>
                  </template>

                  <VListItemTitle class="font-weight-medium">
                    {{ attachment.name || attachment.filename || 'Unknown File' }}
                  </VListItemTitle>
                  <VListItemSubtitle>
                    {{ attachment.size || 'Unknown Size' }}
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
          <VBtn
            color="primary"
            @click="editRequest(selectedRequest); isViewRequestDialogVisible = false"
          >
            Edit Request
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Edit Technical Request Drawer -->
    <EditTechnicalRequestDrawer
      v-model:is-drawer-open="isEditRequestDrawerVisible"
      :request="selectedRequest"
      @request-data="updateRequest"
    />
  </section>
</template>
