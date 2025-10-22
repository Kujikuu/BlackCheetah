<script setup lang="ts">
import AddEditBrokerDrawer from '@/views/admin/modals/AddEditBrokerDrawer.vue'
import ConfirmDeleteDialog from '@/views/admin/modals/ConfirmDeleteDialog.vue'
import ResetPasswordDialog from '@/views/admin/modals/ResetPasswordDialog.vue'
import ViewUserDialog from '@/views/admin/modals/ViewUserDialog.vue'
import { adminApi } from '@/services/api'

interface BrokerUser {
  id: number
  fullName: string
  email: string
  phone: string
  location: string
  city: string
  status: string
  avatar?: string
  joinedDate: string
}

// Store
const searchQuery = ref('')
const selectedStatus = ref()

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref([{ key: 'created_at', order: 'desc' }])
const orderBy = ref('desc')
const selectedRows = ref<number[]>([])

// Update data table options
const updateOptions = (options: any) => {
  if (options.sortBy && options.sortBy.length > 0) {
    sortBy.value = options.sortBy
    orderBy.value = options.sortBy[0]?.order
  }
}

// Headers
const headers = [
  { title: 'User', key: 'user' },
  { title: 'City', key: 'city' },
  { title: 'Email', key: 'email' },
  { title: 'Phone', key: 'phone' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false },
]

// API data
const brokerUsers = ref<BrokerUser[]>([])
const isLoading = ref(false)
const error = ref('')
const totalBrokerUsers = ref(0)

// Fetch broker users from API
const fetchBrokerUsers = async () => {
  isLoading.value = true
  error.value = ''

  try {
    const response = await adminApi.getBrokers()

    if (response.success && response.data) {
      // Handle both direct array and paginated response structures
      let userData: any[] = []

      if (Array.isArray(response.data)) {
        // Direct array response
        userData = response.data
      } else if (response.data && typeof response.data === 'object' && 'data' in response.data && Array.isArray((response.data as any).data)) {
        // Paginated response structure
        userData = (response.data as any).data
      }

      console.log('Broker Users API Response:', { response, userData }) // Debug log

      brokerUsers.value = userData.map((user: any) => ({
        id: user.id,
        fullName: user.fullName || user.name, // Try both possible field names
        email: user.email,
        phone: user.phone || '',
        location: user.location || '',
        city: user.city || user.location || '',
        status: user.status,
        avatar: user.avatar,
        joinedDate: user.joinedDate || user.createdAt || user.created_at,
      }))
      totalBrokerUsers.value = brokerUsers.value.length
    }
    else {
      brokerUsers.value = []
      totalBrokerUsers.value = 0
      error.value = response.message || 'Failed to fetch broker users'
    }
  }
  catch (err) {
    console.error('Error fetching broker users:', err)
    error.value = 'Failed to fetch broker users'
  }
  finally {
    isLoading.value = false
  }
}

// Filtered data
const filteredBrokerUsers = computed(() => {
  let filtered = brokerUsers.value

  if (searchQuery.value) {
    filtered = filtered.filter(broker =>
      broker.fullName.toLowerCase().includes(searchQuery.value.toLowerCase())
      || broker.email.toLowerCase().includes(searchQuery.value.toLowerCase())
      || broker.city.toLowerCase().includes(searchQuery.value.toLowerCase()),
    )
  }

  if (selectedStatus.value)
    filtered = filtered.filter(broker => broker.status === selectedStatus.value)

  return filtered
})

// Status options
const statusOptions = [
  { title: 'Pending', value: 'pending' },
  { title: 'Active', value: 'active' },
  { title: 'Inactive', value: 'inactive' },
]

const resolveUserStatusVariant = (stat: string) => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === 'pending')
    return 'warning'
  if (statLowerCase === 'active')
    return 'success'
  if (statLowerCase === 'inactive')
    return 'secondary'

  return 'primary'
}

