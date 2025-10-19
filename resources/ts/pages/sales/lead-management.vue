<script setup lang="ts">
import AddNoteModal from '@/components/franchisor/AddNoteModal.vue'
import ImportLeadsDialog from '@/components/dialogs/leads/ImportLeadsDialog.vue'
import DeleteLeadDialog from '@/components/dialogs/leads/DeleteLeadDialog.vue'
import { type Lead, type LeadStatistic, leadApi } from '@/services/api/lead'

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
const loading = ref(false)
const error = ref<string | null>(null)

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
  fetchLeads()
}

// Helper functions
const prefixWithPlus = (value: number) => {
  return value > 0 ? `+${value}` : value
}

const avatarText = (name: string) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
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

// Data state
const leadsData = ref<Lead[]>([])
const totalLeads = ref(0)
const statsData = ref<LeadStatistic[]>([])

const leads = computed(() => leadsData.value)

// Fetch leads from API
const fetchLeads = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await leadApi.getLeads({
      status: selectedStatus.value,
      source: selectedSource.value,
      owner: selectedOwner.value,
      search: searchQuery.value,
      itemsPerPage: itemsPerPage.value,
      page: page.value,
      sortBy: sortBy.value,
      orderBy: orderBy.value,
    })

    if (response.success) {
      leadsData.value = response.leads
      totalLeads.value = response.total
    }
  }
  catch (err: any) {
    error.value = err.message || 'Failed to fetch leads'
    console.error('Error fetching leads:', err)
  }
  finally {
    loading.value = false
  }
}

// Fetch statistics
const fetchStatistics = async () => {
  try {
    const response = await leadApi.getStatistics()
    if (response.success)
      statsData.value = response.data
  }
  catch (err: any) {
    console.error('Error fetching statistics:', err)
  }
}

// Watch for filter changes
watch([searchQuery, selectedStatus, selectedSource, selectedOwner, page, itemsPerPage], () => {
  fetchLeads()
})

// Initial load
onMounted(() => {
  fetchLeads()
  fetchStatistics()
})

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

  try {
    loading.value = true

    const response = await leadApi.deleteLead(leadToDelete.value)

    if (response.success) {
      // Remove from local data
      const index = leadsData.value.findIndex((lead: Lead) => lead.id === leadToDelete.value)
      if (index !== -1)
        leadsData.value.splice(index, 1)

      // Remove from selectedRows
      const selectedIndex = selectedRows.value.findIndex((row: number) => row === leadToDelete.value)
      if (selectedIndex !== -1)
        selectedRows.value.splice(selectedIndex, 1)

      totalLeads.value--
    }
  }
  catch (err: any) {
    console.error('Error deleting lead:', err)
    error.value = err.message || 'Failed to delete lead'
  }
  finally {
    loading.value = false
    isDeleteDialogVisible.value = false
    leadToDelete.value = null
  }
}

// Bulk delete
const bulkDelete = async () => {
  if (selectedRows.value.length === 0)
    return

  try {
    loading.value = true

    const response = await leadApi.bulkDelete(selectedRows.value)

    if (response.success) {
      // Refresh leads list
      await fetchLeads()
      await fetchStatistics()
      selectedRows.value = []
    }
  }
  catch (err: any) {
    console.error('Error bulk deleting leads:', err)
    error.value = err.message || 'Failed to delete leads'
  }
  finally {
    loading.value = false
  }
}

// 👉 Import CSV
const csvFile = ref<File | null>(null)

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0])
    csvFile.value = target.files[0]
}

