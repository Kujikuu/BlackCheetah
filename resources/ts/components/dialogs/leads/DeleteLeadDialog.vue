<script setup lang="ts">
import { leadApi } from '@/services/api/lead'

interface Props {
  isDialogVisible: boolean
  leadId: number | null
}

interface Emits {
  'update:isDialogVisible': [value: boolean]
  'lead-deleted': []
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const isLoading = ref(false)

const deleteLead = async () => {
  if (props.leadId === null)
    return

  try {
    isLoading.value = true
    const response = await leadApi.deleteLead(props.leadId)

    if (response.success) {
      emit('lead-deleted')
    }
  }
  catch (error) {
    console.error('Error deleting lead:', error)
  }
  finally {
    isLoading.value = false
    emit('update:isDialogVisible', false)
  }
}

const handleCancel = () => {
  emit('update:isDialogVisible', false)
}
</script>

<template>
  <VDialog
    :model-value="isDialogVisible"
    max-width="600"
    @update:model-value="emit('update:isDialogVisible', $event)"
  >
    <DialogCloseBtn @click="handleCancel" />
    <VCard title="Confirm Delete">
      <VCardText>
        Are you sure you want to delete this lead? This action cannot be undone.
      </VCardText>

      <VCardActions>
        <VSpacer />
        <VBtn
          color="secondary"
          variant="tonal"
          @click="handleCancel"
        >
          Cancel
        </VBtn>
        <VBtn
          color="error"
          :loading="isLoading"
          @click="deleteLead"
        >
          Delete
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
