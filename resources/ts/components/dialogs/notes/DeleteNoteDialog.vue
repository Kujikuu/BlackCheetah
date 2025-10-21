<script setup lang="ts">
import ConfirmDeleteDialog from '@/components/dialogs/common/ConfirmDeleteDialog.vue'
import { notesApi } from '@/services/api'

interface Props {
  isDialogVisible: boolean
  noteId: number | null
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'note-deleted'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const isLoading = ref(false)

const onDeleteConfirm = async () => {
  if (props.noteId === null)
    return

  isLoading.value = true
  try {
    const response = await notesApi.deleteNote(props.noteId)

    if (response.success) {
      emit('note-deleted')
      emit('update:isDialogVisible', false)
    }
    else {
      console.error('Failed to delete note:', response.message)
      // Optionally, show a toast notification
    }
  }
  catch (error) {
    console.error('Error deleting note:', error)
    // Optionally, show a toast notification
  }
  finally {
    isLoading.value = false
  }
}
</script>

<template>
  <ConfirmDeleteDialog
    v-model:is-dialog-visible="props.isDialogVisible"
    title="Confirm Delete Note"
    message="Are you sure you want to delete this note? This action cannot be undone."
    @confirm="onDeleteConfirm"
  />
</template>