const downloadExampleCSV = () => {
  const csvContent = 'First Name,Last Name,Email,Phone,Company,Country,State,City,Lead Source,Lead Status,Lead Owner\nJohn,Doe,john.doe@example.com,+1234567890,Example Corp,USA,California,Los Angeles,Website,qualified,Sarah Johnson'
  const blob = new Blob([csvContent], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')

  a.href = url
  a.download = 'leads_example.csv'
  a.click()
  window.URL.revokeObjectURL(url)
}

const importCSV = async () => {
  if (!csvFile.value) {
    error.value = 'Please select a CSV file'

    return
  }

  try {
    loading.value = true

    const response = await leadApi.importCsv(csvFile.value)

    if (response.success) {
      // Refresh leads list and statistics
      await fetchLeads()
      await fetchStatistics()
      isImportDialogVisible.value = false
      csvFile.value = null
    }
  }
  catch (err: any) {
    console.error('Error importing CSV:', err)
    error.value = err.message || 'Failed to import CSV'
  }
  finally {
    loading.value = false
  }
}

// 👉 Export functionality
const exportLeads = () => {
  const exportIds = selectedRows.value.length > 0 ? selectedRows.value : undefined
  const exportUrl = leadApi.exportCsv(exportIds)

  // Create a temporary link and trigger download
  const a = document.createElement('a')

  a.href = exportUrl
  a.download = `leads_${selectedRows.value.length > 0 ? 'selected' : 'all'}_${new Date().toISOString().split('T')[0]}.csv`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
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

const onLeadDeleted = async () => {
  await fetchLeads()
  await fetchStatistics()
  isDeleteDialogVisible.value = false
  leadToDelete.value = null
}

const onImportCompleted = (file: File | null) => {
  if (!file) return
  csvFile.value = file
  importCSV()
}

// 👉 Navigation
const router = useRouter()

const navigateToAddLead = () => {
  router.push({ name: 'sales-add-lead' })
}
</script>

<template>
  <section>
    <!-- 👉 Stats Cards -->
    <div class="d-flex mb-6">
      <VRow>
        <template
          v-for="(data, id) in statsData"
          :key="id"
        >
          <VCol
            cols="12"
            md="4"
            sm="6"
          >
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
                      <div
                        class="text-base"
                        :class="data.change > 0 ? 'text-success' : 'text-error'"
                      >
                        ({{ prefixWithPlus(data.change) }}%)
                      </div>
                    </div>
                  </div>
                  <VAvatar
                    :color="data.iconColor"
                    variant="tonal"
                    rounded
                    size="42"
                  >
                    <VIcon
                      :icon="data.icon"
                      size="26"
                    />
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
          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="selectedSource"
              placeholder="Select Source"
              :items="sources"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
          <!-- 👉 Select Status -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="selectedStatus"
              placeholder="Select Status"
              :items="statuses"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
          <!-- 👉 Select Owner -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="selectedOwner"
              placeholder="Select Owner"
              :items="owners"
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
              placeholder="Search Lead"
            />
          </div>

          <!-- 👉 Import button -->
          <VBtn
            variant="tonal"
            color="secondary"
            prepend-icon="tabler-file-import"
            @click="isImportDialogVisible = true"
          >
            Import
          </VBtn>

          <!-- 👉 Export button -->
          <VBtn
            variant="tonal"
            color="secondary"
            prepend-icon="tabler-upload"
            @click="exportLeads"
          >
            Export {{ selectedRows.length > 0 ? `(${selectedRows.length})` : 'All' }}
          </VBtn>

          <!-- 👉 Add lead button -->
          <VBtn
            prepend-icon="tabler-plus"
            @click="navigateToAddLead"
          >
            Add Lead
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:model-value="selectedRows"
        v-model:page="page"
        :items="leads"
        item-value="id"
        :items-length="totalLeads"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- Index -->
        <template #item.index="{ index }">
          <div class="text-body-1 font-weight-medium">
            {{ (page - 1) * itemsPerPage + index + 1 }}
          </div>
        </template>

        <!-- Lead Name -->
        <template #item.name="{ item }">
          <div class="d-flex align-center gap-x-4">
            <VAvatar
              size="34"
              variant="tonal"
              color="primary"
            >
              <span>{{ avatarText(`${item.firstName} ${item.lastName}`) }}</span>
            </VAvatar>
            <div class="d-flex flex-column">
              <RouterLink
                :to="{ name: 'sales-leads-id', params: { id: item.id } }"
                class="text-link"
              >
                {{ item.firstName }} {{ item.lastName }}
              </RouterLink>
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
          <VChip
            :color="resolveStatusVariant(item.status)"
            size="small"
            label
            class="text-capitalize"
          >
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
          <VBtn
            icon
            variant="text"
            color="medium-emphasis"
          >
            <VIcon icon="tabler-dots-vertical" />
            <VMenu activator="parent">
              <VList>
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
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalLeads"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>

    <!-- 👉 Import Leads Dialog -->
    <ImportLeadsDialog
      v-model:is-dialog-visible="isImportDialogVisible"
      @import="onImportCompleted"
    />

    <!-- 👉 Delete Lead Dialog -->
    <DeleteLeadDialog
      v-model:is-dialog-visible="isDeleteDialogVisible"
      :lead-id="leadToDelete"
      @lead-deleted="onLeadDeleted"
    />

    <!-- 👉 Add Note Modal -->
    <AddNoteModal
      v-model:is-dialog-visible="isAddNoteModalVisible"
      :lead-id="selectedLeadForNote || 0"
      @note-added="onNoteAdded"
    />
  </section>
</template>
