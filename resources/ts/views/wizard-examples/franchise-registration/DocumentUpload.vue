<script setup lang="ts">
interface DocumentUploadData {
  fdd: File | null
  franchiseAgreement: File | null
  operationsManual: File | null
  brandGuidelines: File | null
  legalDocuments: File[] | null
}

interface Props {
  formData: DocumentUploadData
}

interface Emit {
  (e: 'update:formData', value: DocumentUploadData): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const localFormData = computed({
  get: () => props.formData,
  set: val => emit('update:formData', val),
})
</script>

<template>
  <div>
    <div class="text-h4 mb-1">
      Upload Documents
    </div>
    <p class="text-body-1 mb-6">
      Please upload all required franchise documents
    </p>

    <VForm>
      <VRow>
        <VCol cols="12">
          <VFileInput
            v-model="localFormData.fdd"
            label="Franchise Disclosure Document (FDD)"
            accept=".pdf,.doc,.docx"
            prepend-icon="tabler-file-text"
            required
            hint="Required document - PDF, DOC, or DOCX format"
            persistent-hint
          />
        </VCol>
        <VCol cols="12">
          <VFileInput
            v-model="localFormData.franchiseAgreement"
            label="Franchise Agreement"
            accept=".pdf,.doc,.docx"
            prepend-icon="tabler-file-text"
            required
            hint="Required document - PDF, DOC, or DOCX format"
            persistent-hint
          />
        </VCol>
        <VCol cols="12">
          <VFileInput
            v-model="localFormData.operationsManual"
            label="Operations Manual"
            accept=".pdf,.doc,.docx"
            prepend-icon="tabler-book"
            hint="Optional - PDF, DOC, or DOCX format"
            persistent-hint
          />
        </VCol>
        <VCol cols="12">
          <VFileInput
            v-model="localFormData.brandGuidelines"
            label="Brand Guidelines"
            accept=".pdf,.doc,.docx"
            prepend-icon="tabler-palette"
            hint="Optional - PDF, DOC, or DOCX format"
            persistent-hint
          />
        </VCol>
        <VCol cols="12">
          <VFileInput
            v-model="localFormData.legalDocuments"
            label="Additional Legal Documents"
            accept=".pdf,.doc,.docx"
            prepend-icon="tabler-files"
            multiple
            hint="Optional - Multiple files allowed"
            persistent-hint
          />
        </VCol>
      </VRow>

      <VAlert
        type="info"
        variant="tonal"
        class="mt-4"
      >
        <VAlertTitle>Document Requirements</VAlertTitle>
        <ul class="mt-2">
          <li>All documents must be in PDF, DOC, or DOCX format</li>
          <li>Maximum file size: 10MB per document</li>
          <li>FDD and Franchise Agreement are required documents</li>
          <li>Additional documents help expedite the review process</li>
        </ul>
      </VAlert>
    </VForm>
  </div>
</template>
