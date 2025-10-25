<script setup lang="ts">
import { franchiseApi } from '@/services/api'
import { useFormValidation } from '@/composables/useFormValidation'
import { useValidationRules } from '@/composables/useValidationRules'

interface Props {
  isDialogVisible: boolean
  unitId: number | null
  currentStatus: string
  statusOptions: Array<{ title: string; value: string }>
}

interface Emits {
  'update:isDialogVisible': [value: boolean]
  'status-updated': [data: { unitId: number; newStatus: string }]
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const formRef = ref()
const { backendErrors, setBackendErrors, clearError } = useFormValidation()
const rules = useValidationRules()

const newStatus = ref<string>(props.currentStatus)
const isLoading = ref(false)

// Watch for current status changes
watch(() => props.currentStatus, (newValue) => {
  newStatus.value = newValue
}, { immediate: true })

const changeUnitStatus = async () => {
  if (!props.unitId)
    return

  // Validate form
  const { valid } = await formRef.value.validate()
  if (!valid) return

  if (newStatus.value === props.currentStatus)
    return

  try {
    isLoading.value = true
    
    const response = await franchiseApi.updateUnitStatus(props.unitId, newStatus.value)

    if (response.success) {
      emit('status-updated', { 
        unitId: props.unitId, 
        newStatus: newStatus.value 
      })
      emit('update:isDialogVisible', false)
    }
  } catch (err: any) {
    console.error('Failed to change unit status:', err)
    setBackendErrors(err)
  } finally {
    isLoading.value = false
  }
}

const handleCancel = () => {
  newStatus.value = props.currentStatus // Reset to original
  emit('update:isDialogVisible', false)
}
</script>

<template>
  <VDialog
    :model-value="isDialogVisible"
    max-width="600"
    @update:model-value="emit('update:isDialogVisible', $event)"
  >
    <DialogCloseBtn @click="handleCancel" />
    <VCard title="Change Unit Status">
      <VCardText>
        <VForm ref="formRef">
          <VSelect
            v-model="newStatus"
            label="Status"
            :items="statusOptions"
            placeholder="Select Status"
            :rules="[rules.required('Status is required')]"
            :error-messages="backendErrors.status"
            @update:model-value="clearError('status')"
          />
        </VForm>
      </VCardText>

      <VCardActions>
        <VSpacer />
        <VBtn
          color="secondary"
          variant="tonal"
          @click="handleCancel"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          :disabled="!newStatus || newStatus === currentStatus"
          :loading="isLoading"
          @click="changeUnitStatus"
        >
          Update Status
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
