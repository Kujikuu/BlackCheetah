<script setup lang="ts">
// 👉 Store
const searchQuery = ref('')
const selectedRole = ref()
const selectedStatus = ref()

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])

// Update data table options
const updateOptions = (options: any) => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  { title: 'Sales Associate', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Phone', key: 'phone' },
  { title: 'Role', key: 'role' },
  { title: 'Status', key: 'status' },
  { title: 'Assigned Leads', key: 'assignedLeads' },
  { title: 'Actions', key: 'actions', sortable: false },
]

// Mock data - Replace with actual API call
const salesAssociatesData = ref({
  associates: [
    {
      id: 1,
      name: 'John Smith',
      email: 'john.smith@company.com',
      phone: '+1 234-567-8900',
      role: 'Senior Sales',
      status: 'active',
      country: 'United States',
      state: 'California',
      city: 'Los Angeles',
      assignedLeads: 24,
      avatar: null,
    },
    {
      id: 2,
      name: 'Sarah Johnson',
      email: 'sarah.j@company.com',
      phone: '+1 234-567-8901',
      role: 'Sales Associate',
      status: 'active',
      country: 'United States',
      state: 'New York',
      city: 'New York City',
      assignedLeads: 18,
      avatar: null,
    },
    {
      id: 3,
      name: 'Michael Brown',
      email: 'michael.b@company.com',
      phone: '+1 234-567-8902',
      role: 'Sales Associate',
      status: 'inactive',
      country: 'Canada',
      state: 'Ontario',
      city: 'Toronto',
      assignedLeads: 0,
      avatar: null,
    },
  ],
  total: 3,
})

const associates = computed(() => salesAssociatesData.value.associates)
const totalAssociates = computed(() => salesAssociatesData.value.total)

// 👉 search filters
const roles = [
  { title: 'Senior Sales', value: 'senior_sales' },
  { title: 'Sales Associate', value: 'sales_associate' },
  { title: 'Junior Sales', value: 'junior_sales' },
]

const statuses = [
  { title: 'Active', value: 'active' },
  { title: 'Inactive', value: 'inactive' },
]

const countries = [
  { title: 'United States', value: 'United States' },
  { title: 'Canada', value: 'Canada' },
  { title: 'United Kingdom', value: 'United Kingdom' },
  { title: 'Australia', value: 'Australia' },
  { title: 'Germany', value: 'Germany' },
  { title: 'France', value: 'France' },
  { title: 'Japan', value: 'Japan' },
  { title: 'Brazil', value: 'Brazil' },
  { title: 'India', value: 'India' },
  { title: 'Mexico', value: 'Mexico' },
  { title: 'Spain', value: 'Spain' },
  { title: 'Italy', value: 'Italy' },
  { title: 'Netherlands', value: 'Netherlands' },
  { title: 'Sweden', value: 'Sweden' },
  { title: 'Norway', value: 'Norway' },
  { title: 'Switzerland', value: 'Switzerland' },
  { title: 'South Korea', value: 'South Korea' },
  { title: 'Singapore', value: 'Singapore' },
  { title: 'New Zealand', value: 'New Zealand' },
  { title: 'South Africa', value: 'South Africa' },
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
  if (associateToDelete.value === null) return

  // TODO: Implement API call
  const index = salesAssociatesData.value.associates.findIndex(associate => associate.id === associateToDelete.value)
  if (index !== -1)
    salesAssociatesData.value.associates.splice(index, 1)

  // Delete from selectedRows
  const selectedIndex = selectedRows.value.findIndex(row => row === associateToDelete.value)
  if (selectedIndex !== -1)
    selectedRows.value.splice(selectedIndex, 1)

  isDeleteDialogVisible.value = false
  associateToDelete.value = null
}

// 👉 View and Edit functions
// 👉 Modal states
const isViewAssociateModalVisible = ref(false)
const isEditAssociateModalVisible = ref(false)
const isAddAssociateModalVisible = ref(false)
const selectedAssociate = ref<any>(null)

// 👉 View associate
const viewAssociate = (id: number) => {
  const associate = associates.value.find(a => a.id === id)
  if (associate) {
    selectedAssociate.value = associate
    isViewAssociateModalVisible.value = true
  }
}

// 👉 Edit associate
const editAssociate = (id: number) => {
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
    role: '',
    status: 'active',
    country: '',
    state: '',
    city: '',
    assignedLeads: 0,
    avatar: null,
  }
  isAddAssociateModalVisible.value = true
}

// 👉 Save associate (for both add and edit)
const saveAssociate = async () => {
  if (!selectedAssociate.value) return

  // TODO: Implement API call
  if (selectedAssociate.value.id === null) {
    // Add new associate
    const newId = Math.max(...salesAssociatesData.value.associates.map(a => a.id)) + 1
    selectedAssociate.value.id = newId
    salesAssociatesData.value.associates.push({ ...selectedAssociate.value })
    salesAssociatesData.value.total += 1
  } else {
    // Update existing associate
    const index = salesAssociatesData.value.associates.findIndex(a => a.id === selectedAssociate.value.id)
    if (index !== -1) {
      salesAssociatesData.value.associates[index] = { ...selectedAssociate.value }
    }
  }

  isAddAssociateModalVisible.value = false
  isEditAssociateModalVisible.value = false
  selectedAssociate.value = null
}

