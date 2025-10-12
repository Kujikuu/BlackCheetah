<script setup lang="ts">
interface Props {
  isDialogVisible: boolean
  document: any
  action: 'approve' | 'reject'
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'documentActionConfirmed', data: { document: any; action: string; comment: string }): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

// 👉 Form data
const comment = ref('')

// 👉 Form validation
const isFormValid = ref(false)

// 👉 Form rules
const requiredRule = (value: string) => !!value || 'Comment is required'

// 👉 Computed
const actionTitle = computed(() => {
  return props.action === 'approve' ? 'Approve Document' : 'Reject Document'
})

const actionColor = computed(() => {
  return props.action === 'approve' ? 'success' : 'error'
})

const actionIcon = computed(() => {
  return props.action === 'approve' ? 'tabler-check' : 'tabler-x'
})

const actionDescription = computed(() => {
  return props.action === 'approve'
    ? 'Are you sure you want to approve this document? Please provide a comment.'
    : 'Are you sure you want to reject this document? Please provide a reason for rejection.'
})

// 👉 Methods
const updateModelValue = (val: boolean) => {
  emit('update:isDialogVisible', val)
}

const resetForm = () => {
  comment.value = ''
}

const onSubmit = () => {
  if (isFormValid.value) {
    emit('documentActionConfirmed', {
      document: props.document,
      action: props.action,
      comment: comment.value,
    })
    resetForm()
    updateModelValue(false)
  }
}

const onCancel = () => {
  resetForm()
  updateModelValue(false)
}

// 👉 Watch for dialog visibility changes
watch(() => props.isDialogVisible, newVal => {
  if (!newVal)
    resetForm()
})
</script>

<template>
  <VDialog
    :model-value="props.isDialogVisible"
    max-width="500"
    persistent
    @update:model-value="updateModelValue"
  >
    <VCard>
      <VCardTitle class="text-h5 pa-6 pb-4">
        {{ actionTitle }}
      </VCardTitle>

      <VDivider />
      <VCardText class="text-center px-10 py-6">
        <!-- Icon -->
        <VIcon
          :icon="actionIcon"
          :color="actionColor"
          size="48"
          class="mb-4"
        />

        <!-- Description -->
        <p class="text-body-1 text-medium-emphasis mb-6">
          {{ actionDescription }}
        </p>

        <!-- Document Info -->
        <VCard
          variant="outlined"
          class="mb-6"
        >
          <VCardText class="text-start">
            <div class="d-flex align-center mb-2">
              <VIcon
                icon="tabler-file-text"
                size="20"
                color="primary"
                class="me-2"
              />
              <span class="text-body-1 font-weight-medium">{{ props.document?.title }}</span>
            </div>
            <div class="text-body-2 text-disabled mb-1">
              {{ props.document?.description }}
            </div>
            <div class="text-body-2 text-disabled">
              {{ props.document?.fileName }} • {{ props.document?.fileSize }}
            </div>
          </VCardText>
        </VCard>

        <!-- Comment Form -->
        <VForm
          v-model="isFormValid"
          @submit.prevent="onSubmit"
        >
          <VTextarea
            v-model="comment"
            :label="props.action === 'approve' ? 'Approval Comment' : 'Rejection Reason'"
            :placeholder="props.action === 'approve' ? 'Enter approval comment...' : 'Enter reason for rejection...'"
            rows="3"
            :rules="[requiredRule]"
            required
            class="mb-4"
          />
        </VForm>
      </VCardText>

      <VDivider />

      <VCardActions class="justify-center pa-6">
        <VBtn
          color="error"
          variant="text"
          @click="onCancel"
        >
          Cancel
        </VBtn>
        <VBtn
          :color="actionColor"
          :disabled="!isFormValid"
          @click="onSubmit"
        >
          {{ props.action === 'approve' ? 'Approve' : 'Reject' }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
