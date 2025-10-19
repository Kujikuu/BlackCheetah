<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { $api } from '@/utils/api'
import { franchiseeDashboardApi } from '@/services/api/franchisee-dashboard'

interface DocumentData {
  id: number | null
  name?: string
  title?: string  // Support both name and title for different use cases
  description: string
  file: File | null
  type: string
  status: string
  is_confidential?: boolean
  expiry_date?: string | null
}

interface Props {
  isDialogVisible: boolean
  franchiseId?: number | null      // For franchisor use case
  unitId?: number | null          // For franchisee use case
  documentData?: DocumentData | null
  mode?: 'franchise' | 'unit'     // Add mode to distinguish between use cases
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'documentSaved'): void
  (e: 'documentAdded', document: any): void  // For franchisee compatibility
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const isLoading = ref(false)

const documentForm = ref<DocumentData>({
  id: null,
  name: '',
  title: '',
  description: '',
  file: null,
  type: 'other',
  status: 'active',
  is_confidential: false,
  expiry_date: null,
})

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

// Document type options for franchisee mode
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

// Initialize form data when dialog opens or when documentData changes
watch(() => [props.isDialogVisible, props.documentData], () => {
  if (props.isDialogVisible) {
    if (props.documentData) {
      // Edit mode - populate form with existing data
      documentForm.value = { ...props.documentData }
    } else {
      // Add mode - reset form
      documentForm.value = {
        id: null,
        name: '',
        title: '',
        description: '',
        file: null,
        type: props.mode === 'unit' ? '' : 'other',
        status: 'active',
        is_confidential: false,
        expiry_date: null,
      }
    }
  }
}, { immediate: true })

// Computed for current mode
const isUnitMode = computed(() => props.mode === 'unit')
const currentNameField = computed(() => isUnitMode.value ? documentForm.value.title : documentForm.value.name)

const saveDocument = async () => {
  // Validate based on mode
  if (isUnitMode.value && !props.unitId) {
    console.error('No unit ID available')
    return
  }

  if (!isUnitMode.value && !props.franchiseId) {
    console.error('No franchise ID available')
    return
  }

  const nameValue = isUnitMode.value ? documentForm.value.title : documentForm.value.name
  if (!nameValue || !documentForm.value.file) {
    console.error('Document name/title and file are required')
    return
  }

  isLoading.value = true
  try {
    if (isUnitMode.value) {
      // Handle franchisee unit mode
      const formData = new FormData()
      formData.append('title', documentForm.value.title || '')
      formData.append('description', documentForm.value.description || '')
      formData.append('type', documentForm.value.type)
      formData.append('status', documentForm.value.status || 'pending')
      formData.append('comment', '') // Default comment for franchisee API
      
      if (documentForm.value.file) {
        formData.append('file', documentForm.value.file)
      }

      const response = await franchiseeDashboardApi.createDocument(props.unitId!, formData)
      
      if (response.success) {
        // Emit the document added event for franchisee compatibility
        emit('documentAdded', {
          ...response.data,
          id: response.data?.id || Date.now(),
          title: documentForm.value.title,
          description: documentForm.value.description,
          type: documentForm.value.type,
          fileName: documentForm.value.file?.name,
          fileSize: formatFileSize(documentForm.value.file?.size || 0),
          uploadDate: new Date().toISOString().split('T')[0],
          status: documentForm.value.status || 'pending',
          comment: '',
          file: documentForm.value.file,
        })
        dialogValue.value = false
      } else {
        console.error('Failed to save document:', response.message)
      }
    } else {
      // Handle franchisor mode
      const formData = new FormData()
      formData.append('name', documentForm.value.name || '')
      formData.append('description', documentForm.value.description || '')
      formData.append('type', documentForm.value.type)
      formData.append('status', documentForm.value.status)
      formData.append('is_confidential', documentForm.value.is_confidential ? '1' : '0')

      if (documentForm.value.expiry_date) {
        formData.append('expiry_date', documentForm.value.expiry_date)
      }

      if (documentForm.value.file) {
        formData.append('file', documentForm.value.file)
      }

      let response
      if (documentForm.value.id !== null) {
        // Update existing document
        response = await $api(`/v1/franchises/${props.franchiseId}/documents/${documentForm.value.id}`, {
          method: 'PUT',
          body: formData,
        })
      } else {
        // Add new document
        response = await $api(`/v1/franchises/${props.franchiseId}/documents`, {
          method: 'POST',
          body: formData,
        })
      }

      if (response.success) {
        emit('documentSaved')
        dialogValue.value = false
      } else {
        console.error('Failed to save document:', response.message)
      }
    }
  }
  catch (error) {
    console.error('Error saving document:', error)
  }
  finally {
    isLoading.value = false
  }
}

const formatFileSize = (bytes: number) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return `${Number.parseFloat((bytes / k ** i).toFixed(2))} ${sizes[i]}`
}

const handleClose = () => {
  dialogValue.value = false
}

const dialogTitle = computed(() => {
  return documentForm.value.id !== null ? 'Edit Document' : 'Add New Document'
})
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="600"
  >
    <DialogCloseBtn @click="handleClose" />
    <VCard :title="dialogTitle">
      <VCardText>
        <VForm>
          <VRow>
            <VCol cols="12">
              <!-- Document Name/Title field - different labels based on mode -->
              <VTextField
                v-if="isUnitMode"
                v-model="documentForm.title"
                label="Document Title"
                placeholder="Enter document title"
                variant="outlined"
                required
              />
              <VTextField
                v-else
                v-model="documentForm.name"
                label="Document Name"
                placeholder="Enter document name"
                variant="outlined"
                required
              />
            </VCol>
            <VCol cols="12">
              <VTextarea
                v-model="documentForm.description"
                label="Description"
                placeholder="Enter document description"
                rows="3"
                variant="outlined"
                :required="isUnitMode"
              />
            </VCol>
            <!-- Document Type - only for unit mode -->
            <VCol v-if="isUnitMode" cols="12">
              <VSelect
                v-model="documentForm.type"
                label="Document Type"
                placeholder="Select document type"
                :items="documentTypeOptions"
                variant="outlined"
                required
              />
            </VCol>
            <VCol cols="12">
              <VFileInput
                v-model="documentForm.file"
                label="Attach File"
                :accept="isUnitMode ? '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png' : '.pdf,.doc,.docx'"
                prepend-icon="tabler-paperclip"
                variant="outlined"
                required
              />
              <div v-if="isUnitMode" class="text-caption text-disabled mt-2">
                Supported formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPG, JPEG, PNG
              </div>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>

      <VCardActions>
        <VSpacer />
        <VBtn
          color="secondary"
          variant="outlined"
          @click="handleClose"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          :loading="isLoading"
          @click="saveDocument"
        >
          {{ documentForm.id !== null ? 'Update Document' : 'Add Document' }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