// 👉 Export functionality
const exportToCSV = () => {
  const dataToExport = selectedRows.value.length > 0 
    ? associates.value.filter(associate => selectedRows.value.includes(associate.id))
    : associates.value

  const csvContent = [
    'Name,Email,Phone,Role,Status,Country,State,City,Assigned Leads',
    ...dataToExport.map(associate => 
      `"${associate.name}","${associate.email}","${associate.phone}","${associate.role}","${associate.status}","${associate.country || ''}","${associate.state || ''}","${associate.city || ''}","${associate.assignedLeads}"`
    )
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
</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Filters</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <!-- 👉 Select Role -->
          <VCol cols="12" sm="6">
            <AppSelect v-model="selectedRole" placeholder="Select Role" :items="roles" clearable
              clear-icon="tabler-x" />
          </VCol>
          <!-- 👉 Select Status -->
          <VCol cols="12" sm="6">
            <AppSelect v-model="selectedStatus" placeholder="Select Status" :items="statuses" clearable
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
          <!-- 👉 Search  -->
          <div style="inline-size: 15.625rem;">
            <AppTextField v-model="searchQuery" placeholder="Search Sales Associate" />
          </div>

          <!-- 👉 Export Menu -->
          <VBtn 
            variant="tonal" 
            color="secondary"
          >
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
      <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:model-value="selectedRows" v-model:page="page"
        :items="associates" item-value="id" :items-length="totalAssociates" :headers="headers" class="text-no-wrap"
        show-select @update:options="updateOptions">
        <!-- Sales Associate -->
        <template #item.name="{ item }">
          <div class="d-flex align-center gap-x-4">
            <VAvatar size="34" :variant="!item.avatar ? 'tonal' : undefined"
              :color="!item.avatar ? 'primary' : undefined">
              <VImg v-if="item.avatar" :src="item.avatar" />
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

        <!-- Role -->
        <template #item.role="{ item }">
          <div class="text-body-1 text-capitalize">
            {{ item.role }}
          </div>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <VChip :color="resolveStatusVariant(item.status)" size="small" label class="text-capitalize">
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
          <VBtn icon variant="text" color="medium-emphasis">
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
          <TablePagination v-model:page="page" :items-per-page="itemsPerPage" :total-items="totalAssociates" />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>

    <!-- 👉 View Associate Modal -->
    <VDialog
      v-model="isViewAssociateModalVisible"
      max-width="600"
    >
      <VCard v-if="selectedAssociate">
        <VCardItem>
          <VCardTitle>Sales Associate Details</VCardTitle>
        </VCardItem>

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
                  <h4 class="text-h4 mb-1">{{ selectedAssociate.name }}</h4>
                  <VChip
                    :color="resolveStatusVariant(selectedAssociate.status)"
                    size="small"
                    label
                    class="text-capitalize"
                  >
                    {{ selectedAssociate.status }}
                  </VChip>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">Email</div>
                <div class="text-body-1">{{ selectedAssociate.email }}</div>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">Phone</div>
                <div class="text-body-1">{{ selectedAssociate.phone }}</div>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">Role</div>
                <div class="text-body-1 text-capitalize">{{ selectedAssociate.role }}</div>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">Country</div>
                <div class="text-body-1">{{ selectedAssociate.country || 'Not specified' }}</div>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">State</div>
                <div class="text-body-1">{{ selectedAssociate.state || 'Not specified' }}</div>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">City</div>
                <div class="text-body-1">{{ selectedAssociate.city || 'Not specified' }}</div>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">Assigned Leads</div>
                <div class="text-body-1 font-weight-medium">{{ selectedAssociate.assignedLeads }}</div>
              </div>
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
      @update:model-value="val => { if (!val) { isAddAssociateModalVisible = false; isEditAssociateModalVisible = false } }"
      max-width="700"
    >
      <VCard v-if="selectedAssociate">
        <VCardItem>
          <VCardTitle>{{ selectedAssociate.id === null ? 'Add' : 'Edit' }} Sales Associate</VCardTitle>
        </VCardItem>

        <VCardText>
          <VForm @submit.prevent="saveAssociate">
            <VRow>
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="selectedAssociate.name"
                  label="Full Name"
                  placeholder="Enter full name"
                  required
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="selectedAssociate.email"
                  label="Email Address"
                  type="email"
                  placeholder="Enter email address"
                  required
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="selectedAssociate.phone"
                  label="Phone Number"
                  placeholder="Enter phone number"
                  required
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppSelect
                  v-model="selectedAssociate.role"
                  label="Role"
                  :items="roles"
                  placeholder="Select role"
                  required
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppSelect
                  v-model="selectedAssociate.status"
                  label="Status"
                  :items="statuses"
                  placeholder="Select status"
                  required
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppSelect
                  v-model="selectedAssociate.country"
                  label="Country"
                  :items="countries"
                  placeholder="Select country"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="selectedAssociate.state"
                  label="State/Province"
                  placeholder="Enter state or province"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="selectedAssociate.city"
                  label="City"
                  placeholder="Enter city"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField
                  v-model.number="selectedAssociate.assignedLeads"
                  label="Assigned Leads"
                  type="number"
                  placeholder="0"
                  min="0"
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
            @click="isAddAssociateModalVisible = false; isEditAssociateModalVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            @click="saveAssociate"
          >
            {{ selectedAssociate.id === null ? 'Add' : 'Save Changes' }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- 👉 Delete Confirmation Dialog -->
    <VDialog
      v-model="isDeleteDialogVisible"
      max-width="500"
    >
      <VCard>
        <VCardItem>
          <VCardTitle>Confirm Delete</VCardTitle>
        </VCardItem>

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
