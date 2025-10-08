<script setup lang="ts">
// 👉 Store
const searchQuery = ref('')
const selectedStatus = ref()
const selectedSource = ref()

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
  { title: 'Lead Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Phone', key: 'phone' },
  { title: 'Source', key: 'source' },
  { title: 'Status', key: 'status' },
  { title: 'Created Date', key: 'createdDate' },
  { title: 'Actions', key: 'actions', sortable: false },
]

// Mock data - Replace with actual API call
const leadsData = ref({
  leads: [
    {
      id: 1,
      name: 'John Smith',
      email: 'john.smith@example.com',
      phone: '+1 234-567-8900',
      source: 'Website',
      status: 'pending',
      createdDate: '2024-01-15',
    },
    {
      id: 2,
      name: 'Sarah Johnson',
      email: 'sarah.j@example.com',
      phone: '+1 234-567-8901',
      source: 'Referral',
      status: 'won',
      createdDate: '2024-01-14',
    },
    {
      id: 3,
      name: 'Michael Brown',
      email: 'michael.b@example.com',
      phone: '+1 234-567-8902',
      source: 'Social Media',
      status: 'lost',
      createdDate: '2024-01-13',
    },
  ],
  totalLeads: 3,
})

const leads = computed(() => leadsData.value.leads)
const totalLeads = computed(() => leadsData.value.totalLeads)

// 👉 search filters
const sources = [
  { title: 'Website', value: 'website' },
  { title: 'Referral', value: 'referral' },
  { title: 'Social Media', value: 'social_media' },
  { title: 'Email Campaign', value: 'email' },
]

const statuses = [
  { title: 'Pending', value: 'pending' },
  { title: 'Won', value: 'won' },
  { title: 'Lost', value: 'lost' },
]

const resolveStatusVariant = (stat: string) => {
  const statLowerCase = stat.toLowerCase()
  if (statLowerCase === 'pending')
    return 'warning'
  if (statLowerCase === 'won')
    return 'success'
  if (statLowerCase === 'lost')
    return 'error'

  return 'primary'
}

// 👉 Modal states
const isViewLeadModalVisible = ref(false)
const isEditLeadModalVisible = ref(false)
const isDeleteDialogVisible = ref(false)
const selectedLead = ref<any>(null)
const leadToDelete = ref<number | null>(null)

// 👉 View lead
const viewLead = (lead: any) => {
  selectedLead.value = lead
  isViewLeadModalVisible.value = true
}

// 👉 Edit lead
const editLead = (lead: any) => {
  selectedLead.value = { ...lead }
  isEditLeadModalVisible.value = true
}

// 👉 Delete lead with confirmation
const confirmDelete = (id: number) => {
  leadToDelete.value = id
  isDeleteDialogVisible.value = true
}

