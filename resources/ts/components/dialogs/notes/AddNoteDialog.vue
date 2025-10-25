<script setup lang="ts">
import { notesApi } from '@/services/api'
import { useFormValidation } from '@/composables/useFormValidation'
import { useAddNoteValidation } from '@/validation/notesValidation'

interface Props {
  isDialogVisible: boolean
  leadId: number
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'noteAdded'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const formRef = ref()
const { backendErrors, setBackendErrors, clearError } = useFormValidation()
const validationRules = useAddNoteValidation()

const noteTitle = ref('')
const noteDescription = ref('')
const attachments = ref<File[]>([])

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const resetForm = () => {
  noteTitle.value = ''
  noteDescription.value = ''
  attachments.value = []
}

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files)
    attachments.value = Array.from(target.files)
}

const onSubmit = async () => {
  // Validate form
  const { valid } = await formRef.value.validate()
  if (!valid) return

  try {
    const response = await notesApi.createNote({
      title: noteTitle.value,
      description: noteDescription.value,
      lead_id: props.leadId,
      attachments: attachments.value,
    })

    if (response.success) {
      console.log('Note added successfully:', response.data)

      // Emit event to refresh notes
      emit('noteAdded')

      // Close dialog and reset form
      dialogValue.value = false
      resetForm()
    }
    else {
      console.error('Failed to add note:', response.message)
    }
  }
  catch (error: any) {
    console.error('Error adding note:', error)
    setBackendErrors(error)
  }
}

const onCancel = () => {
  dialogValue.value = false
  resetForm()
}
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="600"
  >
    <DialogCloseBtn @click="onCancel" />
    <VCard title="Add Note">
      <VCardText>
        <VForm ref="formRef" @submit.prevent="onSubmit">
          <VRow>
            <VCol cols="12">
              <AppTextField
                v-model="noteTitle"
                label="Title"
                placeholder="Enter note title"
                :rules="validationRules.note"
                :error-messages="backendErrors.note"
                @input="clearError('note')"
              />
            </VCol>

            <VCol cols="12">
              <AppTextarea
                v-model="noteDescription"
                label="Description"
                placeholder="Enter note description..."
                rows="6"
                :rules="validationRules.content"
                :error-messages="backendErrors.content"
                @input="clearError('content')"
              />
            </VCol>

            <VCol cols="12">
              <VFileInput
                label="Attachments"
                multiple
                prepend-icon="tabler-paperclip"
                placeholder="Upload files"
                chips
                show-size
                @change="handleFileUpload"
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
          @click="onCancel"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          @click="onSubmit"
        >
          Add Note
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
