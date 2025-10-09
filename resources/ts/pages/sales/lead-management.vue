<script setup lang="ts">
import AddNoteModal from '@/components/franchisor/AddNoteModal.vue'

// 👉 Store
const searchQuery = ref('')
const selectedStatus = ref()
const selectedSource = ref()
const selectedOwner = ref()
const isAddNoteModalVisible = ref(false)
const selectedLeadForNote = ref<number | null>(null)
const isImportDialogVisible = ref(false)
const isDeleteDialogVisible = ref(false)
const leadToDelete = ref<number | null>(null)

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
  { title: '#', key: 'index', sortable: false },
  { title: 'Lead Name', key: 'name' },
  { title: 'Company', key: 'company' },
  { title: 'Email', key: 'email' },
  { title: 'Phone', key: 'phone' },
  { title: 'Location', key: 'location' },
  { title: 'Source', key: 'source' },
  { title: 'Status', key: 'status' },
  { title: 'Owner', key: 'owner' },
  { title: 'Last Contacted', key: 'lastContacted' },
  { title: 'Actions', key: 'actions', sortable: false },
]

// Mock data - Replace with actual API call
const leadsData = ref({
  leads: [
    {
      id: 1,
      firstName: 'John',
      lastName: 'Smith',
      email: 'john.smith@example.com',
      phone: '+1 234-567-8900',
      company: 'Tech Corp',
      country: 'USA',
      state: 'California',
      city: 'San Francisco',
      source: 'Website',
      status: 'qualified',
      owner: 'Sarah Johnson',
      lastContacted: '2024-01-15',
      scheduledMeeting: '2024-01-20',
    },
    {
      id: 2,
      firstName: 'Emily',
      lastName: 'Davis',
      email: 'emily.d@example.com',
      phone: '+1 234-567-8901',
      company: 'Business Inc',
      country: 'USA',
      state: 'New York',
      city: 'New York',
      source: 'Referral',
      status: 'unqualified',
      owner: 'John Smith',
      lastContacted: '2024-01-14',
      scheduledMeeting: null,
    },
    {
      id: 3,
      firstName: 'Michael',
      lastName: 'Brown',
      email: 'michael.b@example.com',
      phone: '+1 234-567-8902',
      company: 'Solutions LLC',
      country: 'Canada',
      state: 'Ontario',
      city: 'Toronto',
      source: 'Social Media',
      status: 'qualified',
      owner: 'Sarah Johnson',
      lastContacted: '2024-01-13',
      scheduledMeeting: '2024-01-18',
    },
  ],
  total: 3,
})

const leads = computed(() => leadsData.value.leads)
const totalLeads = computed(() => leadsData.value.total)

// 👉 Stats
const statsData = ref([
  { title: 'Total Leads', value: '1,245', change: 15, icon: 'tabler-users', iconColor: 'primary' },
  { title: 'Qualified', value: '847', change: 22, icon: 'tabler-user-check', iconColor: 'success' },
  { title: 'Unqualified', value: '398', change: -8, icon: 'tabler-user-x', iconColor: 'error' },
])

// 👉 search filters
const sources = [
  { title: 'Website', value: 'website' },
  { title: 'Referral', value: 'referral' },
  { title: 'Social Media', value: 'social_media' },
  { title: 'Email Campaign', value: 'email' },
]

const statuses = [
  { title: 'Qualified', value: 'qualified' },
  { title: 'Unqualified', value: 'unqualified' },
]

const owners = [
  { title: 'Sarah Johnson', value: 'sarah_johnson' },
  { title: 'John Smith', value: 'john_smith' },
  { title: 'Michael Brown', value: 'michael_brown' },
]

const resolveStatusVariant = (stat: string) => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === 'qualified')
    return 'success'
  if (statLowerCase === 'unqualified')
    return 'error'

  return 'primary'
}

// 👉 Delete lead
const confirmDelete = (id: number) => {
  leadToDelete.value = id
  isDeleteDialogVisible.value = true
}

