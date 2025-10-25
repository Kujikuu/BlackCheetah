<script setup lang="ts">
import { leadApi } from '@/services/api'
import { useFormValidation } from '@/composables/useFormValidation'
import { useMarkLeadAsLostValidation } from '@/validation/leadsValidation'

interface Props {
  isDialogVisible: boolean
  leadId: number | null
  leadName?: string
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'leadMarkedAsLost'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const formRef = ref()
const { backendErrors, setBackendErrors, clearError } = useFormValidation()
const validationRules = useMarkLeadAsLostValidation()

const reason = ref('')
const isLoading = ref(false)

const lostReasons = [
  'Not interested',
  'Budget constraints',
  'Chose competitor',
  'Timing not right',
  'No response',
  'Other',
]

const updateModelValue = (val: boolean) => {
  emit('update:isDialogVisible', val)
  if (!val)
    reason.value = ''
}

const markAsLost = async () => {
  if (!props.leadId)
    return

  // Validate form
  const { valid } = await formRef.value.validate()
  if (!valid) return

  isLoading.value = true

  try {
    await leadApi.markAsLost(props.leadId, reason.value)

    emit('leadMarkedAsLost')
    updateModelValue(false)
  }
  catch (error: any) {
    console.error('Error marking lead as lost:', error)
    setBackendErrors(error)
  }
  finally {
    isLoading.value = false
  }
}
</script>

<template>
  <VDialog
    max-width="500"
    :model-value="props.isDialogVisible"
    @update:model-value="updateModelValue"
  >
    <VCard>
      <VCardItem>
        <VCardTitle>Mark Lead as Lost</VCardTitle>
      </VCardItem>

      <VCardText>
        <div class="mb-4">
          <p class="text-body-1 mb-2">
            Are you sure you want to mark
            <strong>{{ props.leadName || 'this lead' }}</strong>
            as lost?
          </p>
          <p class="text-body-2 text-medium-emphasis">
            This action will change the lead status to "Lost" and cannot be undone.
          </p>
        </div>

        <VForm ref="formRef">
          <VSelect
            v-model="reason"
            :items="lostReasons"
            label="Reason for Loss"
            placeholder="Select a reason"
            variant="outlined"
            :disabled="isLoading"
            :rules="validationRules.lostReason"
            :error-messages="backendErrors.lostReason"
            @update:model-value="clearError('lostReason')"
            required
          />
        </VForm>
      </VCardText>

      <VCardText class="d-flex align-center justify-end gap-2">
        <VBtn
          color="secondary"
          variant="tonal"
          :disabled="isLoading"
          @click="updateModelValue(false)"
        >
          Cancel
        </VBtn>

        <VBtn
          color="error"
          variant="elevated"
          :loading="isLoading"
          :disabled="!reason"
          @click="markAsLost"
        >
          Mark as Lost
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>
