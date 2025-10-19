<script setup lang="ts">
// 👉 Interfaces
interface Lead {
  id: number
  first_name: string
  last_name: string
  email: string
  status: string
  priority: string
}

interface SalesAssociate {
  id: number | null
  name: string
  email: string
  phone: string
  status: string
  country?: string
  state?: string
  city?: string
  assignedLeads: number
  avatar?: string
  avatarText?: string
  leads?: Lead[]
  password?: string // For creating new associates
}

interface SalesAssociatesResponse {
  associates: SalesAssociate[]
  total: number
}

// 👉 Store
const searchQuery = ref('')
const selectedStatus = ref()

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref<(number | null)[]>([])

// Loading states
const isLoading = ref(false)
const isSubmitting = ref(false)
const isLoadingAssociateDetails = ref(false)
const associateForm = ref()

// Update data table options
const updateOptions = (options: any) => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
  fetchSalesAssociates()
}

// Headers
const headers = [
  { title: 'Sales Associate', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Phone', key: 'phone' },
  { title: 'Status', key: 'status' },
  { title: 'Assigned Leads', key: 'assignedLeads' },
  { title: 'Actions', key: 'actions', sortable: false },
]

// API data
const salesAssociatesData = ref<SalesAssociatesResponse>({
  associates: [],
  total: 0,
})

const associates = computed(() => salesAssociatesData.value.associates)
const totalAssociates = computed(() => salesAssociatesData.value.total)

// 👉 Helper functions for lead colors
const getLeadStatusColor = (status: string) => {
  const statusColors: Record<string, string> = {
    new: 'primary',
    contacted: 'info',
    qualified: 'warning',
    converted: 'success',
    lost: 'error',
    follow_up: 'secondary',
  }

  return statusColors[status] || 'default'
}

const getLeadPriorityColor = (priority: string) => {
  const priorityColors: Record<string, string> = {
    low: 'success',
    medium: 'warning',
    high: 'error',
  }

  return priorityColors[priority] || 'default'
}

// 👉 API Functions
const fetchSalesAssociates = async () => {
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
    if (sortBy.value)
      params.append('sort_by', sortBy.value)
    if (orderBy.value)
      params.append('order_by', orderBy.value)

    const response = await $api(`/v1/franchisor/sales-associates?${params.toString()}`, {
      method: 'GET',
    })

    if (response.success) {
      salesAssociatesData.value = {
        associates: response.data || [],
        total: response.total || 0,
      }
    }
  }
  catch (error) {
    console.error('Error fetching sales associates:', error)

    // Handle error - show toast notification
  }
  finally {
    isLoading.value = false
  }
}

// 👉 search filters
const statuses = [
  { title: 'Active', value: 'active' },
  { title: 'Inactive', value: 'inactive' },
]

const countries = [
  { title: 'Saudi Arabia', value: 'Saudi Arabia' },
  { title: 'United Arab Emirates', value: 'United Arab Emirates' },
  { title: 'Qatar', value: 'Qatar' },
  { title: 'Kuwait', value: 'Kuwait' },
  { title: 'Oman', value: 'Oman' },
  { title: 'Bahrain', value: 'Bahrain' },
  { title: 'Jordan', value: 'Jordan' },
  { title: 'Lebanon', value: 'Lebanon' },
  { title: 'Egypt', value: 'Egypt' },
  { title: 'Iraq', value: 'Iraq' },
  { title: 'Palestine', value: 'Palestine' },
  { title: 'Syria', value: 'Syria' },
  { title: 'Yemen', value: 'Yemen' },
  { title: 'Iran', value: 'Iran' },
]

const resolveStatusVariant = (stat: string) => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === 'active')
    return 'success'
  if (statLowerCase === 'inactive')
    return 'secondary'

  return 'primary'
}

// 👉 Delete associate with confirmation
const isDeleteDialogVisible = ref(false)
const associateToDelete = ref<number | null>(null)

