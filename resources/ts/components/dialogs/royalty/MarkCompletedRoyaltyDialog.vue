<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { type PaymentData, type RoyaltyRecord, royaltyApi } from '@/services/api'
import { useFormValidation } from '@/composables/useFormValidation'
import { useValidationRules } from '@/composables/useValidationRules'

interface Props {
  isDialogVisible: boolean
  selectedRoyalty: RoyaltyRecord | null
  paymentTypeOptions: Array<{ title: string; value: string }>
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'paymentSubmitted'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const formRef = ref()
const { backendErrors, setBackendErrors, clearError } = useFormValidation()
const rules = useValidationRules()

const isLoading = ref(false)

const paymentData = ref<PaymentData>({
  amount_paid: 0,
  payment_date: '',
  payment_type: '',
  attachment: null,
})

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

// Watch for changes in selectedRoyalty to update payment data
watch(() => props.selectedRoyalty, (royalty) => {
  if (royalty) {
    paymentData.value = {
      amount_paid: royalty.amount,
      payment_date: new Date().toISOString().split('T')[0],
      payment_type: '',
      attachment: null,
    }
  }
}, { immediate: true })

const submitPayment = async () => {
  if (!props.selectedRoyalty) return

  // Validate form
  const { valid } = await formRef.value.validate()
  if (!valid) return

  isLoading.value = true
  try {
    await royaltyApi.markAsPaid(props.selectedRoyalty.id, paymentData.value)
    emit('paymentSubmitted')
    dialogValue.value = false
    
    // Reset form
    paymentData.value = {
      amount_paid: 0,
      payment_date: '',
      payment_type: '',
      attachment: null,
    }
  }
  catch (error: any) {
    console.error('Error submitting payment:', error)
    setBackendErrors(error)
  }
  finally {
    isLoading.value = false
  }
}

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    paymentData.value.attachment = target.files[0]
  }
}
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="600"
  >
    <DialogCloseBtn @click="dialogValue = false" />
    <VCard title="Mark Royalty as Completed">
      <VCardText>
        <VForm ref="formRef" @submit.prevent="submitPayment">
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model.number="paymentData.amount_paid"
                label="Amount Paid (SAR)"
                type="number"
                variant="outlined"
                density="compact"
                :rules="[rules.required('Amount is required'), rules.numeric('Amount must be numeric'), rules.min(0, 'Amount must be positive')]"
                :error-messages="backendErrors.amountPaid"
                @input="clearError('amountPaid')"
              />
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="paymentData.payment_date"
                label="Payment Date"
                type="date"
                variant="outlined"
                density="compact"
                :rules="[rules.required('Payment date is required'), rules.date('Invalid date format')]"
                :error-messages="backendErrors.paymentDate"
                @input="clearError('paymentDate')"
              />
            </VCol>
            <VCol cols="12">
              <VSelect
                v-model="paymentData.payment_type"
                :items="props.paymentTypeOptions"
                item-title="title"
                item-value="value"
                label="Payment Type"
                variant="outlined"
                density="compact"
                :rules="[rules.required('Payment type is required')]"
                :error-messages="backendErrors.paymentType"
                @update:model-value="clearError('paymentType')"
              />
            </VCol>
            <VCol cols="12">
              <VFileInput
                label="Attachment (Optional)"
                variant="outlined"
                density="compact"
                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                :rules="[rules.fileType(['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'], 'Invalid file type'), rules.fileSize(5, 'File must be less than 5MB')]"
                :error-messages="backendErrors.attachment"
                @change="handleFileUpload"
                @update:model-value="clearError('attachment')"
              />
              <div class="text-caption text-medium-emphasis mt-1">
                Supported formats: PDF, JPG, PNG, DOC, DOCX
              </div>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>

      <VCardActions>
        <VSpacer />
        <VBtn
          color="secondary"
          variant="tonal"
          @click="dialogValue = false"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          :loading="isLoading"
          @click="submitPayment"
        >
          Mark as Completed
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
