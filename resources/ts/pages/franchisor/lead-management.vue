<script setup lang="ts">
import AssignLeadModal from '@/components/dialogs/AssignLeadModal.vue'
import ConvertLeadModal from '@/components/dialogs/ConvertLeadModal.vue'
import MarkAsLostModal from '@/components/dialogs/MarkAsLostModal.vue'
import AddNoteModal from '@/components/franchisor/AddNoteModal.vue'
import { type Lead, leadApi } from '@/services/api/lead'
import { avatarText, prefixWithPlus } from '@core/utils/formatters'

// 👉 Store
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedSource = ref('')
const selectedOwner = ref('')

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])
const isLoading = ref(false)

// Update data table options
const updateOptions = (options: any) => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
  fetchLeads()
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

// Real data from API
const leads = ref<Lead[]>([])
const totalLeads = ref(0)

// 👉 API Functions
const fetchLeads = async () => {
  try {
    isLoading.value = true

    const filters: any = {
      page: page.value,
      itemsPerPage: itemsPerPage.value,
    }

    if (searchQuery.value)
      filters.search = searchQuery.value
    if (selectedStatus.value)
      filters.status = selectedStatus.value
    if (selectedSource.value)
      filters.source = selectedSource.value
    if (selectedOwner.value)
      filters.owner = selectedOwner.value
    if (sortBy.value)
      filters.sortBy = sortBy.value
    if (orderBy.value)
      filters.orderBy = orderBy.value

    const response = await leadApi.getLeads(filters)

    if (response.success) {
      leads.value = response.leads || []
      totalLeads.value = response.total || 0
    }
  }
  catch (error) {
    console.error('Error fetching leads:', error)

    // Handle error - show toast notification
  }
  finally {
    isLoading.value = false
  }
}

// 👉 Stats
const statsData = ref([
  { title: 'Total Leads', value: '0', change: 0, icon: 'tabler-users', iconColor: 'primary' },
  { title: 'Qualified', value: '0', change: 0, icon: 'tabler-user-check', iconColor: 'success' },
  { title: 'Unqualified', value: '0', change: 0, icon: 'tabler-user-x', iconColor: 'error' },
])

// Fetch stats
const fetchStats = async () => {
  try {
    const response = await leadApi.getStatistics()

    if (response.success)
      statsData.value = response.data
  }
  catch (error) {
    console.error('Error fetching stats:', error)
  }
}

// 👉 Modal states
const isAddNoteModalVisible = ref(false)
const selectedLeadForNote = ref<number | null>(null)
const isImportDialogVisible = ref(false)
const isDeleteDialogVisible = ref(false)
const leadToDelete = ref<number | null>(null)
const isAssignLeadModalVisible = ref(false)
const selectedLeadForAssign = ref<number | null>(null)
const isConvertLeadModalVisible = ref(false)
const selectedLeadForConvert = ref<number | null>(null)
const isMarkAsLostModalVisible = ref(false)
const selectedLeadForMarkLost = ref<number | null>(null)

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

// Fetch sales associates for assignment
const salesAssociates = ref([])

const fetchSalesAssociates = async () => {
  try {
    const response = await $api('/v1/franchisor/sales-associates', {
      method: 'GET',
    })

    if (response.success)
      salesAssociates.value = response.data || []
  }
  catch (error) {
    console.error('Error fetching sales associates:', error)
  }
}

const owners = computed(() => {
  return salesAssociates.value.map((associate: any) => ({
    title: `${associate.first_name} ${associate.last_name}`,
    value: associate.id.toString(),
  }))
})

const resolveStatusVariant = (stat: string) => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === 'qualified')
    return 'success'
  if (statLowerCase === 'unqualified')
    return 'error'

  return 'primary'
}

// 👉 Lead Actions
const openAssignModal = (leadId: number) => {
  selectedLeadForAssign.value = leadId
  isAssignLeadModalVisible.value = true
}

const openConvertModal = (leadId: number) => {
  selectedLeadForConvert.value = leadId
  isConvertLeadModalVisible.value = true
}

const openMarkAsLostModal = (leadId: number) => {
  selectedLeadForMarkLost.value = leadId
  isMarkAsLostModalVisible.value = true
}

const openAddNoteModal = (leadId: number) => {
  selectedLeadForNote.value = leadId
  isAddNoteModalVisible.value = true
}

// 👉 Delete lead
const confirmDelete = (id: number) => {
  leadToDelete.value = id
  isDeleteDialogVisible.value = true
}

const deleteLead = async () => {
  if (leadToDelete.value === null)
    return

  try {
    const response = await leadApi.deleteLead(leadToDelete.value)

    if (response.success) {
      // Refresh the leads list
      await fetchLeads()

      // Remove from selectedRows if it was selected
      const selectedIndex = selectedRows.value.findIndex(row => row === leadToDelete.value)
      if (selectedIndex !== -1)
        selectedRows.value.splice(selectedIndex, 1)
    }
  }
  catch (error) {
    console.error('Error deleting lead:', error)
  }
  finally {
    isDeleteDialogVisible.value = false
    leadToDelete.value = null
  }
}

