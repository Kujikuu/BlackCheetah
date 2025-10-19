<script setup lang="ts">
import { ref, computed } from 'vue'

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

const handleAddReview = () => {
  // Validate form
  if (!reviewForm.value.customerName || !reviewForm.value.comment || !reviewForm.value.date) {
    return
  }

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
        <VRow>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="reviewForm.customerName"
              label="Customer Name"
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
              required
            />
          </VCol>
          <VCol cols="12">
            <VTextarea
              v-model="reviewForm.comment"
              label="Review Comment"
              rows="4"
              required
            />
          </VCol>
        </VRow>
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
