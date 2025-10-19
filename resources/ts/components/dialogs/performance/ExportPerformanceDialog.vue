<script setup lang="ts">
import { ref, computed } from 'vue'
import { $api } from '@/utils/api'

interface Props {
  isDialogVisible: boolean
  selectedPeriod: string
  selectedUnit: string
  exportDataTypeOptions: Array<{ title: string; value: string }>
  exportFormatOptions: Array<{ title: string; value: string }>
  periodOptions: Array<{ title: string; value: string }>
  franchiseeUnits: Array<{ id: string | number; name: string }>
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'exportCompleted'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const exportDataType = ref('performance')
const exportFormat = ref('csv')
const isLoading = ref(false)

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const API_BASE = '/v1/franchisor/performance-management'

const performExport = async () => {
  isLoading.value = true
  try {
    const unitId = props.selectedUnit === 'all' ? null : props.selectedUnit

    const response = await $api<any>(`${API_BASE}/export`, {
      query: {
        period_type: props.selectedPeriod,
        unit_id: unitId,
        export_type: exportDataType.value,
      },
    })

    if (response.success) {
      const timestamp = new Date().toISOString().split('T')[0]
      const unitName = props.franchiseeUnits.find(u => u.id === props.selectedUnit)?.name || 'All Units'

      if (exportDataType.value === 'performance' || exportDataType.value === 'all') {
        await exportPerformanceData(response.data.performance, timestamp, unitName)
      }

      if (exportDataType.value === 'stats' || exportDataType.value === 'all') {
        await exportStatsData(response.data.stats, timestamp)
      }

      emit('exportCompleted')
      dialogValue.value = false
    }
  }
  catch (err) {
    console.error('Error exporting data:', err)
    // Optionally, show a toast notification
  }
  finally {
    isLoading.value = false
  }
}

const exportPerformanceData = async (performanceData: any[], timestamp: string, unitName: string) => {
  if (!performanceData || performanceData.length === 0) return

  const headers = ['Period Date', 'Branch Name', 'Revenue', 'Expenses', 'Royalties', 'Profit', 'Profit Margin', 'Customer Rating', 'Reviews', 'Growth Rate']
  const filename = `performance-data-${unitName.replace(/\s+/g, '-').toLowerCase()}-${props.selectedPeriod}-${timestamp}.${exportFormat.value}`

  if (exportFormat.value === 'csv') {
    const csvContent = convertToCSV(performanceData, headers)
    downloadFile(csvContent, filename, 'text/csv')
  }
}

const exportStatsData = async (statsData: any, timestamp: string) => {
  // Stats export logic would go here
  const filename = `stats-summary-${timestamp}.${exportFormat.value}`
  
  if (exportFormat.value === 'csv') {
    // Convert stats data to CSV format
    // This would need the actual stats data structure
    console.log('Exporting stats data:', statsData)
  }
}

const convertToCSV = (data: any[], headers: string[]) => {
  const csvRows = [headers.join(',')]
  
  for (const row of data) {
    const values = headers.map(header => {
      const value = row[header.toLowerCase().replace(/\s+/g, '_')] || ''
      return `"${value}"`
    })
    csvRows.push(values.join(','))
  }
  
  return csvRows.join('\n')
}

const downloadFile = (content: string, filename: string, mimeType: string) => {
  const blob = new Blob([content], { type: mimeType })
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  
  link.href = url
  link.download = filename
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  window.URL.revokeObjectURL(url)
}
</script>

<template>
  <VDialog
    v-model="dialogValue"
    max-width="600"
  >
    <DialogCloseBtn @click="dialogValue = false" />
    <VCard title="Export Data" class="text-center px-6 py-8">
      <VDivider class="mb-6" />

      <VCardText class="text-start">
        <VRow>
          <VCol cols="12">
            <VSelect
              v-model="exportDataType"
              :items="props.exportDataTypeOptions"
              item-title="title"
              item-value="value"
              label="Data Type"
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="tabler-database"
            />
          </VCol>
          <VCol cols="12">
            <VSelect
              v-model="exportFormat"
              :items="props.exportFormatOptions"
              item-title="title"
              item-value="value"
              label="Export Format"
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="tabler-file-type-csv"
            />
          </VCol>
        </VRow>

        <!-- Export Info -->
        <VAlert
          type="info"
          variant="tonal"
          class="mt-4"
          density="compact"
        >
          <template #prepend>
            <VIcon icon="tabler-info-circle" />
          </template>
          <div class="text-body-2">
            <strong>Current Selection:</strong><br>
            Period: {{ props.periodOptions.find(p => p.value === props.selectedPeriod)?.title }}<br>
            Unit: {{ props.franchiseeUnits.find(u => u.id === props.selectedUnit)?.name }}
          </div>
        </VAlert>
      </VCardText>

      <VCardActions class="d-flex align-center justify-center gap-3 pt-4">
        <VBtn
          variant="outlined"
          color="secondary"
          @click="dialogValue = false"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          prepend-icon="tabler-download"
          :disabled="isLoading"
          :loading="isLoading"
          @click="performExport"
        >
          Export Data
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
