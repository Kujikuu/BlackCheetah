<script setup lang="ts">
// Props
interface Props {
  visible: boolean
  request: any | null
  loading?: boolean
}

// Emits
interface Emits {
  (e: 'update:visible', value: boolean): void
  (e: 'confirm'): void
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
})

const emit = defineEmits<Emits>()

// Close dialog
const closeDialog = () => {
  emit('update:visible', false)
}

// Confirm delete
const confirmDelete = () => {
  emit('confirm')
}
</script>

<template>
  <VDialog :model-value="visible" max-width="500" @update:model-value="emit('update:visible', $event)">
    <VCard class="text-center px-10 py-6">
      <VCardText>
        <VIcon icon="tabler-alert-triangle" size="64" color="warning" class="mb-4" />
        <h3 class="text-h5 mb-2">
          Confirm Delete
        </h3>
        <p class="text-body-1 text-medium-emphasis mb-4">
          Are you sure you want to delete this technical request?
        </p>
        <div v-if="request" class="text-start pa-4 bg-surface rounded">
          <div class="text-body-2 text-medium-emphasis mb-1">
            Request ID
          </div>
          <div class="text-body-1 font-weight-medium mb-2">
            {{ request.requestId }}
          </div>
          <div class="text-body-2 text-medium-emphasis mb-1">
            Subject
          </div>
          <div class="text-body-1">
            {{ request.subject }}
          </div>
        </div>
        <p class="text-body-2 text-error mt-4 mb-0">
          This action cannot be undone.
        </p>
      </VCardText>
      <VCardText class="d-flex align-center justify-center gap-2">
        <VBtn variant="outlined" @click="closeDialog">
          Cancel
        </VBtn>
        <VBtn color="error" :loading="loading" @click="confirmDelete">
          Delete Request
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>