const isAddNewUserDrawerVisible = ref(false)
const isDeleteDialogVisible = ref(false)
const isResetPasswordDialogVisible = ref(false)
const isViewDialogVisible = ref(false)
const selectedBrokerUser = ref<any>(null)
const userToDelete = ref<any>(null)

// Utility functions
const prefixWithPlus = (value: number) => value > 0 ? `+${value}` : value

const avatarText = (name: string | null | undefined) => {
  if (!name || typeof name !== 'string')
    return 'U'

  const words = name.split(' ')

  return words.length > 1 ? `${words[0][0]}${words[1][0]}` : name.slice(0, 2)
}

// Add new broker user
const addNewBrokerUser = async (brokerUserData: any) => {
  try {
    const response = await adminApi.createUser({
      ...brokerUserData,
      role: 'broker',
    })

    if (response.success)
      await fetchBrokerUsers() // Refresh the list
    else
      error.value = response.message || 'Failed to create broker user'
  }
  catch (err) {
    console.error('Error creating broker user:', err)
    error.value = 'Failed to create broker user'
  }
}

// Edit broker user
const editBrokerUser = (brokerUser: BrokerUser) => {
  selectedBrokerUser.value = { ...brokerUser }
  isAddNewUserDrawerVisible.value = true
}

// Update broker user
const updateBrokerUser = async (brokerUserData: any) => {
  try {
    const response = await adminApi.updateUser(brokerUserData.id, brokerUserData)

    if (response.success) {
      await fetchBrokerUsers() // Refresh the list
      selectedBrokerUser.value = null
    }
    else {
      error.value = response.message || 'Failed to update broker user'
    }
  }
  catch (err) {
    console.error('Error updating broker user:', err)
    error.value = 'Failed to update broker user'
  }
}

// Handle drawer data
const handleBrokerUserData = async (brokerUserData: any) => {
  if (brokerUserData.id)
    await updateBrokerUser(brokerUserData)

  else
    await addNewBrokerUser(brokerUserData)
}

// Open delete dialog
const openDeleteDialog = (brokerUser: any) => {
  userToDelete.value = brokerUser
  isDeleteDialogVisible.value = true
}

// Delete user
const deleteUser = async () => {
  if (!userToDelete.value)
    return

  try {
    const response = await adminApi.deleteUser(userToDelete.value.id)

    if (response.success) {
      await fetchBrokerUsers() // Refresh the list

      // Remove from selectedRows
      const selectedIndex = selectedRows.value.findIndex(row => row === userToDelete.value.id)
      if (selectedIndex !== -1)
        selectedRows.value.splice(selectedIndex, 1)
    }
    else {
      error.value = response.message || 'Failed to delete broker user'
    }
  }
  catch (err) {
    console.error('Error deleting broker user:', err)
    error.value = 'Failed to delete broker user'
  }

  userToDelete.value = null
}

// Bulk delete users
const bulkDelete = async () => {
  if (selectedRows.value.length === 0) return

  try {
    // Delete multiple users sequentially
    for (const userId of selectedRows.value) {
      await adminApi.deleteUser(userId)
    }

    await fetchBrokerUsers() // Refresh the list
    selectedRows.value = [] // Clear selection
  }
  catch (err) {
    console.error('Error bulk deleting broker users:', err)
    error.value = 'Failed to delete selected broker users'
  }
}

// Open reset password dialog
const openResetPasswordDialog = (brokerUser: BrokerUser) => {
  selectedBrokerUser.value = brokerUser
  isResetPasswordDialogVisible.value = true
}

// Reset password
const resetPassword = async (password: string) => {
  if (!selectedBrokerUser.value)
    return

  try {
    const response = await adminApi.resetUserPassword(selectedBrokerUser.value.id, password)

    if (response.success)
      console.log('Password reset successfully for:', selectedBrokerUser.value.fullName)
    else
      error.value = response.message || 'Failed to reset password'
  }
  catch (err) {
    console.error('Error resetting password:', err)
    error.value = 'Failed to reset password'
  }

  selectedBrokerUser.value = null
}

