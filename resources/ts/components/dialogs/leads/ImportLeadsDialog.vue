<script setup lang="ts">
interface Props {
  isDialogVisible: boolean
}

interface Emits {
  'update:isDialogVisible': [value: boolean]
  'import': [file: File | null]
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const csvFile = ref<File | null>(null)

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0])
    csvFile.value = target.files[0]
}

const downloadExampleCSV = () => {
  const csvContent = 'First Name,Last Name,Email,Phone,Company,Country,State,City,Lead Source,Lead Status,Lead Owner\nJohn,Doe,john.doe@example.com,+1234567890,Example Corp,USA,California,Los Angeles,Website,Qualified,Sarah Johnson'
  const blob = new Blob([csvContent], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')

  a.href = url
  a.download = 'leads_example.csv'
  a.click()
  window.URL.revokeObjectURL(url)
}

const importCSV = () => {
  emit('import', csvFile.value)
  emit('update:isDialogVisible', false)
  csvFile.value = null
}

const handleCancel = () => {
  emit('update:isDialogVisible', false)
  csvFile.value = null
}
</script>

<template>
  <VDialog
    :model-value="isDialogVisible"
    max-width="600"
    @update:model-value="emit('update:isDialogVisible', $event)"
  >
    <DialogCloseBtn @click="handleCancel" />
    <VCard title="Import Leads from CSV">
      <VCardText>
        <div class="mb-4">
          <VBtn
            variant="tonal"
            color="secondary"
            prepend-icon="tabler-download"
            @click="downloadExampleCSV"
          >
            Download Example CSV
          </VBtn>
        </div>

        <VFileInput
          v-model="csvFile"
          label="Select CSV File"
          accept=".csv"
          prepend-icon="tabler-file-upload"
          show-size
          @change="handleFileUpload"
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
          @click="importCSV"
        >
          Import
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
