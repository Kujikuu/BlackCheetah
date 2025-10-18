<script setup lang="ts">
import { requiredValidator } from '@/@core/utils/validators'

interface Props {
  isDrawerOpen: boolean
  request?: any
}

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'requestData', value: any): void
}

const props = withDefaults(defineProps<Props>(), {
  request: undefined,
})

const emit = defineEmits<Emit>()

const isFormValid = ref(false)
const refForm = ref()

const formData = ref({
  id: null as number | null,
  subject: '',
  description: '',
  category: '',
  priority: 'medium',
  status: 'open',
  attachments: [] as any[],
})

const fileInput = ref<HTMLInputElement | null>(null)
const uploadedFiles = ref<any[]>([])

const categories = [
  { title: 'Authentication', value: 'Authentication' },
  { title: 'Payment', value: 'Payment' },
  { title: 'Dashboard', value: 'Dashboard' },
  { title: 'Notifications', value: 'Notifications' },
  { title: 'Reports', value: 'Reports' },
  { title: 'Other', value: 'Other' },
]

const priorities = [
  { title: 'Low', value: 'low' },
  { title: 'Medium', value: 'medium' },
  { title: 'High', value: 'high' },
  { title: 'Critical', value: 'critical' },
]

const statusOptions = [
  { title: 'Open', value: 'open' },
  { title: 'In Progress', value: 'in-progress' },
  { title: 'Resolved', value: 'resolved' },
  { title: 'Closed', value: 'closed' },
]

const resetForm = () => {
  formData.value = {
    id: null,
    subject: '',
    description: '',
    category: '',
    priority: 'medium',
    status: 'open',
    attachments: [],
  }
  uploadedFiles.value = []
  refForm.value?.reset()
}

// Format file size
const formatFileSize = (bytes: number) => {
  if (bytes === 0)
    return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))

  return `${Math.round(bytes / k ** i * 100) / 100} ${sizes[i]}`
}

// Handle file upload
const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  const files = target.files

  if (files) {
    Array.from(files).forEach(file => {
      const fileData = {
        name: file.name,
        size: formatFileSize(file.size),
        url: URL.createObjectURL(file),
        file,
      }

      uploadedFiles.value.push(fileData)
    })
  }

  // Reset input
  if (target)
    target.value = ''
}

// Remove uploaded file
const removeFile = (index: number) => {
  uploadedFiles.value.splice(index, 1)
}

// Get file icon
const getFileIcon = (fileName: string) => {
  // Handle null, undefined, or empty fileName
  if (!fileName || typeof fileName !== 'string')
    return 'tabler-file'

  const ext = fileName.split('.').pop()?.toLowerCase()

  if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(ext || ''))
    return 'tabler-photo'
  if (['pdf'].includes(ext || ''))
    return 'tabler-file-type-pdf'
  if (['doc', 'docx'].includes(ext || ''))
    return 'tabler-file-type-doc'
  if (['txt', 'log'].includes(ext || ''))
    return 'tabler-file-text'

  return 'tabler-file'
}

// Watch for request prop changes
watch(() => props.request, newVal => {
  if (newVal) {
    formData.value = {
      id: newVal.id,
      subject: newVal.subject,
      description: newVal.description,
      category: newVal.category,
      priority: newVal.priority,
      status: newVal.status,
      attachments: newVal.attachments || [],
    }
    uploadedFiles.value = [...(Array.isArray(newVal.attachments) ? newVal.attachments.filter(att => att && (att.name || att.filename)) : [])]
  }
  else {
    resetForm()
  }
}, { immediate: true })

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }: any) => {
    if (valid) {
      emit('requestData', { ...formData.value, attachments: uploadedFiles.value })
      emit('update:isDrawerOpen', false)
      resetForm()
    }
  })
}

const handleDrawerModelValueUpdate = (val: boolean) => {
  emit('update:isDrawerOpen', val)
  if (!val)
    resetForm()
}

// Helper function for avatar text
const avatarText = (name: string) => {
  return name.split(' ').map(word => word.charAt(0)).join('').toUpperCase()
}
</script>

