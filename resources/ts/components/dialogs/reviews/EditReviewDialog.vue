<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import type { UnitReview } from '@/services/api'
import { useFormValidation } from '@/composables/useFormValidation'
import { useUpdateReviewValidation } from '@/validation/reviewValidation'

interface Props {
  isDialogVisible: boolean
  selectedReview: UnitReview | null
}

interface Emits {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'reviewUpdated', review: UnitReview): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const formRef = ref()
const { backendErrors, setBackendErrors, clearError } = useFormValidation()
const validationRules = useUpdateReviewValidation()

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const editableReview = ref<UnitReview | null>(null)

// Watch for changes in selectedReview and create a copy for editing
watch(() => props.selectedReview, (newReview) => {
  if (newReview) {
    editableReview.value = { ...newReview }
  }
}, { immediate: true })

const handleSaveReview = async () => {
  // Validate form
  const { valid } = await formRef.value.validate()
  if (!valid) return

  try {
    if (editableReview.value) {
      emit('reviewUpdated', editableReview.value)
      dialogValue.value = false
    }
  }
  catch (error: any) {
    setBackendErrors(error)
  }
}

const handleClose = () => {
  dialogValue.value = false
}
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="600"
  >
    <DialogCloseBtn @click="handleClose" />
    <VCard title="Edit Customer Review">
      <VCardText
        v-if="editableReview"
        class="pa-6"
      >
        <VForm ref="formRef">
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="editableReview.customerName"
                label="Customer Name"
                :rules="validationRules.customerName"
                :error-messages="backendErrors.customerName"
                @input="clearError('customerName')"
                required
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="editableReview.customerEmail"
                label="Customer Email"
                type="email"
                :rules="validationRules.customerEmail"
                :error-messages="backendErrors.customerEmail"
                @input="clearError('customerEmail')"
              />
            </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-2">
              Rating
            </div>
            <VRating
              v-model="editableReview.rating"
              size="large"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="editableReview.date"
              label="Date"
              type="date"
              :rules="validationRules.date"
              :error-messages="backendErrors.date"
              @input="clearError('date')"
              required
            />
          </VCol>
          <VCol cols="12">
            <VTextarea
              v-model="editableReview.comment"
              label="Review Comment"
              rows="4"
              :rules="validationRules.comment"
              :error-messages="backendErrors.comment"
              @input="clearError('comment')"
              required
            />
          </VCol>
        </VRow>
        </VForm>
      </VCardText>

      <VDivider />

      <VCardActions class="pa-6">
        <VSpacer />
        <VBtn
          color="secondary"
          variant="tonal"
          @click="handleClose"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          :disabled="!editableReview?.customerName || !editableReview?.comment || !editableReview?.date"
          @click="handleSaveReview"
        >
          Save Changes
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
