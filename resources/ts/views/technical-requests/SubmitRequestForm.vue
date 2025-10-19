<script setup lang="ts">
import { computed, ref } from 'vue'
import { technicalRequestApi } from '@/services/api/technical-request'

// Props
interface Props {
  visible: boolean
}

// Emits
interface Emits {
  (e: 'update:visible', value: boolean): void
  (e: 'success'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Form data
const submitRequestForm = ref({
  subject: '',
  category: '',
  priority: 'medium',
  description: '',
  attachments: [] as File[],
})

// Loading state
const isLoading = ref(false)

// Form validation
const isFormValid = ref(false)

// Form rules
const subjectRules = [
  (v: string) => !!v || 'Subject is required',
  (v: string) => (v && v.length >= 5) || 'Subject must be at least 5 characters',
]

const categoryRules = [
  (v: string) => !!v || 'Category is required',
]

const descriptionRules = [
  (v: string) => !!v || 'Description is required',
  (v: string) => (v && v.length >= 10) || 'Description must be at least 10 characters',
]

// Computed property to check if form can be submitted
const canSubmit = computed(() => {
  return submitRequestForm.value.subject.length >= 5
    && submitRequestForm.value.category !== ''
    && submitRequestForm.value.description.length >= 10
})

// Category options (matching database enum)
const categoryOptions = [
  { title: 'Hardware', value: 'hardware' },
  { title: 'Software', value: 'software' },
  { title: 'Network', value: 'network' },
  { title: 'POS System', value: 'pos_system' },
  { title: 'Website', value: 'website' },
  { title: 'Mobile App', value: 'mobile_app' },
  { title: 'Training', value: 'training' },
  { title: 'Other', value: 'other' },
]

// Priority options (must match backend enum)
const priorityOptions = [
  { title: 'Low', value: 'low' },
  { title: 'Medium', value: 'medium' },
  { title: 'High', value: 'high' },
  { title: 'Urgent', value: 'urgent' },
]

// Get file icon based on extension
const getFileIcon = (fileName: string) => {
  const ext = fileName.split('.').pop()?.toLowerCase()

  if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(ext || ''))
    return 'tabler-photo'
  else if (['pdf'].includes(ext || ''))
    return 'tabler-file-type-pdf'
  if (['doc', 'docx'].includes(ext || ''))
    return 'tabler-file-type-doc'
  if (['xls', 'xlsx'].includes(ext || ''))
    return 'tabler-file-type-xls'
  if (['txt', 'log'].includes(ext || ''))
    return 'tabler-file-text'
  if (['zip', 'rar', '7z'].includes(ext || ''))
    return 'tabler-file-zip'

  return 'tabler-file'
}

// Reset submit form
const resetSubmitForm = () => {
  submitRequestForm.value = {
    subject: '',
    category: '',
    priority: 'medium',
    description: '',
    attachments: [],
  }
  isFormValid.value = false
}

// Handle file upload
const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files)
    submitRequestForm.value.attachments = Array.from(target.files)
}

// Remove attachment
const removeAttachment = (index: number) => {
  submitRequestForm.value.attachments.splice(index, 1)
}

// Close dialog
const closeDialog = () => {
  emit('update:visible', false)
  resetSubmitForm()
}

// Submit request function
const submitRequest = async () => {
  // Validate using canSubmit computed property
  if (!canSubmit.value)
    return

  try {
    isLoading.value = true

    // Get current user ID from cookie
    const userData = useCookie<any>('userData')
    const currentUserId = userData.value?.id

    if (!currentUserId) {
      isLoading.value = false
      // Emit error - parent should handle showing snackbar
      return
    }

    // Create request via API
    await technicalRequestApi.createTechnicalRequest({
      title: submitRequestForm.value.subject,
      description: submitRequestForm.value.description,
      category: submitRequestForm.value.category as any,
      priority: submitRequestForm.value.priority as any,
      requester_id: currentUserId,
      attachments: submitRequestForm.value.attachments.length > 0 ? submitRequestForm.value.attachments : undefined,
    })

    // Emit success event
    emit('success')

    // Reset form and close modal
    resetSubmitForm()
    emit('update:visible', false)
  }
  catch (error: any) {
    console.error('Error submitting technical request:', error)
    // Emit error - parent should handle showing snackbar
  }
  finally {
    isLoading.value = false
  }
}
</script>

<template>
  <VDialog :model-value="visible" max-width="600" persistent @update:model-value="emit('update:visible', $event)">
    <DialogCloseBtn @click="closeDialog" />
    <VCard title="Submit Technical Request">
      <VForm v-model="isFormValid" @submit.prevent="submitRequest">
        <VCardText>
          <VRow>
            <!-- Subject -->
            <VCol cols="12">
              <AppTextField 
                v-model="submitRequestForm.subject" 
                label="Subject" 
                placeholder="Enter request subject"
                :rules="subjectRules" 
                required 
              />
            </VCol>

            <!-- Category -->
            <VCol cols="12" sm="6">
              <AppSelect 
                v-model="submitRequestForm.category" 
                label="Category" 
                placeholder="Select category"
                :items="categoryOptions" 
                :rules="categoryRules" 
                required 
              />
            </VCol>

            <!-- Priority -->
            <VCol cols="12" sm="6">
              <AppSelect 
                v-model="submitRequestForm.priority" 
                label="Priority" 
                :items="priorityOptions" 
                required 
              />
            </VCol>

            <!-- Description -->
            <VCol cols="12">
              <AppTextarea 
                v-model="submitRequestForm.description" 
                label="Description"
                placeholder="Describe your technical issue in detail..." 
                rows="4" 
                :rules="descriptionRules"
                required 
              />
            </VCol>

            <!-- File Upload -->
            <VCol cols="12">
              <div class="text-body-2 text-medium-emphasis mb-2">
                Attachments (Optional)
              </div>
              <VFileInput 
                label="Choose files" 
                multiple 
                chips 
                show-size
                accept="image/*,.pdf,.doc,.docx,.txt,.log,.zip,.rar" 
                @change="handleFileUpload" 
              />

              <!-- Display selected files -->
              <div v-if="submitRequestForm.attachments.length > 0" class="mt-3">
                <VChip 
                  v-for="(file, index) in submitRequestForm.attachments" 
                  :key="index" 
                  closable 
                  class="me-2 mb-2"
                  @click:close="removeAttachment(index)"
                >
                  <VIcon :icon="getFileIcon(file.name)" class="me-1" />
                  {{ file.name }}
                </VChip>
              </div>
            </VCol>
          </VRow>
        </VCardText>

        <VDivider />

        <VCardActions class="pa-4">
          <VSpacer />
          <VBtn variant="outlined" @click="closeDialog">
            Cancel
          </VBtn>
          <VBtn type="submit" color="primary" :disabled="!canSubmit || isLoading" :loading="isLoading">
            Submit Request
          </VBtn>
        </VCardActions>
      </VForm>
    </VCard>
  </VDialog>
</template>
