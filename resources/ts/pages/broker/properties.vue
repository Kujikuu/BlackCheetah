<script setup lang="ts">
import { propertyApi, type Property, type PropertyFilters } from '@/services/api'
import type { VDataTableServer } from 'vuetify/components'
import { formatCurrency } from '@/@core/utils/formatters'

// Component imports
import CreatePropertyDialog from '@/components/dialogs/broker/CreatePropertyDialog.vue'
import EditPropertyDialog from '@/components/dialogs/broker/EditPropertyDialog.vue'
import DeletePropertyDialog from '@/components/dialogs/broker/DeletePropertyDialog.vue'

// Filters and search
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedPropertyType = ref('')

// Data table state
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])
const isLoading = ref(false)

// Data
const properties = ref<Property[]>([])
const totalProperties = ref(0)

// Modal states
const isCreateDialogVisible = ref(false)
const isEditDialogVisible = ref(false)
const isDeleteDialogVisible = ref(false)
const selectedProperty = ref<Property | null>(null)

// Table headers
const headers = [
  { title: '#', key: 'index', sortable: false },
  { title: 'Title', key: 'title' },
  { title: 'Type', key: 'property_type' },
  { title: 'Location', key: 'city' },
  { title: 'Monthly Rent', key: 'monthly_rent' },
  { title: 'Size (m²)', key: 'size_sqm' },
  { title: 'Status', key: 'status' },
  { title: 'Created', key: 'created_at' },
  { title: 'Actions', key: 'actions', sortable: false },
]

// Statistics
const statsData = ref([
  { title: 'Total Properties', value: '0', change: 0, icon: 'tabler-building', iconColor: 'primary' },
  { title: 'Available', value: '0', change: 0, icon: 'tabler-home-check', iconColor: 'success' },
  { title: 'Leased', value: '0', change: 0, icon: 'tabler-home-hand', iconColor: 'info' },
  { title: 'Under Negotiation', value: '0', change: 0, icon: 'tabler-message-circle', iconColor: 'warning' },
])

// API Functions
const fetchProperties = async () => {
  try {
    isLoading.value = true

    const filters: PropertyFilters = {
      page: page.value,
      per_page: itemsPerPage.value,
    }

    if (searchQuery.value)
      filters.search = searchQuery.value
    if (selectedStatus.value)
      filters.status = selectedStatus.value
    if (selectedPropertyType.value)
      filters.property_type = selectedPropertyType.value
    if (sortBy.value)
      filters.sortBy = sortBy.value
    if (orderBy.value)
      filters.orderBy = orderBy.value

    const response = await propertyApi.getProperties(filters)

    if (response.success && response.data) {
      properties.value = response.data.data || []
      totalProperties.value = response.data.total || 0
    }
  }
  catch (error) {
    console.error('Error fetching properties:', error)
  }
  finally {
    isLoading.value = false
  }
}

// Fetch stats
const fetchStats = async () => {
  // In a real implementation, you'd have a statistics endpoint
  // For now, calculate from the loaded properties
  const total = properties.value.length
  const available = properties.value.filter(p => p.status === 'available').length
  const leased = properties.value.filter(p => p.status === 'leased').length
  const underNegotiation = properties.value.filter(p => p.status === 'under_negotiation').length

  statsData.value = [
    { title: 'Total Properties', value: String(total), change: 0, icon: 'tabler-building', iconColor: 'primary' },
    { title: 'Available', value: String(available), change: 0, icon: 'tabler-home-check', iconColor: 'success' },
    { title: 'Leased', value: String(leased), change: 0, icon: 'tabler-home-hand', iconColor: 'info' },
    { title: 'Under Negotiation', value: String(underNegotiation), change: 0, icon: 'tabler-message-circle', iconColor: 'warning' },
  ]
}

// Actions
const openEditDialog = (property: Property) => {
  selectedProperty.value = property
  isEditDialogVisible.value = true
}

const openDeleteDialog = (property: Property) => {
  selectedProperty.value = property
  isDeleteDialogVisible.value = true
}

const onPropertyCreated = async () => {
  await fetchProperties()
  await fetchStats()
  isCreateDialogVisible.value = false
}

const onPropertyUpdated = async () => {
  await fetchProperties()
  await fetchStats()
  isEditDialogVisible.value = false
}

const onPropertyDeleted = async () => {
  await fetchProperties()
  await fetchStats()
  isDeleteDialogVisible.value = false
}

// Bulk delete
const bulkDelete = async () => {
  if (selectedRows.value.length === 0)
    return

  try {
    const response = await propertyApi.bulkDelete(selectedRows.value)
    if (response.success) {
      await fetchProperties()
      await fetchStats()
      selectedRows.value = []
    }
  }
  catch (error) {
    console.error('Error bulk deleting properties:', error)
  }
}

// Mark as leased
const markAsLeased = async (property: Property) => {
  try {
    const response = await propertyApi.markLeased(property.id)
    if (response.success) {
      await fetchProperties()
      await fetchStats()
    }
  }
  catch (error) {
    console.error('Error marking property as leased:', error)
  }
}

// Update table options
const updateOptions = (options: any) => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
  fetchProperties()
}
// Format date
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

// Lifecycle
onMounted(async () => {
  await fetchProperties()
  await fetchStats()
})

