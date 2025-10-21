<script setup lang="ts">
import { franchiseApi } from '@/services/api'

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

const newStatus = ref<string>(props.currentStatus)
const isLoading = ref(false)

// Watch for current status changes
watch(() => props.currentStatus, (newValue) => {
  newStatus.value = newValue
}, { immediate: true })

const changeUnitStatus = async () => {
  if (!props.unitId || !newStatus.value || newStatus.value === props.currentStatus)
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
        <VSelect
          v-model="newStatus"
          label="Status"
          :items="statusOptions"
          placeholder="Select Status"
        />
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