<template>
  <VNavigationDrawer
    temporary
    location="end"
    :model-value="props.isDrawerOpen"
    width="500"
    @update:model-value="handleDrawerModelValueUpdate"
  >
    <!-- Header -->
    <AppDrawerHeaderSection
      title="Edit Technical Request"
      @cancel="handleDrawerModelValueUpdate(false)"
    />

    <VDivider />

    <!-- Requester Information Section -->
    <VCard
      flat
      class="mb-4"
    >
      <VCardText>
        <div class="text-body-2 text-medium-emphasis mb-3">
          Requested By
        </div>
        <div class="d-flex align-center gap-3">
          <VAvatar
            size="40"
            :color="props.request?.userAvatar ? undefined : 'primary'"
            :image="props.request?.userAvatar || undefined"
            variant="tonal"
          >
            <span
              v-if="!props.request?.userAvatar && props.request?.userName"
              class="text-sm font-weight-medium"
            >
              {{ avatarText(props.request.userName) }}
            </span>
          </VAvatar>
          <div>
            <div class="text-body-1 font-weight-medium">
              {{ props.request?.userName || 'Unknown User' }}
            </div>
            <div class="text-body-2 text-medium-emphasis">
              {{ props.request?.userEmail || 'No email available' }}
            </div>
          </div>
        </div>
      </VCardText>
    </VCard>

    <VDivider />

    <VCard flat>
      <VCardText>
        <VForm
          ref="refForm"
          v-model="isFormValid"
          @submit.prevent="onSubmit"
        >
          <VRow>
            <!-- Subject -->
            <VCol cols="12">
              <AppTextField
                v-model="formData.subject"
                label="Subject"
                placeholder="Enter request subject"
                :rules="[requiredValidator]"
              />
            </VCol>

            <!-- Description -->
            <VCol cols="12">
              <AppTextarea
                v-model="formData.description"
                label="Description"
                placeholder="Enter detailed description"
                rows="4"
                :rules="[requiredValidator]"
              />
            </VCol>

            <!-- Category -->
            <VCol cols="12">
              <AppSelect
                v-model="formData.category"
                label="Category"
                :items="categories"
                :rules="[requiredValidator]"
              />
            </VCol>

            <!-- Priority -->
            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="formData.priority"
                label="Priority"
                :items="priorities"
                :rules="[requiredValidator]"
              />
            </VCol>

            <!-- Status -->
            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="formData.status"
                label="Status"
                :items="statusOptions"
                :rules="[requiredValidator]"
              />
            </VCol>

            <!-- Attachments -->
            <VCol cols="12">
              <div class="text-body-2 text-medium-emphasis mb-2">
                Attachments
              </div>

              <input
                ref="fileInput"
                type="file"
                multiple
                hidden
                @change="handleFileUpload"
              >

              <VBtn
                variant="outlined"
                color="primary"
                prepend-icon="tabler-paperclip"
                @click="fileInput?.click()"
              >
                Add Attachment
              </VBtn>

              <!-- Uploaded Files List -->
              <VList
                v-if="uploadedFiles.length > 0"
                lines="two"
                density="compact"
                class="mt-3"
              >
                <VListItem
                  v-for="(file, index) in uploadedFiles"
                  :key="index"
                >
                  <template #prepend>
                    <VAvatar
                      color="primary"
                      variant="tonal"
                      size="40"
                    >
                      <VIcon :icon="getFileIcon(file.name || file.filename)" />
                    </VAvatar>
                  </template>

                  <VListItemTitle class="font-weight-medium">
                    {{ file.name || file.filename || 'Unknown File' }}
                  </VListItemTitle>
                  <VListItemSubtitle>
                    {{ file.size || 'Unknown Size' }}
                  </VListItemSubtitle>

                  <template #append>
                    <VBtn
                      icon
                      variant="text"
                      size="small"
                      color="error"
                      @click="removeFile(index)"
                    >
                      <VIcon icon="tabler-x" />
                      <VTooltip
                        activator="parent"
                        location="top"
                      >
                        Remove
                      </VTooltip>
                    </VBtn>
                  </template>
                </VListItem>
              </VList>
            </VCol>

            <!-- Submit and Cancel -->
            <VCol cols="12">
              <VBtn
                type="submit"
                class="me-3"
              >
                Update Request
              </VBtn>
              <VBtn
                variant="outlined"
                color="secondary"
                @click="handleDrawerModelValueUpdate(false)"
              >
                Cancel
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </VNavigationDrawer>
</template>