const confirmDelete = (id: number) => {
  associateToDelete.value = id
  isDeleteDialogVisible.value = true
}

const deleteAssociate = async () => {
  if (associateToDelete.value === null)
    return

  try {
    isSubmitting.value = true

    const response = await $api(`/v1/franchisor/sales-associates/${associateToDelete.value}`, {
      method: 'DELETE',
    })

    if (response.success) {
      // Remove from local data
      const index = salesAssociatesData.value.associates.findIndex(associate => associate.id === associateToDelete.value)
      if (index !== -1) {
        salesAssociatesData.value.associates.splice(index, 1)
        salesAssociatesData.value.total -= 1
      }

      // Delete from selectedRows
      const selectedIndex = selectedRows.value.findIndex(row => row === associateToDelete.value)
      if (selectedIndex !== -1)
        selectedRows.value.splice(selectedIndex, 1)

      // Show success message
      console.log('Sales associate deleted successfully')
    }
  }
  catch (error) {
    console.error('Error deleting sales associate:', error)

    // Handle error - show toast notification
  }
  finally {
    isSubmitting.value = false
    isDeleteDialogVisible.value = false
    associateToDelete.value = null
  }
}

// 👉 View and Edit functions
// 👉 Modal states
const isViewAssociateModalVisible = ref(false)
const isEditAssociateModalVisible = ref(false)
const isAddAssociateModalVisible = ref(false)
const selectedAssociate = ref<SalesAssociate | null>(null)

// 👉 View associate
const viewAssociate = async (id: number | null) => {
  if (id === null)
    return
  const associate = associates.value.find(a => a.id === id)
  if (associate) {
    try {
      isLoading.value = true

      // Fetch detailed associate data including leads
      const response = await $api(`/v1/franchisor/sales-associates/${associate.id}`)

      if (response.success) {
        selectedAssociate.value = response.data
        isViewAssociateModalVisible.value = true
      }
    }
    catch (error) {
      console.error('Error fetching associate details:', error)

      // Fallback to basic data
      selectedAssociate.value = associate
      isViewAssociateModalVisible.value = true
    }
    finally {
      isLoading.value = false
    }
  }
}

// 👉 Edit associate
const editAssociate = (id: number | null) => {
  if (id === null)
    return
  const associate = associates.value.find(a => a.id === id)
  if (associate) {
    selectedAssociate.value = { ...associate }
    isEditAssociateModalVisible.value = true
  }
}

// 👉 Add Sales Associate
const addSalesAssociate = () => {
  selectedAssociate.value = {
    id: null,
    name: '',
    email: '',
    phone: '',
    status: 'active',
    country: '',
    state: '',
    city: '',
    assignedLeads: 0, // This is for display only, not sent to API
    avatar: '',
    password: '', // For new associates
  }
  isAddAssociateModalVisible.value = true
}

// 👉 Save associate (for both add and edit)
const saveAssociate = async () => {
  if (!selectedAssociate.value)
    return

  // Validate form before submission
  if (associateForm.value) {
    const { valid } = await associateForm.value.validate()
    if (!valid)
      return
  }

  try {
    isSubmitting.value = true

    const isEditing = selectedAssociate.value.id !== null
    const method = isEditing ? 'PUT' : 'POST'

    const url = isEditing
      ? `/v1/franchisor/sales-associates/${selectedAssociate.value.id}`
      : '/v1/franchisor/sales-associates'

    // Prepare form data
    const formData: any = {
      name: selectedAssociate.value.name,
      email: selectedAssociate.value.email,
      phone: selectedAssociate.value.phone,
      status: selectedAssociate.value.status,
      country: selectedAssociate.value.country || '',
      state: selectedAssociate.value.state || '',
      city: selectedAssociate.value.city || '',
    }

    // Add password for new associates
    if (!isEditing && selectedAssociate.value.password)
      formData.password = selectedAssociate.value.password

    const response = await $api(url, {
      method,
      body: formData,
    })

    if (response.success) {
      // Reset form validation first
      if (associateForm.value)
        associateForm.value.reset()

      // Close modals
      isAddAssociateModalVisible.value = false
      isEditAssociateModalVisible.value = false

      // Reset selectedAssociate to initial state instead of null
      selectedAssociate.value = {
        id: null,
        name: '',
        email: '',
        phone: '',
        status: 'active',
        country: '',
        state: '',
        city: '',
        password: '',
        assignedLeads: 0,
        avatar: '',
        avatarText: '',
      }

      // Refresh the associates list
      await fetchSalesAssociates()

      // Show success message (you can add a toast notification here)
      console.log(isEditing ? 'Associate updated successfully' : 'Associate created successfully')
    }
  }
  catch (error) {
    console.error('Error saving associate:', error)

    // Handle error (you can add error notification here)
  }
  finally {
    isSubmitting.value = false
  }
}

