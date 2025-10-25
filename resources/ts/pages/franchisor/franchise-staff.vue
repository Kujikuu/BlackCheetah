<script setup lang="ts">
import { franchiseStaffApi, type FranchiseStaff, type StaffFilters } from '@/services/api'
import ConfirmDeleteDialog from '@/components/dialogs/common/ConfirmDeleteDialog.vue'
import AddFranchiseStaffDialog from '@/components/dialogs/franchise/AddFranchiseStaffDialog.vue'
import EditFranchiseStaffDialog from '@/components/dialogs/franchise/EditFranchiseStaffDialog.vue'

// Search and filters
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedDepartment = ref('')
const selectedEmploymentType = ref('')

// Data table state
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref('created_at')
const sortOrder = ref<'asc' | 'desc'>('desc')
const selectedRows = ref<number[]>([])
const isLoading = ref(false)

// Data
const staff = ref<FranchiseStaff[]>([])
const totalStaff = ref(0)

// Modal states
const isAddStaffDialogVisible = ref(false)
const isEditStaffDialogVisible = ref(false)
const isDeleteStaffDialogVisible = ref(false)
const selectedStaff = ref<FranchiseStaff | null>(null)

// Stats
const statsData = ref([
  { title: 'Total Staff', value: '0', change: 0, icon: 'tabler-users', iconColor: 'primary' },
  { title: 'Active Staff', value: '0', change: 0, icon: 'tabler-user-check', iconColor: 'success' },
  { title: 'On Leave', value: '0', change: 0, icon: 'tabler-user-pause', iconColor: 'warning' },
])

// Table headers
const headers = [
  { title: '#', key: 'index', sortable: false },
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Job Title', key: 'jobTitle' },
  { title: 'Department', key: 'department' },
  { title: 'Employment Type', key: 'employmentType' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false },
]

// Status options
const statusOptions = [
  { title: 'Active', value: 'active' },
  { title: 'On Leave', value: 'on_leave' },
  { title: 'Terminated', value: 'terminated' },
  { title: 'Inactive', value: 'inactive' },
]

const employmentTypeOptions = [
  { title: 'Full Time', value: 'full_time' },
  { title: 'Part Time', value: 'part_time' },
  { title: 'Contract', value: 'contract' },
  { title: 'Temporary', value: 'temporary' },
]

// API Functions
const fetchStaff = async () => {
  try {
    isLoading.value = true

    const filters: StaffFilters = {
      page: page.value,
      per_page: itemsPerPage.value,
      sort_by: sortBy.value,
      sort_order: sortOrder.value,
    }

    if (searchQuery.value)
      filters.search = searchQuery.value
    if (selectedStatus.value)
      filters.status = selectedStatus.value
    if (selectedDepartment.value)
      filters.department = selectedDepartment.value
    if (selectedEmploymentType.value)
      filters.employment_type = selectedEmploymentType.value

    const response = await franchiseStaffApi.getStaff(filters)

    if (response.success && response.data) {
      staff.value = response.data.data || []
      totalStaff.value = response.data.total || 0
    }
  }
  catch (error) {
    console.error('Error fetching staff:', error)
  }
  finally {
    isLoading.value = false
  }
}

const fetchStats = async () => {
  try {
    const response = await franchiseStaffApi.getStatistics()
    if (response.success && response.data) {
      statsData.value = [
        { title: 'Total Staff', value: response.data.totalStaff.toString(), change: 0, icon: 'tabler-users', iconColor: 'primary' },
        { title: 'Active Staff', value: response.data.activeStaff.toString(), change: 0, icon: 'tabler-user-check', iconColor: 'success' },
        { title: 'On Leave', value: response.data.onLeaveStaff.toString(), change: 0, icon: 'tabler-user-pause', iconColor: 'warning' },
      ]
    }
  }
  catch (error) {
    console.error('Error fetching stats:', error)
  }
}

// Actions
const openAddDialog = () => {
  isAddStaffDialogVisible.value = true
}

const openEditDialog = (staffMember: FranchiseStaff) => {
  selectedStaff.value = staffMember
  isEditStaffDialogVisible.value = true
}

const openDeleteDialog = (staffMember: FranchiseStaff) => {
  selectedStaff.value = staffMember
  isDeleteStaffDialogVisible.value = true
}

const onStaffCreated = async () => {
  await fetchStaff()
  await fetchStats()
  isAddStaffDialogVisible.value = false
}

const onStaffUpdated = async () => {
  await fetchStaff()
  await fetchStats()
  isEditStaffDialogVisible.value = false
}

const onStaffDeleted = async () => {
  if (!selectedStaff.value)
    return

  try {
    const response = await franchiseStaffApi.deleteStaff(selectedStaff.value.id)
    if (response.success) {
      await fetchStaff()
      await fetchStats()
      isDeleteStaffDialogVisible.value = false
      selectedStaff.value = null
    }
  }
  catch (error) {
    console.error('Error deleting staff:', error)
  }
}

// Table sorting
const updateOptions = (options: any) => {
  if (options.sortBy && options.sortBy.length > 0) {
    sortBy.value = options.sortBy[0].key
    sortOrder.value = options.sortBy[0].order
  }
  fetchStaff()
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    fetchStaff(),
    fetchStats(),
  ])
})

// Watchers
watch([searchQuery, selectedStatus, selectedDepartment, selectedEmploymentType], () => {
  page.value = 1
  fetchStaff()
})

// Status variant resolver
const resolveStatusVariant = (status: string) => {
  const variants: Record<string, string> = {
    active: 'success',
    on_leave: 'warning',
    terminated: 'error',
    inactive: 'secondary',
  }
  return variants[status] || 'secondary'
}

