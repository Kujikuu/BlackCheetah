<script setup lang="ts">
import AddEditFranchisorDrawer from '@/views/admin/modals/AddEditFranchisorDrawer.vue'
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
  { title: 'Franchise Name', key: 'franchiseName' },
  { title: 'Email', key: 'email' },
  { title: 'Last Login', key: 'lastLogin' },
  { title: 'Plan', key: 'plan' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false },
]

// Mock data - Replace with API call
const franchisors = ref([
  {
    id: 1,
    fullName: 'John Doe',
    email: 'john.doe@example.com',
    lastLogin: '2024-01-15',
    franchiseName: 'Acme Corporation',
    plan: 'Enterprise',
    status: 'active',
    avatar: '',
    joinedDate: '2024-01-15',
  },
  {
    id: 2,
    fullName: 'Sarah Williams',
    email: 'sarah.w@example.com',
    lastLogin: '2024-01-14',
    franchiseName: 'Tech Solutions Inc',
    plan: 'Basic',
    status: 'active',
    avatar: '',
    joinedDate: '2024-01-14',
  },
  {
    id: 3,
    fullName: 'David Brown',
    email: 'david.b@example.com',
    lastLogin: '2024-01-13',
    franchiseName: 'Global Ventures',
    plan: 'Pro',
    status: 'inactive',
    avatar: '',
    joinedDate: '2024-01-13',
  },
  {
    id: 4,
    fullName: 'Emily Johnson',
    email: 'emily.j@example.com',
    lastLogin: '2024-01-12',
    franchiseName: 'Innovation Labs',
    plan: 'Basic',
    status: 'pending',
    avatar: '',
    joinedDate: '2024-01-12',
  },
  {
    id: 5,
    fullName: 'Michael Chen',
    email: 'michael.c@example.com',
    lastLogin: '2024-01-11',
    franchiseName: 'Future Systems',
    plan: 'Pro',
    status: 'active',
    avatar: '',
    joinedDate: '2024-01-11',
  },
])

const totalFranchisors = computed(() => franchisors.value.length)

