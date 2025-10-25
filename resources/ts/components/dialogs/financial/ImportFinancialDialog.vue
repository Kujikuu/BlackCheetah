<script setup lang="ts">
import { ref, computed } from 'vue'
import { financialApi } from '@/services/api'
import { useFormValidation } from '@/composables/useFormValidation'
import { useValidationRules } from '@/composables/useValidationRules'

interface Props {
  isDialogVisible: boolean
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'dataImported'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const formRef = ref()
const { backendErrors, setBackendErrors, clearError } = useFormValidation()
const rules = useValidationRules()

const isLoading = ref(false)
const importCategory = ref<'sales' | 'expenses'>('sales')
const importFile = ref<File | null>(null)

const dialogValue = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const submitImport = async () => {
  // Validate form
  const { valid } = await formRef.value.validate()
  if (!valid || !importFile.value) return

  isLoading.value = true
  try {
    await financialApi.importData(importFile.value, importCategory.value)
    
    // Reset form
    importFile.value = null
    importCategory.value = 'sales'

    emit('dataImported')
    dialogValue.value = false
  }
  catch (error: any) {
    console.error('Error importing data:', error)
    setBackendErrors(error)
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
        <VForm ref="formRef">
          <VSelect
            v-model="importCategory"
            :items="[
              { value: 'sales', title: 'Sales' },
              { value: 'expenses', title: 'Expense' },
            ]"
            label="Category"
            variant="outlined"
            class="mb-4"
            :rules="[rules.required('Category is required')]"
            :error-messages="backendErrors.category"
            @update:model-value="clearError('category')"
          />
          <VFileInput
            v-model="importFile"
            label="Choose file"
            accept=".csv,.xlsx"
            variant="outlined"
            :rules="[rules.file('Please select a file'), rules.fileType(['csv', 'xlsx'], 'Only CSV and Excel files are allowed')]"
            :error-messages="backendErrors.file"
            @update:model-value="clearError('file')"
          />
        </VForm>
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