// 👉 Export functionality
const exportToCSV = () => {
  const dataToExport = selectedRows.value.length > 0
    ? associates.value.filter(associate => selectedRows.value.includes(associate.id))
    : associates.value

  const csvContent = [
    'Name,Email,Phone,Status,Country,State,City,Assigned Leads',
    ...dataToExport.map(associate =>
      `"${associate.name}","${associate.email}","${associate.phone}","${associate.status}","${associate.country || ''}","${associate.state || ''}","${associate.city || ''}","${associate.assignedLeads}"`,
    ),
  ].join('\n')

  const blob = new Blob([csvContent], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')

  a.href = url
  a.download = `sales_associates_${selectedRows.value.length > 0 ? 'selected' : 'all'}_${new Date().toISOString().split('T')[0]}.csv`
  a.click()
  window.URL.revokeObjectURL(url)
}

const exportToPDF = () => {
  const dataToExport = selectedRows.value.length > 0
    ? associates.value.filter(associate => selectedRows.value.includes(associate.id))
    : associates.value

  console.log('Exporting to PDF:', dataToExport)

  // TODO: Implement PDF export logic
}

// 👉 Lifecycle hooks
onMounted(() => {
  fetchSalesAssociates()
})
</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Filters</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <!-- 👉 Select Status -->
          <VCol
            cols="12"
            sm="6"
          >
            <AppSelect
              v-model="selectedStatus"
              placeholder="Select Status"
              :items="statuses"
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
          <!-- 👉 Search  -->
          <div style="inline-size: 15.625rem;">
            <AppTextField
              v-model="searchQuery"
              placeholder="Search Sales Associate"
            />
          </div>

          <!-- 👉 Export Menu -->
          <VBtn
            variant="tonal"
            color="secondary"
          >
            <VIcon
              icon="tabler-upload"
              class="me-2"
            />
            Export {{ selectedRows.length > 0 ? `(${selectedRows.length})` : 'All' }}
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

          <!-- 👉 Add associate button -->
          <VBtn
            prepend-icon="tabler-plus"
            @click="addSalesAssociate"
          >
            Add Sales Associate
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:model-value="selectedRows"
        v-model:page="page"
        :items="associates"
        item-value="id"
        :items-length="totalAssociates"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Sales Associate -->
        <template #item.name="{ item }">
          <div class="d-flex align-center gap-x-4">
            <VAvatar
              size="34"
              :variant="!item.avatar ? 'tonal' : undefined"
              :color="!item.avatar ? 'primary' : undefined"
            >
              <VImg
                v-if="item.avatar"
                :src="item.avatar"
              />
              <span v-else>{{ avatarText(item.name) }}</span>
            </VAvatar>
            <div class="d-flex flex-column">
              <h6 class="text-base font-weight-medium">
                {{ item.name }}
              </h6>
            </div>
          </div>
        </template>

        <!-- Email -->
        <template #item.email="{ item }">
          <div class="text-body-1">
            {{ item.email }}
          </div>
        </template>

        <!-- Phone -->
        <template #item.phone="{ item }">
          <div class="text-body-1">
            {{ item.phone }}
          </div>
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

        <!-- Assigned Leads -->
        <template #item.assignedLeads="{ item }">
          <div class="text-body-1 font-weight-medium">
            {{ item.assignedLeads }}
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <VBtn
            icon
            variant="text"
            color="medium-emphasis"
          >
            <VIcon icon="tabler-dots-vertical" />
            <VMenu activator="parent">
              <VList>
                <VListItem @click="viewAssociate(item.id)">
                  <template #prepend>
                    <VIcon icon="tabler-eye" />
                  </template>
                  <VListItemTitle>View</VListItemTitle>
                </VListItem>

                <VListItem @click="editAssociate(item.id)">
                  <template #prepend>
                    <VIcon icon="tabler-pencil" />
                  </template>
                  <VListItemTitle>Edit</VListItemTitle>
                </VListItem>

                <VListItem @click="confirmDelete(item.id)">
                  <template #prepend>
                    <VIcon icon="tabler-trash" />
                  </template>
                  <VListItemTitle>Delete</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </VBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalAssociates"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>

    <!-- 👉 View Associate Modal -->
    <VDialog
      v-model="isViewAssociateModalVisible"
      max-width="600"
    >
      <DialogCloseBtn @click="isViewAssociateModalVisible = false" />
      <VCard v-if="selectedAssociate" title="Sales Associate Details">
        <VCardText>
          <VRow>
            <VCol cols="12">
              <div class="d-flex align-center gap-4 mb-6">
                <VAvatar
                  size="80"
                  variant="tonal"
                  color="primary"
                >
                  <span class="text-h4">{{ avatarText(selectedAssociate.name) }}</span>
                </VAvatar>
                <div>
                  <h4 class="text-h4 mb-1">
                    {{ selectedAssociate.name }}
                  </h4>
                  <VChip
                    :color="resolveStatusVariant(selectedAssociate?.status || '')"
                    size="small"
                    label
                    class="text-capitalize"
                  >
                    {{ selectedAssociate.status }}
                  </VChip>
                </div>
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">
                  Email
                </div>
                <div class="text-body-1">
                  {{ selectedAssociate.email }}
                </div>
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">
                  Phone
                </div>
                <div class="text-body-1">
                  {{ selectedAssociate.phone }}
                </div>
              </div>
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">
                  Country
                </div>
                <div class="text-body-1">
                  {{ selectedAssociate.country || 'Not specified' }}
                </div>
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">
                  State
                </div>
                <div class="text-body-1">
                  {{ selectedAssociate.state || 'Not specified' }}
                </div>
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">
                  City
                </div>
                <div class="text-body-1">
                  {{ selectedAssociate.city || 'Not specified' }}
                </div>
              </div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">
                  Assigned Leads
                </div>
                <div class="text-body-1">
                  {{ selectedAssociate.assignedLeads }}
                </div>
              </div>
            </VCol>
          </VRow>

          <!-- Assigned Leads Details -->
          <VRow v-if="selectedAssociate.leads && selectedAssociate.leads.length > 0">
            <VCol cols="12">
              <VDivider class="my-4" />
              <div class="text-h6 mb-4">
                Assigned Leads
              </div>
              <VCard variant="outlined">
                <VCardText class="pa-0">
                  <VTable>
                    <thead>
                      <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Priority</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        v-for="lead in selectedAssociate?.leads || []"
                        :key="lead.id"
                      >
                        <td>{{ lead.first_name }} {{ lead.last_name }}</td>
                        <td>{{ lead.email }}</td>
                        <td>
                          <VChip
                            :color="getLeadStatusColor(lead.status)"
                            size="small"
                            class="text-capitalize"
                          >
                            {{ lead.status.replace('_', ' ') }}
                          </VChip>
                        </td>
                        <td>
                          <VChip
                            :color="getLeadPriorityColor(lead.priority)"
                            size="small"
                            class="text-capitalize"
                          >
                            {{ lead.priority }}
                          </VChip>
                        </td>
                      </tr>
                    </tbody>
                  </VTable>
                </VCardText>
              </VCard>
            </VCol>
          </VRow>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isViewAssociateModalVisible = false"
          >
            Close
          </VBtn>
          <VBtn
            color="primary"
            @click="editAssociate(selectedAssociate.id)"
          >
            Edit
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- 👉 Add/Edit Associate Modal -->
    <VDialog
      :model-value="isAddAssociateModalVisible || isEditAssociateModalVisible"
      max-width="600"
      persistent
      @update:model-value="val => { if (!val) { isAddAssociateModalVisible = false; isEditAssociateModalVisible = false } }"
    >
      <DialogCloseBtn @click="isAddAssociateModalVisible = false; isEditAssociateModalVisible = false" />
      <VCard v-if="selectedAssociate" :title="selectedAssociate.id === null ? 'Add New' : 'Edit' + ' Sales Associate'">
        <VCardText>
          <VForm
            ref="associateForm"
            @submit.prevent="saveAssociate"
          >
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="selectedAssociate.name"
                  label="Full Name"
                  placeholder="Enter full name"
                  :rules="[
                    (v: string) => !!v || 'Full name is required',
                    (v: string) => v.length <= 255 || 'Name must not exceed 255 characters',
                  ]"
                  required
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="selectedAssociate.email"
                  label="Email Address"
                  type="email"
                  placeholder="Enter email address"
                  :rules="[
                    (v: string) => !!v || 'Email is required',
                    (v: string) => /.+@.+\..+/.test(v) || 'Email must be valid',
                  ]"
                  required
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="selectedAssociate.phone"
                  label="Phone Number"
                  placeholder="+966 50 123 4567"
                  :rules="[
                    (v: string) => !!v || 'Phone number is required',
                    (v: string) => v.length <= 20 || 'Phone number must not exceed 20 characters',
                  ]"
                  required
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <AppSelect
                  v-model="selectedAssociate.status"
                  label="Status"
                  :items="statuses"
                  placeholder="Select status"
                  :rules="[(v: string) => !!v || 'Status is required']"
                  required
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppSelect
                  v-model="selectedAssociate.country"
                  label="Country"
                  :items="countries"
                  placeholder="Select country"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="selectedAssociate.state"
                  label="State/Province"
                  placeholder="Enter state or province"
                  :rules="[
                    (v: string) => !v || v.length <= 100 || 'State must not exceed 100 characters',
                  ]"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="selectedAssociate.city"
                  label="City"
                  placeholder="Enter city"
                  :rules="[
                    (v: string) => !v || v.length <= 100 || 'City must not exceed 100 characters',
                  ]"
                />
              </VCol>

              <!-- Password field for new associates only -->
              <VCol
                v-if="selectedAssociate.id === null"
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="selectedAssociate.password"
                  label="Password"
                  type="password"
                  placeholder="Enter password (minimum 8 characters)"
                  :rules="[
                    (v: string) => !!v || 'Password is required',
                    (v: string) => v.length >= 8 || 'Password must be at least 8 characters',
                  ]"
                  required
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
            :disabled="isSubmitting"
            @click="isAddAssociateModalVisible = false; isEditAssociateModalVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            :loading="isSubmitting"
            @click="saveAssociate"
          >
            {{ selectedAssociate.id === null ? 'Create Associate' : 'Save Changes' }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- 👉 Delete Confirmation Dialog -->
    <VDialog
      v-model="isDeleteDialogVisible"
      max-width="600"
    >
      <DialogCloseBtn @click="isDeleteDialogVisible = false" />
      <VCard title="Confirm Delete">
        <VCardText>
          Are you sure you want to delete this sales associate? This action cannot be undone.
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isDeleteDialogVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="error"
            @click="deleteAssociate"
          >
            Delete
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </section>
</template>
