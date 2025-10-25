<script setup lang="ts">
import { ref, computed } from 'vue'
import { useFormValidation } from '@/composables/useFormValidation'
import { useStoreReviewValidation } from '@/validation/reviewValidation'

interface ReviewForm {
  customerName: string
  customerEmail: string
  rating: number
  comment: string
  date: string
}

interface Props {
  isDialogVisible: boolean
}

interface Emits {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'reviewAdded', review: ReviewForm): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const formRef = ref()
const { backendErrors, setBackendErrors, clearError } = useFormValidation()
const validationRules = useStoreReviewValidation()

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const reviewForm = ref<ReviewForm>({
  customerName: '',
  customerEmail: '',
  rating: 0,
  comment: '',
  date: '',
})

const handleAddReview = async () => {
  // Validate form
  const { valid } = await formRef.value.validate()
  if (!valid) return

  try {
    emit('reviewAdded', { ...reviewForm.value })
    
    // Reset form
    reviewForm.value = {
      customerName: '',
      customerEmail: '',
      rating: 0,
      comment: '',
      date: '',
    }
    
    dialogValue.value = false
  }
  catch (error: any) {
    setBackendErrors(error)
  }
}

const handleClose = () => {
  dialogValue.value = false
  // Reset form on close
  reviewForm.value = {
    customerName: '',
    customerEmail: '',
    rating: 0,
    comment: '',
    date: '',
  }
}
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="600"
  >
    <DialogCloseBtn @click="handleClose" />
    <VCard title="Add Customer Review">
      <VCardText class="pa-6">
        <VForm ref="formRef">
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="reviewForm.customerName"
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
                v-model="reviewForm.customerEmail"
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
              v-model="reviewForm.rating"
              size="large"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="reviewForm.date"
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
              v-model="reviewForm.comment"
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
          :disabled="!reviewForm.customerName || !reviewForm.comment || !reviewForm.date"
          @click="handleAddReview"
        >
          Add Review
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