// 👉 Bulk delete
const bulkDelete = async () => {
  if (selectedRows.value.length === 0)
    return

  try {
    const response = await leadApi.bulkDelete(selectedRows.value)

    if (response.success) {
      // Refresh leads list
      await fetchLeads()
      await fetchStats()
      selectedRows.value = []
    }
  }
  catch (error) {
    console.error('Error bulk deleting leads:', error)
  }
}

// 👉 Modal event handlers
const onLeadAssigned = () => {
  fetchLeads()
  isAssignLeadModalVisible.value = false
  selectedLeadForAssign.value = null
}

const onLeadConverted = () => {
  fetchLeads()
  isConvertLeadModalVisible.value = false
  selectedLeadForConvert.value = null
}

const onLeadMarkedLost = () => {
  fetchLeads()
  isMarkAsLostModalVisible.value = false
  selectedLeadForMarkLost.value = null
}

const onNoteAdded = () => {
  fetchLeads()
  isAddNoteModalVisible.value = false
  selectedLeadForNote.value = null
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
    ? leads.value.filter(lead => selectedRows.value.includes(lead.id as never))
    : leads.value

  const csvContent = [
    'First Name,Last Name,Company,Email,Phone,City,State,Source,Status,Owner,Last Contacted',
    ...dataToExport.map(lead =>
      `"${lead.firstName}","${lead.lastName}","${lead.company}","${lead.email}","${lead.phone}","${lead.city}","${lead.state}","${lead.source}","${lead.status}","${lead.owner}","${lead.lastContacted}"`,
    ),
  ].join('\n')

  const blob = new Blob([csvContent], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')

  a.href = url
  a.download = `leads_${selectedRows.value.length > 0 ? 'selected' : 'all'}_${new Date().toISOString().split('T')[0]}.csv`
  a.click()
  window.URL.revokeObjectURL(url)
}

// 👉 Navigation
const router = useRouter()

const navigateToAddLead = () => {
  router.push({ name: 'franchisor-add-lead' })
}

// 👉 Lifecycle
onMounted(async () => {
  await Promise.all([
    fetchLeads(),
    fetchStats(),
    fetchSalesAssociates(),
  ])
})

// 👉 Watchers
watch([searchQuery, selectedStatus, selectedSource, selectedOwner], () => {
  page.value = 1
  fetchLeads()
})
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

          <!-- Bulk Actions -->
          <VBtn v-if="selectedRows.length > 0" variant="tonal" color="error" @click="bulkDelete">
            <VIcon icon="tabler-trash" class="me-2" />
            Delete Selected ({{ selectedRows.length }})
          </VBtn>
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
        :items="leads" item-value="id" :items-length="totalLeads" :headers="headers" :loading="isLoading"
        class="text-no-wrap" show-select @update:options="updateOptions">
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
                <RouterLink :to="{ name: 'franchisor-leads-id', params: { id: item.id } }" class="text-link">
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
          <VChip size="small" color="primary" variant="tonal">
            {{ item.source }}
          </VChip>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <VChip size="small" :color="resolveStatusVariant(item.status)" variant="tonal">
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
                <VListItem @click="openAssignModal(item.id)">
                  <template #prepend>
                    <VIcon icon="tabler-user-plus" />
                  </template>
                  <VListItemTitle>Assign Lead</VListItemTitle>
                </VListItem>

                <VListItem @click="openConvertModal(item.id)">
                  <template #prepend>
                    <VIcon icon="tabler-check" />
                  </template>
                  <VListItemTitle>Convert Lead</VListItemTitle>
                </VListItem>

                <VListItem @click="openMarkAsLostModal(item.id)">
                  <template #prepend>
                    <VIcon icon="tabler-x" />
                  </template>
                  <VListItemTitle>Mark as Lost</VListItemTitle>
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
      <DialogCloseBtn @click="isImportDialogVisible = false" />
      <VCard title="Import Leads from CSV">
        <VCardText>
          <div class="mb-4">
            <VBtn variant="tonal" color="secondary" prepend-icon="tabler-download"
              @click="downloadExampleCSV">
              Download Example CSV
            </VBtn>
          </div>

          <VFileInput v-model="csvFile" label="Select CSV File" accept=".csv" prepend-icon="tabler-file-upload"
            show-size @change="handleFileUpload" />
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
    <VDialog v-model="isDeleteDialogVisible" max-width="600">
      <DialogCloseBtn @click="isDeleteDialogVisible = false" />
      <VCard title="Confirm Delete">
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

    <!-- 👉 Assign Lead Modal -->
    <AssignLeadModal v-model:is-dialog-visible="isAssignLeadModalVisible" :lead-id="selectedLeadForAssign || 0"
      :sales-associates="salesAssociates" @lead-assigned="onLeadAssigned" />

    <!-- 👉 Convert Lead Modal -->
    <ConvertLeadModal v-model:is-dialog-visible="isConvertLeadModalVisible" :lead-id="selectedLeadForConvert || 0"
      @lead-converted="onLeadConverted" />

    <!-- 👉 Mark as Lost Modal -->
    <MarkAsLostModal v-model:is-dialog-visible="isMarkAsLostModalVisible" :lead-id="selectedLeadForMarkLost || 0"
      @lead-marked-lost="onLeadMarkedLost" />

    <!-- 👉 Add Note Modal -->
    <AddNoteModal v-model:is-dialog-visible="isAddNoteModalVisible" :lead-id="selectedLeadForNote || 0"
      @note-added="onNoteAdded" />
  </section>
</template>
