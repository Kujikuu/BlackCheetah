<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { technicalRequestApi } from '@/services/api/technical-request'

// Props
interface Props {
  visible: boolean
  request: any | null
  isAdmin?: boolean
}

// Emits
interface Emits {
  (e: 'update:visible', value: boolean): void
  (e: 'success'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Form data
const editRequestForm = ref({
  title: '',
  description: '',
  category: '',
  priority: 'medium',
  status: 'open',
})

// Loading state
const isLoading = ref(false)

// Form validation
const isFormValid = ref(false)

// Form rules
const titleRules = [
  (v: string) => !!v || 'Title is required',
  (v: string) => (v && v.length >= 5) || 'Title must be at least 5 characters',
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
  return props.isAdmin 
    && editRequestForm.value.title.length >= 5
    && editRequestForm.value.category !== ''
    && editRequestForm.value.description.length >= 10
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

// Status options (must match backend enum)
const statusOptions = [
  { title: 'Open', value: 'open' },
  { title: 'In Progress', value: 'in_progress' },
  { title: 'Pending Info', value: 'pending_info' },
  { title: 'Resolved', value: 'resolved' },
  { title: 'Closed', value: 'closed' },
  { title: 'Cancelled', value: 'cancelled' },
]

// Watch for request changes to populate form
watch(() => props.request, (newRequest) => {
  if (newRequest) {
    editRequestForm.value = {
      title: newRequest.subject || '',
      description: newRequest.description || '',
      category: newRequest.category || '',
      priority: newRequest.priority || 'medium',
      status: newRequest.status || 'open',
    }
  }
}, { immediate: true })

// Close dialog
const closeDialog = () => {
  emit('update:visible', false)
}

// Update request function
const updateRequest = async () => {
  if (!canSubmit.value || !props.request || !props.isAdmin) return

  try {
    isLoading.value = true

    await technicalRequestApi.updateTechnicalRequest(props.request.id, {
      title: editRequestForm.value.title,
      description: editRequestForm.value.description,
      category: editRequestForm.value.category,
      priority: editRequestForm.value.priority,
      status: editRequestForm.value.status,
    })

    // Emit success event
    emit('success')
    emit('update:visible', false)
  }
  catch (error: any) {
    console.error('Error updating technical request:', error)
    // Emit error - parent should handle showing snackbar
  }
  finally {
    isLoading.value = false
  }
}
</script>

<template>
  <VDialog :model-value="visible" max-width="600" persistent @update:model-value="emit('update:visible', $event)">
    <VCard>
      <VCardItem>
        <VCardTitle>Edit Technical Request</VCardTitle>
        <template #append>
          <IconBtn @click="closeDialog">
            <VIcon icon="tabler-x" />
          </IconBtn>
        </template>
      </VCardItem>

      <VDivider />

      <VForm v-model="isFormValid" @submit.prevent="updateRequest">
        <VCardText>
          <VRow>
            <!-- Request ID (Read-only) -->
            <VCol cols="12" v-if="request">
              <div class="text-body-2 text-medium-emphasis mb-1">
                Request ID
              </div>
              <div class="text-body-1 font-weight-medium text-primary">
                {{ request.requestId }}
              </div>
            </VCol>

            <!-- Title -->
            <VCol cols="12">
              <AppTextField 
                v-model="editRequestForm.title" 
                label="Title" 
                placeholder="Enter request title"
                :rules="titleRules" 
                required 
              />
            </VCol>

            <!-- Category and Priority -->
            <VCol cols="12" sm="6">
              <AppSelect 
                v-model="editRequestForm.category" 
                label="Category" 
                placeholder="Select category"
                :items="categoryOptions" 
                :rules="categoryRules" 
                required 
              />
            </VCol>

            <VCol cols="12" sm="6">
              <AppSelect 
                v-model="editRequestForm.priority" 
                label="Priority" 
                :items="priorityOptions" 
                required 
              />
            </VCol>

            <!-- Status (Admin only can edit) -->
            <VCol cols="12" sm="6">
              <AppSelect 
                v-model="editRequestForm.status" 
                label="Status" 
                :items="statusOptions" 
                required 
              />
            </VCol>

            <!-- Description -->
            <VCol cols="12">
              <AppTextarea 
                v-model="editRequestForm.description" 
                label="Description"
                placeholder="Describe your technical issue in detail..." 
                rows="4" 
                :rules="descriptionRules"
                required 
              />
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
            Update Request
          </VBtn>
        </VCardActions>
      </VForm>
    </VCard>
  </VDialog>
</template>