// Handle drawer close
const handleDrawerClose = () => {
  selectedBrokerUser.value = null
}

// Widget data
const widgetData = ref([
  { title: 'Total Broker Users', value: '0', change: 0, desc: 'All broker team members', icon: 'tabler-chart-line', iconColor: 'primary' },
  { title: 'Active Broker', value: '0', change: 0, desc: 'Currently active', icon: 'tabler-user-check', iconColor: 'success' },
  { title: 'Pending Approval', value: '0', change: 0, desc: 'Awaiting verification', icon: 'tabler-clock', iconColor: 'warning' },
])

// Fetch widget statistics
const fetchWidgetStats = async () => {
  try {
    const response = await adminApi.getBrokersStats()
    if (response.success)
      widgetData.value = response.data
  }
  catch (err) {
    console.error('Error fetching broker stats:', err)
  }
}

// View user
const viewUser = (brokerUser: any) => {
  selectedBrokerUser.value = brokerUser
  isViewDialogVisible.value = true
}

// Handle edit from view dialog
const handleEditFromView = () => {
  isAddNewUserDrawerVisible.value = true
}

// Export functions
const exportToCSV = () => {
  const dataToExport = selectedRows.value.length > 0
    ? brokerUsers.value.filter(broker => selectedRows.value.includes(broker.id))
    : brokerUsers.value

  console.log('Exporting to CSV:', dataToExport)

  // Implement CSV export logic
}

const exportToPDF = () => {
  const dataToExport = selectedRows.value.length > 0
    ? brokerUsers.value.filter(broker => selectedRows.value.includes(broker.id))
    : brokerUsers.value

  console.log('Exporting to PDF:', dataToExport)

  // Implement PDF export logic
}

