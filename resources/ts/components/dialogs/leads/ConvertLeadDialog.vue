<script setup lang="ts">
import { leadApi } from '@/services/api'
import { useFormValidation } from '@/composables/useFormValidation'
import { useValidationRules } from '@/composables/useValidationRules'

interface Props {
  isDialogVisible: boolean
  leadId: number | null
  leadName?: string
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'leadConverted'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const formRef = ref()
const { backendErrors, setBackendErrors, clearError } = useFormValidation()
const rules = useValidationRules()

const conversionNotes = ref('')
const isLoading = ref(false)

const updateModelValue = (val: boolean) => {
  emit('update:isDialogVisible', val)
  if (!val)
    conversionNotes.value = ''
}

const convertLead = async () => {
  if (!props.leadId)
    return

  // Validate form
  const { valid } = await formRef.value.validate()
  if (!valid) return

  isLoading.value = true

  try {
    await leadApi.convertLead(props.leadId, conversionNotes.value.trim())

    emit('leadConverted')
    updateModelValue(false)
  }
  catch (error: any) {
    console.error('Error converting lead:', error)
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
    <DialogCloseBtn @click="updateModelValue(false)" />
    <VCard title="Convert Lead to Customer">
      <VCardText>
        <div class="mb-4">
          <p class="text-body-1 mb-2">
            Are you sure you want to convert
            <strong>{{ props.leadName || 'this lead' }}</strong>
            to a customer?
          </p>
          <p class="text-body-2 text-medium-emphasis">
            This action will change the lead status to "Converted" and cannot be undone.
          </p>
        </div>

        <VForm ref="formRef">
          <VTextarea
            v-model="conversionNotes"
            label="Conversion Notes (Optional)"
            placeholder="Add any notes about the conversion..."
            rows="3"
            variant="outlined"
            :disabled="isLoading"
            :rules="[rules.string()]"
            :error-messages="backendErrors.notes"
            @input="clearError('notes')"
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
          color="success"
          variant="elevated"
          :loading="isLoading"
          @click="convertLead"
        >
          Convert to Customer
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>