const deleteLead = async () => {
  if (leadToDelete.value === null) return

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

// 👉 Save edited lead
const saveLead = async () => {
  if (!selectedLead.value) return

  // TODO: Implement API call
  const index = leadsData.value.leads.findIndex(lead => lead.id === selectedLead.value.id)
  if (index !== -1) {
    leadsData.value.leads[index] = { ...selectedLead.value }
  }

  isEditLeadModalVisible.value = false
  selectedLead.value = null
}

// 👉 Export functionality
const exportLeads = () => {
  const dataToExport = selectedRows.value.length > 0 
    ? leads.value.filter(lead => selectedRows.value.includes(lead.id))
    : leads.value

  const csvContent = [
    'Name,Email,Phone,Source,Status,Created Date',
    ...dataToExport.map(lead => 
      `"${lead.name}","${lead.email}","${lead.phone}","${lead.source}","${lead.status}","${lead.createdDate}"`
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

const widgetData = ref([
  { title: 'Total Leads', value: '1,245', change: 15, desc: 'All time leads', icon: 'tabler-users', iconColor: 'primary' },
  { title: 'Closed and Won', value: '487', change: 22, desc: 'Converted leads', icon: 'tabler-trophy', iconColor: 'success' },
  { title: 'Closed and Lost', value: '156', change: -8, desc: 'Lost opportunities', icon: 'tabler-x', iconColor: 'error' },
  { title: 'Pending Leads', value: '602', change: 12, desc: 'Active leads', icon: 'tabler-clock', iconColor: 'warning' },
])
</script>

<template>
  <section>
    <!-- 👉 Widgets -->
    <div class="d-flex mb-6">
      <VRow>
        <template
          v-for="(data, id) in widgetData"
          :key="id"
        >
          <VCol
            cols="12"
            md="3"
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
                    <div class="text-sm">
                      {{ data.desc }}
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
            sm="6"
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

          <!-- 👉 Export button -->
          <VBtn
            variant="tonal"
            color="secondary"
            prepend-icon="tabler-upload"
            @click="exportLeads"
          >
            Export {{ selectedRows.length > 0 ? `(${selectedRows.length})` : 'All' }}
          </VBtn>

          <!-- 👉 Add lead button - Commented out as requested -->
          <!-- <VBtn prepend-icon="tabler-plus">
            Add New Lead
          </VBtn> -->
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
        <!-- Lead Name -->
        <template #item.name="{ item }">
          <div class="d-flex align-center gap-x-4">
            <VAvatar
              size="34"
              variant="tonal"
              color="primary"
            >
              <span>{{ avatarText(item.name) }}</span>
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

        <!-- Created Date -->
        <template #item.createdDate="{ item }">
          <div class="text-body-1">
            {{ item.createdDate }}
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
                <VListItem @click="viewLead(item)">
                  <template #prepend>
                    <VIcon icon="tabler-eye" />
                  </template>
                  <VListItemTitle>View</VListItemTitle>
                </VListItem>

                <VListItem @click="editLead(item)">
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
            :total-items="totalLeads"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>

    <!-- 👉 View Lead Modal -->
    <VDialog
      v-model="isViewLeadModalVisible"
      max-width="600"
    >
      <VCard v-if="selectedLead">
        <VCardItem>
          <VCardTitle>Lead Details</VCardTitle>
        </VCardItem>

        <VCardText>
          <VRow>
            <VCol cols="12" md="6">
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">Name</div>
                <div class="text-body-1 font-weight-medium">{{ selectedLead.name }}</div>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">Email</div>
                <div class="text-body-1">{{ selectedLead.email }}</div>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">Phone</div>
                <div class="text-body-1">{{ selectedLead.phone }}</div>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">Source</div>
                <div class="text-body-1 text-capitalize">{{ selectedLead.source }}</div>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">Status</div>
                <VChip
                  :color="resolveStatusVariant(selectedLead.status)"
                  size="small"
                  label
                  class="text-capitalize"
                >
                  {{ selectedLead.status }}
                </VChip>
              </div>
            </VCol>
            <VCol cols="12" md="6">
              <div class="mb-4">
                <div class="text-sm text-disabled mb-1">Created Date</div>
                <div class="text-body-1">{{ selectedLead.createdDate }}</div>
              </div>
            </VCol>
          </VRow>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isViewLeadModalVisible = false"
          >
            Close
          </VBtn>
          <VBtn
            color="primary"
            @click="editLead(selectedLead)"
          >
            Edit
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- 👉 Edit Lead Modal -->
    <VDialog
      v-model="isEditLeadModalVisible"
      max-width="700"
    >
      <VCard v-if="selectedLead">
        <VCardItem>
          <VCardTitle>Edit Lead</VCardTitle>
        </VCardItem>

        <VCardText>
          <VForm @submit.prevent="saveLead">
            <VRow>
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="selectedLead.name"
                  label="Name"
                  placeholder="Enter lead name"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="selectedLead.email"
                  label="Email"
                  type="email"
                  placeholder="Enter email address"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="selectedLead.phone"
                  label="Phone"
                  placeholder="Enter phone number"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppSelect
                  v-model="selectedLead.source"
                  label="Source"
                  :items="sources"
                  placeholder="Select source"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppSelect
                  v-model="selectedLead.status"
                  label="Status"
                  :items="statuses"
                  placeholder="Select status"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="selectedLead.createdDate"
                  label="Created Date"
                  type="date"
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
            @click="isEditLeadModalVisible = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            @click="saveLead"
          >
            Save Changes
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
          Are you sure you want to delete this lead? This action cannot be undone.
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
            @click="deleteLead"
          >
            Delete
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </section>
</template>
