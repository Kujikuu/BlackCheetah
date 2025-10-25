<script setup lang="ts">
import { useFormValidation } from '@/composables/useFormValidation'
import { useValidationRules } from '@/composables/useValidationRules'

interface Props {
  isDialogVisible: boolean
  title?: string
  fileAccept?: string
  exampleDownloadText?: string
  exampleFileName?: string
  exampleContent?: string
}

interface Emits {
  'update:isDialogVisible': [value: boolean]
  'import': [file: File | null]
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Import Data',
  fileAccept: '.csv',
  exampleDownloadText: 'Download Example',
  exampleFileName: 'example.csv',
  exampleContent: 'Example,Data\nValue1,Value2'
})

const emit = defineEmits<Emits>()

const formRef = ref()
const { backendErrors, setBackendErrors, clearError } = useFormValidation()
const rules = useValidationRules()

const file = ref<File | null>(null)

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0])
    file.value = target.files[0]
}

const downloadExample = () => {
  const blob = new Blob([props.exampleContent], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')

  a.href = url
  a.download = props.exampleFileName
  a.click()
  window.URL.revokeObjectURL(url)
}

const handleImport = async () => {
  // Validate form
  const { valid } = await formRef.value.validate()
  if (!valid || !file.value) return

  try {
    emit('import', file.value)
    emit('update:isDialogVisible', false)
    file.value = null
  }
  catch (error: any) {
    setBackendErrors(error)
  }
}

const handleCancel = () => {
  emit('update:isDialogVisible', false)
  file.value = null
}
</script>

<template>
  <VDialog
    :model-value="isDialogVisible"
    max-width="600"
    @update:model-value="emit('update:isDialogVisible', $event)"
  >
    <DialogCloseBtn @click="handleCancel" />
    <VCard :title="title">
      <VCardText>
        <div class="mb-4">
          <VBtn
            variant="tonal"
            color="secondary"
            prepend-icon="tabler-download"
            @click="downloadExample"
          >
            {{ exampleDownloadText }}
          </VBtn>
        </div>

        <VForm ref="formRef">
          <VFileInput
            :model-value="file"
            label="Select File"
            :accept="fileAccept"
            prepend-icon="tabler-file-upload"
            show-size
            :rules="[rules.file('Please select a file')]"
            :error-messages="backendErrors.file"
            @update:model-value="file = $event; clearError('file')"
            @change="handleFileUpload"
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
          :disabled="!file"
          @click="handleImport"
        >
          Import
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
