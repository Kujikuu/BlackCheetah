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
    <DialogCloseBtn @click="closeDialog" />
    <VCard title="Confirm Delete">
      <VCardText>
        <div v-if="request" class="text-start pa-4 bg-surface rounded">
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
