<script setup lang="ts">
import { royaltyApi } from '@/services/api'

interface Props {
  isDialogVisible: boolean
  selectedPeriod: string
}

interface Emits {
  'update:isDialogVisible': [value: boolean]
  'export-completed': []
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const exportFormat = ref('csv')
const exportDataType = ref('all')
const isLoading = ref(false)

const exportFormatOptions = [
  { title: 'CSV', value: 'csv' },
  { title: 'Excel', value: 'excel' },
]

const exportDataTypeOptions = [
  { title: 'All Royalties', value: 'all' },
  { title: 'Paid Only', value: 'paid' },
  { title: 'Pending Only', value: 'pending' },
  { title: 'Overdue Only', value: 'overdue' },
]

const performExport = async () => {
  try {
    isLoading.value = true
    
    const blob = await royaltyApi.exportRoyalties({
      format: exportFormat.value as 'csv' | 'excel',
      data_type: exportDataType.value as 'all' | 'paid' | 'pending' | 'overdue',
      period: props.selectedPeriod as 'daily' | 'monthly' | 'yearly',
    })

    // Create download link
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `royalties_${props.selectedPeriod}_${new Date().toISOString().split('T')[0]}.${exportFormat.value}`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)

    emit('export-completed')
    emit('update:isDialogVisible', false)
  } catch (error) {
    console.error('Export failed:', error)
  } finally {
    isLoading.value = false
  }
}

const handleCancel = () => {
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
    <VCard title="Export Royalty Data">
      <VCardText>
        <VRow>
          <VCol cols="12">
            <VSelect
              v-model="exportDataType"
              :items="exportDataTypeOptions"
              item-title="title"
              item-value="value"
              label="Data Type"
              variant="outlined"
              density="compact"
            />
          </VCol>
          <VCol cols="12">
            <VSelect
              v-model="exportFormat"
              :items="exportFormatOptions"
              item-title="title"
              item-value="value"
              label="Export Format"
              variant="outlined"
              density="compact"
            />
          </VCol>
        </VRow>

        <VAlert
          type="info"
          variant="tonal"
          class="mt-4"
        >
          <div class="text-body-2">
            <strong>Current Selection:</strong><br>
            Period: {{ selectedPeriod.charAt(0).toUpperCase() + selectedPeriod.slice(1) }}<br>
            Data Type: {{ exportDataTypeOptions.find(opt => opt.value === exportDataType)?.title }}<br>
            Format: {{ exportFormat.toUpperCase() }}
          </div>
        </VAlert>
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
          :loading="isLoading"
          @click="performExport"
        >
          Export Data
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