const deleteLead = async () => {
  if (leadToDelete.value === null)
    return

  // TODO: Implement API call
  const index = leadsData.value.leads.findIndex(lead => lead.id === leadToDelete.value)
  if (index !== -1)
    leadsData.value.leads.splice(index, 1)

  // Delete from selectedRows
  const selectedIndex = selectedRows.value.findIndex(row => row === leadToDelete.value)
  if (selectedIndex !== -1)
    selectedRows.value.splice(selectedIndex, 1)

  isDeleteDialogVisible.value = false
  leadToDelete.value = null
}

// 👉 Import CSV
const csvFile = ref<File | null>(null)

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0])
    csvFile.value = target.files[0]
}

const downloadExampleCSV = () => {
  const csvContent = 'First Name,Last Name,Email,Phone,Company,Country,State,City,Lead Source,Lead Status,Lead Owner\nJohn,Doe,john.doe@example.com,+1234567890,Example Corp,USA,California,Los Angeles,Website,Qualified,Sarah Johnson'
  const blob = new Blob([csvContent], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')

  a.href = url
  a.download = 'leads_example.csv'
  a.click()
  window.URL.revokeObjectURL(url)
}

const importCSV = () => {
  if (!csvFile.value) {
    // Show error message
    return
  }

  // TODO: Implement CSV import logic
  console.log('Importing CSV:', csvFile.value)
  isImportDialogVisible.value = false
  csvFile.value = null
}

// 👉 Export functionality
const exportLeads = () => {
  const dataToExport = selectedRows.value.length > 0 
    ? leads.value.filter(lead => selectedRows.value.includes(lead.id))
    : leads.value

  const csvContent = [
    'First Name,Last Name,Company,Email,Phone,City,State,Source,Status,Owner,Last Contacted',
    ...dataToExport.map(lead => 
      `"${lead.firstName}","${lead.lastName}","${lead.company}","${lead.email}","${lead.phone}","${lead.city}","${lead.state}","${lead.source}","${lead.status}","${lead.owner}","${lead.lastContacted}"`
    )
  ].join('\n')

  const blob = new Blob([csvContent], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `leads_${selectedRows.value.length > 0 ? 'selected' : 'all'}_${new Date().toISOString().split('T')[0]}.csv`
  a.click()
  window.URL.revokeObjectURL(url)
}

// 👉 Add note functionality
const openAddNoteModal = (leadId: number) => {
  selectedLeadForNote.value = leadId
  isAddNoteModalVisible.value = true
}

const onNoteAdded = () => {
  // TODO: Refresh notes or show success message
  console.log('Note added successfully')
}

// 👉 Navigation
const router = useRouter()

const navigateToAddLead = () => {
  router.push({ name: 'franchisor-add-lead' })
}
</script>

<template>
  <section>
    <!-- 👉 Stats Cards -->
    <div class="d-flex mb-6">
      <VRow>
        <template v-for="(data, id) in statsData" :key="id">
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
    </div>

    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Filters</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <!-- 👉 Select Source -->
          <VCol cols="12" sm="4">
            <AppSelect v-model="selectedSource" placeholder="Select Source" :items="sources" clearable
              clear-icon="tabler-x" />
          </VCol>
          <!-- 👉 Select Status -->
          <VCol cols="12" sm="4">
            <AppSelect v-model="selectedStatus" placeholder="Select Status" :items="statuses" clearable
              clear-icon="tabler-x" />
          </VCol>
          <!-- 👉 Select Owner -->
          <VCol cols="12" sm="4">
            <AppSelect v-model="selectedOwner" placeholder="Select Owner" :items="owners" clearable
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
            <AppTextField v-model="searchQuery" placeholder="Search Lead" />
          </div>

          <!-- 👉 Import button -->
          <VBtn variant="tonal" color="secondary" prepend-icon="tabler-file-import"
            @click="isImportDialogVisible = true">
            Import
          </VBtn>

          <!-- 👉 Export button -->
          <VBtn variant="tonal" color="secondary" prepend-icon="tabler-upload" @click="exportLeads">
            Export {{ selectedRows.length > 0 ? `(${selectedRows.length})` : 'All' }}
          </VBtn>

          <!-- 👉 Add lead button -->
          <VBtn prepend-icon="tabler-plus" @click="navigateToAddLead">
            Add Lead
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:model-value="selectedRows" v-model:page="page"
        :items="leads" item-value="id" :items-length="totalLeads" :headers="headers" class="text-no-wrap" show-select
        @update:options="updateOptions">
        <!-- Index -->
        <template #item.index="{ index }">
          <div class="text-body-1 font-weight-medium">
            {{ (page - 1) * itemsPerPage + index + 1 }}
          </div>
        </template>

        <!-- Lead Name -->
        <template #item.name="{ item }">
          <div class="d-flex align-center gap-x-4">
            <VAvatar size="34" variant="tonal" color="primary">
              <span>{{ avatarText(`${item.firstName} ${item.lastName}`) }}</span>
            </VAvatar>
            <div class="d-flex flex-column">
              <h6 class="text-base font-weight-medium">
                <RouterLink :to="{ name: 'franchisor-lead-view-id', params: { id: item.id } }" class="text-link">
                  {{ item.firstName }} {{ item.lastName }}
                </RouterLink>
              </h6>
            </div>
          </div>
        </template>

        <!-- Company -->
        <template #item.company="{ item }">
          <div class="text-body-1">
            {{ item.company }}
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

        <!-- Location -->
        <template #item.location="{ item }">
          <div class="text-body-1">
            {{ item.city }}, {{ item.state }}
          </div>
        </template>

        <!-- Source -->
        <template #item.source="{ item }">
          <div class="text-body-1 text-capitalize">
            {{ item.source }}
          </div>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <VChip :color="resolveStatusVariant(item.status)" size="small" label class="text-capitalize">
            {{ item.status }}
          </VChip>
        </template>

        <!-- Owner -->
        <template #item.owner="{ item }">
          <div class="text-body-1">
            {{ item.owner }}
          </div>
        </template>

        <!-- Last Contacted -->
        <template #item.lastContacted="{ item }">
          <div class="text-body-1">
            {{ item.lastContacted }}
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <VBtn icon variant="text" color="medium-emphasis">
            <VIcon icon="tabler-dots-vertical" />
            <VMenu activator="parent">
              <VList>
                <VListItem :to="{ name: 'franchisor-lead-view-id', params: { id: item.id } }">
                  <template #prepend>
                    <VIcon icon="tabler-eye" />
                  </template>
                  <VListItemTitle>View & Edit</VListItemTitle>
                </VListItem>

                <VListItem @click="openAddNoteModal(item.id)">
                  <template #prepend>
                    <VIcon icon="tabler-note" />
                  </template>
                  <VListItemTitle>Add Note</VListItemTitle>
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
          <TablePagination v-model:page="page" :items-per-page="itemsPerPage" :total-items="totalLeads" />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>

    <!-- 👉 Import Dialog -->
    <VDialog v-model="isImportDialogVisible" max-width="600">
      <VCard>
        <VCardItem>
          <VCardTitle>Import Leads from CSV</VCardTitle>
        </VCardItem>

        <VCardText>
          <div class="mb-4">
            <VBtn variant="tonal" color="secondary" size="small" prepend-icon="tabler-download"
              @click="downloadExampleCSV">
              Download Example CSV
            </VBtn>
          </div>

          <VFileInput
            v-model="csvFile"
            label="Select CSV File"
            accept=".csv"
            prepend-icon="tabler-file-upload"
            show-size
            @change="handleFileUpload"
          />
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn color="secondary" variant="tonal" @click="isImportDialogVisible = false">
            Cancel
          </VBtn>
          <VBtn color="primary" @click="importCSV">
            Import
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- 👉 Delete Confirmation Dialog -->
    <VDialog v-model="isDeleteDialogVisible" max-width="500">
      <VCard>
        <VCardItem>
          <VCardTitle>Confirm Delete</VCardTitle>
        </VCardItem>

        <VCardText>
          Are you sure you want to delete this lead? This action cannot be undone.
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn color="secondary" variant="tonal" @click="isDeleteDialogVisible = false">
            Cancel
          </VBtn>
          <VBtn color="error" @click="deleteLead">
            Delete
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- 👉 Add Note Modal -->
    <AddNoteModal
      v-model:is-dialog-visible="isAddNoteModalVisible"
      :lead-id="selectedLeadForNote || 0"
      @note-added="onNoteAdded"
    />
  </section>
</template>
