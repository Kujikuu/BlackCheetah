<script setup lang="ts">
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
  try {
    // Create FormData to handle file uploads
    const formData = new FormData()

    formData.append('lead_id', String(props.leadId))
    formData.append('title', noteTitle.value)
    formData.append('description', noteDescription.value)

    // Append all attachments
    attachments.value.forEach(file => {
      formData.append('attachments[]', file)
    })

    const response = await $api('/v1/notes', {
      method: 'POST',
      body: formData,
    })

    if (response.success) {
      console.log('Note added successfully:', response.data)

      // Emit event to refresh notes
      emit('noteAdded')

      // Close dialog and reset form
      dialogValue.value = false
      resetForm()

      // TODO: Show success toast
    }
    else {
      console.error('Failed to add note:', response.message)

      // TODO: Show error toast
    }
  }
  catch (error) {
    console.error('Error adding note:', error)

    // TODO: Show error toast
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
        <VForm @submit.prevent="onSubmit">
          <VRow>
            <VCol cols="12">
              <AppTextField
                v-model="noteTitle"
                label="Title"
                placeholder="Enter note title"
              />
            </VCol>

            <VCol cols="12">
              <AppTextarea
                v-model="noteDescription"
                label="Description"
                placeholder="Enter note description..."
                rows="6"
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
