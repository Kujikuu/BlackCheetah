<script setup lang="ts">
import { leadApi } from '@/services/api'

interface Props {
  isDialogVisible: boolean
  leadId: number | null
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'noteAdded'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const note = ref('')
const isLoading = ref(false)

const updateModelValue = (val: boolean) => {
  emit('update:isDialogVisible', val)
  if (!val)
    note.value = ''
}

const addNote = async () => {
  if (!props.leadId || !note.value.trim())
    return

  isLoading.value = true

  try {
    // API call to add note
    await leadApi.addNoteToLead(props.leadId, note.value.trim())

    emit('noteAdded')
    updateModelValue(false)
  }
  catch (error) {
    console.error('Error adding note:', error)
  }
  finally {
    isLoading.value = false
  }
}
</script>

<template>
  <VDialog
    max-width="500"
    :model-value="props.isDialogVisible"
    @update:model-value="updateModelValue"
  >
    <VCard>
      <VCardItem>
        <VCardTitle>Add Note</VCardTitle>
      </VCardItem>

      <VCardText>
        <VTextarea
          v-model="note"
          label="Note"
          placeholder="Enter your note here..."
          rows="4"
          variant="outlined"
          :disabled="isLoading"
        />
      </VCardText>

      <VCardText class="d-flex align-center justify-end gap-2">
        <VBtn
          color="secondary"
          variant="tonal"
          :disabled="isLoading"
          @click="updateModelValue(false)"
        >
          Cancel
        </VBtn>

        <VBtn
          color="primary"
          variant="elevated"
          :loading="isLoading"
          :disabled="!note.trim()"
          @click="addNote"
        >
          Add Note
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>