// Fetch data on component mount
onMounted(() => {
  fetchBrokerUsers()
  fetchWidgetStats()
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
              Sales Team Management
            </h2>
            <p class="text-body-1 text-medium-emphasis">
              Manage and monitor all sales team members
            </p>
          </div>
        </div>
      </VCol>
    </VRow>

    <!-- Widgets -->
    <VRow class="mb-6">
      <template v-for="(data, id) in widgetData" :key="id">
        <VCol cols="12" md="4" sm="6">
          <VCard>
            <VCardText>
              <div class="d-flex justify-space-between">
                <div class="d-flex flex-column gap-y-1">
                  <div class="text-body-1 text-high-emphasis">
                    {{ data.title }}
                  </div>
                  <div class="d-flex gap-x-2 align-center">
                    <h4 class="text-h4">
                      {{ data.value }}
                    </h4>
                    <div class="text-base" :class="data.change > 0 ? 'text-success' : 'text-error'">
                      ({{ prefixWithPlus(data.change) }}%)
                    </div>
                  </div>
                  <div class="text-sm">
                    {{ data.desc }}
                  </div>
                </div>
                <VAvatar :color="data.iconColor" variant="tonal" rounded size="42">
                  <VIcon :icon="data.icon" size="26" />
                </VAvatar>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </template>
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
            <AppTextField v-model="searchQuery" placeholder="Search Broker User" />
          </div>

          <!-- Export Menu -->
          <VBtn variant="tonal" color="secondary">
            <VIcon icon="tabler-upload" class="me-2" />
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

          <!-- Add user button -->
          <VBtn prepend-icon="tabler-plus" @click="isAddNewUserDrawerVisible = true">
            Add New Broker User
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- Error Alert -->
      <VAlert v-if="error" type="error" class="ma-4" closable @click:close="error = ''">
        {{ error }}
      </VAlert>

      <!-- Data Table -->
      <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:model-value="selectedRows" v-model:page="page"
        :items="filteredBrokerUsers" item-value="id" :items-length="totalBrokerUsers" :headers="headers"
        class="text-no-wrap" show-select :loading="isLoading" @update:options="updateOptions">
        <!-- Empty State -->
        <template #no-data>
          <div class="text-center pa-8">
            <VIcon icon="tabler-users-off" size="64" class="mb-4 text-disabled" />
            <h3 class="text-h5 mb-2">
              No Broker Users Found
            </h3>
            <p class="text-body-1 text-medium-emphasis mb-4">
              No broker users match your search criteria. Try adjusting your filters.
            </p>
            <VBtn color="primary" @click="isAddNewUserDrawerVisible = true">
              Add First Sales User
            </VBtn>
          </div>
        </template>

        <!-- User -->
        <template #item.user="{ item }">
          <div class="d-flex align-center gap-x-4">
            <VAvatar size="34" :variant="!item.avatar ? 'tonal' : undefined" color="warning">
              <VImg v-if="item.avatar" :src="item.avatar" />
              <span v-else>{{ avatarText(item.fullName) }}</span>
            </VAvatar>
            <div class="d-flex flex-column">
              <h6 class="text-base font-weight-medium">
                {{ item.fullName }}
              </h6>
              <div class="text-sm text-medium-emphasis">
                Joined: {{ item.joinedDate }}
              </div>
            </div>
          </div>
        </template>

        <!-- Territory -->
        <template #item.city="{ item }">
          <div class="text-body-1 text-high-emphasis">
            {{ item.city }}
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
          <VChip :color="resolveUserStatusVariant(item.status)" size="small" label class="text-capitalize">
            {{ item.status }}
          </VChip>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <div class="d-flex gap-1">
            <VBtn icon variant="text" color="medium-emphasis" size="small">
              <VIcon icon="tabler-dots-vertical" size="22" />
              <VMenu activator="parent">
                <VList>
                  <VListItem @click="viewUser(item)">
                    <template #prepend>
                      <VIcon icon="tabler-eye" />
                    </template>
                    <VListItemTitle>View</VListItemTitle>
                  </VListItem>

                  <VListItem @click="editBrokerUser(item)">
                    <template #prepend>
                      <VIcon icon="tabler-pencil" />
                    </template>
                    <VListItemTitle>Edit</VListItemTitle>
                  </VListItem>

                  <VListItem @click="openResetPasswordDialog(item)">
                    <template #prepend>
                      <VIcon icon="tabler-key" />
                    </template>
                    <VListItemTitle>Reset Password</VListItemTitle>
                  </VListItem>

                  <VDivider class="my-2" />

                  <VListItem @click="openDeleteDialog(item)">
                    <template #prepend>
                      <VIcon icon="tabler-trash" color="error" />
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
          <TablePagination v-model:page="page" :items-per-page="itemsPerPage" :total-items="totalBrokerUsers" />
        </template>
      </VDataTableServer>
    </VCard>

    <!-- Add/Edit Broker User Drawer -->
    <AddEditBrokerDrawer v-model:is-drawer-open="isAddNewUserDrawerVisible" :broker-user="selectedBrokerUser"
      @broker-user-data="handleBrokerUserData" @update:is-drawer-open="handleDrawerClose" />

    <!-- Delete Confirmation Dialog -->
    <ConfirmDeleteDialog v-model:is-dialog-open="isDeleteDialogVisible" :user-name="userToDelete?.fullName"
      user-type="Broker User" @confirm="deleteUser" />

    <!-- Reset Password Dialog -->
    <ResetPasswordDialog v-model:is-dialog-open="isResetPasswordDialogVisible" :user-name="selectedBrokerUser?.fullName"
      @confirm="resetPassword" />

    <!-- View User Dialog -->
    <ViewUserDialog v-model:is-dialog-open="isViewDialogVisible" :user="selectedBrokerUser" user-type="Broker User"
      @edit="handleEditFromView" />
  </section>
</template>