// Filtered data
const filteredFranchisors = computed(() => {
  let filtered = franchisors.value

  if (searchQuery.value) {
    filtered = filtered.filter(user =>
      user.fullName.toLowerCase().includes(searchQuery.value.toLowerCase())
      || user.email.toLowerCase().includes(searchQuery.value.toLowerCase())
      || user.franchiseName.toLowerCase().includes(searchQuery.value.toLowerCase()),
    )
  }

  if (selectedStatus.value)
    filtered = filtered.filter(user => user.status === selectedStatus.value)

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

const resolveUserPlanVariant = (plan: string) => {
  const planLowerCase = plan.toLowerCase()
  if (planLowerCase === 'basic')
    return 'primary'
  if (planLowerCase === 'pro')
    return 'success'
  if (planLowerCase === 'enterprise')
    return 'warning'

  return 'primary'
}

const isAddNewUserDrawerVisible = ref(false)
const isDeleteDialogVisible = ref(false)
const isResetPasswordDialogVisible = ref(false)
const isViewDialogVisible = ref(false)
const selectedFranchisor = ref<any>(null)
const userToDelete = ref<any>(null)

// Add new franchisor
const addNewFranchisor = (franchisorData: any) => {
  const newId = Math.max(...franchisors.value.map(f => f.id)) + 1

  franchisors.value.push({
    ...franchisorData,
    id: newId,
    joinedDate: new Date().toISOString().split('T')[0],
    lastLogin: new Date().toISOString().split('T')[0],
  })
}

// Edit franchisor
const editFranchisor = (franchisor: any) => {
  selectedFranchisor.value = { ...franchisor }
  isAddNewUserDrawerVisible.value = true
}

// Update franchisor
const updateFranchisor = (franchisorData: any) => {
  const index = franchisors.value.findIndex(f => f.id === franchisorData.id)
  if (index !== -1)
    franchisors.value[index] = { ...franchisors.value[index], ...franchisorData }

  selectedFranchisor.value = null
}

// Handle drawer data
const handleFranchisorData = (franchisorData: any) => {
  if (franchisorData.id)
    updateFranchisor(franchisorData)

  else
    addNewFranchisor(franchisorData)
}

// Open delete dialog
const openDeleteDialog = (franchisor: any) => {
  userToDelete.value = franchisor
  isDeleteDialogVisible.value = true
}

// Delete user
const deleteUser = async () => {
  if (!userToDelete.value)
    return

  const index = franchisors.value.findIndex(user => user.id === userToDelete.value.id)
  if (index !== -1)
    franchisors.value.splice(index, 1)

  // Remove from selectedRows
  const selectedIndex = selectedRows.value.findIndex(row => row === userToDelete.value.id)
  if (selectedIndex !== -1)
    selectedRows.value.splice(selectedIndex, 1)

  userToDelete.value = null
}

// Open reset password dialog
const openResetPasswordDialog = (franchisor: any) => {
  selectedFranchisor.value = franchisor
  isResetPasswordDialogVisible.value = true
}

// Reset password
const resetPassword = (password: string) => {
  console.log('Password reset for:', selectedFranchisor.value?.fullName, 'New password:', password)

  // Implement password reset logic here
  selectedFranchisor.value = null
}

// Handle drawer close
const handleDrawerClose = () => {
  selectedFranchisor.value = null
}

// View user
const viewUser = (franchisor: any) => {
  selectedFranchisor.value = franchisor
  isViewDialogVisible.value = true
}

// Handle edit from view dialog
const handleEditFromView = () => {
  isAddNewUserDrawerVisible.value = true
}

// Export functions
const exportToCSV = () => {
  const dataToExport = selectedRows.value.length > 0
    ? franchisors.value.filter(user => selectedRows.value.includes(user.id))
    : franchisors.value

  console.log('Exporting to CSV:', dataToExport)

  // Implement CSV export logic
}

const exportToPDF = () => {
  const dataToExport = selectedRows.value.length > 0
    ? franchisors.value.filter(user => selectedRows.value.includes(user.id))
    : franchisors.value

  console.log('Exporting to PDF:', dataToExport)

  // Implement PDF export logic
}

// Widget data
const widgetData = ref([
  { title: 'Total Franchisors', value: '156', change: 12.5, desc: 'All registered franchisors', icon: 'tabler-building-store', iconColor: 'primary' },
  { title: 'Basic Plan', value: '100', change: 3.2, desc: 'Basic Plan', icon: 'tabler-file-chart', iconColor: 'success' },
  { title: 'Pro Plan', value: '56', change: -5.2, desc: 'Pro Plan', icon: 'tabler-file-chart', iconColor: 'warning' },
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
              Franchisor Management
            </h2>
            <p class="text-body-1 text-medium-emphasis">
              Manage and monitor all franchisor accounts
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
            <AppTextField v-model="searchQuery" placeholder="Search Franchisor" />
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
            Add New Franchisor
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- Data Table -->
      <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:model-value="selectedRows" v-model:page="page"
        :items="filteredFranchisors" item-value="id" :items-length="totalFranchisors" :headers="headers"
        class="text-no-wrap" show-select @update:options="updateOptions">
        <!-- Empty State -->
        <template #no-data>
          <div class="text-center pa-8">
            <VIcon icon="tabler-users-off" size="64" class="mb-4 text-disabled" />
            <h3 class="text-h5 mb-2">
              No Franchisors Found
            </h3>
            <p class="text-body-1 text-medium-emphasis mb-4">
              No franchisors match your search criteria. Try adjusting your filters.
            </p>
            <VBtn color="primary" @click="isAddNewUserDrawerVisible = true">
              Add First Franchisor
            </VBtn>
          </div>
        </template>

        <!-- User -->
        <template #item.user="{ item }">
          <div class="d-flex align-center gap-x-4">
            <VAvatar size="34" :variant="!item.avatar ? 'tonal' : undefined" color="primary">
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

        <!-- FranchiseName -->
        <template #item.franchiseName="{ item }">
          <div class="text-body-1 text-high-emphasis">
            {{ item.franchiseName }}
          </div>
        </template>

        <!-- Email -->
        <template #item.email="{ item }">
          <div class="text-body-1">
            {{ item.email }}
          </div>
        </template>

        <!-- Last Login -->
        <template #item.lastLogin="{ item }">
          <div class="text-body-1">
            {{ item.lastLogin }}
          </div>
        </template>

        <!-- Plan -->
        <template #item.plan="{ item }">
          <VChip :color="resolveUserPlanVariant(item.plan)" size="small" label class="text-capitalize">
            {{ item.plan }}
          </VChip>
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

                  <VListItem @click="editFranchisor(item)">
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
          <TablePagination v-model:page="page" :items-per-page="itemsPerPage" :total-items="totalFranchisors" />
        </template>
      </VDataTableServer>
    </VCard>

    <!-- Add/Edit Franchisor Drawer -->
    <AddEditFranchisorDrawer v-model:is-drawer-open="isAddNewUserDrawerVisible" :franchisor="selectedFranchisor"
      @franchisor-data="handleFranchisorData" @update:is-drawer-open="handleDrawerClose" />

    <!-- Delete Confirmation Dialog -->
    <ConfirmDeleteDialog v-model:is-dialog-open="isDeleteDialogVisible" :user-name="userToDelete?.fullName"
      user-type="Franchisor" @confirm="deleteUser" />

    <!-- Reset Password Dialog -->
    <ResetPasswordDialog v-model:is-dialog-open="isResetPasswordDialogVisible" :user-name="selectedFranchisor?.fullName"
      @confirm="resetPassword" />

    <!-- View User Dialog -->
    <ViewUserDialog v-model:is-dialog-open="isViewDialogVisible" :user="selectedFranchisor" user-type="Franchisor"
      @edit="handleEditFromView" />
  </section>
</template>
