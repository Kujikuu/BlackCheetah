<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { $api } from '@/utils/api'
import { franchiseApi, franchiseeDashboardApi } from '@/services/api'
import { useFormValidation } from '@/composables/useFormValidation'
import { useStoreDocumentValidation } from '@/validation/documentValidation'

interface DocumentData {
  id: number | null
  name?: string
  title?: string  // Support both name and title for different use cases
  description: string
  file: File | null
  file_name?: string | null
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

const formRef = ref()
const { backendErrors, setBackendErrors, clearError } = useFormValidation()
const validationRules = useStoreDocumentValidation()

const isLoading = ref(false)

const documentForm = ref<DocumentData>({
  id: null,
  name: '',
  title: '',
  description: '',
  file: null,
  file_name: null,
  type: 'other',
  status: 'active',
  is_confidential: false,
  expiry_date: null,
})

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

// Document type options - matching backend validation types
const documentTypeOptions = [
  { title: 'FDD', value: 'fdd' },
  { title: 'Franchise Agreement', value: 'franchise_agreement' },
  { title: 'Financial Study', value: 'financial_study' },
  { title: 'Franchise Kit', value: 'franchise_kit' },
  { title: 'Contract', value: 'contract' },
  { title: 'Agreement', value: 'agreement' },
  { title: 'Manual', value: 'manual' },
  { title: 'Certificate', value: 'certificate' },
  { title: 'Report', value: 'report' },
  { title: 'Other', value: 'other' },
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
        file_name: null,
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
  // Validate form
  const { valid } = await formRef.value.validate()
  if (!valid) return

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
  if (!nameValue) {
    console.error('Document name/title is required')
    return
  }

  // File is required only for new documents (not when editing)
  if (!documentForm.value.id && !documentForm.value.file) {
    console.error('File is required for new documents')
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
      if (!props.franchiseId) {
        console.error('No franchise ID provided')
        return
      }

      const payload: any = {
        name: documentForm.value.name || '',
        description: documentForm.value.description || '',
        type: documentForm.value.type,
        status: documentForm.value.status,
        is_confidential: documentForm.value.is_confidential,
      }

      // Only add expiry_date if it's not null
      if (documentForm.value.expiry_date) {
        payload.expiry_date = documentForm.value.expiry_date
      }

      // Only add file if it exists
      if (documentForm.value.file) {
        payload.file = documentForm.value.file
      }

      let response
      if (documentForm.value.id !== null) {
        // Update existing document
        response = await franchiseApi.updateDocument(props.franchiseId, documentForm.value.id, payload)
      } else {
        // Add new document
        response = await franchiseApi.createDocument(props.franchiseId, payload)
      }

      if (response.success) {
        emit('documentSaved')
        dialogValue.value = false
      } else {
        console.error('Failed to save document:', response.message)
      }
    }
  }
  catch (error: any) {
    console.error('Error saving document:', error)
    setBackendErrors(error)
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
        <VForm ref="formRef">
          <VRow>
            <VCol cols="12">
              <!-- Document Name/Title field - different labels based on mode -->
              <VTextField
                v-if="isUnitMode"
                v-model="documentForm.title"
                label="Document Title"
                placeholder="Enter document title"
                variant="outlined"
                :rules="validationRules.name"
                :error-messages="backendErrors.name"
                @input="clearError('name')"
                required
              />
              <VTextField
                v-else
                v-model="documentForm.name"
                label="Document Name"
                placeholder="Enter document name"
                variant="outlined"
                :rules="validationRules.name"
                :error-messages="backendErrors.name"
                @input="clearError('name')"
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
                :rules="validationRules.description"
                :error-messages="backendErrors.description"
                @input="clearError('description')"
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
                :rules="validationRules.type"
                :error-messages="backendErrors.type"
                @update:model-value="clearError('type')"
                required
              />
            </VCol>
            <VCol cols="12">
              <!-- Show current file info when editing -->
              <VAlert
                v-if="documentForm.id && props.documentData"
                type="info"
                variant="tonal"
                class="mb-4"
              >
                <div class="d-flex align-center">
                  <VIcon icon="tabler-file" class="me-2" />
                  <div>
                    <div class="font-weight-medium">Current File</div>
                    <div class="text-caption">{{ props.documentData.file_name || 'Document file' }}</div>
                  </div>
                </div>
              </VAlert>
              
              <VFileInput
                v-model="documentForm.file"
                :label="documentForm.id ? 'Replace File (Optional)' : 'Attach File'"
                :accept="isUnitMode ? '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png' : '.pdf,.doc,.docx'"
                prepend-icon="tabler-paperclip"
                variant="outlined"
                :rules="documentForm.id ? [] : validationRules.file"
                :error-messages="backendErrors.file"
                @update:model-value="clearError('file')"
                :required="!documentForm.id"
              />
              <div v-if="isUnitMode" class="text-caption text-disabled mt-2">
                Supported formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPG, JPEG, PNG (Max 10MB)
              </div>
              <div v-else-if="documentForm.id" class="text-caption text-disabled mt-2">
                Leave empty to keep the current file, or upload a new file to replace it (Max 10MB)
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
