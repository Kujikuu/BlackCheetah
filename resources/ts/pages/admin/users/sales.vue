<script setup lang="ts">
import AddEditSalesDrawer from '@/views/admin/modals/AddEditSalesDrawer.vue'
import ConfirmDeleteDialog from '@/views/admin/modals/ConfirmDeleteDialog.vue'
import ResetPasswordDialog from '@/views/admin/modals/ResetPasswordDialog.vue'
import ViewUserDialog from '@/views/admin/modals/ViewUserDialog.vue'

definePage({
  meta: {
    subject: 'Admin',
    action: 'read',
  },
})

// Store
const searchQuery = ref('')
const selectedStatus = ref()

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref<number[]>([])

// Update data table options
const updateOptions = (options: any) => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
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

// Mock data - Replace with API call
const salesUsers = ref([
  {
    id: 1,
    fullName: 'Mike Johnson',
    email: 'mike.j@example.com',
    phone: '+1 234 567 8910',
    city: 'Riyadh',
    status: 'active',
    avatar: '',
    joinedDate: '2024-01-15',
  },
  {
    id: 2,
    fullName: 'Amanda Davis',
    email: 'amanda.d@example.com',
    phone: '+1 234 567 8911',
    city: 'Makkah',
    status: 'active',
    avatar: '',
    joinedDate: '2024-01-14',
  },
  {
    id: 3,
    fullName: 'Chris Martinez',
    email: 'chris.m@example.com',
    phone: '+1 234 567 8912',
    city: 'Jeddah',
    status: 'pending',
    avatar: '',
    joinedDate: '2024-01-13',
  },
  {
    id: 4,
    fullName: 'Jessica Lee',
    email: 'jessica.l@example.com',
    phone: '+1 234 567 8913',
    city: 'Madinah',
    status: 'active',
    avatar: '',
    joinedDate: '2024-01-12',
  },
  {
    id: 5,
    fullName: 'Daniel White',
    email: 'daniel.w@example.com',
    phone: '+1 234 567 8914',
    city: 'Madinah',
    status: 'inactive',
    avatar: '',
    joinedDate: '2024-01-11',
  },
])

const totalSalesUsers = computed(() => salesUsers.value.length)

