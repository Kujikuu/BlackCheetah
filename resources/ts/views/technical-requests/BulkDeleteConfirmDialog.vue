<script setup lang="ts">
// Props
interface Props {
  visible: boolean
  selectedCount: number
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

// Confirm bulk delete
const confirmBulkDelete = () => {
  emit('confirm')
}
</script>

<template>
  <VDialog :model-value="visible" max-width="500" @update:model-value="emit('update:visible', $event)">
    <DialogCloseBtn @click="closeDialog" />
    <VCard title="Confirm Bulk Delete">
      <VCardText>
        
      </VCardText>
      <VCardText class="d-flex align-center justify-center gap-2">
        <VBtn variant="outlined" @click="closeDialog">
          Cancel
        </VBtn>
        <VBtn color="error" :loading="loading" @click="confirmBulkDelete">
          Delete {{ selectedCount }} Request(s)
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>
