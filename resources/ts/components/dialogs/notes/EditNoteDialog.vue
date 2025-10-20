<script setup lang="ts">
interface Attachment {
  name: string
  path: string
  size: number
  type: string
}

interface Note {
  id: number
  title: string
  description: string
  createdBy: string
  createdAt: string
  attachments: Attachment[]
}

interface Props {
  isDialogVisible: boolean
  note: Note | null
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'noteUpdated'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const editedNote = ref<Note | null>(null)
const newAttachments = ref<File[]>([])
const isSubmitting = ref(false)

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

// Watch for note changes and create a copy for editing
watch(() => props.note, newNote => {
  if (newNote)
    editedNote.value = { ...newNote }
}, { immediate: true })

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files)
    newAttachments.value = Array.from(target.files)
}

const formatFileSize = (bytes: number): string => {
  if (bytes === 0)
    return '0 Bytes'

  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))

  return `${Number.parseFloat((bytes / k ** i).toFixed(2))} ${sizes[i]}`
}

const removeExistingAttachment = async (index: number) => {
  if (!editedNote.value)
    return

  try {
    const response = await $api(`/v1/notes/${editedNote.value.id}/attachments/${index}`, {
      method: 'DELETE',
    })

    if (response.success) {
      // Remove from local array
      editedNote.value.attachments.splice(index, 1)
      console.log('Attachment removed successfully')

      // TODO: Show success toast
    }
    else {
      console.error('Failed to remove attachment:', response.message)

      // TODO: Show error toast
    }
  }
  catch (error) {
    console.error('Error removing attachment:', error)

    // TODO: Show error toast
  }
}

const onSubmit = async () => {
  if (!editedNote.value)
    return

  try {
    isSubmitting.value = true

    // Create FormData to handle file uploads
    const formData = new FormData()

    formData.append('title', editedNote.value.title)
    formData.append('description', editedNote.value.description)
    formData.append('_method', 'PUT') // Method spoofing for Laravel

    // Append new attachments
    newAttachments.value.forEach(file => {
      formData.append('attachments[]', file)
    })

    const response = await $api(`/v1/notes/${editedNote.value.id}`, {
      method: 'POST',
      body: formData,
    })

    if (response.success) {
      console.log('Note updated successfully')
      emit('noteUpdated')
      dialogValue.value = false
      newAttachments.value = []

      // TODO: Show success toast
    }
    else {
      console.error('Failed to update note:', response.message)

      // TODO: Show error toast
    }
  }
  catch (error) {
    console.error('Error updating note:', error)

    // TODO: Show error toast
  }
  finally {
    isSubmitting.value = false
  }
}

const onCancel = () => {
  dialogValue.value = false
  newAttachments.value = []

  // Reset edited note to original
  if (props.note)
    editedNote.value = { ...props.note }
}

const getFileIcon = (type: string): string => {
  if (type.startsWith('image/'))
    return 'tabler-photo'
  if (type.startsWith('video/'))
    return 'tabler-video'
  if (type.startsWith('audio/'))
    return 'tabler-music'
  if (type.includes('pdf'))
    return 'tabler-file-type-pdf'
  if (type.includes('word') || type.includes('document'))
    return 'tabler-file-type-doc'
  if (type.includes('excel') || type.includes('spreadsheet'))
    return 'tabler-file-type-xls'
  if (type.includes('zip') || type.includes('compressed'))
    return 'tabler-file-zip'

  return 'tabler-file'
}
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="800"
  >
    <DialogCloseBtn @click="onCancel" />
    <VCard v-if="editedNote" title="Edit Note">
      <VCardText>
        <VForm @submit.prevent="onSubmit">
          <VRow>
            <VCol cols="12">
              <AppTextField
                v-model="editedNote.title"
                label="Title"
                placeholder="Enter note title"
                :disabled="isSubmitting"
              />
            </VCol>

            <VCol cols="12">
              <AppTextarea
                v-model="editedNote.description"
                label="Description"
                placeholder="Enter note description..."
                rows="6"
                :disabled="isSubmitting"
              />
            </VCol>

            <!-- Existing Attachments -->
            <VCol
              v-if="editedNote.attachments && editedNote.attachments.length > 0"
              cols="12"
            >
              <h6 class="text-h6 mb-3">
                Existing Attachments
              </h6>
              <VRow>
                <VCol
                  v-for="(attachment, index) in editedNote.attachments"
                  :key="index"
                  cols="12"
                >
                  <VCard
                    variant="tonal"
                    class="d-flex align-center pa-3"
                  >
                    <VIcon
                      :icon="getFileIcon(attachment.type)"
                      size="32"
                      class="me-3"
                    />
                    <div class="flex-grow-1 me-3">
                      <div class="text-body-2 font-weight-medium">
                        {{ attachment.name }}
                      </div>
                      <div class="text-caption text-disabled">
                        {{ formatFileSize(attachment.size) }}
                      </div>
                    </div>
                    <VBtn
                      icon
                      size="small"
                      variant="text"
                      color="error"
                      :disabled="isSubmitting"
                      @click="removeExistingAttachment(index)"
                    >
                      <VIcon icon="tabler-trash" />
                    </VBtn>
                  </VCard>
                </VCol>
              </VRow>
            </VCol>

            <!-- New Attachments -->
            <VCol cols="12">
              <VFileInput
                label="Attachments"
                multiple
                prepend-icon="tabler-paperclip"
                placeholder="Upload files"
                chips
                show-size
                :disabled="isSubmitting"
                @change="handleFileUpload"
              />
            </VCol>
          </VRow>
        </VForm>
      </VCardText>

      <VDivider />

      <VCardActions>
        <VSpacer />
        <VBtn
          color="secondary"
          variant="tonal"
          :disabled="isSubmitting"
          @click="onCancel"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          :loading="isSubmitting"
          :disabled="isSubmitting"
          @click="onSubmit"
        >
          Update Note
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