// Watchers
watch([searchQuery, selectedStatus, selectedPropertyType], () => {
  page.value = 1
  fetchProperties()
})
</script>

<template>
  <section>
    <!-- Stats Cards -->
    <div class="d-flex mb-6">
      <VRow>
        <template v-for="(data, id) in statsData" :key="id">
          <VCol cols="12" md="3" sm="6">
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
    </div>

    <!-- Main Card -->
    <VCard>
      <!-- Filters -->
      <VCardItem class="pb-4">
        <VCardTitle>Filters</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <VCol cols="12" sm="3">
            <AppSelect v-model="selectedStatus" placeholder="Select Status" :items="[
              { title: 'All Statuses', value: '' },
              { title: 'Available', value: 'available' },
              { title: 'Under Negotiation', value: 'under_negotiation' },
              { title: 'Leased', value: 'leased' },
              { title: 'Unavailable', value: 'unavailable' },
            ]" clearable clear-icon="tabler-x" />
          </VCol>

          <VCol cols="12" sm="3">
            <AppSelect v-model="selectedPropertyType" placeholder="Property Type" :items="[
              { title: 'All Types', value: '' },
              { title: 'Retail', value: 'retail' },
              { title: 'Office', value: 'office' },
              { title: 'Kiosk', value: 'kiosk' },
              { title: 'Food Court', value: 'food_court' },
              { title: 'Standalone', value: 'standalone' },
            ]" clearable clear-icon="tabler-x" />
          </VCol>

        </VRow>
      </VCardText>

      <VDivider />

      <!-- Toolbar -->
      <VCardText class="d-flex flex-wrap gap-4">
        <div class="me-3 d-flex gap-3">
          <AppSelect :model-value="itemsPerPage" :items="[
            { value: 10, title: '10' },
            { value: 25, title: '25' },
            { value: 50, title: '50' },
            { value: 100, title: '100' },
          ]" style="inline-size: 6.25rem;" @update:model-value="itemsPerPage = parseInt($event, 10)" />

          <VBtn v-if="selectedRows.length > 0" variant="tonal" color="error" @click="bulkDelete">
            <VIcon icon="tabler-trash" class="me-2" />
            Delete Selected ({{ selectedRows.length }})
          </VBtn>
        </div>
        <VSpacer />

        <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
          <div style="inline-size: 15.625rem;">
            <AppTextField v-model="searchQuery" placeholder="Search Properties" prepend-inner-icon="tabler-search" />
          </div>

          <VBtn prepend-icon="tabler-plus" @click="isCreateDialogVisible = true">
            Add Property
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- Data Table -->
      <VDataTableServer v-model:items-per-page="itemsPerPage" v-model:model-value="selectedRows" v-model:page="page"
        :items="properties" item-value="id" :items-length="totalProperties" :headers="headers" :loading="isLoading"
        class="text-no-wrap" show-select @update:options="updateOptions">
        <!-- Index -->
        <template #item.index="{ index }">
          <div class="text-body-1 font-weight-medium">
            {{ (page - 1) * itemsPerPage + index + 1 }}
          </div>
        </template>

        <!-- Title -->
        <template #item.title="{ item }">
          <div class="text-body-1 font-weight-medium">
            {{ item.title }}
          </div>
        </template>

        <!-- Type -->
        <template #item.property_type="{ item }">
          <VChip size="small" color="primary" variant="tonal">
            {{ item.property_type }}
          </VChip>
        </template>

        <!-- Location -->
        <template #item.city="{ item }">
          <div class="text-body-2">
            {{ item.city }}
          </div>
        </template>

        <!-- Monthly Rent -->
        <template #item.monthly_rent="{ item }">
          <div class="text-body-1 font-weight-medium">
            {{ formatCurrency(item.monthly_rent) }}
          </div>
        </template>

        <!-- Size -->
        <template #item.size_sqm="{ item }">
          <div class="text-body-2">
            {{ item.size_sqm }} m²
          </div>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <VChip size="small"
            :color="item.status === 'available' ? 'success' : item.status === 'leased' ? 'info' : item.status === 'under_negotiation' ? 'warning' : 'error'"
            variant="tonal">
            {{ item.status.replace('_', ' ') }}
          </VChip>
        </template>

        <!-- Created At -->
        <template #item.created_at="{ item }">
          <div class="text-body-2">
            {{ formatDate(item.created_at) }}
          </div>
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

                <VListItem v-if="item.status !== 'leased'" @click="markAsLeased(item)">
                  <template #prepend>
                    <VIcon icon="tabler-home-check" />
                  </template>
                  <VListItemTitle>Mark as Leased</VListItemTitle>
                </VListItem>

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
        </template>

        <!-- Pagination -->
        <template #bottom>
          <TablePagination v-model:page="page" :items-per-page="itemsPerPage" :total-items="totalProperties" />
        </template>
      </VDataTableServer>
    </VCard>

    <!-- Dialogs -->
    <CreatePropertyDialog v-model:is-dialog-visible="isCreateDialogVisible" @property-created="onPropertyCreated" />

    <EditPropertyDialog v-model:is-dialog-visible="isEditDialogVisible" :property="selectedProperty"
      @property-updated="onPropertyUpdated" />

    <DeletePropertyDialog v-model:is-dialog-visible="isDeleteDialogVisible" :property="selectedProperty"
      @property-deleted="onPropertyDeleted" />
  </section>
</template>
