<script setup lang="ts">
import { computed } from 'vue'

interface Review {
  id: number
  customerName: string
  customerEmail?: string
  rating: number
  date: string
  sentiment: 'positive' | 'neutral' | 'negative'
  comment: string
}

interface Props {
  isDialogVisible: boolean
  selectedReview: Review | null
}

interface Emits {
  (e: 'update:isDialogVisible', value: boolean): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

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
    <VCard title="Review Details">
      <VCardText
        v-if="selectedReview"
        class="pa-6"
      >
        <VRow>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Customer Name
            </div>
            <div class="text-body-1 font-weight-medium">
              {{ selectedReview.customerName }}
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Customer Email
            </div>
            <div class="text-body-1 font-weight-medium">
              {{ selectedReview.customerEmail || 'N/A' }}
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Rating
            </div>
            <div class="d-flex align-center gap-1">
              <VRating
                :model-value="selectedReview.rating"
                readonly
                size="small"
                density="compact"
              />
              <span class="text-body-1 font-weight-medium">{{ selectedReview.rating }}/5</span>
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Date
            </div>
            <div class="text-body-1 font-weight-medium">
              {{ selectedReview.date }}
            </div>
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <div class="text-body-2 text-disabled mb-1">
              Sentiment
            </div>
            <VChip
              :color="selectedReview.sentiment === 'positive' ? 'success' : selectedReview.sentiment === 'neutral' ? 'warning' : 'error'"
              size="small"
              label
              class="text-capitalize"
            >
              {{ selectedReview.sentiment }}
            </VChip>
          </VCol>
          <VCol cols="12">
            <div class="text-body-2 text-disabled mb-1">
              Comment
            </div>
            <div class="text-body-1">
              {{ selectedReview.comment }}
            </div>
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
          Close
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
