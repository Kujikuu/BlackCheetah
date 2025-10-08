<script setup lang="ts">
interface Props {
  isDialogVisible: boolean
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'documentAdded', document: any): void
}

interface DocumentForm {
  title: string
  description: string
  type: string
  file: File | null
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

// 👉 Form data
const documentForm = ref<DocumentForm>({
  title: '',
  description: '',
  type: '',
  file: null,
})

// 👉 Form validation
const isFormValid = ref(false)

// 👉 File input ref
const fileInput = ref<HTMLInputElement>()

// 👉 Document type options
const documentTypeOptions = [
  'FDD',
  'Franchise Agreement',
  'Operations Manual',
  'Brand Guidelines',
  'Legal Documents',
  'Sales Report',
  'Financial Statement',
  'Marketing Materials',
  'Training Materials',
  'Quality Standards',
  'Other',
]

// 👉 Form rules
const requiredRule = (value: string) => !!value || 'This field is required'
const fileRequiredRule = (value: File | null) => !!value || 'Please select a file'

// 👉 Methods
const updateModelValue = (val: boolean) => {
  emit('update:isDialogVisible', val)
}

const resetForm = () => {
  documentForm.value = {
    title: '',
    description: '',
    type: '',
    file: null,
  }
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const onFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    documentForm.value.file = target.files[0]
  }
}

const formatFileSize = (bytes: number) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const onSubmit = () => {
  if (isFormValid.value && documentForm.value.file) {
    const newDocument = {
      id: Date.now(), // Simple ID generation
      title: documentForm.value.title,
      description: documentForm.value.description,
      type: documentForm.value.type,
      fileName: documentForm.value.file.name,
      fileSize: formatFileSize(documentForm.value.file.size),
      uploadDate: new Date().toISOString().split('T')[0],
      status: 'pending',
      comment: '',
    }

    emit('documentAdded', newDocument)
    resetForm()
    updateModelValue(false)
  }
}

const onCancel = () => {
  resetForm()
  updateModelValue(false)
}

// 👉 Watch for dialog visibility changes
watch(() => props.isDialogVisible, (newVal) => {
  if (!newVal) {
    resetForm()
  }
})
</script>

<template>
  <VDialog :model-value="props.isDialogVisible" max-width="600" persistent @update:model-value="updateModelValue">
    <VCard>
      <VCardTitle class="text-h5 pa-6 pb-4">
        Add Document
      </VCardTitle>

      <VDivider />

      <VCardText class="pa-6">
        <VForm v-model="isFormValid" @submit.prevent="onSubmit">
          <VRow>
            <!-- Document Title -->
            <VCol cols="12">
              <VTextField v-model="documentForm.title" label="Document Title" placeholder="Enter document title"
                :rules="[requiredRule]" required />
            </VCol>

            <!-- Description -->
            <VCol cols="12">
              <VTextarea v-model="documentForm.description" label="Description" placeholder="Enter document description"
                rows="3" :rules="[requiredRule]" required />
            </VCol>

            <!-- Document Type -->
            <VCol cols="12">
              <VSelect v-model="documentForm.type" label="Document Type" placeholder="Select document type"
                :items="documentTypeOptions" :rules="[requiredRule]" required />
            </VCol>

            <!-- File Upload -->
            <VCol cols="12">
              <VFileInput ref="fileInput" label="Select File" placeholder="Choose a file to upload"
                prepend-icon="tabler-paperclip" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png"
                :rules="[fileRequiredRule]" required @change="onFileChange" />
              <div class="text-caption text-disabled mt-2">
                Supported formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPG, JPEG, PNG
              </div>
            </VCol>

            <!-- File Preview -->
            <VCol v-if="documentForm.file" cols="12">
              <VCard variant="outlined">
                <VCardText class="d-flex align-center gap-3">
                  <VIcon icon="tabler-file-text" size="24" color="primary" />
                  <div class="flex-grow-1">
                    <div class="text-body-1 font-weight-medium">{{ documentForm.file.name }}</div>
                    <div class="text-body-2 text-disabled">{{ formatFileSize(documentForm.file.size) }}</div>
                  </div>
                  <VBtn icon variant="text" color="error" size="small"
                    @click="documentForm.file = null; fileInput && (fileInput.value = '')">
                    <VIcon icon="tabler-x" />
                  </VBtn>
                </VCardText>
              </VCard>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>

      <VDivider />

      <VCardActions class="pa-6">
        <VSpacer />
        <VBtn color="error" variant="text" @click="onCancel">
          Cancel
        </VBtn>
        <VBtn color="primary" :disabled="!isFormValid" @click="onSubmit">
          Add Document
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
