<script setup lang="ts">
import { computed } from 'vue'

// Interface for display request
interface DisplayRequest {
  id: number | string
  requestId: string
  userName: string
  userEmail: string
  userAvatar: string
  subject: string
  description: string
  priority: string
  status: string
  date: string
  category: string
  attachments: any[]
}

// Props
interface Props {
  items: DisplayRequest[]
  itemsLength: number
  loading: boolean
  selectedRows: number[]
  headers: any[]
  isAdmin?: boolean
  itemsPerPage: number
  page: number
  resolvePriorityVariant: (priority: string) => { color: string; icon: string }
  resolveStatusVariant: (status: string) => string
  formatStatus: (status: string) => string
  getFileIcon: (fileName: string) => string
}

// Emits
interface Emits {
  (e: 'update:selectedRows', value: number[]): void
  (e: 'update:itemsPerPage', value: number): void
  (e: 'update:page', value: number): void
  (e: 'view-request', request: DisplayRequest): void
  (e: 'edit-request', request: DisplayRequest): void
  (e: 'delete-request', request: DisplayRequest): void
  (e: 'update:options', options: any): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Create refs for v-model bindings
const localSelectedRows = computed({
  get: () => props.selectedRows,
  set: (value) => emit('update:selectedRows', value)
})

const localItemsPerPage = computed({
  get: () => props.itemsPerPage,
  set: (value) => emit('update:itemsPerPage', value)
})

const localPage = computed({
  get: () => props.page,
  set: (value) => emit('update:page', value)
})

// Computed headers that include requester column for admin
const computedHeaders = computed(() => {
  const baseHeaders = [...props.headers]
  
  // Add requester column for admin users after Request ID
  if (props.isAdmin) {
    const requesterIndex = baseHeaders.findIndex(header => header.key === 'requestId')
    if (requesterIndex !== -1) {
      baseHeaders.splice(requesterIndex + 1, 0, {
        title: 'Requester',
        key: 'userName',
        sortable: false,
      })
    }
  }
  
  return baseHeaders
})

// Event handlers
const handleOptionsUpdate = (options: any) => {
  emit('update:options', options)
}

const handleViewRequest = (request: DisplayRequest) => {
  emit('view-request', request)
}

const handleEditRequest = (request: DisplayRequest) => {
  emit('edit-request', request)
}

const handleDeleteRequest = (request: DisplayRequest) => {
  emit('delete-request', request)
}
</script>

<template>
  <VDataTableServer
    v-model:items-per-page="localItemsPerPage"
    v-model:model-value="localSelectedRows"
    v-model:page="localPage"
    :items="items"
    item-value="id"
    :items-length="itemsLength"
    :headers="computedHeaders"
    class="text-no-wrap"
    show-select
    :loading="loading"
    @update:options="handleOptionsUpdate"
  >
    <!-- Empty State -->
    <template #no-data>
      <div class="text-center pa-8">
        <VIcon icon="tabler-inbox-off" size="64" class="mb-4 text-disabled" />
        <h3 class="text-h5 mb-2">
          No Technical Requests Found
        </h3>
        <p class="text-body-1 text-medium-emphasis mb-4">
          No requests match your search criteria. Try adjusting your filters.
        </p>
      </div>
    </template>

    <!-- Request ID -->
    <template #item.requestId="{ item }">
      <div class="text-body-1 font-weight-medium text-primary">
        {{ item.requestId }}
      </div>
    </template>

    <!-- Requester (Admin only) -->
    <template #item.userName="{ item }">
      <div class="d-flex align-center">
        <VAvatar
          v-if="item.userAvatar"
          size="32"
          class="me-3"
        >
          <VImg :src="item.userAvatar" />
        </VAvatar>
        <VAvatar
          v-else
          size="32"
          class="me-3"
          color="primary"
        >
          <span class="text-sm font-weight-medium">
            {{ item.userName?.charAt(0)?.toUpperCase() }}
          </span>
        </VAvatar>
        <div>
          <div class="text-body-1 font-weight-medium">
            {{ item.userName }}
          </div>
          <div class="text-sm text-medium-emphasis">
            {{ item.userEmail }}
          </div>
        </div>
      </div>
    </template>

    <!-- Subject -->
    <template #item.subject="{ item }">
      <div class="text-body-1">
        {{ item.subject }}
      </div>
      <div class="text-sm text-medium-emphasis">
        {{ item.category }}
      </div>
    </template>

    <!-- Priority -->
    <template #item.priority="{ item }">
      <VChip :color="props.resolvePriorityVariant(item.priority).color" size="small" label class="text-capitalize">
        <VIcon :icon="props.resolvePriorityVariant(item.priority).icon" size="16" class="me-1" />
        {{ item.priority }}
      </VChip>
    </template>

    <!-- Status -->
    <template #item.status="{ item }">
      <VChip :color="props.resolveStatusVariant(item.status)" size="small" label class="text-capitalize">
        {{ props.formatStatus(item.status) }}
      </VChip>
    </template>

    <!-- Date -->
    <template #item.date="{ item }">
      <div class="text-body-1">
        {{ item.date }}
      </div>
    </template>

    <!-- Actions -->
    <template #item.actions="{ item }">
      <div class="d-flex gap-1">
        <VBtn icon variant="text" color="medium-emphasis" size="small">
          <VIcon icon="tabler-dots-vertical" size="22" />
          <VMenu activator="parent">
            <VList>
              <VListItem @click="handleViewRequest(item)">
                <template #prepend>
                  <VIcon icon="tabler-eye" />
                </template>
                <VListItemTitle>View Details</VListItemTitle>
              </VListItem>

              <VListItem v-if="isAdmin" @click="handleEditRequest(item)">
                <template #prepend>
                  <VIcon icon="tabler-edit" />
                </template>
                <VListItemTitle>Edit Request</VListItemTitle>
              </VListItem>

              <VListItem @click="handleDeleteRequest(item)">
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
      <TablePagination
        v-model:page="localPage"
        v-model:items-per-page="localItemsPerPage"
        :total-items="itemsLength"
      />
    </template>
  </VDataTableServer>
</template>