// Employment type formatter
const formatEmploymentType = (type: string) => {
  const formats: Record<string, string> = {
    full_time: 'Full Time',
    part_time: 'Part Time',
    contract: 'Contract',
    temporary: 'Temporary',
  }
  return formats[type] || type
}

// Status formatter
const formatStatus = (status: string) => {
  const formats: Record<string, string> = {
    active: 'Active',
    on_leave: 'On Leave',
    terminated: 'Terminated',
    inactive: 'Inactive',
  }
  return formats[status] || status
}

const deleteMessage = computed(() => {
  if (!selectedStaff.value)
    return ''
  return `Are you sure you want to delete ${selectedStaff.value.name}? This action cannot be undone.`
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
              Franchise Staff
            </h2>
            <p class="text-body-1 text-medium-emphasis">
              Manage your franchise-level staff members
            </p>
          </div>
        </div>
      </VCol>
    </VRow>

    <!-- Stats Cards -->
    <VRow class="mb-6">
      <template v-for="(data, id) in statsData" :key="id">
        <VCol cols="12" md="4">
          <VCard>
            <VCardText>
              <div class="d-flex justify-space-between">
                <div class="d-flex flex-column gap-y-1">
                  <div class="text-body-1 text-high-emphasis">
                    {{ data.title }}
                  </div>
                  <h4 class="text-h4">
                    {{ data.value }}
                  </h4>
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

    <!-- Main Card with Filters and Table -->
    <VCard>
      <!-- Filters -->
      <VCardItem class="pb-4">
        <VCardTitle>Filters</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <VCol cols="12" sm="4">
            <AppSelect
              v-model="selectedStatus"
              placeholder="Select Status"
              :items="statusOptions"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
          <VCol cols="12" sm="4">
            <AppTextField
              v-model="selectedDepartment"
              placeholder="Department"
              clearable
            />
          </VCol>
          <VCol cols="12" sm="4">
            <AppSelect
              v-model="selectedEmploymentType"
              placeholder="Employment Type"
              :items="employmentTypeOptions"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <!-- Toolbar -->
      <VCardText class="d-flex flex-wrap gap-4">
        <div class="me-3 d-flex gap-3">
          <AppSelect
            :model-value="itemsPerPage"
            :items="[
              { value: 10, title: '10' },
              { value: 25, title: '25' },
              { value: 50, title: '50' },
              { value: 100, title: '100' },
            ]"
            style="inline-size: 6.25rem;"
            @update:model-value="itemsPerPage = parseInt($event, 10)"
          />
        </div>
        <VSpacer />

        <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
          <div style="inline-size: 15.625rem;">
            <AppTextField
              v-model="searchQuery"
              placeholder="Search Staff"
            />
          </div>

          <VBtn
            prepend-icon="tabler-plus"
            @click="openAddDialog"
          >
            Add Staff Member
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- Data Table -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="staff"
        item-value="id"
        :items-length="totalStaff"
        :headers="headers"
        :loading="isLoading"
        class="text-no-wrap"
        @update:options="updateOptions"
      >
        <!-- Index -->
        <template #item.index="{ index }">
          <div class="text-body-1 font-weight-medium">
            {{ (page - 1) * itemsPerPage + index + 1 }}
          </div>
        </template>

        <!-- Name -->
        <template #item.name="{ item }">
          <div class="d-flex align-center gap-x-3">
            <VAvatar
              size="34"
              color="primary"
              variant="tonal"
            >
              <span class="text-sm">{{ item.name.charAt(0).toUpperCase() }}</span>
            </VAvatar>
            <div>
              <div class="text-body-1 font-weight-medium">
                {{ item.name }}
              </div>
            </div>
          </div>
        </template>

        <!-- Employment Type -->
        <template #item.employmentType="{ item }">
          <div class="text-body-2">
            {{ formatEmploymentType(item.employmentType) }}
          </div>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <VChip
            size="small"
            :color="resolveStatusVariant(item.status)"
            variant="tonal"
          >
            {{ formatStatus(item.status) }}
          </VChip>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <VBtn icon variant="text" color="medium-emphasis" size="small">
            <VIcon icon="tabler-dots-vertical" />
            <VMenu activator="parent">
              <VList>
                <VListItem @click="openEditDialog(item)">
                  <template #prepend>
                    <VIcon icon="tabler-edit" />
                  </template>
                  <VListItemTitle>Edit</VListItemTitle>
                </VListItem>

                <VListItem @click="openDeleteDialog(item)">
                  <template #prepend>
                    <VIcon icon="tabler-trash" />
                  </template>
                  <VListItemTitle>Delete</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </VBtn>
        </template>

        <!-- Pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalStaff"
          />
        </template>
      </VDataTableServer>
    </VCard>

    <!-- Add Staff Dialog -->
    <AddFranchiseStaffDialog
      v-model:is-dialog-visible="isAddStaffDialogVisible"
      @staff-created="onStaffCreated"
    />

    <!-- Edit Staff Dialog -->
    <EditFranchiseStaffDialog
      v-model:is-dialog-visible="isEditStaffDialogVisible"
      :staff="selectedStaff"
      @staff-updated="onStaffUpdated"
    />

    <!-- Delete Confirmation Dialog -->
    <ConfirmDeleteDialog
      v-model:is-dialog-visible="isDeleteStaffDialogVisible"
      title="Delete Staff Member"
      :message="deleteMessage"
      @confirm="onStaffDeleted"
    />
  </section>
</template>

