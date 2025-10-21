<script setup lang="ts">
import { ref, computed } from 'vue'
import { financialApi } from '@/services/api'

interface Props {
  isDialogVisible: boolean
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'dataImported'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const isLoading = ref(false)
const importCategory = ref<'sales' | 'expenses'>('sales')
const importFile = ref<File | null>(null)

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const submitImport = async () => {
  if (!importFile.value) return

  isLoading.value = true
  try {
    await financialApi.importData(importFile.value, importCategory.value)
    
    // Reset form
    importFile.value = null
    importCategory.value = 'sales'

    emit('dataImported')
    dialogValue.value = false
  }
  catch (error) {
    console.error('Error importing data:', error)
  }
  finally {
    isLoading.value = false
  }
}

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
    <VCard title="Import Data">
      <VCardText>
        <VSelect
          v-model="importCategory"
          :items="[
            { value: 'sales', title: 'Sales' },
            { value: 'expenses', title: 'Expense' },
          ]"
          label="Category"
          variant="outlined"
          class="mb-4"
        />
        <VFileInput
          v-model="importFile"
          label="Choose file"
          accept=".csv,.xlsx"
          variant="outlined"
        />
      </VCardText>
      <VCardActions>
        <VSpacer />
        <VBtn
          variant="outlined"
          @click="handleClose"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          :loading="isLoading"
          :disabled="!importFile"
          @click="submitImport"
        >
          Import
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