// Filtered data
const filteredSalesUsers = computed(() => {
  let filtered = salesUsers.value

  if (searchQuery.value) {
    filtered = filtered.filter(user =>
      user.fullName.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.city.toLowerCase().includes(searchQuery.value.toLowerCase()),
    )
  }

  if (selectedStatus.value) {
    filtered = filtered.filter(user => user.status === selectedStatus.value)
  }

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
const selectedSalesUser = ref<any>(null)
const userToDelete = ref<any>(null)

// Add new sales user
const addNewSalesUser = (salesUserData: any) => {
  const newId = Math.max(...salesUsers.value.map(s => s.id)) + 1
  salesUsers.value.push({
    ...salesUserData,
    id: newId,
    joinedDate: new Date().toISOString().split('T')[0],
  })
}

// Edit sales user
const editSalesUser = (salesUser: any) => {
  selectedSalesUser.value = { ...salesUser }
  isAddNewUserDrawerVisible.value = true
}

// Update sales user
const updateSalesUser = (salesUserData: any) => {
  const index = salesUsers.value.findIndex(s => s.id === salesUserData.id)
  if (index !== -1) {
    salesUsers.value[index] = { ...salesUsers.value[index], ...salesUserData }
  }
  selectedSalesUser.value = null
}

// Handle drawer data
const handleSalesUserData = (salesUserData: any) => {
  if (salesUserData.id) {
    updateSalesUser(salesUserData)
  }
  else {
    addNewSalesUser(salesUserData)
  }
}

// Open delete dialog
const openDeleteDialog = (salesUser: any) => {
  userToDelete.value = salesUser
  isDeleteDialogVisible.value = true
}

// Delete user
const deleteUser = async () => {
  if (!userToDelete.value) return

  const index = salesUsers.value.findIndex(user => user.id === userToDelete.value.id)
  if (index !== -1)
    salesUsers.value.splice(index, 1)

  // Remove from selectedRows
  const selectedIndex = selectedRows.value.findIndex(row => row === userToDelete.value.id)
  if (selectedIndex !== -1)
    selectedRows.value.splice(selectedIndex, 1)

  userToDelete.value = null
}

// Open reset password dialog
const openResetPasswordDialog = (salesUser: any) => {
  selectedSalesUser.value = salesUser
  isResetPasswordDialogVisible.value = true
}

// Reset password
const resetPassword = (password: string) => {
  console.log('Password reset for:', selectedSalesUser.value?.fullName, 'New password:', password)
  selectedSalesUser.value = null
}

// Handle drawer close
const handleDrawerClose = () => {
  selectedSalesUser.value = null
}

// View user
const viewUser = (salesUser: any) => {
  selectedSalesUser.value = salesUser
  isViewDialogVisible.value = true
}

// Handle edit from view dialog
const handleEditFromView = () => {
  isAddNewUserDrawerVisible.value = true
}

// Export functions
const exportToCSV = () => {
  const dataToExport = selectedRows.value.length > 0
    ? salesUsers.value.filter(user => selectedRows.value.includes(user.id))
    : salesUsers.value

  console.log('Exporting to CSV:', dataToExport)
  // Implement CSV export logic
}

const exportToPDF = () => {
  const dataToExport = selectedRows.value.length > 0
    ? salesUsers.value.filter(user => selectedRows.value.includes(user.id))
    : salesUsers.value

  console.log('Exporting to PDF:', dataToExport)
  // Implement PDF export logic
}

// Widget data
const widgetData = ref([
  { title: 'Total Sales Users', value: '197', change: 8.4, desc: 'All sales team members', icon: 'tabler-chart-line', iconColor: 'primary' },
  { title: 'Active Sales', value: '183', change: 6.2, desc: 'Currently active', icon: 'tabler-user-check', iconColor: 'success' },
  { title: 'Pending Approval', value: '12', change: 15.8, desc: 'Awaiting verification', icon: 'tabler-clock', iconColor: 'warning' },
])
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
        </div>
        <VSpacer />

        <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
          <!-- Search -->
          <div style="inline-size: 15.625rem;">
            <AppTextField v-model="searchQuery" placeholder="Search Sales User" />
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
            Add New Sales User
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- Data Table -->
      <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:model-value="selectedRows" v-model:page="page"
        :items="filteredSalesUsers" item-value="id" :items-length="totalSalesUsers" :headers="headers"
        class="text-no-wrap" show-select @update:options="updateOptions">
        <!-- Empty State -->
        <template #no-data>
          <div class="text-center pa-8">
            <VIcon icon="tabler-users-off" size="64" class="mb-4 text-disabled" />
            <h3 class="text-h5 mb-2">
              No Sales Users Found
            </h3>
            <p class="text-body-1 text-medium-emphasis mb-4">
              No sales users match your search criteria. Try adjusting your filters.
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

                  <VListItem @click="editSalesUser(item)">
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
                    <VListItemTitle class="text-error">Delete</VListItemTitle>
                  </VListItem>
                </VList>
              </VMenu>
            </VBtn>
          </div>
        </template>

        <!-- Pagination -->
        <template #bottom>
          <TablePagination v-model:page="page" :items-per-page="itemsPerPage" :total-items="totalSalesUsers" />
        </template>
      </VDataTableServer>
    </VCard>

    <!-- Add/Edit Sales User Drawer -->
    <AddEditSalesDrawer
      v-model:is-drawer-open="isAddNewUserDrawerVisible"
      :sales-user="selectedSalesUser"
      @sales-user-data="handleSalesUserData"
      @update:is-drawer-open="handleDrawerClose"
    />

    <!-- Delete Confirmation Dialog -->
    <ConfirmDeleteDialog
      v-model:is-dialog-open="isDeleteDialogVisible"
      :user-name="userToDelete?.fullName"
      user-type="Sales User"
      @confirm="deleteUser"
    />

    <!-- Reset Password Dialog -->
    <ResetPasswordDialog
      v-model:is-dialog-open="isResetPasswordDialogVisible"
      :user-name="selectedSalesUser?.fullName"
      @confirm="resetPassword"
    />

    <!-- View User Dialog -->
    <ViewUserDialog
      v-model:is-dialog-open="isViewDialogVisible"
      :user="selectedSalesUser"
      user-type="Sales User"
      @edit="handleEditFromView"
    />
  </section>
</template>
