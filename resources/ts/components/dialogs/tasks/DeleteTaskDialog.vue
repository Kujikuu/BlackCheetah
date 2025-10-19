<script setup lang="ts">
interface Props {
  isDialogVisible: boolean
  taskId: number | null
}

interface Emits {
  'update:isDialogVisible': [value: boolean]
  'task-deleted': [taskId: number]
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const isLoading = ref(false)

const deleteTask = async () => {
  if (props.taskId === null) return

  try {
    isLoading.value = true
    const response = await $api<{ success: boolean }>(`/v1/franchisor/tasks/${props.taskId}`, {
      method: 'DELETE',
    })

    if (response.success) {
      emit('task-deleted', props.taskId)
    } else {
      console.error('Failed to delete task:', response)
    }
  } catch (error) {
    console.error('Error deleting task:', error)
  } finally {
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
        Are you sure you want to delete this task? This action cannot be undone.
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
          @click="deleteTask"
        >
          Delete
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
